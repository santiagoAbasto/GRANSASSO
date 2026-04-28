import { useState, useRef } from "react";
import AdminLayout from "@/Layouts/AdminLayout";
import { useForm, router } from "@inertiajs/react";
import { Icon } from "@iconify/react";
import RichTextEditor from "@/Components/Admin/RichTextEditor";
import ImageUploadField from "@/Components/Admin/ImageUploadField";

const LANGS = ["es", "en", "pt"];
const LANG_LABELS = { es: "🇦🇷 ES", en: "🇺🇸 EN", pt: "🇧🇷 PT" };

/* ─────────────────────────────────────────────────────────────────────────
   BANNER SECTION — gestión especial de imágenes slider
───────────────────────────────────────────────────────────────────────── */
function BannerSection({ section }) {
    const { data, setData, post, processing } = useForm({ image: null });
    const fileRef = useRef(null);
    const [preview, setPreview] = useState(null);

    const handleFile = (e) => {
        const file = e.target.files[0];
        if (!file) return;
        setData("image", file);
        setPreview(URL.createObjectURL(file));
    };

    const submit = (e) => {
        e.preventDefault();
        if (!data.image) return;
        post(`/admin/sections/${section.id}/images`, {
            forceFormData: true,
            onSuccess: () => {
                setData("image", null);
                setPreview(null);
                if (fileRef.current) fileRef.current.value = "";
            },
        });
    };

    const deleteImage = (id) => {
        if (!confirm("¿Eliminar esta imagen del banner?")) return;
        router.delete(`/admin/section-images/${id}`);
    };

    const images = section.images ?? [];

    return (
        <div className="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            {/* Header */}
            <div className="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <div className="flex items-center gap-2">
                    <div className="flex items-center justify-center w-8 h-8 rounded-lg bg-[#25A7CA]/10">
                        <Icon icon="solar:gallery-wide-outline" width={16} className="text-[#25A7CA]" />
                    </div>
                    <div>
                        <p className="text-sm font-semibold text-gray-800">Banner principal</p>
                        <p className="text-xs text-gray-400">
                            {images.length === 0
                                ? "Sin imágenes — el banner no se mostrará"
                                : images.length === 1
                                ? "1 imagen — banner estático (sin indicadores)"
                                : `${images.length} imágenes — carrusel automático con indicadores`}
                        </p>
                    </div>
                </div>
                <span className={`text-xs px-2 py-0.5 rounded-full font-medium ${section.is_active ? "bg-green-100 text-green-700" : "bg-gray-100 text-gray-500"}`}>
                    {section.is_active ? "Activo" : "Inactivo"}
                </span>
            </div>

            <div className="p-5">
                {/* Grid de imágenes actuales */}
                {images.length === 0 ? (
                    <div className="flex items-center justify-center h-28 rounded-xl border-2 border-dashed border-gray-200 text-gray-300 mb-4">
                        <div className="text-center">
                            <Icon icon="solar:gallery-outline" width={28} className="mx-auto mb-1" />
                            <p className="text-xs">Sin imágenes todavía</p>
                        </div>
                    </div>
                ) : (
                    <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 mb-4">
                        {images.map((img) => (
                            <div key={img.id} className="relative group rounded-xl overflow-hidden border border-gray-100" style={{ aspectRatio: "16/7" }}>
                                <img src={img.image_url} alt="" className="w-full h-full object-cover" />
                                <div className="absolute top-1.5 left-1.5 bg-black/50 text-white text-[10px] font-bold px-1.5 py-0.5 rounded">
                                    #{img.order}
                                </div>
                                <button
                                    onClick={() => deleteImage(img.id)}
                                    className="absolute top-1.5 right-1.5 p-1 rounded-lg bg-red-500 text-white opacity-0 group-hover:opacity-100 transition"
                                    title="Eliminar"
                                >
                                    <Icon icon="solar:trash-bin-trash-outline" width={13} />
                                </button>
                            </div>
                        ))}
                    </div>
                )}

                {/* Upload nueva imagen */}
                <form onSubmit={submit} className="flex flex-col sm:flex-row items-start sm:items-end gap-3 p-4 bg-gray-50 rounded-xl border border-dashed border-gray-200">
                    <div className="flex-1">
                        <label className="block text-xs font-medium text-gray-500 mb-1">Agregar imagen al banner</label>
                        <p className="text-xs text-gray-400 mb-2">Recomendado: 1920×521 px · JPG/PNG · máx 5 MB</p>
                        <input
                            ref={fileRef}
                            type="file"
                            accept="image/*"
                            onChange={handleFile}
                            className="block w-full text-sm text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-[#25A7CA]/10 file:text-[#25A7CA] hover:file:bg-[#25A7CA]/20"
                        />
                        {preview && (
                            <img src={preview} className="mt-2 h-16 rounded-lg object-cover border border-gray-100" alt="preview" />
                        )}
                    </div>
                    <button
                        type="submit"
                        disabled={!data.image || processing}
                        className="inline-flex items-center gap-1.5 px-4 py-2 bg-[#25A7CA] text-white rounded-lg text-sm font-medium hover:bg-[#1d96b8] disabled:opacity-40 transition whitespace-nowrap"
                    >
                        <Icon icon={processing ? "solar:refresh-outline" : "solar:add-circle-outline"} width={15} className={processing ? "animate-spin" : ""} />
                        {processing ? "Subiendo..." : "Agregar imagen"}
                    </button>
                </form>
            </div>
        </div>
    );
}

/* ─────────────────────────────────────────────────────────────────────────
   SECTION FORM — tabs ES/EN/PT con rich text
───────────────────────────────────────────────────────────────────────── */
function SectionForm({ initial = null, pageId, onCancel, onSuccess }) {
    const isNew = !initial;
    const [activeLang, setActiveLang] = useState("es");

    const { data, setData, post, processing, errors } = useForm(
        isNew
            ? {
                type: "about_main",
                title_es: "", title_en: "", title_pt: "",
                subtitle_es: "", subtitle_en: "", subtitle_pt: "",
                content_es: "", content_en: "", content_pt: "",
                button_text_es: "", button_text_en: "", button_text_pt: "",
                button_link: "", order: "A", is_active: true, image: null,
            }
            : {
                _method:        "PUT",
                title_es:       initial.title_es       ?? "",
                title_en:       initial.title_en       ?? "",
                title_pt:       initial.title_pt       ?? "",
                subtitle_es:    initial.subtitle_es    ?? "",
                subtitle_en:    initial.subtitle_en    ?? "",
                subtitle_pt:    initial.subtitle_pt    ?? "",
                content_es:     initial.content_es     ?? "",
                content_en:     initial.content_en     ?? "",
                content_pt:     initial.content_pt     ?? "",
                button_text_es: initial.button_text_es ?? "",
                button_text_en: initial.button_text_en ?? "",
                button_text_pt: initial.button_text_pt ?? "",
                button_link:    initial.button_link    ?? "",
                order:          initial.order          ?? "A",
                is_active:      initial.is_active      ?? true,
                image:          null,
            }
    );

    const submit = (e) => {
        e.preventDefault();
        const url = isNew
            ? `/admin/pages/${pageId}/sections`
            : `/admin/sections/${initial.id}`;
        post(url, { forceFormData: true, onSuccess });
    };

    return (
        <form onSubmit={submit} className="space-y-5">
            {/* Imagen — solo para Quiénes somos */}
            {(isNew ? data.type === "about_main" : initial?.type === "about_main") && (
                <div className="bg-white rounded-xl border border-[#25A7CA]/40 shadow-sm p-5">
                    <p className="text-sm font-semibold text-gray-700 mb-1">Imagen de sección</p>
                    <p className="text-xs text-gray-400 mb-3">Aparece a la izquierda del texto en la página web.</p>
                    <ImageUploadField
                        currentUrl={initial?.image_url}
                        onChange={(f) => setData("image", f)}
                        specs={{ width: 600, height: 600, maxMB: 4, formats: ["JPG", "PNG"] }}
                        error={errors.image}
                    />
                    {!initial?.image_url && !data.image && (
                        <p className="mt-2 text-xs text-amber-600 flex items-center gap-1">
                            <Icon icon="solar:danger-triangle-outline" width={13} />
                            Sin imagen — seleccioná un archivo para asignarla.
                        </p>
                    )}
                </div>
            )}

            {/* Tabs multiidioma */}
            <div className="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <div className="flex border-b border-gray-100">
                    {LANGS.map((lang) => (
                        <button
                            key={lang}
                            type="button"
                            onClick={() => setActiveLang(lang)}
                            className={`flex-1 py-2.5 text-xs font-semibold transition ${
                                activeLang === lang
                                    ? "bg-[#25A7CA]/5 text-[#25A7CA] border-b-2 border-[#25A7CA]"
                                    : "text-gray-500 hover:bg-gray-50"
                            }`}
                        >
                            {LANG_LABELS[lang]}
                        </button>
                    ))}
                </div>

                {LANGS.map((lang) => (
                    <div key={lang} className={`p-5 space-y-4 ${activeLang !== lang ? "hidden" : ""}`}>
                        <div>
                            <label className="block text-xs font-medium text-gray-500 mb-1">
                                Título {lang === "es" && <span className="text-red-400">*</span>}
                            </label>
                            <input
                                type="text"
                                value={data[`title_${lang}`]}
                                onChange={(e) => setData(`title_${lang}`, e.target.value)}
                                placeholder="Ej: Quiénes somos"
                                className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30"
                            />
                        </div>
                        <div>
                            <label className="block text-xs font-medium text-gray-500 mb-1">Subtítulo</label>
                            <input
                                type="text"
                                value={data[`subtitle_${lang}`]}
                                onChange={(e) => setData(`subtitle_${lang}`, e.target.value)}
                                placeholder="Ej: 60 años de trayectoria"
                                className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30"
                            />
                        </div>
                        <div>
                            <label className="block text-xs font-medium text-gray-500 mb-1">Contenido</label>
                            <RichTextEditor
                                value={data[`content_${lang}`]}
                                onChange={(v) => setData(`content_${lang}`, v)}
                                placeholder={`Contenido en ${lang.toUpperCase()}...`}
                            />
                        </div>
                        <div>
                            <label className="block text-xs font-medium text-gray-500 mb-1">Texto del botón</label>
                            <input
                                type="text"
                                value={data[`button_text_${lang}`]}
                                onChange={(e) => setData(`button_text_${lang}`, e.target.value)}
                                placeholder="Ej: Ver más"
                                className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30"
                            />
                        </div>
                    </div>
                ))}
            </div>

            {/* Configuración */}
            <div className="bg-white rounded-xl border border-gray-100 shadow-sm p-5 space-y-4">
                <p className="text-xs font-semibold text-gray-500 uppercase tracking-wide">Configuración</p>
                <div>
                    <label className="block text-xs text-gray-500 mb-1">URL del botón</label>
                    <input
                        type="text"
                        value={data.button_link}
                        onChange={(e) => setData("button_link", e.target.value)}
                        placeholder="/empresa"
                        className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30"
                    />
                </div>
                {isNew && (
                    <div>
                        <label className="block text-xs text-gray-500 mb-1">Tipo de sección</label>
                        <select
                            value={data.type}
                            onChange={(e) => setData("type", e.target.value)}
                            className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30 bg-white"
                        >
                            <option value="about_main">Quiénes somos</option>
                            <option value="about_mision">Misión</option>
                            <option value="about_vision">Visión</option>
                            <option value="about_calidad">Valores / Calidad</option>
                        </select>
                    </div>
                )}
                <div className="flex items-end gap-4">
                    <div>
                        <label className="block text-xs text-gray-500 mb-1">Orden</label>
                        <input
                            type="text"
                            maxLength={20}
                            value={data.order}
                            onChange={(e) => setData("order", e.target.value.toUpperCase())}
                            placeholder="A"
                            className="w-20 border border-gray-200 rounded-lg px-3 py-2 text-sm font-mono uppercase focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30"
                        />
                    </div>
                    <label className="flex items-center gap-2 cursor-pointer mb-2">
                        <input
                            type="checkbox"
                            checked={data.is_active}
                            onChange={(e) => setData("is_active", e.target.checked)}
                            className="w-4 h-4 rounded accent-[#25A7CA]"
                        />
                        <span className="text-sm text-gray-700">Activo</span>
                    </label>
                </div>
            </div>

            <div className="flex justify-end gap-3">
                <button type="button" onClick={onCancel} className="px-4 py-2 border border-gray-200 rounded-lg text-sm text-gray-600 hover:bg-gray-50 transition">
                    Cancelar
                </button>
                <button type="submit" disabled={processing} className="inline-flex items-center gap-2 px-5 py-2 bg-[#25A7CA] text-white rounded-lg text-sm font-medium hover:bg-[#1d96b8] disabled:opacity-60 transition">
                    <Icon icon={processing ? "solar:refresh-outline" : "solar:diskette-outline"} width={15} className={processing ? "animate-spin" : ""} />
                    {processing ? "Guardando..." : isNew ? "Crear sección" : "Guardar cambios"}
                </button>
            </div>
        </form>
    );
}

/* ─────────────────────────────────────────────────────────────────────────
   SECTION CARD
───────────────────────────────────────────────────────────────────────── */
const TYPE_LABELS = {
    about_main:    "Quiénes somos",
    about_mision:  "Misión",
    about_vision:  "Visión",
    about_calidad: "Valores / Calidad",
};

function SectionCard({ section, onDelete }) {
    const [editing, setEditing] = useState(false);

    if (editing) {
        return (
            <div className="bg-white rounded-xl border border-[#25A7CA]/30 shadow-sm p-5">
                <div className="flex items-center justify-between mb-4">
                    <p className="font-semibold text-gray-700 text-sm">
                        Editando: {TYPE_LABELS[section.type] ?? section.type}
                    </p>
                    <button onClick={() => setEditing(false)} className="text-gray-400 hover:text-gray-600">
                        <Icon icon="solar:close-circle-outline" width={20} />
                    </button>
                </div>
                <SectionForm
                    initial={section}
                    onCancel={() => setEditing(false)}
                    onSuccess={() => setEditing(false)}
                />
            </div>
        );
    }

    return (
        <div className="bg-white rounded-xl border border-gray-100 shadow-sm p-5 flex items-start gap-4">
            <div className="w-8 text-center flex-shrink-0 mt-0.5">
                <span className="text-xs font-mono font-bold text-gray-400">{section.order}</span>
            </div>
            {/* Imagen solo para Quiénes somos (about_main) */}
            {section.type === "about_main" && (
                section.image_url ? (
                    <img src={section.image_url} alt="" className="h-14 w-20 object-cover rounded-lg flex-shrink-0 border border-gray-100" />
                ) : (
                    <div className="h-14 w-20 rounded-lg flex-shrink-0 border-2 border-dashed border-amber-300 bg-amber-50 flex items-center justify-center">
                        <Icon icon="solar:camera-add-outline" width={18} className="text-amber-400" />
                    </div>
                )
            )}
            <div className="flex-1 min-w-0">
                <div className="flex items-center gap-2 mb-1">
                    <span className="text-xs bg-[#25A7CA]/10 text-[#25A7CA] px-2 py-0.5 rounded-full font-medium">
                        {TYPE_LABELS[section.type] ?? section.type}
                    </span>
                    <span className={`text-xs px-2 py-0.5 rounded-full font-medium ${section.is_active ? "bg-green-100 text-green-700" : "bg-gray-100 text-gray-500"}`}>
                        {section.is_active ? "Activo" : "Inactivo"}
                    </span>
                </div>
                <p className="font-semibold text-gray-800 truncate">{section.title_es || "(Sin título)"}</p>
                {section.content_es && (
                    <p className="text-xs text-gray-400 mt-0.5 line-clamp-2" dangerouslySetInnerHTML={{ __html: section.content_es }} />
                )}
            </div>
            <div className="flex items-center gap-1 flex-shrink-0">
                <button onClick={() => setEditing(true)} className="p-2 text-gray-400 hover:text-[#25A7CA] hover:bg-blue-50 rounded-lg transition" title="Editar">
                    <Icon icon="solar:pen-outline" width={16} />
                </button>
                <button onClick={() => onDelete(section.id)} className="p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition" title="Eliminar">
                    <Icon icon="solar:trash-bin-trash-outline" width={16} />
                </button>
            </div>
        </div>
    );
}

/* ─────────────────────────────────────────────────────────────────────────
   PAGE PRINCIPAL
───────────────────────────────────────────────────────────────────────── */
export default function Empresa({ page, sections }) {
    const [showCreate, setShowCreate] = useState(false);

    const banner          = sections.find((s) => s.type === "banner");
    const contentSections = sections.filter((s) => s.type !== "banner");

    const deleteSection = (id) => {
        if (!confirm("¿Eliminar esta sección?")) return;
        router.delete(`/admin/sections/${id}`);
    };

    return (
        <AdminLayout>
            <div className="max-w-4xl space-y-6">

                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold text-gray-800">Empresa</h1>
                        <p className="text-sm text-gray-500">Editá el contenido de la página Empresa.</p>
                    </div>
                    {!showCreate && (
                        <button
                            onClick={() => setShowCreate(true)}
                            className="inline-flex items-center gap-2 px-4 py-2 bg-[#25A7CA] text-white rounded-lg text-sm font-medium hover:bg-[#1d96b8] transition"
                        >
                            <Icon icon="solar:add-circle-outline" width={17} />
                            Nueva sección
                        </button>
                    )}
                </div>

                {/* Banner */}
                {banner && <BannerSection section={banner} />}

                {/* Crear sección */}
                {showCreate && (
                    <div className="bg-white rounded-xl border border-dashed border-[#25A7CA]/50 p-5">
                        <div className="flex items-center justify-between mb-4">
                            <p className="font-semibold text-gray-700 flex items-center gap-2">
                                <Icon icon="solar:add-circle-outline" width={17} className="text-[#25A7CA]" />
                                Nueva sección
                            </p>
                            <button onClick={() => setShowCreate(false)} className="text-gray-400 hover:text-gray-600">
                                <Icon icon="solar:close-circle-outline" width={20} />
                            </button>
                        </div>
                        <SectionForm
                            pageId={page.id}
                            onCancel={() => setShowCreate(false)}
                            onSuccess={() => setShowCreate(false)}
                        />
                    </div>
                )}

                {/* Secciones de contenido */}
                {contentSections.length === 0 && !showCreate ? (
                    <div className="bg-white rounded-xl border border-dashed border-gray-300 p-12 text-center">
                        <Icon icon="solar:document-text-outline" width={40} className="text-gray-300 mx-auto mb-3" />
                        <p className="text-gray-400 text-sm">No hay secciones de contenido.</p>
                        <button onClick={() => setShowCreate(true)} className="mt-3 text-sm text-[#25A7CA] hover:underline">
                            Agregar primera sección
                        </button>
                    </div>
                ) : (
                    <div className="space-y-3">
                        {contentSections.map((s) => (
                            <SectionCard key={s.id} section={s} onDelete={deleteSection} />
                        ))}
                    </div>
                )}

            </div>
        </AdminLayout>
    );
}
