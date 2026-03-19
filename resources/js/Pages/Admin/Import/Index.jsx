import React from 'react';
import { useForm, usePage } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';

export default function ImportIndex() {
    const { flash } = usePage().props;
    const result = flash?.import_result;

    const { data, setData, post, processing, errors, reset } = useForm({
        file:   null,
        format: 'auto',
    });

    function submit(e) {
        e.preventDefault();
        post(route('admin.import.products'), {
            forceFormData: true,
            onSuccess: () => reset('file'),
        });
    }

    const formatLabel = { auto: 'Auto-detectado', flat: 'Plano', catalog: 'Catálogo' };

    return (
        <AdminLayout title="Importar Productos">
            <div style={{ maxWidth: 680, margin: '0 auto' }}>

                <h1 style={{ fontSize: 22, fontWeight: 700, marginBottom: 24 }}>
                    Importación masiva de productos
                </h1>

                {/* ── Resultado ─────────────────────────────────────────── */}
                {result && (
                    <div style={{
                        marginBottom: 24,
                        padding: 16,
                        background: result.errors?.length ? '#fff3cd' : '#d4edda',
                        borderRadius: 8,
                        fontSize: 14,
                    }}>
                        <p style={{ fontWeight: 600, marginBottom: 8 }}>
                            Importación completada
                            {result.format && (
                                <span style={{ fontWeight: 400, color: '#555', marginLeft: 8 }}>
                                    (formato: {formatLabel[result.format] ?? result.format})
                                </span>
                            )}
                        </p>
                        <p>🟢 Productos creados: <strong>{result.products_created}</strong></p>
                        <p>🔄 Productos actualizados: <strong>{result.products_updated}</strong></p>
                        <p>🟢 Items creados: <strong>{result.items_created}</strong></p>
                        <p>🔄 Items actualizados: <strong>{result.items_updated}</strong></p>

                        {result.errors?.length > 0 && (
                            <>
                                <p style={{ color: '#856404', marginTop: 10, fontWeight: 600 }}>
                                    ⚠️ Errores ({result.errors.length}):
                                </p>
                                <ul style={{ fontSize: 13, paddingLeft: 20, marginTop: 4 }}>
                                    {result.errors.map((e, i) => <li key={i}>{e}</li>)}
                                </ul>
                            </>
                        )}
                    </div>
                )}

                {/* ── Información de formatos ────────────────────────────── */}
                <div style={{ marginBottom: 24, padding: 16, background: '#f8f9fa', borderRadius: 8, fontSize: 13 }}>
                    <p style={{ fontWeight: 600, marginBottom: 10 }}>Formatos soportados</p>

                    <p style={{ fontWeight: 600, marginBottom: 4 }}>Formato Plano (columnas en fila 1):</p>
                    <code style={{ display: 'block', whiteSpace: 'pre-wrap', lineHeight: 1.8, marginBottom: 12, background: '#e9ecef', padding: '6px 10px', borderRadius: 4 }}>
{`marca | modelo_caja | modelos_vehiculo | fig | cod_gs | codigo_oem
nombre | descripcion | referencia_original | referencias_adicionales | notas
[G60] | [G85] | [variante_n] ...  ← poner "X" si aplica`}
                    </code>

                    <p style={{ fontWeight: 600, marginBottom: 4 }}>Formato Catálogo (estructura fija de 3 filas de encabezado):</p>
                    <code style={{ display: 'block', whiteSpace: 'pre-wrap', lineHeight: 1.8, background: '#e9ecef', padding: '6px 10px', borderRadius: 4 }}>
{`Fila 1: Tipo | Marca      |       |       | Caja de cambios
Fila 2: Modelos: |    | L1218/1518/... (vehículos compatibles)
Fila 3: Fig. | Cod. GS | Código OEM | Descripción | G60 | G85 | ...
Fila 4+: 7   | 800     | 694-262-0002 | Nombre producto | X  |    | ...`}
                    </code>
                    <p style={{ marginTop: 8, color: '#6c757d' }}>
                        La detección es automática. Si el formato no se reconoce correctamente, selecciónalo manualmente.
                    </p>
                </div>

                {/* ── Formulario ────────────────────────────────────────── */}
                <form onSubmit={submit} encType="multipart/form-data">

                    <div style={{ marginBottom: 16 }}>
                        <label style={{ display: 'block', fontWeight: 600, marginBottom: 6 }}>
                            Archivo Excel (.xlsx, .xls, .csv)
                        </label>
                        <input
                            type="file"
                            accept=".xlsx,.xls,.csv"
                            onChange={e => setData('file', e.target.files[0])}
                            style={{ display: 'block', width: '100%', padding: '8px 0' }}
                        />
                        {errors.file && <p style={{ color: 'red', fontSize: 13, marginTop: 4 }}>{errors.file}</p>}
                    </div>

                    <div style={{ marginBottom: 24 }}>
                        <label style={{ display: 'block', fontWeight: 600, marginBottom: 6 }}>
                            Formato del archivo
                        </label>
                        <select
                            value={data.format}
                            onChange={e => setData('format', e.target.value)}
                            style={{
                                padding: '8px 12px',
                                border: '1px solid #ced4da',
                                borderRadius: 6,
                                fontSize: 14,
                                minWidth: 220,
                            }}
                        >
                            <option value="auto">Auto-detectar (recomendado)</option>
                            <option value="catalog">Catálogo (3 filas de encabezado)</option>
                            <option value="flat">Plano (columnas en fila 1)</option>
                        </select>
                    </div>

                    <button
                        type="submit"
                        disabled={processing || !data.file}
                        style={{
                            padding: '10px 28px',
                            background: '#25A7CA',
                            color: '#fff',
                            border: 'none',
                            borderRadius: 6,
                            fontWeight: 600,
                            fontSize: 15,
                            cursor: processing ? 'wait' : 'pointer',
                            opacity: !data.file ? 0.5 : 1,
                        }}
                    >
                        {processing ? 'Importando…' : 'Importar productos'}
                    </button>

                </form>

            </div>
        </AdminLayout>
    );
}
