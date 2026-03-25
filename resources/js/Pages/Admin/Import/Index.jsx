import React, { useState, useRef, useCallback } from 'react';
import { useForm, usePage } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';

/* ─── íconos SVG inline ──────────────────────────────────────────────── */
const IconUpload = () => (
    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round">
        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
        <polyline points="17 8 12 3 7 8"/>
        <line x1="12" y1="3" x2="12" y2="15"/>
    </svg>
);
const IconFile = () => (
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
        <polyline points="14 2 14 8 20 8"/>
    </svg>
);
const IconX = () => (
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round">
        <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
    </svg>
);
const IconCheck = ({ size = 18 }) => (
    <svg width={size} height={size} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round">
        <polyline points="20 6 9 17 4 12"/>
    </svg>
);
const IconWarning = () => (
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
        <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
    </svg>
);
const IconPlus = () => (
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round">
        <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
    </svg>
);
const IconRefresh = () => (
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
        <polyline points="1 4 1 10 7 10"/>
        <path d="M3.51 15a9 9 0 1 0 .49-4.53"/>
    </svg>
);
const IconInfo = () => (
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
        <circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>
    </svg>
);
const IconChevron = ({ open }) => (
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"
        style={{ transform: open ? 'rotate(180deg)' : 'rotate(0deg)', transition: 'transform .2s' }}>
        <polyline points="6 9 12 15 18 9"/>
    </svg>
);

/* ─── constantes de estilo ───────────────────────────────────────────── */
const BRAND   = '#25A7CA';
const BRAND_D = '#1a8aaa';
const GREEN   = '#16a34a';
const AMBER   = '#d97706';
const RED     = '#dc2626';
const GRAY_50 = '#f9fafb';
const GRAY_100= '#f3f4f6';
const GRAY_200= '#e5e7eb';
const GRAY_400= '#9ca3af';
const GRAY_600= '#4b5563';
const GRAY_700= '#374151';
const GRAY_900= '#111827';

function formatBytes(bytes) {
    if (!bytes) return '';
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
}

/* ─── componente principal ───────────────────────────────────────────── */
export default function ImportIndex() {
    const { flash } = usePage().props;
    const result = flash?.import_result;

    const { data, setData, post, processing, errors, reset } = useForm({
        file:   null,
        format: 'auto',
    });

    const [dragging, setDragging]     = useState(false);
    const [guideOpen, setGuideOpen]   = useState(false);
    const [errorsOpen, setErrorsOpen] = useState(true);
    const fileInputRef = useRef(null);

    /* drag & drop */
    const handleDrop = useCallback((e) => {
        e.preventDefault();
        setDragging(false);
        const file = e.dataTransfer.files[0];
        if (file) setData('file', file);
    }, []);

    const handleDragOver = useCallback((e) => {
        e.preventDefault();
        setDragging(true);
    }, []);

    const handleDragLeave = useCallback(() => setDragging(false), []);

    function submit(e) {
        e.preventDefault();
        post(route('admin.import.products'), {
            forceFormData: true,
            onSuccess: () => reset('file'),
        });
    }

    const formatLabel = { auto: 'Auto-detectado', flat: 'Plano', catalog: 'Catálogo' };
    const hasErrors   = result?.errors?.length > 0;
    const hasResult   = !!result;
    const totalCreated = (result?.products_created ?? 0) + (result?.items_created ?? 0);
    const totalUpdated = (result?.products_updated ?? 0) + (result?.items_updated ?? 0);

    /* pasos */
    const step = !data.file ? 1 : 2;

    return (
        <AdminLayout title="Importar Productos">
            <div style={{ maxWidth: 760, margin: '0 auto', padding: '0 4px', fontFamily: 'inherit' }}>

                {/* ── encabezado ──────────────────────────────────────── */}
                <div style={{ marginBottom: 32 }}>
                    <h1 style={{ fontSize: 22, fontWeight: 700, color: GRAY_900, margin: 0 }}>
                        Importación masiva de productos
                    </h1>
                    <p style={{ margin: '6px 0 0', color: GRAY_600, fontSize: 14 }}>
                        Cargá un archivo Excel o CSV con tu catálogo. El sistema detecta el formato automáticamente.
                    </p>
                </div>

                {/* ── indicador de pasos ──────────────────────────────── */}
                <div style={{ display: 'flex', alignItems: 'center', gap: 0, marginBottom: 32 }}>
                    {[
                        { n: 1, label: 'Seleccionar archivo' },
                        { n: 2, label: 'Confirmar formato' },
                        { n: 3, label: 'Importar' },
                    ].map(({ n, label }, idx, arr) => {
                        const done    = hasResult ? true : step > n;
                        const active  = !hasResult && step === n;
                        const pending = !hasResult && step < n;
                        return (
                            <React.Fragment key={n}>
                                <div style={{ display: 'flex', alignItems: 'center', gap: 8, flexShrink: 0 }}>
                                    <div style={{
                                        width: 28, height: 28, borderRadius: '50%',
                                        background: done ? GREEN : active ? BRAND : GRAY_200,
                                        color: done || active ? '#fff' : GRAY_400,
                                        display: 'flex', alignItems: 'center', justifyContent: 'center',
                                        fontSize: 13, fontWeight: 700,
                                        transition: 'background .3s',
                                    }}>
                                        {done ? <IconCheck size={14}/> : n}
                                    </div>
                                    <span style={{
                                        fontSize: 13, fontWeight: active ? 600 : 400,
                                        color: done ? GREEN : active ? BRAND : GRAY_400,
                                        whiteSpace: 'nowrap',
                                    }}>{label}</span>
                                </div>
                                {idx < arr.length - 1 && (
                                    <div style={{
                                        flex: 1, height: 2, margin: '0 10px',
                                        background: done ? GREEN : GRAY_200,
                                        transition: 'background .3s',
                                    }}/>
                                )}
                            </React.Fragment>
                        );
                    })}
                </div>

                {/* ── resultado de importación ─────────────────────────── */}
                {hasResult && (
                    <div style={{
                        marginBottom: 28,
                        border: `1px solid ${hasErrors ? '#fde68a' : '#bbf7d0'}`,
                        borderRadius: 12,
                        overflow: 'hidden',
                        boxShadow: '0 1px 4px rgba(0,0,0,.06)',
                    }}>
                        {/* cabecera resultado */}
                        <div style={{
                            padding: '14px 20px',
                            background: hasErrors ? '#fffbeb' : '#f0fdf4',
                            borderBottom: `1px solid ${hasErrors ? '#fde68a' : '#bbf7d0'}`,
                            display: 'flex', alignItems: 'center', gap: 10,
                        }}>
                            <span style={{ color: hasErrors ? AMBER : GREEN }}>
                                {hasErrors ? <IconWarning/> : <IconCheck/>}
                            </span>
                            <span style={{ fontWeight: 700, color: GRAY_900, fontSize: 15 }}>
                                Importación completada
                            </span>
                            {result.format && (
                                <span style={{
                                    marginLeft: 'auto', fontSize: 12, color: GRAY_600,
                                    background: GRAY_100, padding: '2px 10px', borderRadius: 20,
                                }}>
                                    Formato: {formatLabel[result.format] ?? result.format}
                                </span>
                            )}
                        </div>

                        {/* tarjetas de estadísticas */}
                        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(4, 1fr)', gap: 0 }}>
                            {[
                                { label: 'Productos creados',   value: result.products_created, color: GREEN, icon: <IconPlus/> },
                                { label: 'Productos actualizados', value: result.products_updated, color: BRAND, icon: <IconRefresh/> },
                                { label: 'Items creados',       value: result.items_created,   color: GREEN, icon: <IconPlus/> },
                                { label: 'Items actualizados',  value: result.items_updated,   color: BRAND, icon: <IconRefresh/> },
                            ].map((stat, i, arr) => (
                                <div key={stat.label} style={{
                                    padding: '16px 20px',
                                    borderRight: i < arr.length - 1 ? `1px solid ${GRAY_200}` : 'none',
                                    background: '#fff',
                                    textAlign: 'center',
                                }}>
                                    <div style={{ color: stat.color, marginBottom: 4, display: 'flex', justifyContent: 'center' }}>
                                        {stat.icon}
                                    </div>
                                    <div style={{ fontSize: 26, fontWeight: 800, color: GRAY_900, lineHeight: 1 }}>
                                        {stat.value ?? 0}
                                    </div>
                                    <div style={{ fontSize: 11, color: GRAY_600, marginTop: 4, lineHeight: 1.3 }}>
                                        {stat.label}
                                    </div>
                                </div>
                            ))}
                        </div>

                        {/* errores colapsables */}
                        {hasErrors && (
                            <div style={{ borderTop: `1px solid #fde68a` }}>
                                <button
                                    type="button"
                                    onClick={() => setErrorsOpen(o => !o)}
                                    style={{
                                        width: '100%', padding: '12px 20px',
                                        background: '#fffbeb', border: 'none', cursor: 'pointer',
                                        display: 'flex', alignItems: 'center', gap: 8,
                                        fontSize: 13, fontWeight: 600, color: AMBER,
                                        textAlign: 'left',
                                    }}
                                >
                                    <IconWarning/>
                                    {result.errors.length} advertencia{result.errors.length !== 1 ? 's' : ''} durante la importación
                                    <span style={{ marginLeft: 'auto' }}><IconChevron open={errorsOpen}/></span>
                                </button>
                                {errorsOpen && (
                                    <ul style={{
                                        margin: 0, padding: '0 20px 16px 20px',
                                        background: '#fffbeb', listStyle: 'none',
                                        display: 'flex', flexDirection: 'column', gap: 4,
                                    }}>
                                        {result.errors.map((err, i) => (
                                            <li key={i} style={{
                                                fontSize: 12, color: '#92400e',
                                                padding: '5px 10px', background: '#fef3c7',
                                                borderRadius: 6, borderLeft: '3px solid #fbbf24',
                                            }}>
                                                {err}
                                            </li>
                                        ))}
                                    </ul>
                                )}
                            </div>
                        )}
                    </div>
                )}

                {/* ── formulario ──────────────────────────────────────── */}
                <form onSubmit={submit} encType="multipart/form-data">

                    {/* zona drag & drop */}
                    <div style={{ marginBottom: 20 }}>
                        <label style={{
                            display: 'block', fontWeight: 600, color: GRAY_700,
                            fontSize: 13, marginBottom: 8,
                        }}>
                            Paso 1 — Archivo Excel o CSV
                        </label>

                        {/* área de drop */}
                        {!data.file ? (
                            <div
                                onDrop={handleDrop}
                                onDragOver={handleDragOver}
                                onDragLeave={handleDragLeave}
                                onClick={() => fileInputRef.current?.click()}
                                style={{
                                    border: `2px dashed ${dragging ? BRAND : GRAY_200}`,
                                    borderRadius: 12,
                                    padding: '40px 24px',
                                    textAlign: 'center',
                                    cursor: 'pointer',
                                    background: dragging ? '#f0f9ff' : GRAY_50,
                                    transition: 'all .2s',
                                }}
                            >
                                <div style={{ color: dragging ? BRAND : GRAY_400, marginBottom: 12 }}>
                                    <IconUpload/>
                                </div>
                                <p style={{ margin: 0, fontWeight: 600, color: dragging ? BRAND : GRAY_700, fontSize: 14 }}>
                                    {dragging ? 'Soltá el archivo aquí' : 'Arrastrá tu archivo o hacé clic para seleccionar'}
                                </p>
                                <p style={{ margin: '6px 0 0', fontSize: 12, color: GRAY_400 }}>
                                    Formatos soportados: .xlsx · .xls · .csv — Máximo 10 MB
                                </p>
                            </div>
                        ) : (
                            /* archivo seleccionado */
                            <div style={{
                                display: 'flex', alignItems: 'center', gap: 12,
                                padding: '14px 16px',
                                border: `1px solid ${GRAY_200}`,
                                borderRadius: 10, background: '#fff',
                            }}>
                                <div style={{
                                    width: 40, height: 40, borderRadius: 8,
                                    background: '#e0f2fe', color: BRAND,
                                    display: 'flex', alignItems: 'center', justifyContent: 'center',
                                    flexShrink: 0,
                                }}>
                                    <IconFile/>
                                </div>
                                <div style={{ flex: 1, minWidth: 0 }}>
                                    <p style={{ margin: 0, fontWeight: 600, color: GRAY_900, fontSize: 13, overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>
                                        {data.file.name}
                                    </p>
                                    <p style={{ margin: '2px 0 0', fontSize: 12, color: GRAY_400 }}>
                                        {formatBytes(data.file.size)}
                                    </p>
                                </div>
                                <button
                                    type="button"
                                    onClick={() => { setData('file', null); fileInputRef.current && (fileInputRef.current.value = ''); }}
                                    style={{
                                        width: 28, height: 28, borderRadius: '50%',
                                        border: `1px solid ${GRAY_200}`, background: '#fff',
                                        cursor: 'pointer', display: 'flex', alignItems: 'center', justifyContent: 'center',
                                        color: GRAY_600, flexShrink: 0,
                                    }}
                                    title="Quitar archivo"
                                >
                                    <IconX/>
                                </button>
                            </div>
                        )}

                        {/* input oculto */}
                        <input
                            ref={fileInputRef}
                            type="file"
                            accept=".xlsx,.xls,.csv"
                            onChange={e => setData('file', e.target.files[0] ?? null)}
                            style={{ display: 'none' }}
                        />
                        {errors.file && (
                            <p style={{ margin: '6px 0 0', fontSize: 12, color: RED }}>{errors.file}</p>
                        )}
                    </div>

                    {/* selector de formato */}
                    <div style={{ marginBottom: 24 }}>
                        <label style={{
                            display: 'block', fontWeight: 600, color: GRAY_700,
                            fontSize: 13, marginBottom: 8,
                        }}>
                            Paso 2 — Formato del archivo
                        </label>
                        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(3, 1fr)', gap: 10 }}>
                            {[
                                {
                                    value: 'auto',
                                    title: 'Auto-detectar',
                                    desc: 'Recomendado. El sistema identifica el formato por la estructura del archivo.',
                                },
                                {
                                    value: 'flat',
                                    title: 'Formato Plano',
                                    desc: 'Encabezados en fila 1. Una fila por cada código OEM / variante.',
                                },
                                {
                                    value: 'catalog',
                                    title: 'Formato Catálogo',
                                    desc: '3 filas de encabezado. Variantes en columnas (G60, G85…).',
                                },
                            ].map(opt => {
                                const selected = data.format === opt.value;
                                return (
                                    <button
                                        key={opt.value}
                                        type="button"
                                        onClick={() => setData('format', opt.value)}
                                        style={{
                                            padding: '14px 12px',
                                            border: `2px solid ${selected ? BRAND : GRAY_200}`,
                                            borderRadius: 10,
                                            background: selected ? '#f0f9ff' : '#fff',
                                            cursor: 'pointer',
                                            textAlign: 'left',
                                            transition: 'all .15s',
                                        }}
                                    >
                                        <div style={{ display: 'flex', alignItems: 'center', gap: 6, marginBottom: 4 }}>
                                            <div style={{
                                                width: 16, height: 16, borderRadius: '50%',
                                                border: `2px solid ${selected ? BRAND : GRAY_400}`,
                                                background: selected ? BRAND : '#fff',
                                                flexShrink: 0, display: 'flex', alignItems: 'center', justifyContent: 'center',
                                            }}>
                                                {selected && <div style={{ width: 6, height: 6, background: '#fff', borderRadius: '50%' }}/>}
                                            </div>
                                            <span style={{ fontWeight: 700, fontSize: 13, color: selected ? BRAND : GRAY_700 }}>
                                                {opt.title}
                                            </span>
                                        </div>
                                        <p style={{ margin: 0, fontSize: 11, color: GRAY_600, lineHeight: 1.45 }}>
                                            {opt.desc}
                                        </p>
                                    </button>
                                );
                            })}
                        </div>
                    </div>

                    {/* botón principal */}
                    <button
                        type="submit"
                        disabled={processing || !data.file}
                        style={{
                            width: '100%', padding: '13px 24px',
                            background: !data.file ? GRAY_200 : processing ? BRAND_D : BRAND,
                            color: !data.file ? GRAY_400 : '#fff',
                            border: 'none', borderRadius: 10,
                            fontWeight: 700, fontSize: 15,
                            cursor: !data.file ? 'not-allowed' : processing ? 'wait' : 'pointer',
                            transition: 'background .2s',
                            display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 10,
                        }}
                    >
                        {processing ? (
                            <>
                                <Spinner/>
                                Procesando archivo…
                            </>
                        ) : (
                            'Importar productos'
                        )}
                    </button>

                </form>

                {/* ── guía de formatos (colapsable) ────────────────────── */}
                <div style={{ marginTop: 28, border: `1px solid ${GRAY_200}`, borderRadius: 12, overflow: 'hidden' }}>
                    <button
                        type="button"
                        onClick={() => setGuideOpen(o => !o)}
                        style={{
                            width: '100%', padding: '14px 18px',
                            background: GRAY_50, border: 'none', cursor: 'pointer',
                            display: 'flex', alignItems: 'center', gap: 8,
                            fontSize: 13, fontWeight: 600, color: GRAY_700,
                            textAlign: 'left',
                        }}
                    >
                        <span style={{ color: BRAND }}><IconInfo/></span>
                        Guía de formatos soportados
                        <span style={{ marginLeft: 'auto', color: GRAY_400 }}><IconChevron open={guideOpen}/></span>
                    </button>

                    {guideOpen && (
                        <div style={{ padding: '20px 20px 24px', background: '#fff', display: 'flex', flexDirection: 'column', gap: 24 }}>

                            {/* formato plano */}
                            <div>
                                <div style={{ display: 'flex', alignItems: 'center', gap: 8, marginBottom: 10 }}>
                                    <span style={{
                                        fontSize: 11, fontWeight: 700, padding: '2px 8px',
                                        background: '#e0f2fe', color: BRAND, borderRadius: 20,
                                    }}>PLANO</span>
                                    <span style={{ fontWeight: 700, fontSize: 13, color: GRAY_700 }}>Encabezados en fila 1</span>
                                </div>
                                <p style={{ margin: '0 0 10px', fontSize: 12, color: GRAY_600, lineHeight: 1.6 }}>
                                    Cada fila del archivo corresponde a un código OEM o variante. Los campos disponibles son:
                                </p>
                                <div style={{ display: 'flex', flexWrap: 'wrap', gap: 6, marginBottom: 12 }}>
                                    {['marca','modelo_caja','modelos_vehiculo','fig','cod_gs','codigo_oem','nombre','descripcion','referencia_original','referencias_adicionales','notas'].map(col => (
                                        <code key={col} style={{
                                            fontSize: 11, padding: '2px 8px', borderRadius: 6,
                                            background: GRAY_100, color: GRAY_700, fontFamily: 'monospace',
                                        }}>{col}</code>
                                    ))}
                                    <code style={{
                                        fontSize: 11, padding: '2px 8px', borderRadius: 6,
                                        background: '#fef3c7', color: '#92400e', fontFamily: 'monospace',
                                    }}>G60 · G85 · [variante]</code>
                                </div>
                                <div style={{
                                    background: GRAY_50, borderRadius: 8, padding: '10px 14px',
                                    fontFamily: 'monospace', fontSize: 11, color: GRAY_700, lineHeight: 1.9,
                                    border: `1px solid ${GRAY_200}`, overflowX: 'auto', whiteSpace: 'nowrap',
                                }}>
                                    <span style={{ color: GRAY_400 }}>{/* fila 1: encabezados */}</span><br/>
                                    marca · modelo_caja · fig · cod_gs · codigo_oem · G60 · G85<br/>
                                    <span style={{ color: GRAY_400 }}>{/* fila 2+: datos */}</span><br/>
                                    Bosch · G60 · 7 · 800 · 694-262-0002 · X ·
                                </div>
                                <p style={{ margin: '8px 0 0', fontSize: 11, color: GRAY_400 }}>
                                    Poner <strong>X</strong> en las columnas de variantes indica que el producto es compatible con esa versión.
                                </p>
                            </div>

                            <div style={{ height: 1, background: GRAY_100 }}/>

                            {/* formato catálogo */}
                            <div>
                                <div style={{ display: 'flex', alignItems: 'center', gap: 8, marginBottom: 10 }}>
                                    <span style={{
                                        fontSize: 11, fontWeight: 700, padding: '2px 8px',
                                        background: '#f0fdf4', color: GREEN, borderRadius: 20,
                                    }}>CATÁLOGO</span>
                                    <span style={{ fontWeight: 700, fontSize: 13, color: GRAY_700 }}>3 filas de encabezado</span>
                                </div>
                                <p style={{ margin: '0 0 10px', fontSize: 12, color: GRAY_600, lineHeight: 1.6 }}>
                                    Estructura del catálogo Gran Sasso. Variantes en columnas. Las filas de datos comienzan en la fila 4.
                                </p>
                                <div style={{
                                    background: GRAY_50, borderRadius: 8, padding: '10px 14px',
                                    fontFamily: 'monospace', fontSize: 11, color: GRAY_700, lineHeight: 1.9,
                                    border: `1px solid ${GRAY_200}`, overflowX: 'auto',
                                }}>
                                    <span style={{ color: AMBER, fontWeight: 700 }}>Fila 1:</span> tipo · Bosch · · · G60<br/>
                                    <span style={{ color: AMBER, fontWeight: 700 }}>Fila 2:</span> Modelos: · · L1218 / 1518 / 1620 …<br/>
                                    <span style={{ color: AMBER, fontWeight: 700 }}>Fila 3:</span> Fig. · Cod. GS · Código OEM · Descripción · G60 · G85 · …<br/>
                                    <span style={{ color: GRAY_400 }}>Fila 4+:</span> 7 · 800 · 694-262-0002 · Cigüeñal · X · · …
                                </div>
                                <p style={{ margin: '8px 0 0', fontSize: 11, color: GRAY_400 }}>
                                    La primera celda de la fila 1 debe ser <strong>tipo</strong> (en minúsculas) para que la detección automática funcione.
                                </p>
                            </div>

                        </div>
                    )}
                </div>

            </div>
        </AdminLayout>
    );
}

/* ─── spinner de carga ───────────────────────────────────────────────── */
function Spinner() {
    return (
        <svg
            width="18" height="18" viewBox="0 0 24 24"
            fill="none" stroke="currentColor" strokeWidth="2.5"
            strokeLinecap="round" strokeLinejoin="round"
            style={{ animation: 'spin 0.8s linear infinite' }}
        >
            <style>{`@keyframes spin { to { transform: rotate(360deg); } }`}</style>
            <path d="M21 12a9 9 0 1 1-6.219-8.56"/>
        </svg>
    );
}
