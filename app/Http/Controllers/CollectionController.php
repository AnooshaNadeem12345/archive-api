<?php

namespace App\Http\Controllers;

use App\Models\Collection;
use App\Http\Resources\CollectionResource;
use App\Http\Resources\CollectionCollection;
use Illuminate\Http\Request;

class CollectionController extends Controller
{
    /**
     * Display a paginated listing of collections.
     */
    public function index(Request $request)
    {
        $query = Collection::with('owner');

        // Search by name
        if ($request->has('search')) {
            $query->where('name', 'ilike', '%' . $request->search . '%');
        }

        // Sort by newest first by default
        $query->orderBy('created_at', 'desc');

        // Paginate results
        $perPage = $request->get('per_page', 20);
        $collections = $query->paginate($perPage);

        return new CollectionCollection($collections);
    }

    /**
     * Store a newly created collection.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
        ]);

        $collection = Collection::create([
            ...$validated,
            'owner_id' => $request->user()->id,
        ]);

        return new CollectionResource($collection->load('owner'));
    }

    /**
     * Display the specified collection with its items.
     */
    public function show($id)
    {
        $collection = Collection::with('owner', 'items.uploader')->findOrFail($id);

        return new CollectionResource($collection);
    }

    /**
     * Update the specified collection (owner only).
     */
    public function update(Request $request, $id)
    {
        $collection = Collection::findOrFail($id);

        // Authorization check
        if ($collection->owner_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string|max:5000',
        ]);

        $collection->update($validated);

        return new CollectionResource($collection->load('owner'));
    }

    /**
     * Remove the specified collection (owner only).
     */
    public function destroy(Request $request, $id)
    {
        $collection = Collection::findOrFail($id);

        // Authorization check
        if ($collection->owner_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $collection->delete();

        return response()->json(['message' => 'Collection deleted successfully'], 200);
    }

    /**
     * Add an item to a collection (owner only).
     */
    public function addItem(Request $request, $id)
    {
        $collection = Collection::findOrFail($id);

        // Authorization check
        if ($collection->owner_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
        ]);

        $collection->items()->syncWithoutDetaching([$validated['item_id']]);

        return new CollectionResource($collection->load('owner', 'items.uploader'));
    }

    /**
     * Remove an item from a collection (owner only).
     */
    public function removeItem(Request $request, $id, $itemId)
    {
        $collection = Collection::findOrFail($id);

        // Authorization check
        if ($collection->owner_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $collection->items()->detach($itemId);

        return response()->json(['message' => 'Item removed from collection successfully'], 200);
    }
}
