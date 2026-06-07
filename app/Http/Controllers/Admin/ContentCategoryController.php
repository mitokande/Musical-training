<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContentCategory;
use Illuminate\Http\Request;

class ContentCategoryController extends Controller
{
    public function index()
    {
        $categories = ContentCategory::with('children')
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->get();

        return view('admin.content-categories.index', compact('categories'));
    }

    public function create()
    {
        $parents = ContentCategory::whereNull('parent_id')->get();

        return view('admin.content-categories.create', compact('parents'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'slug'        => 'required|string|max:255|unique:content_categories',
            'parent_id'   => 'nullable|exists:content_categories,id',
            'description' => 'nullable|string',
            'sort_order'  => 'integer|min:0',
            'is_active'   => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        ContentCategory::create($validated);

        return redirect()->route('admin.content-categories.index')
            ->with('success', 'Category created successfully.');
    }

    public function edit(ContentCategory $contentCategory)
    {
        $parents = ContentCategory::whereNull('parent_id')
            ->where('id', '!=', $contentCategory->id)
            ->get();

        return view('admin.content-categories.edit', compact('contentCategory', 'parents'));
    }

    public function update(Request $request, ContentCategory $contentCategory)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'slug'        => 'required|string|max:255|unique:content_categories,slug,' . $contentCategory->id,
            'parent_id'   => 'nullable|exists:content_categories,id',
            'description' => 'nullable|string',
            'sort_order'  => 'integer|min:0',
            'is_active'   => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $contentCategory->update($validated);

        return redirect()->route('admin.content-categories.index')
            ->with('success', 'Category updated successfully.');
    }

    public function destroy(ContentCategory $contentCategory)
    {
        $contentCategory->delete();

        return redirect()->route('admin.content-categories.index')
            ->with('success', 'Category deleted successfully.');
    }
}
