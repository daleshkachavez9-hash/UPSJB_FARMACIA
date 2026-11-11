@extends('template.administrador')

@section('title', 'CATEGORIAS')
@section('name-section', 'CATEGORIAS')

@section('descripcion', 'Nuevo registro de categoría')
@section('item-breadcrumb', 'Nuevo')
@section('section-breadcrumb', 'Categorías')
@section('module-breadcrumb', 'Configuraciones')


@section('content')
    <div class="card bg-white border-0 shadow-sm border-5 border-top border-secondary">
        <form action="{{ route('categories.store') }}" method="POST" class="card-body">
            @csrf

            @include('categories._form')

            <button type="submit" class="btn btn-primary">Guardar</button>
            <a href="{{ route('categories.index') }}" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
@endsection
