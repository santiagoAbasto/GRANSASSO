import React, { useState, useRef, useCallback } from 'react';
import { useForm, usePage } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';

/* ─── paleta ─────────────────────────────────────────────────────────── */
const C = {
    brand:   '#25A7CA',
    brandD:  '#1a8aaa',
    brandBg: '#f0f9ff',
    brandSoft:'#e0f2fe',
    green:   '#16a34a',
    greenBg: '#f0fdf4',
    greenSoft:'#bbf7d0',
    amber:   '#d97706',
    amberBg: '#fffbeb',
    amberSoft:'#fde68a',
    red:     '#dc2626',
    g50:     '#f8fafc',
    g100:    '#f1f5f9',
    g200:    '#e2e8f0',
    g300:    '#cbd5e1',
    g400:    '#94a3b8',
    g500:    '#64748b',
    g600:    '#475569',
    g700:    '#334155',
    g900:    '#0f172a',
    white:   '#ffffff',
};

/* ─── íconos SVG ─────────────────────────────────────────────────────── */
const Icon = {
    Upload: () => (
        <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round">
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
            <polyline points="17 8 12 3 7 8"/>
            <line x1="12" y1="3" x2="12" y2="15"/>
        </svg>
    ),
    File: () => (
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
            <polyline points="14 2 14 8 20 8"/>
        </svg>
    ),
    X: () => (
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round">
            <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
        </svg>
    ),
    Check: ({ size = 16 }) => (
        <svg width={size} height={size} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round">
            <polyline points="20 6 9 17 4 12"/>
        </svg>
    ),
    Warning: ({ size = 17 }) => (
        <svg width={size} height={size} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
            <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
        </svg>
    ),
    Plus: () => (
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round">
            <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
    ),
    Refresh: () => (
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
            <polyline points="1 4 1 10 7 10"/>
            <path d="M3.51 15a9 9 0 1 0 .49-4.53"/>
        </svg>
    ),
    Info: () => (
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
            <circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>
        </svg>
    ),
    ChevDown: ({ open }) => (
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"
            style={{ transform: open ? 'rotate(180deg)' : 'rotate(0)', transition: 'transform .2s' }}>
            <polyline points="6 9 12 15 18 9"/>
        </svg>
    ),
    Database: () => (
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
            <ellipse cx="12" cy="5" rx="9" ry="3"/>
            <path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/>
            <path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/>
        </svg>
    ),
    Pin: () => (
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
            <circle cx="12" cy="12" r="10"/>
        </svg>
    ),
};

function Spinner() {
    return (
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round"
            style={{ animation: 'gs-spin .75s linear infinite' }}>
            <style>{`@keyframes gs-spin{to{transform:rotate(360deg)}}`}</style>
            <path d="M21 12a9 9 0 1 1-6.219-8.56"/>
        </svg>
    );
}

function formatBytes(b) {
    if (!b) return '';
    if (b < 1024) return b + ' B';
    if (b < 1048576) return (b / 1024).toFixed(1) + ' KB';
    return (b / 1048576).toFixed(2) + ' MB';
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
    const [errorsOpen, setErrorsOpen] = useState(true);
    const fileInputRef = useRef(null);

    const handleDrop = useCallback((e) => {
        e.preventDefault();
        setDragging(false);
        const file = e.dataTransfer.files[0];
        if (file) setData('file', file);
    }, []);
    const onDragOver  = useCallback((e) => { e.preventDefault(); setDragging(true); }, []);
    const onDragLeave = useCallback(() => setDragging(false), []);

    function submit(e) {
        e.preventDefault();
        post(route('admin.import.products'), {
            forceFormData: true,
            onSuccess: () => reset('file'),
        });
    }

    const fmtLabel = { auto: 'Auto-detectado', flat: 'Tabla Plana', catalog: 'Catálogo' };
    const hasResult = !!result;
    const hasErrors = result?.errors?.length > 0;

    /* estado de cada paso */
    const step1done = !!data.file || hasResult;
    const step2done = hasResult;

    return (
        <AdminLayout title="Carga Masiva de Productos">

            {/* ── encabezado ──────────────────────────────────────────── */}
            <div style={{ marginBottom: 28 }}>
                <h1 style={{ fontSize: 22, fontWeight: 800, color: C.g900, margin: 0 }}>
                    Carga Masiva de Productos
                </h1>
                <p style={{ margin: '5px 0 0', color: C.g500, fontSize: 14 }}>
                    Sube tu catálogo en formato Excel (.xlsx, .xls) o CSV.
                </p>
            </div>

            {/* ── resultado (ancho completo) ───────────────────────────── */}
            {hasResult && (
                <div style={{
                    marginBottom: 24,
                    borderRadius: 14,
                    border: `1px solid ${hasErrors ? C.amberSoft : C.greenSoft}`,
                    overflow: 'hidden',
                    boxShadow: '0 1px 6px rgba(0,0,0,.06)',
                }}>
                    {/* barra superior */}
                    <div style={{
                        padding: '13px 20px',
                        background: hasErrors ? C.amberBg : C.greenBg,
                        borderBottom: `1px solid ${hasErrors ? C.amberSoft : C.greenSoft}`,
                        display: 'flex', alignItems: 'center', gap: 9,
                    }}>
                        <span style={{ color: hasErrors ? C.amber : C.green, display: 'flex' }}>
                            {hasErrors ? <Icon.Warning/> : <Icon.Check size={17}/>}
                        </span>
                        <span style={{ fontWeight: 700, color: C.g900, fontSize: 14 }}>
                            Importación completada
                        </span>
                        {result.format && (
                            <span style={{
                                marginLeft: 'auto', fontSize: 11, color: C.g500,
                                background: C.g100, padding: '3px 10px', borderRadius: 20, fontWeight: 600,
                            }}>
                                Formato detectado: {fmtLabel[result.format] ?? result.format}
                            </span>
                        )}
                    </div>

                    {/* tarjetas de stats */}
                    <div style={{ display: 'grid', gridTemplateColumns: 'repeat(4,1fr)', background: C.white }}>
                        {[
                            { label: 'Productos creados',      value: result.products_created, color: C.green,  Icon: Icon.Plus    },
                            { label: 'Productos actualizados', value: result.products_updated, color: C.brand,  Icon: Icon.Refresh },
                            { label: 'Items creados',          value: result.items_created,    color: C.green,  Icon: Icon.Plus    },
                            { label: 'Items actualizados',     value: result.items_updated,    color: C.brand,  Icon: Icon.Refresh },
                        ].map((s, i, a) => (
                            <div key={s.label} style={{
                                padding: '18px 16px', textAlign: 'center',
                                borderRight: i < a.length - 1 ? `1px solid ${C.g100}` : 'none',
                            }}>
                                <div style={{ color: s.color, display: 'flex', justifyContent: 'center', marginBottom: 5 }}>
                                    <s.Icon/>
                                </div>
                                <div style={{ fontSize: 28, fontWeight: 800, color: C.g900, lineHeight: 1 }}>
                                    {s.value ?? 0}
                                </div>
                                <div style={{ fontSize: 11, color: C.g500, marginTop: 5, lineHeight: 1.3 }}>
                                    {s.label}
                                </div>
                            </div>
                        ))}
                    </div>

                    {/* errores colapsables */}
                    {hasErrors && (
                        <div style={{ borderTop: `1px solid ${C.amberSoft}` }}>
                            <button type="button" onClick={() => setErrorsOpen(o => !o)} style={{
                                width: '100%', padding: '11px 18px',
                                background: C.amberBg, border: 'none', cursor: 'pointer',
                                display: 'flex', alignItems: 'center', gap: 7,
                                fontSize: 12, fontWeight: 700, color: C.amber, textAlign: 'left',
                            }}>
                                <Icon.Warning size={14}/>
                                {result.errors.length} advertencia{result.errors.length !== 1 ? 's' : ''} durante la importación
                                <span style={{ marginLeft: 'auto', color: C.g400 }}><Icon.ChevDown open={errorsOpen}/></span>
                            </button>
                            {errorsOpen && (
                                <ul style={{
                                    margin: 0, padding: '0 18px 14px', background: C.amberBg,
                                    listStyle: 'none', display: 'flex', flexDirection: 'column', gap: 4,
                                }}>
                                    {result.errors.map((err, i) => (
                                        <li key={i} style={{
                                            fontSize: 12, color: '#78350f', padding: '5px 10px',
                                            background: '#fef3c7', borderRadius: 6, borderLeft: `3px solid #fbbf24`,
                                        }}>{err}</li>
                                    ))}
                                </ul>
                            )}
                        </div>
                    )}
                </div>
            )}

            {/* ── cuerpo: dos columnas ────────────────────────────────── */}
            <div style={{ display: 'grid', gridTemplateColumns: '1fr 340px', gap: 20, alignItems: 'start' }}>

                {/* ── columna izquierda: formulario ─────────────────────── */}
                <div style={{
                    background: C.white,
                    borderRadius: 16,
                    border: `1px solid ${C.g200}`,
                    padding: '28px 28px 24px',
                    boxShadow: '0 1px 4px rgba(0,0,0,.05)',
                }}>

                    {/* pasos */}
                    <div style={{ display: 'flex', alignItems: 'center', gap: 0, marginBottom: 26 }}>
                        {[
                            { n: 1, label: 'Subir archivo',    done: step1done },
                            { n: 2, label: 'Elegir formato',   done: step2done },
                            { n: 3, label: 'Importar',         done: step2done },
                        ].map(({ n, label, done }, idx, arr) => {
                            const active = !hasResult && (n === 1 ? !data.file : n === 2 ? !!data.file : false);
                            return (
                                <React.Fragment key={n}>
                                    <div style={{ display: 'flex', alignItems: 'center', gap: 6, flexShrink: 0 }}>
                                        <div style={{
                                            width: 24, height: 24, borderRadius: '50%',
                                            background: done ? C.green : active ? C.brand : C.g200,
                                            color: done || active ? C.white : C.g400,
                                            display: 'flex', alignItems: 'center', justifyContent: 'center',
                                            fontSize: 11, fontWeight: 800, transition: 'background .25s',
                                        }}>
                                            {done ? <Icon.Check size={12}/> : n}
                                        </div>
                                        <span style={{
                                            fontSize: 12, fontWeight: active ? 700 : done ? 600 : 400,
                                            color: done ? C.green : active ? C.brand : C.g400,
                                            whiteSpace: 'nowrap',
                                        }}>{label}</span>
                                    </div>
                                    {idx < arr.length - 1 && (
                                        <div style={{
                                            flex: 1, height: 2, margin: '0 8px',
                                            background: done ? C.green : C.g200, transition: 'background .25s',
                                        }}/>
                                    )}
                                </React.Fragment>
                            );
                        })}
                    </div>

                    {/* zona drag & drop / preview archivo */}
                    <div style={{ marginBottom: 22 }}>
                        <label style={{ display: 'block', fontSize: 13, fontWeight: 700, color: C.g700, marginBottom: 8 }}>
                            Archivo Excel o CSV
                        </label>

                        {!data.file ? (
                            <div
                                onDrop={handleDrop}
                                onDragOver={onDragOver}
                                onDragLeave={onDragLeave}
                                onClick={() => fileInputRef.current?.click()}
                                style={{
                                    border: `2px dashed ${dragging ? C.brand : C.g300}`,
                                    borderRadius: 12,
                                    padding: '36px 20px',
                                    textAlign: 'center',
                                    cursor: 'pointer',
                                    background: dragging ? C.brandBg : C.g50,
                                    transition: 'all .2s',
                                }}
                            >
                                <div style={{ color: dragging ? C.brand : C.g400, display: 'flex', justifyContent: 'center', marginBottom: 10 }}>
                                    <Icon.Upload/>
                                </div>
                                <p style={{ margin: 0, fontWeight: 700, fontSize: 14, color: dragging ? C.brand : C.g700 }}>
                                    {dragging ? 'Soltá el archivo aquí' : 'Arrastra tu archivo aquí'}
                                </p>
                                <p style={{ margin: '4px 0 0', fontSize: 12, color: C.g400 }}>
                                    o haz clic para explorar en tu equipo
                                </p>
                                <p style={{ margin: '10px 0 0', fontSize: 11, color: C.g300 }}>
                                    .xlsx · .xls · .csv — máx. 10 MB
                                </p>
                            </div>
                        ) : (
                            <div style={{
                                display: 'flex', alignItems: 'center', gap: 12,
                                padding: '12px 14px',
                                border: `1px solid ${C.g200}`, borderRadius: 10,
                                background: C.white,
                            }}>
                                <div style={{
                                    width: 38, height: 38, borderRadius: 8,
                                    background: C.brandSoft, color: C.brand,
                                    display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0,
                                }}>
                                    <Icon.File/>
                                </div>
                                <div style={{ flex: 1, minWidth: 0 }}>
                                    <p style={{ margin: 0, fontWeight: 700, fontSize: 13, color: C.g900, overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>
                                        {data.file.name}
                                    </p>
                                    <p style={{ margin: '1px 0 0', fontSize: 11, color: C.g400 }}>
                                        {formatBytes(data.file.size)}
                                    </p>
                                </div>
                                <button type="button" onClick={() => { setData('file', null); if (fileInputRef.current) fileInputRef.current.value = ''; }}
                                    style={{
                                        width: 26, height: 26, borderRadius: '50%',
                                        border: `1px solid ${C.g200}`, background: C.white,
                                        cursor: 'pointer', display: 'flex', alignItems: 'center', justifyContent: 'center',
                                        color: C.g500, flexShrink: 0,
                                    }} title="Quitar archivo">
                                    <Icon.X/>
                                </button>
                            </div>
                        )}

                        <input ref={fileInputRef} type="file" accept=".xlsx,.xls,.csv"
                            onChange={e => setData('file', e.target.files[0] ?? null)}
                            style={{ display: 'none' }}/>
                        {errors.file && <p style={{ margin: '6px 0 0', fontSize: 12, color: C.red }}>{errors.file}</p>}
                    </div>

                    {/* selector de formato */}
                    <div style={{ marginBottom: 24 }}>
                        <label style={{ display: 'block', fontSize: 13, fontWeight: 700, color: C.g700, marginBottom: 8 }}>
                            Formato del Excel
                        </label>
                        <div style={{ display: 'flex', flexDirection: 'column', gap: 8 }}>
                            {[
                                { value: 'auto',    emoji: '✨', label: 'Auto-detectar (Recomendado)', desc: 'El sistema identifica el formato por la estructura del archivo.' },
                                { value: 'catalog', emoji: '📋', label: 'Catálogo (Variantes)',        desc: '3 filas de encabezado. Variantes G60, G85… en columnas.' },
                                { value: 'flat',    emoji: '📄', label: 'Tabla Plana',                 desc: 'Una fila de encabezados. Una fila por cada item/OEM.' },
                            ].map(opt => {
                                const sel = data.format === opt.value;
                                return (
                                    <button key={opt.value} type="button" onClick={() => setData('format', opt.value)} style={{
                                        display: 'flex', alignItems: 'center', gap: 12,
                                        padding: '11px 14px',
                                        border: `1.5px solid ${sel ? C.brand : C.g200}`,
                                        borderRadius: 10,
                                        background: sel ? C.brandBg : C.white,
                                        cursor: 'pointer', textAlign: 'left',
                                        transition: 'all .15s',
                                    }}>
                                        <span style={{ fontSize: 18, lineHeight: 1, flexShrink: 0 }}>{opt.emoji}</span>
                                        <div style={{ flex: 1, minWidth: 0 }}>
                                            <p style={{ margin: 0, fontSize: 13, fontWeight: 700, color: sel ? C.brand : C.g700 }}>
                                                {opt.label}
                                            </p>
                                            <p style={{ margin: '2px 0 0', fontSize: 11, color: C.g500 }}>
                                                {opt.desc}
                                            </p>
                                        </div>
                                        {/* radio visual */}
                                        <div style={{
                                            width: 17, height: 17, borderRadius: '50%', flexShrink: 0,
                                            border: `2px solid ${sel ? C.brand : C.g300}`,
                                            background: sel ? C.brand : C.white,
                                            display: 'flex', alignItems: 'center', justifyContent: 'center',
                                        }}>
                                            {sel && <div style={{ width: 6, height: 6, background: C.white, borderRadius: '50%' }}/>}
                                        </div>
                                    </button>
                                );
                            })}
                        </div>
                    </div>

                    {/* botón submit */}
                    <button type="submit" form="import-form" disabled={processing || !data.file}
                        onClick={submit}
                        style={{
                            width: '100%', padding: '13px 20px',
                            background: !data.file ? C.g200 : C.brand,
                            color: !data.file ? C.g400 : C.white,
                            border: 'none', borderRadius: 10,
                            fontWeight: 700, fontSize: 14,
                            cursor: !data.file ? 'not-allowed' : processing ? 'wait' : 'pointer',
                            display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 8,
                            transition: 'background .2s',
                            letterSpacing: '.01em',
                        }}>
                        {processing ? (
                            <><Spinner/> Procesando archivo…</>
                        ) : (
                            <><Icon.Database/> Importar Datos</>
                        )}
                    </button>

                </div>

                {/* ── columna derecha: guía de formatos ─────────────────── */}
                <div style={{ display: 'flex', flexDirection: 'column', gap: 14 }}>

                    {/* encabezado guía */}
                    <div style={{
                        display: 'flex', alignItems: 'center', gap: 7,
                        fontSize: 13, fontWeight: 700, color: C.brand,
                        padding: '0 2px',
                    }}>
                        <Icon.Info/>
                        Guía de Formatos
                    </div>

                    {/* tarjeta catálogo */}
                    <FormatCard
                        badge="CATÁLOGO (VARIANTES)"
                        badgeColor={C.brand}
                        badgeBg={C.brandSoft}
                        desc="Ideal para listas con cajas base y columnas de variantes (G60, G85) al final con una X."
                        rows={[
                            { label: 'Fila 1:', value: 'Tipo | Marca | … | Caja' },
                            { label: 'Fila 2:', value: 'Modelos: | L1218/1518 …' },
                            { label: 'Fila 3:', value: 'Fig. | Cod. GS | OEM …' },
                            { label: 'Fila 4+:', value: '7  | 800  | ABC … | X' },
                        ]}
                        tip='La celda A1 debe decir exactamente "tipo" para la auto-detección.'
                    />

                    {/* tarjeta plana */}
                    <FormatCard
                        badge="TABLA PLANA"
                        badgeColor={C.green}
                        badgeBg={C.greenBg}
                        desc="Una sola fila de encabezados estándar. Cada fila representa un item/OEM específico."
                        rows={[
                            { label: 'Fila 1:', value: 'marca | modelo_caja | …' },
                            { label: 'Fila 2+:', value: 'VW  | G-60  | 7 | 800 …' },
                        ]}
                        tip='Poner "X" en columnas de variantes (G60, G85…) indica compatibilidad.'
                    />

                    {/* nota hojas múltiples */}
                    <div style={{
                        padding: '11px 14px',
                        background: C.brandBg,
                        borderRadius: 10,
                        border: `1px solid ${C.brandSoft}`,
                        display: 'flex', alignItems: 'flex-start', gap: 8,
                        fontSize: 12, color: C.g600, lineHeight: 1.55,
                    }}>
                        <span style={{ color: C.brand, flexShrink: 0, marginTop: 1 }}><Icon.Info/></span>
                        <span>
                            El sistema <strong style={{ color: C.brand }}>soporta múltiples hojas (pestañas)</strong> en un mismo Excel siempre que mantengan la misma estructura.
                        </span>
                    </div>

                </div>
            </div>

        </AdminLayout>
    );
}

/* ─── tarjeta de formato ─────────────────────────────────────────────── */
function FormatCard({ badge, badgeColor, badgeBg, desc, rows, tip }) {
    return (
        <div style={{
            background: C.white, borderRadius: 12,
            border: `1px solid ${C.g200}`,
            padding: '16px 16px 14px',
            boxShadow: '0 1px 3px rgba(0,0,0,.04)',
        }}>
            <div style={{ display: 'flex', alignItems: 'center', gap: 8, marginBottom: 8 }}>
                <span style={{
                    fontSize: 10, fontWeight: 800, padding: '2px 8px',
                    background: badgeBg, color: badgeColor,
                    borderRadius: 20, letterSpacing: '.04em',
                }}>{badge}</span>
            </div>
            <p style={{ margin: '0 0 10px', fontSize: 12, color: C.g600, lineHeight: 1.55 }}>
                {desc}
            </p>
            <div style={{
                background: C.g50, border: `1px solid ${C.g200}`,
                borderRadius: 8, padding: '9px 12px',
                fontFamily: 'ui-monospace, "Cascadia Code", monospace',
                fontSize: 11, color: C.g700, lineHeight: 1.9,
            }}>
                {rows.map((r, i) => (
                    <div key={i}>
                        <span style={{ color: badgeColor, fontWeight: 700, marginRight: 4 }}>{r.label}</span>
                        {r.value}
                    </div>
                ))}
            </div>
            {tip && (
                <p style={{ margin: '8px 0 0', fontSize: 11, color: C.g400, lineHeight: 1.5 }}>
                    {tip}
                </p>
            )}
        </div>
    );
}
