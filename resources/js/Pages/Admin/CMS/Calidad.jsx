import { useState } from "react";
import AdminLayout from "@/Layouts/AdminLayout";
import { useForm, router } from "@inertiajs/react";
import { Icon } from "@iconify/react";
import RichTextEditor from "@/Components/Admin/RichTextEditor";

const LANGS = ["es", "en", "pt"];
const LANG_COLORS = {
    es: "bg-red-50 text-red-700 border-red-200",
    en: "bg-blue-50 text-blue-700 border-blue-200",
    pt: "bg-green-50 text-green-700 border-green-200",
};

function LangBadge({ lang }) {
    return (
        <span className={`inline-block text-[10px] font-bold px-1.5 py-0.5 rounded border uppercase leading-none ${LANG_COLORS[lang]}`}>
            {lang}
        </span>
    );
}

function MultiInput({ label, field, data, setData, placeholder = "" }) {
    return (
        <div>
            <label className="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">{label}</label>
            <div className="grid grid-cols-3 gap-2">
                {LANGS.map((lang) => (
                    <div key={lang} className="flex flex-col gap-1">
                        <LangBadge lang={lang} />
                        <input
                            type="text"
                            value={data[`${field}_${lang}`] ?? ""}
                            onChange={(e) => setData(`${field}_${lang}`, e.target.value)}
                            placeholder={placeholder}
                            className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30"
                        />
                    </div>
                ))}
            </div>
        </div>
    );
}

function MultiRich({ label, field, data, setData }) {
    return (
        <div className="space-y-3">
            <label className="block text-xs font-semibold text-gray-500 uppercase tracking-wide">{label}</label>
            {LANGS.map((lang) => (
                <div key={lang} className="flex flex-col gap-1">
                    <LangBadge lang={lang} />
                    <RichTextEditor
                        key={`${field}_${lang}`}
                        value={data[`${field}_${lang}`] ?? ""}
                        onChange={(v) => setData(`${field}_${lang}`, v)}
                        placeholder={`Contenido en ${lang.toUpperCase()}...`}
                    />
                </div>
            ))}
        </div>
    );
}

function SectionForm({ initial = null, pageId, onCancel, onSuccess }) {
    const isNew = !initial;

    const { data, setData, post, processing, errors } = useForm(
        isNew
            ? {
                type: "text",
                title_es: "", title_en: "", title_pt: "",
                subtitle_es: "", subtitle_en: "", subtitle_pt: "",
                content_es: "", content_en: "", content_pt: "",
                button_text_es: "", button_text_en: "", button_text_pt: "",
                button_link: "",
                order: "A",
                is_active: true,
                image: null,
            }
            : {
                _method: "PUT",
                title_es: initial.title_es ?? "",
                title_en: initial.title_en ?? "",
                title_pt: initial.title_pt ?? "",
                subtitle_es: initial.subtitle_es ?? "",
                subtitle_en: initial.subtitle_en ?? "",
                subtitle_pt: initial.subtitle_pt ?? "",
                content_es: initial.content_es ?? "",
                content_en: initial.content_en ?? "",
                content_pt: initial.content_pt ?? "",
                button_text_es: initial.button_text_es ?? "",
                button_text_en: initial.button_text_en ?? "",
                button_text_pt: initial.button_text_pt ?? "",
                button_link: initial.button_link ?? "",
                order: initial.order ?? "A",
                is_active: initial.is_active ?? true,
                image: null,
            }
    );

    const [imgPreview, setImgPreview] = useState(initial?.image_url ?? null);

    const handleFile = (e) => {
        const file = e.target.files[0];
        if (!file) return;
        setData("image", file);
        setImgPreview(URL.createObjectURL(file));
    };

    const submit = (e) => {
        e.preventDefault();
        const url = isNew
            ? `/admin/pages/${pageId}/sections`
            : `/admin/sections/${initial.id}`;
        post(url, { forceFormData: true, onSuccess });
    };

    return (
        <form onSubmit={submit} className="space-y-6">
            {/* Imagen */}
            <div className="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                <h3 className="text-sm font-semibold text-gray-600 mb-3 flex items-center gap-2">
                    <Icon icon="solar:gallery-outline" width={15} className="text-[#25A7CA]" />
                    Imagen de sección
                </h3>
                <div className="flex items-center gap-4">
                    {imgPreview ? (
                        <img src={imgPreview} alt="" className="h-20 w-auto object-cover rounded-lg border border-gray-100" />
                    ) : (
                        <div className="h-20 w-24 rounded-lg border-2 border-dashed border-gray-200 flex items-center justify-center text-gray-300">
                            <Icon icon="solar:image-outline" width={24} />
                        </div>
                    )}
                    <label className="cursor-pointer inline-flex items-center gap-2 px-4 py-2 border border-gray-200 rounded-lg text-sm text-gray-600 hover:bg-gray-50 transition">
                        <Icon icon="solar:upload-outline" width={14} />
                        {imgPreview ? "Cambiar imagen" : "Subir imagen"}
                        <input type="file" accept="image/*" className="hidden" onChange={handleFile} />
                    </label>
                </div>
            </div>

            {/* Textos multilingual */}
            <div className="bg-white rounded-xl border border-gray-100 shadow-sm p-5 space-y-5">
                <h3 className="text-sm font-semibold text-gray-600 flex items-center gap-2">
                    <Icon icon="solar:translation-outline" width={15} className="text-[#25A7CA]" />
                    Contenido multilenguaje
                </h3>

                <MultiInput label="Título" field="title" data={data} setData={setData} placeholder="Ej: Calidad Gran Sasso" />
                <MultiInput label="Subtítulo" field="subtitle" data={data} setData={setData} placeholder="Ej: Estándares internacionales" />
                <MultiRich label="Contenido (texto enriquecido)" field="content" data={data} setData={setData} />
                <MultiInput label="Texto del botón" field="button_text" data={data} setData={setData} placeholder="Ej: Más información" />
            </div>

            {/* Config */}
            <div className="bg-white rounded-xl border border-gray-100 shadow-sm p-5 space-y-4">
                <h3 className="text-sm font-semibold text-gray-600">Configuración</h3>
                <div>
                    <label className="block text-xs text-gray-500 mb-1">URL del botón</label>
                    <input
                        type="text"
                        value={data.button_link}
                        onChange={(e) => setData("button_link", e.target.value)}
                        placeholder="/calidad"
                        className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30"
                    />
                </div>
                {isNew && (
                    <div>
                        <label className="block text-xs text-gray-500 mb-1">Tipo</label>
                        <input
                            type="text"
                            value={data.type}
                            onChange={(e) => setData("type", e.target.value)}
                            placeholder="texto, hero, cta…"
                            className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30"
                        />
                    </div>
                )}
                <div className="flex items-end gap-4">
                    <div>
                        <label className="block text-xs text-gray-500 mb-1">
                            Orden <span className="text-gray-400">(A, AB, B…)</span>
                        </label>
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

            {/* Acciones */}
            <div className="flex justify-end gap-3">
                <button
                    type="button"
                    onClick={onCancel}
                    className="px-4 py-2 border border-gray-200 rounded-lg text-sm text-gray-600 hover:bg-gray-50 transition"
                >
                    Cancelar
                </button>
                <button
                    type="submit"
                    disabled={processing}
                    className="inline-flex items-center gap-2 px-5 py-2 bg-[#25A7CA] text-white rounded-lg text-sm font-medium hover:bg-[#1d96b8] disabled:opacity-60 transition"
                >
                    <Icon icon={processing ? "solar:refresh-outline" : "solar:diskette-outline"} width={15} />
                    {processing ? "Guardando..." : (isNew ? "Agregar sección" : "Guardar cambios")}
                </button>
            </div>
        </form>
    );
}

function SectionCard({ section, onDelete }) {
    const [editing, setEditing] = useState(false);

    if (editing) {
        return (
            <div className="bg-white rounded-xl border border-[#25A7CA]/30 shadow-sm p-5">
                <div className="flex items-center justify-between mb-4">
                    <h3 className="font-semibold text-gray-700">Editando: {section.title_es || section.type}</h3>
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
            {/* Orden */}
            <div className="w-8 text-center flex-shrink-0">
                <span className="text-xs font-mono font-bold text-gray-400">{section.order}</span>
            </div>

            {/* Imagen preview */}
            {section.image_url && (
                <img src={section.image_url} alt="" className="h-14 w-20 object-cover rounded-lg flex-shrink-0 border border-gray-100" />
            )}

            {/* Info */}
            <div className="flex-1 min-w-0">
                <div className="flex items-center gap-2 mb-1">
                    <span className="text-xs bg-[#25A7CA]/10 text-[#25A7CA] px-2 py-0.5 rounded-full font-medium">{section.type}</span>
                    <span className={`text-xs px-2 py-0.5 rounded-full font-medium ${section.is_active ? "bg-green-100 text-green-700" : "bg-gray-100 text-gray-500"}`}>
                        {section.is_active ? "Activo" : "Inactivo"}
                    </span>
                </div>
                <p className="font-semibold text-gray-800 truncate">{section.title_es || "(Sin título)"}</p>
                {section.content_es && (
                    <p
                        className="text-xs text-gray-400 mt-0.5 line-clamp-2"
                        dangerouslySetInnerHTML={{ __html: section.content_es }}
                    />
                )}
            </div>

            {/* Acciones */}
            <div className="flex items-center gap-1 flex-shrink-0">
                <button
                    onClick={() => setEditing(true)}
                    className="p-2 text-gray-400 hover:text-[#25A7CA] hover:bg-blue-50 rounded-lg transition"
                    title="Editar"
                >
                    <Icon icon="solar:pen-outline" width={16} />
                </button>
                <button
                    onClick={() => onDelete(section.id)}
                    className="p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition"
                    title="Eliminar"
                >
                    <Icon icon="solar:trash-bin-trash-outline" width={16} />
                </button>
            </div>
        </div>
    );
}

export default function Calidad({ page, sections }) {
    const [showCreate, setShowCreate] = useState(false);

    const deleteSection = (id) => {
        if (!confirm("¿Eliminar esta sección?")) return;
        router.delete(`/admin/sections/${id}`);
    };

    return (
        <AdminLayout>
            <div className="max-w-4xl">
                {/* Header */}
                <div className="flex items-center justify-between mb-6">
                    <div>
                        <h1 className="text-2xl font-bold text-gray-800">CMS – Calidad</h1>
                        <p className="text-sm text-gray-500">Gestión de secciones de la página "Calidad"</p>
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

                {/* Formulario crear */}
                {showCreate && (
                    <div className="mb-6 bg-white rounded-xl border border-dashed border-[#25A7CA]/50 p-5">
                        <div className="flex items-center justify-between mb-4">
                            <h3 className="font-semibold text-gray-700 flex items-center gap-2">
                                <Icon icon="solar:add-circle-outline" width={17} className="text-[#25A7CA]" />
                                Nueva sección
                            </h3>
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

                {/* Lista de secciones */}
                {sections.length === 0 && !showCreate ? (
                    <div className="bg-white rounded-xl border border-dashed border-gray-300 p-12 text-center">
                        <Icon icon="solar:document-text-outline" width={40} className="text-gray-300 mx-auto mb-3" />
                        <p className="text-gray-400 text-sm">No hay secciones todavía.</p>
                        <button
                            onClick={() => setShowCreate(true)}
                            className="mt-3 text-sm text-[#25A7CA] hover:underline"
                        >
                            Agregar primera sección
                        </button>
                    </div>
                ) : (
                    <div className="space-y-3">
                        {sections.map((s) => (
                            <SectionCard key={s.id} section={s} onDelete={deleteSection} />
                        ))}
                    </div>
                )}
            </div>
        </AdminLayout>
    );
}
