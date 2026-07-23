<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Item;
use App\Models\Collection;

// Public routes (no auth required)
Route::get('/items', function () {
    return Item::with('uploader', 'collections')->paginate(20);
});

Route::get('/items/{id}', function ($id) {
    return Item::with('uploader', 'collections')->findOrFail($id);
});

Route::get('/collections', function () {
    return Collection::with('owner')->paginate(20);
});

Route::get('/collections/{id}', function ($id) {
    return Collection::with('owner', 'items')->findOrFail($id);
});

// Protected routes (require Supabase JWT)
Route::middleware('supabase.auth')->group(function () {

    // Get authenticated user
    Route::get('/me', function (Request $request) {
        return $request->user();
    });

    // Create item
    Route::post('/items', function (Request $request) {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'file_url' => 'required|url',
            'file_type' => 'required|string|max:100',
        ]);

        $item = Item::create([
            ...$validated,
            'uploader_id' => $request->user()->id,
        ]);

        return response()->json($item, 201);
    });

    // Update item (only owner)
    Route::put('/items/{id}', function (Request $request, $id) {
        $item = Item::findOrFail($id);

        if ($item->uploader_id !== $request->user()->id) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'file_url' => 'sometimes|url',
            'file_type' => 'sometimes|string|max:100',
        ]);

        $item->update($validated);
        return $item;
    });

    // Delete item (only owner)
    Route::delete('/items/{id}', function (Request $request, $id) {
        $item = Item::findOrFail($id);

        if ($item->uploader_id !== $request->user()->id) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $item->delete();
        return response()->json(['message' => 'Item deleted'], 200);
    });

    // Create collection
    Route::post('/collections', function (Request $request) {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $collection = Collection::create([
            ...$validated,
            'owner_id' => $request->user()->id,
        ]);

        return response()->json($collection, 201);
    });

    // Update collection (only owner)
    Route::put('/collections/{id}', function (Request $request, $id) {
        $collection = Collection::findOrFail($id);

        if ($collection->owner_id !== $request->user()->id) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
        ]);

        $collection->update($validated);
        return $collection;
    });

    // Delete collection (only owner)
    Route::delete('/collections/{id}', function (Request $request, $id) {
        $collection = Collection::findOrFail($id);

        if ($collection->owner_id !== $request->user()->id) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $collection->delete();
        return response()->json(['message' => 'Collection deleted'], 200);
    });

    // Add item to collection
    Route::post('/collections/{id}/items', function (Request $request, $id) {
        $collection = Collection::findOrFail($id);

        if ($collection->owner_id !== $request->user()->id) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
        ]);

        $collection->items()->syncWithoutDetaching([$validated['item_id']]);

        return response()->json(['message' => 'Item added to collection'], 200);
    });

    // Remove item from collection
    Route::delete('/collections/{id}/items/{itemId}', function (Request $request, $id, $itemId) {
        $collection = Collection::findOrFail($id);

        if ($collection->owner_id !== $request->user()->id) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $collection->items()->detach($itemId);

        return response()->json(['message' => 'Item removed from collection'], 200);
    });
});
