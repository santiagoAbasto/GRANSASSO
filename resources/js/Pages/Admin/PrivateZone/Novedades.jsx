import { useState, useRef, useEffect, useCallback } from "react";
import { router } from "@inertiajs/react";
import AdminLayout from "@/Layouts/AdminLayout";
import { Icon } from "@iconify/react";

/* ─── constantes ─────────────────────────────────────────────────────── */
const LANGS = [
    { code: "es", label: "Español", flag: "🇦🇷" },
    { code: "en", label: "English", flag: "🇺🇸" },
    { code: "pt", label: "Português", flag: "🇧🇷" },
];

const emptyLang = () => ({ es: "", en: "", pt: "" });

const emptyForm = {
    category:        emptyLang(),
    is_active:       true,
    image:           null,
    currentImageUrl: null,
    title:           emptyLang(),
    excerpt:         emptyLang(),
    body:            emptyLang(),
};

/* ─── editor de texto enriquecido ────────────────────────────────────── */
const TOOLBAR = [
    { cmd: "bold",          icon: "solar:bold-square-outline",         title: "Negrita" },
    { cmd: "italic",        icon: "solar:text-italic-outline",         title: "Cursiva" },
    { cmd: "underline",     icon: "solar:text-underline-outline",      title: "Subrayado" },
    { cmd: "separator" },
    { cmd: "insertUnorderedList", icon: "solar:list-outline",          title: "Lista" },
    { cmd: "insertOrderedList",   icon: "solar:list-cross-bold",       title: "Lista numerada" },
    { cmd: "separator" },
    { cmd: "h2",            icon: "solar:text-bold-square-linear",     title: "Título H2", block: true },
    { cmd: "h3",            icon: "solar:text-square-outline",         title: "Título H3", block: true },
    { cmd: "separator" },
    { cmd: "removeFormat",  icon: "solar:close-square-outline",        title: "Limpiar formato" },
];

function RichEditor({ value, onChange, placeholder }) {
    const ref = useRef(null);
    const isComposing = useRef(false);

    // Inicializar contenido cuando cambia el valor desde fuera (cambio de idioma)
    useEffect(() => {
        if (ref.current && ref.current.innerHTML !== value) {
            ref.current.innerHTML = value || "";
        }
    }, [value]);

    const emit = useCallback(() => {
        if (ref.current && !isComposing.current) {
            onChange(ref.current.innerHTML);
        }
    }, [onChange]);

    function execCmd(item) {
        ref.current?.focus();
        if (item.block) {
            document.execCommand("formatBlock", false, item.cmd);
        } else {
            document.execCommand(item.cmd, false, null);
        }
        emit();
    }

    function isActive(cmd) {
        try { return document.queryCommandState(cmd); } catch { return false; }
    }

    return (
        <div style={{ border: "1.5px solid #e2e8f0", borderRadius: 10, overflow: "hidden" }}>
            {/* toolbar */}
            <div style={{
                display: "flex", alignItems: "center", gap: 2,
                padding: "6px 10px", background: "#f8fafc",
                borderBottom: "1px solid #e2e8f0", flexWrap: "wrap",
            }}>
                {TOOLBAR.map((item, i) =>
                    item.cmd === "separator" ? (
                        <div key={i} style={{ width: 1, height: 20, background: "#e2e8f0", margin: "0 4px" }} />
                    ) : (
                        <button
                            key={item.cmd}
                            type="button"
                            title={item.title}
                            onMouseDown={e => { e.preventDefault(); execCmd(item); }}
                            style={{
                                padding: "4px 6px",
                                border: "none",
                                borderRadius: 6,
                                background: isActive(item.cmd) ? "#e0f2fe" : "transparent",
                                color: isActive(item.cmd) ? "#0369a1" : "#475569",
                                cursor: "pointer",
                                display: "flex", alignItems: "center",
                                lineHeight: 1,
                                transition: "background .15s",
                            }}
                        >
                            <Icon icon={item.icon} width={16} />
                        </button>
                    )
                )}
            </div>

            {/* área editable */}
            <div
                ref={ref}
                contentEditable
                suppressContentEditableWarning
                data-placeholder={placeholder || "Escribí el contenido aquí…"}
                onInput={emit}
                onCompositionStart={() => { isComposing.current = true; }}
                onCompositionEnd={() => { isComposing.current = false; emit(); }}
                style={{
                    minHeight: 180,
                    padding: "14px 16px",
                    outline: "none",
                    fontSize: 14,
                    lineHeight: 1.7,
                    color: "#1e293b",
                    background: "#fff",
                }}
                // CSS para placeholder vía :empty:before
            />
            <style>{`
                [contenteditable][data-placeholder]:empty:before {
                    content: attr(data-placeholder);
                    color: #94a3b8;
                    pointer-events: none;
                }
                [contenteditable] h2 { font-size: 1.2em; font-weight: 700; margin: .6em 0 .3em; }
                [contenteditable] h3 { font-size: 1.05em; font-weight: 700; margin: .5em 0 .25em; }
                [contenteditable] ul { list-style: disc; padding-left: 1.4em; }
                [contenteditable] ol { list-style: decimal; padding-left: 1.4em; }
                [contenteditable] p  { margin: 0 0 .5em; }
            `}</style>
        </div>
    );
}

/* ─── tabs de idiomas ────────────────────────────────────────────────── */
function LangTabs({ active, onChange, errors }) {
    return (
        <div style={{ display: "flex", gap: 4, marginBottom: 20 }}>
            {LANGS.map(l => {
                const hasErr = errors?.[`title_${l.code}`];
                return (
                    <button
                        key={l.code}
                        type="button"
                        onClick={() => onChange(l.code)}
                        style={{
                            padding: "7px 16px",
                            border: `1.5px solid ${active === l.code ? "#25A7CA" : "#e2e8f0"}`,
                            borderRadius: 8,
                            background: active === l.code ? "#f0fdff" : "#fff",
                            color: active === l.code ? "#25A7CA" : "#64748b",
                            fontWeight: active === l.code ? 700 : 400,
                            fontSize: 13,
                            cursor: "pointer",
                            display: "flex", alignItems: "center", gap: 6,
                            transition: "all .15s",
                            position: "relative",
                        }}
                    >
                        <span>{l.flag}</span>
                        {l.label}
                        {hasErr && (
                            <span style={{
                                position: "absolute", top: -4, right: -4,
                                width: 8, height: 8, borderRadius: "50%",
                                background: "#ef4444",
                            }} />
                        )}
                    </button>
                );
            })}
        </div>
    );
}

/* ─── componente principal ───────────────────────────────────────────── */
export default function Novedades({ novedades }) {
    const [showForm, setShowForm] = useState(false);
    const [editing, setEditing]   = useState(null);
    const [form, setForm]         = useState(emptyForm);
    const [saving, setSaving]     = useState(false);
    const [activeLang, setLang]   = useState("es");
    const [formErrors, setErrors] = useState({});

    function openNew() {
        setEditing(null);
        setForm(emptyForm);
        setLang("es");
        setErrors({});
        setShowForm(true);
    }

    function openEdit(n) {
        setEditing(n.id);
        setForm({
            category:        { es: n.category?.es || "", en: n.category?.en || "", pt: n.category?.pt || "" },
            is_active:       !!n.is_active,
            image:           null,
            currentImageUrl: n.image_url || null,
            title:   { es: n.title?.es   || "", en: n.title?.en   || "", pt: n.title?.pt   || "" },
            excerpt: { es: n.excerpt?.es || "", en: n.excerpt?.en || "", pt: n.excerpt?.pt || "" },
            body:    { es: n.body?.es    || "", en: n.body?.en    || "", pt: n.body?.pt    || "" },
        });
        setLang("es");
        setErrors({});
        setShowForm(true);
    }

    function setLangField(field, lang, val) {
        setForm(f => ({ ...f, [field]: { ...f[field], [lang]: val } }));
    }

    function handleSubmit(e) {
        e.preventDefault();
        const errs = {};
        if (!form.title.es.trim()) errs.title_es = "El título en español es obligatorio.";
        if (Object.keys(errs).length) { setErrors(errs); return; }

        setSaving(true);
        const fd = new FormData();
        fd.append("is_active",  form.is_active ? "1" : "0");
        if (form.image) fd.append("image", form.image);

        LANGS.forEach(l => {
            fd.append(`category_${l.code}`, form.category[l.code] ?? "");
            fd.append(`title_${l.code}`,    form.title[l.code]    ?? "");
            fd.append(`excerpt_${l.code}`,  form.excerpt[l.code]  ?? "");
            fd.append(`body_${l.code}`,     form.body[l.code]     ?? "");
        });

        const url = editing ? `/admin/private-novedades/${editing}` : "/admin/private-novedades";
        if (editing) fd.append("_method", "PUT");

        router.post(url, fd, {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => { setShowForm(false); setSaving(false); },
            onError: (e) => { setErrors(e); setSaving(false); },
        });
    }

    function handleDelete(id) {
        if (confirm("¿Eliminar esta novedad?")) {
            router.delete(`/admin/private-novedades/${id}`, { preserveScroll: true });
        }
    }

    const lang = activeLang;

    return (
        <AdminLayout title="Novedades — Zona Privada">
            <div className="max-w-6xl">
                {/* encabezado */}
                <div className="flex items-center justify-between mb-6">
                    <div>
                        <h1 className="text-2xl font-bold text-gray-800">Novedades</h1>
                        <p className="text-sm text-gray-500 mt-1">Contenido en 3 idiomas con texto enriquecido.</p>
                    </div>
                    <button
                        onClick={openNew}
                        className="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#25A7CA] text-white text-sm font-medium hover:bg-[#1d96b8] transition"
                    >
                        <Icon icon="solar:add-circle-outline" width={18} />
                        Nueva novedad
                    </button>
                </div>

                {/* ── modal ──────────────────────────────────────────── */}
                {showForm && (
                    <div className="fixed inset-0 bg-black/50 z-50 flex items-start justify-center overflow-y-auto py-6 px-4">
                        <div className="bg-white rounded-2xl shadow-2xl w-full max-w-3xl">

                            {/* cabecera modal */}
                            <div className="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                                <div>
                                    <h2 className="font-bold text-gray-800 text-base">
                                        {editing ? "Editar novedad" : "Nueva novedad"}
                                    </h2>
                                    <p className="text-xs text-gray-400 mt-0.5">Completá al menos el español.</p>
                                </div>
                                <button onClick={() => setShowForm(false)} className="text-gray-400 hover:text-gray-600 transition">
                                    <Icon icon="solar:close-circle-outline" width={24} />
                                </button>
                            </div>

                            <form onSubmit={handleSubmit}>
                                <div className="p-6 space-y-5">

                                    {/* fila: activa + imagen */}
                                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div className="flex items-center gap-3">
                                            <label className="flex items-center gap-2 cursor-pointer">
                                                <input
                                                    type="checkbox"
                                                    checked={form.is_active}
                                                    onChange={e => setForm(f => ({ ...f, is_active: e.target.checked }))}
                                                    className="w-4 h-4 accent-[#25A7CA]"
                                                />
                                                <span className="text-sm text-gray-700 font-medium">Activa</span>
                                            </label>
                                        </div>
                                        <div>
                                            <label className="block text-xs font-semibold text-gray-600 mb-1">
                                                {form.currentImageUrl ? "Reemplazar imagen" : "Imagen"}
                                            </label>
                                            {form.currentImageUrl && !form.image && (
                                                <div className="mb-2 rounded-lg overflow-hidden border border-gray-100 w-full aspect-video bg-gray-50">
                                                    <img src={form.currentImageUrl} alt="Imagen actual" className="w-full h-full object-cover" />
                                                </div>
                                            )}
                                            {form.image && (
                                                <div className="mb-2 rounded-lg overflow-hidden border border-gray-100 w-full aspect-video bg-gray-50">
                                                    <img src={URL.createObjectURL(form.image)} alt="Nueva imagen" className="w-full h-full object-cover" />
                                                </div>
                                            )}
                                            <input
                                                type="file"
                                                accept="image/*"
                                                onChange={e => setForm(f => ({ ...f, image: e.target.files[0] || null }))}
                                                className="text-xs text-gray-600 file:mr-2 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-[#25A7CA]/10 file:text-[#25A7CA] hover:file:bg-[#25A7CA]/20"
                                            />
                                        </div>
                                    </div>

                                    {/* divider */}
                                    <div className="border-t border-gray-100" />

                                    {/* tabs de idiomas */}
                                    <div>
                                        <div className="flex items-center justify-between mb-3">
                                            <span className="text-xs font-semibold text-gray-500 uppercase tracking-wider">Contenido por idioma</span>
                                        </div>
                                        <LangTabs active={lang} onChange={setLang} errors={formErrors} />

                                        {/* categoría por idioma */}
                                        <div className="mb-4">
                                            <label className="block text-xs font-semibold text-gray-600 mb-1">
                                                Categoría
                                            </label>
                                            <input
                                                type="text"
                                                value={form.category[lang]}
                                                onChange={e => setLangField("category", lang, e.target.value)}
                                                placeholder={`Ej: Productos, Empresa… (${LANGS.find(l => l.code === lang)?.label})`}
                                                className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30"
                                            />
                                        </div>

                                        {/* título */}
                                        <div className="mb-4">
                                            <label className="block text-xs font-semibold text-gray-600 mb-1">
                                                Título {lang === "es" && <span className="text-red-500">*</span>}
                                            </label>
                                            <input
                                                type="text"
                                                value={form.title[lang]}
                                                onChange={e => setLangField("title", lang, e.target.value)}
                                                placeholder={`Título en ${LANGS.find(l => l.code === lang)?.label}…`}
                                                className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30"
                                            />
                                            {formErrors[`title_${lang}`] && (
                                                <p className="text-xs text-red-500 mt-1">{formErrors[`title_${lang}`]}</p>
                                            )}
                                        </div>

                                        {/* extracto */}
                                        <div className="mb-4">
                                            <label className="block text-xs font-semibold text-gray-600 mb-1">
                                                Extracto
                                                <span className="font-normal text-gray-400 ml-1">(resumen visible en la lista)</span>
                                            </label>
                                            <textarea
                                                rows={3}
                                                value={form.excerpt[lang]}
                                                onChange={e => setLangField("excerpt", lang, e.target.value)}
                                                placeholder={`Breve descripción en ${LANGS.find(l => l.code === lang)?.label}…`}
                                                className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30 resize-none"
                                            />
                                        </div>

                                        {/* cuerpo enriquecido */}
                                        <div>
                                            <label className="block text-xs font-semibold text-gray-600 mb-1">
                                                Cuerpo
                                                <span className="font-normal text-gray-400 ml-1">(contenido completo)</span>
                                            </label>
                                            <RichEditor
                                                key={lang}
                                                value={form.body[lang]}
                                                onChange={val => setLangField("body", lang, val)}
                                                placeholder={`Contenido en ${LANGS.find(l => l.code === lang)?.label}…`}
                                            />
                                        </div>
                                    </div>

                                </div>

                                {/* footer modal */}
                                <div className="flex justify-end gap-3 px-6 py-4 border-t border-gray-100 bg-gray-50 rounded-b-2xl">
                                    <button
                                        type="button"
                                        onClick={() => setShowForm(false)}
                                        className="px-4 py-2 border border-gray-200 rounded-lg text-sm text-gray-600 hover:bg-gray-100 transition"
                                    >
                                        Cancelar
                                    </button>
                                    <button
                                        type="submit"
                                        disabled={saving}
                                        className="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-[#25A7CA] text-white text-sm font-medium hover:bg-[#1d96b8] disabled:opacity-60 transition"
                                    >
                                        {saving && <Icon icon="solar:refresh-outline" width={15} className="animate-spin" />}
                                        {saving ? "Guardando…" : "Guardar novedad"}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                )}

                {/* ── tabla ────────────────────────────────────────────── */}
                <div className="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                    <table className="w-full text-sm">
                        <thead>
                            <tr className="bg-[#0D2038] text-white text-left">
                                <th className="px-4 py-3 w-16">Img</th>
                                <th className="px-4 py-3">Título (ES)</th>
                                <th className="px-4 py-3">Categoría</th>
                                <th className="px-4 py-3">Idiomas</th>
                                <th className="px-4 py-3">Estado</th>
                                <th className="px-4 py-3">Fecha</th>
                                <th className="px-4 py-3 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            {novedades.length === 0 && (
                                <tr>
                                    <td colSpan={7} className="text-center py-12 text-gray-400">
                                        No hay novedades. Creá la primera.
                                    </td>
                                </tr>
                            )}
                            {novedades.map(n => {
                                const hasEn = !!n.title?.en;
                                const hasPt = !!n.title?.pt;
                                return (
                                    <tr key={n.id} className="border-b border-gray-100 hover:bg-gray-50">
                                        <td className="px-4 py-2">
                                            {n.image_url ? (
                                                <img src={n.image_url} alt="" className="w-12 h-12 rounded-lg object-cover border border-gray-100" />
                                            ) : (
                                                <div className="w-12 h-12 rounded-lg bg-gray-100 flex items-center justify-center">
                                                    <Icon icon="solar:gallery-outline" width={18} className="text-gray-300" />
                                                </div>
                                            )}
                                        </td>
                                        <td className="px-4 py-3 font-semibold text-gray-800 max-w-xs truncate">
                                            {n.title?.es || "—"}
                                        </td>
                                        <td className="px-4 py-3 text-gray-500">{n.category?.es || "—"}</td>
                                        <td className="px-4 py-3">
                                            <div className="flex gap-1">
                                                <span className="text-xs px-1.5 py-0.5 rounded bg-blue-100 text-blue-700 font-semibold">ES</span>
                                                {hasEn && <span className="text-xs px-1.5 py-0.5 rounded bg-green-100 text-green-700 font-semibold">EN</span>}
                                                {hasPt && <span className="text-xs px-1.5 py-0.5 rounded bg-yellow-100 text-yellow-700 font-semibold">PT</span>}
                                            </div>
                                        </td>
                                        <td className="px-4 py-3">
                                            <span className={`px-2 py-1 rounded-full text-xs font-semibold ${n.is_active ? "bg-green-100 text-green-700" : "bg-gray-100 text-gray-500"}`}>
                                                {n.is_active ? "Activa" : "Inactiva"}
                                            </span>
                                        </td>
                                        <td className="px-4 py-3 text-gray-400 text-xs">
                                            {new Date(n.created_at).toLocaleDateString("es-AR")}
                                        </td>
                                        <td className="px-4 py-3">
                                            <div className="flex items-center gap-2 justify-end">
                                                <button
                                                    onClick={() => openEdit(n)}
                                                    className="p-1.5 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition"
                                                    title="Editar"
                                                >
                                                    <Icon icon="solar:pen-outline" width={15} />
                                                </button>
                                                <button
                                                    onClick={() => handleDelete(n.id)}
                                                    className="p-1.5 rounded-lg bg-red-50 text-red-500 hover:bg-red-100 transition"
                                                    title="Eliminar"
                                                >
                                                    <Icon icon="solar:trash-bin-minimalistic-outline" width={15} />
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                );
                            })}
                        </tbody>
                    </table>
                </div>
            </div>
        </AdminLayout>
    );
}
