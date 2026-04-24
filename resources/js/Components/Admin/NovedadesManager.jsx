import { useState, useCallback, useEffect, useRef } from "react";
import { router } from "@inertiajs/react";
import { Icon } from "@iconify/react";

const LANGS = [
    { code: "es", label: "Español", flag: "🇦🇷" },
    { code: "en", label: "English", flag: "🇺🇸" },
    { code: "pt", label: "Português", flag: "🇧🇷" },
];

const LANG_CODES = LANGS.map((lang) => lang.code);

const emptyLang = () => ({ es: "", en: "", pt: "" });

const emptyForm = {
    category: emptyLang(),
    is_active: true,
    image: null,
    currentImageUrl: null,
    galleryImages: [],
    currentGalleryImages: [],
    title: emptyLang(),
    excerpt: emptyLang(),
    body: emptyLang(),
};

function decodeNestedJson(value) {
    let current = value;

    while (typeof current === "string") {
        const trimmed = current.trim();

        if (!trimmed) {
            return "";
        }

        try {
            current = JSON.parse(trimmed);
        } catch {
            return trimmed;
        }
    }

    return current;
}

function normalizeTranslations(value) {
    const decoded = decodeNestedJson(value);

    if (!decoded || typeof decoded !== "object" || Array.isArray(decoded)) {
        const scalar = typeof decoded === "string" ? decoded : "";
        return Object.fromEntries(LANG_CODES.map((code) => [code, scalar]));
    }

    return Object.fromEntries(
        LANG_CODES.map((code) => {
            const candidate = decoded[code] ?? decoded.es ?? decoded.en ?? decoded.pt ?? "";
            const normalized = decodeNestedJson(candidate);
            return [code, typeof normalized === "string" ? normalized : ""];
        })
    );
}

function normalizeNovedad(novedad) {
    const title = normalizeTranslations(novedad.title);
    const category = normalizeTranslations(novedad.category);
    const excerpt = normalizeTranslations(novedad.excerpt);
    const body = normalizeTranslations(novedad.body);

    if (!title.es && novedad.title_display) title.es = novedad.title_display;
    if (!category.es && novedad.category_display) category.es = novedad.category_display;
    if (!excerpt.es && novedad.excerpt_display) excerpt.es = novedad.excerpt_display;
    if (!body.es && novedad.body_display) body.es = novedad.body_display;

    return { ...novedad, title, category, excerpt, body, gallery_images: novedad.gallery_images || [] };
}

function RichEditor({ value, onChange, placeholder }) {
    const editorRef = useRef(null);
    const isComposing = useRef(false);

    useEffect(() => {
        if (editorRef.current && editorRef.current.innerHTML !== value) {
            editorRef.current.innerHTML = value || "";
        }
    }, [value]);

    const emit = useCallback(() => {
        if (editorRef.current && !isComposing.current) {
            onChange(editorRef.current.innerHTML);
        }
    }, [onChange]);

    function execCmd(item) {
        editorRef.current?.focus();
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

    const toolbar = [
        { cmd: "bold", icon: "solar:bold-square-outline", title: "Negrita" },
        { cmd: "italic", icon: "solar:text-italic-outline", title: "Cursiva" },
        { cmd: "underline", icon: "solar:text-underline-outline", title: "Subrayado" },
        { cmd: "separator" },
        { cmd: "insertUnorderedList", icon: "solar:list-outline", title: "Lista" },
        { cmd: "insertOrderedList", icon: "solar:list-cross-bold", title: "Lista numerada" },
        { cmd: "separator" },
        { cmd: "h2", icon: "solar:text-bold-square-linear", title: "Título H2", block: true },
        { cmd: "h3", icon: "solar:text-square-outline", title: "Título H3", block: true },
        { cmd: "separator" },
        { cmd: "removeFormat", icon: "solar:close-square-outline", title: "Limpiar formato" },
    ];

    return (
        <div style={{ border: "1.5px solid #e2e8f0", borderRadius: 10, overflow: "hidden" }}>
            <div style={{ display: "flex", alignItems: "center", gap: 2, padding: "6px 10px", background: "#f8fafc", borderBottom: "1px solid #e2e8f0", flexWrap: "wrap" }}>
                {toolbar.map((item, index) =>
                    item.cmd === "separator" ? (
                        <div key={index} style={{ width: 1, height: 20, background: "#e2e8f0", margin: "0 4px" }} />
                    ) : (
                        <button
                            key={item.cmd}
                            type="button"
                            title={item.title}
                            onMouseDown={(event) => {
                                event.preventDefault();
                                execCmd(item);
                            }}
                            style={{
                                padding: "4px 6px",
                                border: "none",
                                borderRadius: 6,
                                background: isActive(item.cmd) ? "#e0f2fe" : "transparent",
                                color: isActive(item.cmd) ? "#0369a1" : "#475569",
                                cursor: "pointer",
                                display: "flex",
                                alignItems: "center",
                                lineHeight: 1,
                                transition: "background .15s",
                            }}
                        >
                            <Icon icon={item.icon} width={16} />
                        </button>
                    )
                )}
            </div>

            <div
                ref={editorRef}
                contentEditable
                suppressContentEditableWarning
                data-placeholder={placeholder || "Escribí el contenido aquí…"}
                onInput={emit}
                onCompositionStart={() => { isComposing.current = true; }}
                onCompositionEnd={() => { isComposing.current = false; emit(); }}
                style={{ minHeight: 180, padding: "14px 16px", outline: "none", fontSize: 14, lineHeight: 1.7, color: "#1e293b", background: "#fff" }}
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

function LangTabs({ active, onChange, errors }) {
    return (
        <div style={{ display: "flex", gap: 4, marginBottom: 20 }}>
            {LANGS.map((lang) => {
                const hasErr = errors?.[`title_${lang.code}`];
                return (
                    <button
                        key={lang.code}
                        type="button"
                        onClick={() => onChange(lang.code)}
                        style={{
                            padding: "7px 16px",
                            border: `1.5px solid ${active === lang.code ? "#25A7CA" : "#e2e8f0"}`,
                            borderRadius: 8,
                            background: active === lang.code ? "#f0fdff" : "#fff",
                            color: active === lang.code ? "#25A7CA" : "#64748b",
                            fontWeight: active === lang.code ? 700 : 400,
                            fontSize: 13,
                            cursor: "pointer",
                            display: "flex",
                            alignItems: "center",
                            gap: 6,
                            transition: "all .15s",
                            position: "relative",
                        }}
                    >
                        <span>{lang.flag}</span>
                        {lang.label}
                        {hasErr && (
                            <span style={{ position: "absolute", top: -4, right: -4, width: 8, height: 8, borderRadius: "50%", background: "#ef4444" }} />
                        )}
                    </button>
                );
            })}
        </div>
    );
}

export default function NovedadesManager({
    novedades,
    baseUrl,
    title = "Novedades",
    description = "Contenido en 3 idiomas con texto enriquecido.",
    createLabel = "Nueva novedad",
    emptyLabel = "No hay novedades. Creá la primera.",
}) {
    const normalizedNovedades = novedades.map(normalizeNovedad);
    const [showForm, setShowForm] = useState(false);
    const [editing, setEditing] = useState(null);
    const [form, setForm] = useState(emptyForm);
    const [saving, setSaving] = useState(false);
    const [activeLang, setLang] = useState("es");
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
            category: { es: n.category?.es || "", en: n.category?.en || "", pt: n.category?.pt || "" },
            is_active: !!n.is_active,
            image: null,
            currentImageUrl: n.image_url || null,
            galleryImages: [],
            currentGalleryImages: n.gallery_images || [],
            title: { es: n.title?.es || "", en: n.title?.en || "", pt: n.title?.pt || "" },
            excerpt: { es: n.excerpt?.es || "", en: n.excerpt?.en || "", pt: n.excerpt?.pt || "" },
            body: { es: n.body?.es || "", en: n.body?.en || "", pt: n.body?.pt || "" },
        });
        setLang("es");
        setErrors({});
        setShowForm(true);
    }

    function setLangField(field, lang, val) {
        setForm((current) => ({ ...current, [field]: { ...current[field], [lang]: val } }));
    }

    function handleSubmit(event) {
        event.preventDefault();
        const errs = {};
        if (!form.title.es.trim()) errs.title_es = "El título en español es obligatorio.";
        if (Object.keys(errs).length) {
            setErrors(errs);
            return;
        }

        setSaving(true);
        const fd = new FormData();
        fd.append("is_active", form.is_active ? "1" : "0");
        if (form.image) fd.append("image", form.image);
        form.galleryImages.forEach((file) => fd.append("gallery_images[]", file));

        LANGS.forEach((lang) => {
            fd.append(`category_${lang.code}`, form.category[lang.code] ?? "");
            fd.append(`title_${lang.code}`, form.title[lang.code] ?? "");
            fd.append(`excerpt_${lang.code}`, form.excerpt[lang.code] ?? "");
            fd.append(`body_${lang.code}`, form.body[lang.code] ?? "");
        });

        const url = editing ? `${baseUrl}/${editing}` : baseUrl;
        if (editing) fd.append("_method", "PUT");

        router.post(url, fd, {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                setShowForm(false);
                setSaving(false);
            },
            onError: (errors) => {
                setErrors(errors);
                setSaving(false);
            },
        });
    }

    function handleDelete(id) {
        if (confirm("¿Eliminar esta novedad?")) {
            router.delete(`${baseUrl}/${id}`, { preserveScroll: true });
        }
    }

    useEffect(() => {
        return () => {
            if (form.image instanceof File) {
                URL.revokeObjectURL(form.image);
            }
            form.galleryImages.forEach((file) => {
                if (file instanceof File) {
                    URL.revokeObjectURL(file);
                }
            });
        };
    }, [form.image, form.galleryImages]);

    function removeCurrentGalleryImage(imageId) {
        if (!confirm("¿Eliminar esta imagen adicional?")) return;

        router.delete(`${baseUrl}/images/${imageId}`, {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                setForm((current) => ({
                    ...current,
                    currentGalleryImages: current.currentGalleryImages.filter((image) => image.id !== imageId),
                }));
            },
        });
    }

    const lang = activeLang;

    return (
        <div className="max-w-6xl">
            <div className="mb-6 flex items-center justify-between">
                <div>
                    <h2 className="text-2xl font-bold text-gray-800">{title}</h2>
                    <p className="mt-1 text-sm text-gray-500">{description}</p>
                </div>
                <div className="flex items-center gap-3">
                    <div className="rounded-xl border border-gray-100 bg-white px-4 py-2 text-right shadow-sm">
                        <p className="text-[11px] font-semibold uppercase tracking-wide text-gray-400">Publicadas</p>
                        <p className="text-lg font-bold text-gray-800">{normalizedNovedades.length}</p>
                    </div>
                    <button
                        onClick={openNew}
                        className="inline-flex items-center gap-2 rounded-xl bg-[#25A7CA] px-5 py-2.5 text-sm font-medium text-white transition hover:bg-[#1d96b8]"
                    >
                        <Icon icon="solar:add-circle-outline" width={18} />
                        {createLabel}
                    </button>
                </div>
            </div>

            {showForm && (
                <div className="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-black/50 px-4 py-6">
                    <div className="w-full max-w-3xl rounded-2xl bg-white shadow-2xl">
                        <div className="flex items-center justify-between border-b border-gray-100 px-6 py-4">
                            <div>
                                <h3 className="text-base font-bold text-gray-800">{editing ? "Editar novedad" : "Nueva novedad"}</h3>
                                <p className="mt-0.5 text-xs text-gray-400">Completá al menos el español.</p>
                            </div>
                            <button onClick={() => setShowForm(false)} className="text-gray-400 transition hover:text-gray-600">
                                <Icon icon="solar:close-circle-outline" width={24} />
                            </button>
                        </div>

                        <form onSubmit={handleSubmit}>
                            <div className="space-y-5 p-6">
                                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <div className="flex items-center gap-3">
                                        <label className="flex cursor-pointer items-center gap-2">
                                            <input
                                                type="checkbox"
                                                checked={form.is_active}
                                                onChange={(e) => setForm((current) => ({ ...current, is_active: e.target.checked }))}
                                                className="h-4 w-4 accent-[#25A7CA]"
                                            />
                                            <span className="text-sm font-medium text-gray-700">Activa</span>
                                        </label>
                                    </div>
                                    <div>
                                        <label className="mb-1 block text-xs font-semibold text-gray-600">{form.currentImageUrl ? "Imagen principal" : "Imagen principal"}</label>
                                        {form.currentImageUrl && !form.image && (
                                            <div className="mb-2 aspect-video w-full overflow-hidden rounded-lg border border-gray-100 bg-gray-50">
                                                <img src={form.currentImageUrl} alt="Imagen actual" className="h-full w-full object-cover" />
                                            </div>
                                        )}
                                        {form.image && (
                                            <div className="mb-2 aspect-video w-full overflow-hidden rounded-lg border border-gray-100 bg-gray-50">
                                                <img src={URL.createObjectURL(form.image)} alt="Nueva imagen" className="h-full w-full object-cover" />
                                            </div>
                                        )}
                                        <input
                                            type="file"
                                            accept="image/*"
                                            onChange={(e) => setForm((current) => ({ ...current, image: e.target.files[0] || null }))}
                                            className="text-xs text-gray-600 file:mr-2 file:rounded-lg file:border-0 file:bg-[#25A7CA]/10 file:px-3 file:py-1 file:text-xs file:font-semibold file:text-[#25A7CA] hover:file:bg-[#25A7CA]/20"
                                        />
                                        <p className="mt-2 text-[11px] text-gray-400">Esta es la imagen principal de la card y del detalle.</p>
                                    </div>
                                </div>

                                <div className="rounded-xl border border-gray-100 bg-gray-50/60 p-4">
                                    <div className="mb-3 flex items-start justify-between gap-3">
                                        <div>
                                            <p className="text-sm font-semibold text-gray-700">Imágenes adicionales</p>
                                            <p className="text-xs text-gray-400">Podés sumar más imágenes para mostrar en el detalle público de la novedad.</p>
                                        </div>
                                        <span className="rounded-full bg-white px-2.5 py-1 text-xs font-medium text-gray-500 shadow-sm">
                                            {(form.currentGalleryImages?.length || 0) + (form.galleryImages?.length || 0)} imágenes
                                        </span>
                                    </div>

                                    {form.currentGalleryImages?.length > 0 && (
                                        <div className="mb-3 grid grid-cols-2 gap-3 sm:grid-cols-3">
                                            {form.currentGalleryImages.map((image) => (
                                                <div key={image.id} className="group relative aspect-video overflow-hidden rounded-lg border border-gray-100 bg-white">
                                                    <img src={image.image_url} alt="Imagen adicional" className="h-full w-full object-cover" />
                                                    <button
                                                        type="button"
                                                        onClick={() => removeCurrentGalleryImage(image.id)}
                                                        className="absolute right-1.5 top-1.5 rounded-lg bg-red-500 p-1 text-white opacity-0 transition group-hover:opacity-100"
                                                        title="Eliminar imagen"
                                                    >
                                                        <Icon icon="solar:trash-bin-trash-outline" width={12} />
                                                    </button>
                                                </div>
                                            ))}
                                        </div>
                                    )}

                                    {form.galleryImages?.length > 0 && (
                                        <div className="mb-3 grid grid-cols-2 gap-3 sm:grid-cols-3">
                                            {form.galleryImages.map((file, index) => (
                                                <div key={`${file.name}-${index}`} className="aspect-video overflow-hidden rounded-lg border border-dashed border-[#25A7CA]/30 bg-white">
                                                    <img src={URL.createObjectURL(file)} alt="Nueva imagen adicional" className="h-full w-full object-cover" />
                                                </div>
                                            ))}
                                        </div>
                                    )}

                                    <input
                                        type="file"
                                        multiple
                                        accept="image/*"
                                        onChange={(e) => setForm((current) => ({ ...current, galleryImages: Array.from(e.target.files || []) }))}
                                        className="text-xs text-gray-600 file:mr-2 file:rounded-lg file:border-0 file:bg-[#25A7CA]/10 file:px-3 file:py-1 file:text-xs file:font-semibold file:text-[#25A7CA] hover:file:bg-[#25A7CA]/20"
                                    />
                                    <p className="mt-2 text-[11px] text-gray-400">Si cargás una sola imagen adicional se verá grande. Si cargás varias, se listarán en el lateral del detalle.</p>
                                </div>

                                <div className="border-t border-gray-100" />

                                <div>
                                    <div className="mb-3 flex items-center justify-between">
                                        <span className="text-xs font-semibold uppercase tracking-wider text-gray-500">Contenido por idioma</span>
                                    </div>
                                    <LangTabs active={lang} onChange={setLang} errors={formErrors} />

                                    <div className="mb-4">
                                        <label className="mb-1 block text-xs font-semibold text-gray-600">Categoría</label>
                                        <input
                                            type="text"
                                            value={form.category[lang]}
                                            onChange={(e) => setLangField("category", lang, e.target.value)}
                                            placeholder={`Ej: Productos, Empresa… (${LANGS.find((item) => item.code === lang)?.label})`}
                                            className="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-[#25A7CA]/30"
                                        />
                                    </div>

                                    <div className="mb-4">
                                        <label className="mb-1 block text-xs font-semibold text-gray-600">Título {lang === "es" && <span className="text-red-500">*</span>}</label>
                                        <input
                                            type="text"
                                            value={form.title[lang]}
                                            onChange={(e) => setLangField("title", lang, e.target.value)}
                                            placeholder={`Título en ${LANGS.find((item) => item.code === lang)?.label}…`}
                                            className="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-[#25A7CA]/30"
                                        />
                                        {formErrors[`title_${lang}`] && <p className="mt-1 text-xs text-red-500">{formErrors[`title_${lang}`]}</p>}
                                    </div>

                                    <div className="mb-4">
                                        <label className="mb-1 block text-xs font-semibold text-gray-600">Extracto <span className="ml-1 font-normal text-gray-400">(resumen visible en la lista)</span></label>
                                        <textarea
                                            rows={3}
                                            value={form.excerpt[lang]}
                                            onChange={(e) => setLangField("excerpt", lang, e.target.value)}
                                            placeholder={`Breve descripción en ${LANGS.find((item) => item.code === lang)?.label}…`}
                                            className="w-full resize-none rounded-lg border border-gray-200 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-[#25A7CA]/30"
                                        />
                                    </div>

                                    <div>
                                        <label className="mb-1 block text-xs font-semibold text-gray-600">Cuerpo <span className="ml-1 font-normal text-gray-400">(contenido completo)</span></label>
                                        <RichEditor
                                            key={lang}
                                            value={form.body[lang]}
                                            onChange={(value) => setLangField("body", lang, value)}
                                            placeholder={`Contenido en ${LANGS.find((item) => item.code === lang)?.label}…`}
                                        />
                                    </div>
                                </div>
                            </div>

                            <div className="flex justify-end gap-3 rounded-b-2xl border-t border-gray-100 bg-gray-50 px-6 py-4">
                                <button
                                    type="button"
                                    onClick={() => setShowForm(false)}
                                    className="rounded-lg border border-gray-200 px-4 py-2 text-sm text-gray-600 transition hover:bg-gray-100"
                                >
                                    Cancelar
                                </button>
                                <button
                                    type="submit"
                                    disabled={saving}
                                    className="inline-flex items-center gap-2 rounded-xl bg-[#25A7CA] px-6 py-2.5 text-sm font-medium text-white transition hover:bg-[#1d96b8] disabled:opacity-60"
                                >
                                    {saving && <Icon icon="solar:refresh-outline" width={15} className="animate-spin" />}
                                    {saving ? "Guardando…" : "Guardar novedad"}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}

            <div className="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
                <table className="w-full text-sm">
                    <thead>
                        <tr className="bg-[#0D2038] text-left text-white">
                            <th className="w-16 px-4 py-3">Img</th>
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
                                <td colSpan={7} className="py-12 text-center text-gray-400">{emptyLabel}</td>
                            </tr>
                        )}
                        {normalizedNovedades.map((novedad) => {
                            const hasEn = !!novedad.title?.en;
                            const hasPt = !!novedad.title?.pt;

                            return (
                                <tr key={novedad.id} className="border-b border-gray-100 hover:bg-gray-50">
                                    <td className="px-4 py-2">
                                        {novedad.image_url ? (
                                            <div className="relative inline-flex">
                                                <img src={novedad.image_url} alt="" className="h-12 w-12 rounded-lg border border-gray-100 object-cover" />
                                                {novedad.gallery_images?.length > 0 && (
                                                    <span className="absolute -right-1.5 -top-1.5 rounded-full bg-[#25A7CA] px-1.5 py-0.5 text-[10px] font-bold text-white">
                                                        +{novedad.gallery_images.length}
                                                    </span>
                                                )}
                                            </div>
                                        ) : (
                                            <div className="flex h-12 w-12 items-center justify-center rounded-lg bg-gray-100">
                                                <Icon icon="solar:gallery-outline" width={18} className="text-gray-300" />
                                            </div>
                                        )}
                                    </td>
                                    <td className="max-w-xs truncate px-4 py-3 font-semibold text-gray-800">{novedad.title?.es || "—"}</td>
                                    <td className="px-4 py-3 text-gray-500">{novedad.category?.es || "—"}</td>
                                    <td className="px-4 py-3">
                                        <div className="flex gap-1">
                                            <span className="rounded bg-blue-100 px-1.5 py-0.5 text-xs font-semibold text-blue-700">ES</span>
                                            {hasEn && <span className="rounded bg-green-100 px-1.5 py-0.5 text-xs font-semibold text-green-700">EN</span>}
                                            {hasPt && <span className="rounded bg-yellow-100 px-1.5 py-0.5 text-xs font-semibold text-yellow-700">PT</span>}
                                        </div>
                                    </td>
                                    <td className="px-4 py-3">
                                        <span className={`rounded-full px-2 py-1 text-xs font-semibold ${novedad.is_active ? "bg-green-100 text-green-700" : "bg-gray-100 text-gray-500"}`}>
                                            {novedad.is_active ? "Activa" : "Inactiva"}
                                        </span>
                                    </td>
                                    <td className="px-4 py-3 text-xs text-gray-400">
                                        {novedad.created_at ? new Date(novedad.created_at).toLocaleDateString("es-AR") : "—"}
                                    </td>
                                    <td className="px-4 py-3">
                                        <div className="flex items-center justify-end gap-2">
                                            <button
                                                onClick={() => openEdit(novedad)}
                                                className="rounded-lg bg-blue-50 p-1.5 text-blue-600 transition hover:bg-blue-100"
                                                title="Editar"
                                            >
                                                <Icon icon="solar:pen-outline" width={15} />
                                            </button>
                                            <button
                                                onClick={() => handleDelete(novedad.id)}
                                                className="rounded-lg bg-red-50 p-1.5 text-red-500 transition hover:bg-red-100"
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
    );
}
