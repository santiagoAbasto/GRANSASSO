import React, { useState } from 'react';
import { useForm, usePage } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';

export default function ImportIndex() {
    const { flash } = usePage().props;
    const result = flash?.import_result;

    const { data, setData, post, processing, errors, reset } = useForm({ file: null });

    function submit(e) {
        e.preventDefault();
        post(route('admin.import.products'), {
            forceFormData: true,
            onSuccess: () => reset('file'),
        });
    }

    return (
        <AdminLayout title="Importar Productos">
            <div style={{ maxWidth: 640, margin: '0 auto' }}>

                <h1 style={{ fontSize: 22, fontWeight: 700, marginBottom: 24 }}>
                    Importación masiva de productos
                </h1>

                {/* Resultado */}
                {result && (
                    <div style={{ marginBottom: 24, padding: 16, background: result.errors?.length ? '#fff3cd' : '#d4edda', borderRadius: 8 }}>
                        <p><strong>Creados:</strong> {result.created}</p>
                        <p><strong>Actualizados:</strong> {result.updated}</p>
                        {result.errors?.length > 0 && (
                            <>
                                <p style={{ color: '#856404', marginTop: 8 }}><strong>Errores ({result.errors.length}):</strong></p>
                                <ul style={{ fontSize: 13, paddingLeft: 20 }}>
                                    {result.errors.map((e, i) => <li key={i}>{e}</li>)}
                                </ul>
                            </>
                        )}
                    </div>
                )}

                {/* Formato esperado */}
                <div style={{ marginBottom: 24, padding: 16, background: '#f8f9fa', borderRadius: 8, fontSize: 13 }}>
                    <p style={{ fontWeight: 600, marginBottom: 8 }}>Columnas esperadas en el Excel:</p>
                    <code style={{ display: 'block', whiteSpace: 'pre-wrap', lineHeight: 1.8 }}>
{`marca | modelo_caja | modelos_vehiculo | fig | cod_gs | codigo_oem
nombre | descripcion | referencia_original | referencias_adicionales
aplicaciones | [variante_1] | [variante_2] | ...

- referencias_adicionales: separadas por ";"
- aplicaciones: separadas por "|"
- variantes (G60, G85, G3/36...): columnas dinámicas, poner "X" si aplica`}
                    </code>
                </div>

                {/* Formulario */}
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
                        {errors.file && <p style={{ color: 'red', fontSize: 13 }}>{errors.file}</p>}
                    </div>

                    <button
                        type="submit"
                        disabled={processing || !data.file}
                        style={{
                            padding: '10px 24px',
                            background: '#25A7CA',
                            color: '#fff',
                            border: 'none',
                            borderRadius: 6,
                            fontWeight: 600,
                            cursor: processing ? 'wait' : 'pointer',
                            opacity: !data.file ? 0.5 : 1,
                        }}
                    >
                        {processing ? 'Importando...' : 'Importar productos'}
                    </button>
                </form>

            </div>
        </AdminLayout>
    );
}
