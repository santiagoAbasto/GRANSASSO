@extends('admin.layouts.app')

@section('title', 'Nuevo Campo Técnico')

@section('content')

<div class="admin-page-header">
    <h1 class="admin-page-title">Nuevo Campo Técnico</h1>
    <a href="{{ route('admin.technical-fields.index') }}" class="btn btn--ghost">← Volver</a>
</div>

<div class="admin-card">
    <form method="POST" action="{{ route('admin.technical-fields.store') }}" class="admin-form">
        @csrf

        <div class="form-field">
            <label for="subcategory_id">Subcategoría*</label>
            <select name="subcategory_id" id="subcategory_id" required>
                <option value="">Seleccione una subcategoría</option>
                @foreach($subcategories as $sub)
                <option value="{{ $sub->id }}" {{ old('subcategory_id') == $sub->id ? 'selected' : '' }}>
                    {{ $sub->name }}
                </option>
                @endforeach
            </select>
        </div>

        <div class="form-field">
            <label for="label">Label (visible al usuario)*</label>
            <input type="text" name="label" id="label"
                   value="{{ old('label') }}"
                   placeholder="Ej: Fluido, Rango de caudal, Temperatura"
                   required>
        </div>

        <div class="form-field">
            <label for="name">Name (clave interna)*</label>
            <input type="text" name="name" id="name"
                   value="{{ old('name') }}"
                   placeholder="Ej: fluido, rango_caudal, temperatura"
                   required>
            <small>Solo minúsculas, números y guiones bajos. Sin espacios ni caracteres especiales.</small>
        </div>

        <div class="form-field">
            <label for="type">Tipo de campo*</label>
            <select name="type" id="type" required>
                <option value="text"   {{ old('type') == 'text'   ? 'selected' : '' }}>Texto</option>
                <option value="number" {{ old('type') == 'number' ? 'selected' : '' }}>Número</option>
                <option value="select" {{ old('type') == 'select' ? 'selected' : '' }}>Select (desplegable)</option>
            </select>
        </div>

        <div class="form-field">
            <label for="order">Orden</label>
            <input type="number" name="order" id="order"
                   value="{{ old('order', 0) }}" min="0">
            <small>Número menor = aparece primero en el formulario.</small>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn--primary">Guardar campo</button>
            <a href="{{ route('admin.technical-fields.index') }}" class="btn btn--ghost">Cancelar</a>
        </div>

    </form>
</div>

@endsection
