@extends('admin.layouts.app')

@section('title', 'Campos Técnicos')

@section('content')

<div class="admin-page-header">
    <h1 class="admin-page-title">Campos Técnicos por Subcategoría</h1>
    <a href="{{ route('admin.technical-fields.create') }}" class="btn btn--primary">
        + Nuevo campo
    </a>
</div>

<div class="admin-card">
    @if($fields->isEmpty())
        <div style="padding: 40px 24px; text-align: center; color: #888; font-size: 14px;">
            No hay campos técnicos definidos aún.
            <a href="{{ route('admin.technical-fields.create') }}">Crear el primero</a>.
        </div>
    @else
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Subcategoría</th>
                    <th>Label</th>
                    <th>Name (clave)</th>
                    <th>Tipo</th>
                    <th>Orden</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($fields as $subcategoryId => $groupFields)

                    {{-- Fila de grupo --}}
                    <tr class="group-header">
                        <td colspan="6">
                            {{ $subcategories->get($subcategoryId)?->name ?? 'Subcategoría #'.$subcategoryId }}
                        </td>
                    </tr>

                    @foreach($groupFields as $field)
                    <tr>
                        <td></td>
                        <td>{{ $field->label }}</td>
                        <td><code>{{ $field->name }}</code></td>
                        <td>
                            @if($field->type === 'text')
                                <span class="badge badge--text">Texto</span>
                            @elseif($field->type === 'number')
                                <span class="badge badge--number">Número</span>
                            @else
                                <span class="badge badge--select">Select</span>
                            @endif
                        </td>
                        <td>{{ $field->order }}</td>
                        <td style="display:flex; gap:8px; justify-content:flex-end;">
                            <a href="{{ route('admin.technical-fields.edit', $field) }}" class="btn btn--ghost">
                                Editar
                            </a>
                            <form method="POST"
                                  action="{{ route('admin.technical-fields.destroy', $field) }}"
                                  onsubmit="return confirm('¿Eliminar este campo?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn--danger">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach

                @endforeach
            </tbody>
        </table>
    @endif
</div>

@endsection
