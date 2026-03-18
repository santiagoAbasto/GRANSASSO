@extends('web.layouts.app')

@section('title','Presupuesto')

@section('content')

<section class="quote-section">
<div class="container">

{{-- BREADCRUMB --}}
<div class="breadcrumb">
    <a href="{{ route('home') }}">Inicio</a>
    <span>›</span>
    <span>Solicitud de Cotización</span>
</div>

{{-- Mensaje de éxito --}}
@if(session('success'))
<div class="alert-success">
    {{ session('success') }}
</div>
@endif

{{-- Errores de validación --}}
@if($errors->any())
<div class="alert-error">
    <ul>
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif


<form action="{{ route('quote.store') }}" method="POST" enctype="multipart/form-data">
@csrf

<div class="quote-grid">

    {{-- Instrumental --}}
    <div class="form-group">
        <label>Instrumental*</label>
        <div class="select-wrapper">
            <select id="instrumental-select" required>
                <option value="">Seleccione</option>
                @foreach($instrumentals as $instrumental)
                <option
                    value="{{ $instrumental->id }}"
                    data-name="{{ $instrumental->name }}"
                    {{ old('instrumental') == $instrumental->id ? 'selected' : '' }}>
                    {{ $instrumental->name }}
                </option>
                @endforeach
            </select>
            <span class="select-arrow">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <path d="M6 9L12 15L18 9" stroke="#D9D9D9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </span>
        </div>
        <input type="hidden" name="instrumental" id="instrumental-name-input" value="{{ old('instrumental') }}">
    </div>

    {{-- Solución --}}
    <div class="form-group">
        <label>Solución*</label>
        <div class="select-wrapper">
            <select name="solution" id="solution-select" required disabled>
                <option value="">Primero seleccione Instrumental</option>
            </select>
            <span class="select-arrow">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <path d="M6 9L12 15L18 9" stroke="#D9D9D9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </span>
        </div>
    </div>

    {{-- DATOS TÉCNICOS DINÁMICOS --}}
    <div id="technical-fields" class="quote-technical-fields"></div>

    {{-- Cantidad --}}
    <div class="form-group">
        <label>Cantidad</label>
        <input type="number" name="quantity" min="1" value="{{ old('quantity') }}">
    </div>

    {{-- Archivos --}}
    <div class="form-group">
        <label>Adjuntar archivo</label>
        <label class="file-upload-label" for="file-upload-input">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                <path d="M14.5 2H6C5.46957 2 4.96086 2.21071 4.58579 2.58579C4.21071 2.96086 4 3.46957 4 4V20C4 20.5304 4.21071 21.0391 4.58579 21.4142C4.96086 21.7893 5.46957 22 6 22H18C18.5304 22 19.0391 21.7893 19.4142 21.4142C19.7893 21.0391 20 20.5304 20 20V7.5L14.5 2Z" stroke="#25A7CA" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M14 2V8H20" stroke="#25A7CA" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M12 12V18" stroke="#25A7CA" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M15 15L12 12L9 15" stroke="#25A7CA" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span class="file-upload-text" id="file-upload-text">Seleccionar archivo</span>
            <input id="file-upload-input" type="file" name="attachments[]" multiple
                accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.dwg,.dxf">
        </label>
        <small>Planos, fichas técnicas o documentos (PDF, DWG, XLS, JPG). Máx. 10 MB c/u.</small>
    </div>

    {{-- DIVISOR --}}
    <hr class="quote-divider">

    {{-- Nombre --}}
    <div class="form-group">
        <label>Nombre y apellido*</label>
        <input type="text" name="name" value="{{ old('name') }}" required>
    </div>

    {{-- Email --}}
    <div class="form-group">
        <label>Email*</label>
        <input type="email" name="email" value="{{ old('email') }}" required>
    </div>

    {{-- Teléfono --}}
    <div class="form-group">
        <label>Teléfono*</label>
        <input type="text" name="phone" value="{{ old('phone') }}" required>
    </div>

    {{-- Empresa + Submit (columna derecha, span 2 filas) --}}
    <div class="quote-right-block">

        <div class="form-group">
            <label>Empresa</label>
            <input type="text" name="company" value="{{ old('company') }}">
        </div>

        <div class="quote-submit">
            <span class="quote-required">*Campos Obligatorios</span>
            <button type="submit">Enviar solicitud</button>
        </div>

    </div>

    {{-- Mensaje --}}
    <div class="form-group">
        <label>Mensaje*</label>
        <textarea name="message" rows="5" required>{{ old('message') }}</textarea>
    </div>

</div>{{-- /quote-grid --}}

</form>

</div>
</section>

@endsection


@push('scripts')
<script>
(function () {

    const allSolutions = @json($solutionsJson);

    const instrumentalSelect   = document.getElementById('instrumental-select');
    const instrumentalNameInput = document.getElementById('instrumental-name-input');
    const solutionSelect       = document.getElementById('solution-select');
    const container            = document.getElementById('technical-fields');

    const oldSolution = @json(old('solution', ''));
    const oldValues   = @json(old('process_data', []));
    const preselect   = @json($preselect);

    // ─────────────────────────────────────────────
    // 1. Filtrar soluciones según instrumental
    // ─────────────────────────────────────────────
    function filterSolutions() {

        const subcategoryId = parseInt(instrumentalSelect.value);
        const selectedOption = instrumentalSelect.options[instrumentalSelect.selectedIndex];
        instrumentalNameInput.value = selectedOption ? (selectedOption.dataset.name || '') : '';

        solutionSelect.innerHTML = '';
        clearTechnicalFields();

        if (!subcategoryId) {
            solutionSelect.innerHTML = '<option value="">Primero seleccione Instrumental</option>';
            solutionSelect.disabled = true;
            return;
        }

        const filtered = allSolutions.filter(p => p.subcategory_id === subcategoryId);

        if (!filtered.length) {
            solutionSelect.innerHTML = '<option value="">Sin productos disponibles</option>';
            solutionSelect.disabled = true;
            return;
        }

        solutionSelect.disabled = false;
        solutionSelect.innerHTML = '<option value="">Seleccione</option>';

        filtered.forEach(function (p) {
            const opt = document.createElement('option');
            opt.value = p.name;
            opt.textContent = p.name;
            if (oldSolution && oldSolution === p.name) opt.selected = true;
            solutionSelect.appendChild(opt);
        });

        if (solutionSelect.value) renderTechnicalFields();
    }

    // ─────────────────────────────────────────────
    // 2. Generar campos técnicos según solución
    // ─────────────────────────────────────────────
    function clearTechnicalFields() {
        container.innerHTML = '';
        container.classList.remove('is-visible');
    }

    function renderTechnicalFields() {

        clearTechnicalFields();

        const selectedName = solutionSelect.value;
        if (!selectedName) return;

        const product = allSolutions.find(p => p.name === selectedName);
        if (!product || !product.technical_fields.length) return;

        const title = document.createElement('p');
        title.className = 'quote-technical-title';
        title.textContent = 'Datos técnicos del proceso';
        container.appendChild(title);

        product.technical_fields.forEach(function (field) {

            const group = document.createElement('div');
            group.className = 'form-group';

            const label = document.createElement('label');
            label.textContent = field.label;
            group.appendChild(label);

            let input;
            if (field.type === 'select') {
                input = document.createElement('select');
                input.innerHTML = '<option value="">Seleccione</option>';
            } else {
                input = document.createElement('input');
                input.type = field.type === 'number' ? 'number' : 'text';
            }

            input.name  = 'process_data[' + field.name + ']';
            input.value = (oldValues && oldValues[field.name]) ? oldValues[field.name] : '';

            group.appendChild(input);
            container.appendChild(group);
        });

        container.classList.add('is-visible');
    }

    // ─────────────────────────────────────────────
    // 3. Eventos
    // ─────────────────────────────────────────────
    instrumentalSelect.addEventListener('change', filterSolutions);
    solutionSelect.addEventListener('change', renderTechnicalFields);

    if (instrumentalSelect.value) filterSolutions();

    // ─────────────────────────────────────────────
    // 5. Preselección desde botón "Consultar"
    // ─────────────────────────────────────────────
    if (preselect && preselect.subcategory_id) {
        instrumentalSelect.value = preselect.subcategory_id;
        filterSolutions();
        if (preselect.product_name) {
            solutionSelect.value = preselect.product_name;
            if (solutionSelect.value) renderTechnicalFields();
        }
    }

    // ─────────────────────────────────────────────
    // 4. Actualizar texto al seleccionar archivos
    // ─────────────────────────────────────────────
    const fileInput = document.getElementById('file-upload-input');
    const fileText  = document.getElementById('file-upload-text');

    fileInput.addEventListener('change', function () {
        if (this.files.length === 0) {
            fileText.textContent = 'Seleccionar archivo';
            fileText.style.color = '#AAAAAA';
        } else if (this.files.length === 1) {
            fileText.textContent = this.files[0].name;
            fileText.style.color = '#101010';
        } else {
            fileText.textContent = this.files.length + ' archivos seleccionados';
            fileText.style.color = '#101010';
        }
    });

})();
</script>
@endpush
