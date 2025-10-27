<?php

namespace App\Http\Controllers;

use App\Http\Resources\PremiumProductResource;
use App\Models\PremiumProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PremiumProductController extends Controller
{
    /**
     * Display a listing of products (Public & Admin)
     * GET /api/premium-products (public)
     * Supports filtering, sorting, pagination
     */
    public function index(Request $request)
    {
        $query = PremiumProduct::query();

        // Filter by is_active
        if ($request->has('filter.is_active')) {
            $query->where('is_active', $request->input('filter.is_active'));
        }

        // Filter by is_featured
        if ($request->has('filter.is_featured')) {
            $query->where('is_featured', $request->input('filter.is_featured'));
        }

        // Filter by name (search)
        if ($request->has('filter.name')) {
            $query->where('name', 'like', '%' . $request->input('filter.name') . '%');
        }

        // Sorting
        $sortField = $request->input('sort', '-created_at'); // Default: newest first
        $sortDirection = 'asc';
        
        if (Str::startsWith($sortField, '-')) {
            $sortDirection = 'desc';
            $sortField = Str::substr($sortField, 1);
        }

        $query->orderBy($sortField, $sortDirection);

        // Pagination
        $perPage = $request->input('per_page', 15);
        $products = $query->paginate($perPage);

        return PremiumProductResource::collection($products);
    }

    /**
     * Display a single product
     * 🎯 SUPPORT BOTH: ID (for admin) and SLUG (for public)
     * GET /api/premium-products/{slug} (public)
     * GET /api/admin/premium-products/{id} (admin)
     */
    public function show($identifier)
    {
        // Check if identifier is numeric (ID) or string (slug)
        if (is_numeric($identifier)) {
            // Admin route: Get by ID
            $product = PremiumProduct::findOrFail($identifier);
        } else {
            // Public route: Get by slug
            $product = PremiumProduct::where('slug', $identifier)->firstOrFail();
        }

        // Increment view count (only for public access via slug)
        if (!is_numeric($identifier)) {
            $product->increment('view_count');
        }

        return new PremiumProductResource($product);
    }

    /**
     * Store a new product
     * POST /api/admin/premium-products
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'discount_price' => 'required|numeric|min:0',
            'thumbnail_url' => 'required|string',
            'file_url' => 'required|string',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ]);

        // Auto-generate slug from name
        $validated['slug'] = Str::slug($validated['name']);

        // Ensure slug is unique
        $originalSlug = $validated['slug'];
        $count = 1;
        while (PremiumProduct::where('slug', $validated['slug'])->exists()) {
            $validated['slug'] = $originalSlug . '-' . $count;
            $count++;
        }

        // Set created_by
        $validated['created_by'] = auth()->id();

        $product = PremiumProduct::create($validated);

        return response()->json([
            'meta' => [
                'message' => 'Product created successfully',
                'code' => 201,
            ],
            'data' => new PremiumProductResource($product),
        ], 201);
    }

    /**
     * Update a product
     * PUT /api/admin/premium-products/{id}
     */
    public function update(Request $request, $id)
    {
        $product = PremiumProduct::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'price' => 'sometimes|required|numeric|min:0',
            'discount_price' => 'sometimes|required|numeric|min:0',
            'thumbnail_url' => 'sometimes|required|string',
            'file_url' => 'sometimes|required|string',
            'is_active' => 'sometimes|boolean',
            'is_featured' => 'sometimes|boolean',
        ]);

        // If name is updated, regenerate slug
        if (isset($validated['name']) && $validated['name'] !== $product->name) {
            $validated['slug'] = Str::slug($validated['name']);

            // Ensure slug is unique (exclude current product)
            $originalSlug = $validated['slug'];
            $count = 1;
            while (PremiumProduct::where('slug', $validated['slug'])
                ->where('id', '!=', $id)
                ->exists()) {
                $validated['slug'] = $originalSlug . '-' . $count;
                $count++;
            }
        }

        // Set updated_by
        $validated['updated_by'] = auth()->id();

        $product->update($validated);

        return response()->json([
            'meta' => [
                'message' => 'Product updated successfully',
                'code' => 200,
            ],
            'data' => new PremiumProductResource($product),
        ], 200);
    }

    /**
     * Delete a product
     * DELETE /api/admin/premium-products/{id}
     */
    public function destroy($id)
    {
        $product = PremiumProduct::findOrFail($id);

        // Soft delete
        $product->deleted_by = auth()->id();
        $product->save();
        $product->delete();

        return response()->json([
            'meta' => [
                'message' => 'Product deleted successfully',
                'code' => 200,
            ],
            'data' => [],
        ], 200);
    }
}