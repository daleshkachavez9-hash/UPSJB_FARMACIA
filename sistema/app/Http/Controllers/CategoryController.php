<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('categories.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('categories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $datos = $request->validate([
            'name'        => 'required|max:50|unique:categories',
            'description' => 'nullable|max:255',
        ], $this->mensajes());

        Category::create($datos);

        return to_route('categories.index')
            ->with('ok', 'Categoría registrada correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        //
    }

    private function mensajes(): array
    {
        return [
            'name.required'   => 'Escribe el nombre de la categoría.',
            'name.max'        => 'El nombre admite máximo 50 caracteres.',
            'name.unique'     => 'Esa categoría ya está registrada.',
            'description.max' => 'La descripción admite máximo 255 caracteres.',
        ];
    }
}
