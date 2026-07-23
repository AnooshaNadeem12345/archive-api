<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Http\Resources\ItemResource;
use App\Http\Resources\ItemCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ItemController extends Controller
{
    /**
     * Display a paginated, filterable, and searchable listing of items.
     */
    public function index(Request $request)
    {
        $query = Item::with('uploader', 'collections');

        // Full-text search using PostgreSQL tsvector
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;

            // Use websearch_to_tsquery for user-friendly search (supports quotes, OR, AND, -)
            $query->whereRaw(
                "search_vector @@ websearch_to_tsquery('english', ?)",
                [$searchTerm]
            );

            // Add relevance ranking
            $query->selectRaw(
                "items.*, ts_rank(search_vector, websearch_to_tsquery('english', ?)) as relevance",
                [$searchTerm]
            );

            // Order by relevance (most relevant first)
            $query->orderBy('relevance', 'desc');
        } else {
            // No search term: order by newest first
            $query->orderBy('created_at', 'desc');
        }

        // Filter by category
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        // Filter by tags (JSON contains)
        if ($request->has('tag')) {
            $query->whereJsonContains('tags', $request->tag);
        }

        // Paginate results
        $perPage = $request->get('per_page', 20);
        $items = $query->paginate($perPage);

        return new ItemCollection($items);
    }

    /**
     * Store a newly created item.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'file_url' => 'required|url|max:1000',
            'file_type' => 'required|string|max:100',
            'category' => 'nullable|string|max:100',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
        ]);

        $item = Item::create([
            ...$validated,
            'uploader_id' => $request->user()->id,
        ]);

        return new ItemResource($item->load('uploader', 'collections'));
    }

    /**
     * Display the specified item.
     */
    public function show($id)
    {
        $item = Item::with('uploader', 'collections')->findOrFail($id);

        return new ItemResource($item);
    }

    /**
     * Update the specified item (owner only).
     */
    public function update(Request $request, $id)
    {
        $item = Item::findOrFail($id);

        // Authorization check
        if ($item->uploader_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'file_url' => 'sometimes|required|url|max:1000',
            'file_type' => 'sometimes|required|string|max:100',
            'category' => 'nullable|string|max:100',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
        ]);

        $item->update($validated);

        return new ItemResource($item->load('uploader', 'collections'));
    }

    /**
     * Remove the specified item (owner only).
     */
    public function destroy(Request $request, $id)
    {
        $item = Item::findOrFail($id);

        // Authorization check
        if ($item->uploader_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $item->delete();

        return response()->json(['message' => 'Item deleted successfully'], 200);
    }
}
