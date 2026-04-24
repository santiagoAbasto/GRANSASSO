import { useState } from "react";
import AdminLayout from "@/Layouts/AdminLayout";
import { useForm, router } from "@inertiajs/react";
import { Icon } from "@iconify/react";
import ImageUploadField from "@/Components/Admin/ImageUploadField";

function getYouTubeId(url) {
    if (!url) return null;
    const m = url.match(
        /(?:youtube\.com\/(?:[^/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?/\s]{11})/
    );
    return m ? m[1] : null;
}

/* ── Tarjeta de una diapositiva existente (con edición inline) ── */
function SlideCard({ slide, onDelete }) {
    const [editing, setEditing] = useState(false);
    const isVideo = !!slide.video_url;
    const ytId    = isVideo ? getYouTubeId(slide.video_url) : null;
    const thumb   = isVideo
        ? (ytId ? `https://img.youtube.com/vi/${ytId}/hqdefault.jpg` : null)
        : slide.image_url;

    const { data, setData, patch, processing, errors, reset } = useForm({
        order:     slide.order ?? "",
        video_url: slide.video_url ?? "",
        image:     null,
    });

    const handleSave = (e) => {
        e.preventDefault();
        patch(`/admin/section-images/${slide.id}`, {
            forceFormData: true,
            onSuccess: () => setEditing(false),
        });
    };

    return (
        <div className="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            {/* Miniatura */}
            <div className="relative h-36 bg-gray-100">
                {thumb ? (
                    <img src={thumb} alt="" className="w-full h-full object-cover" />
                ) : (
                    <div className="w-full h-full flex items-center justify-center text-gray-300">
                        <Icon icon="solar:gallery-outline" width={32} />
                    </div>
                )}
                {isVideo && (
                    <div className="absolute inset-0 flex items-center justify-center bg-black/30">
                        <Icon icon="solar:play-circle-bold" width={36} className="text-white" />
                    </div>
                )}
                <span className={`absolute top-2 left-2 text-[10px] px-2 py-0.5 rounded-full font-semibold uppercase tracking-wide ${
                    isVideo ? "bg-purple-100 text-purple-700" : "bg-blue-100 text-blue-700"
                }`}>
                    {isVideo ? "Video" : "Imagen"}
                </span>
                {/* Badge de orden alfanumérico */}
                <span className="absolute top-2 right-2 bg-black/40 text-white text-xs px-2 py-0.5 rounded-full font-mono font-bold tracking-widest">
                    {slide.order}
                </span>
            </div>

            {/* Formulario de edición inline */}
            {editing ? (
                <form onSubmit={handleSave} className="p-3 space-y-3 border-t border-gray-100">
                    {/* Orden */}
                    <div>
                        <label className="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">
                            Orden (ej: A, B, AA, AB…)
                        </label>
                        <input
                            type="text"
                            value={data.order}
                            onChange={(e) => setData("order", e.target.value.toUpperCase())}
                            maxLength={20}
                            placeholder="A"
                            className="w-full border border-gray-200 rounded-lg px-3 py-1.5 text-sm font-mono uppercase focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30"
                        />
                        {errors.order && (
                            <p className="text-red-500 text-xs mt-1">{errors.order}</p>
                        )}
                    </div>

                    {/* Video URL (solo si es video) */}
                    {isVideo && (
                        <div>
                            <label className="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">
                                URL de YouTube
                            </label>
                            <input
                                type="text"
                                value={data.video_url}
                                onChange={(e) => setData("video_url", e.target.value)}
                                placeholder="https://www.youtube.com/watch?v=..."
                                className="w-full border border-gray-200 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30"
                            />
                            {errors.video_url && (
                                <p className="text-red-500 text-xs mt-1">{errors.video_url}</p>
                            )}
                        </div>
                    )}

                    {/* Reemplazar imagen (solo si es imagen) */}
                    {!isVideo && (
                        <div>
                            <label className="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">
                                Reemplazar imagen (opcional)
                            </label>
                            <ImageUploadField
                                onChange={(f) => setData("image", f)}
                                specs={{ width: 1366, height: 400, maxMB: 5, formats: ["JPG", "PNG"] }}
                                error={errors.image}
                            />
                        </div>
                    )}

                    {/* Acciones */}
                    <div className="flex items-center justify-end gap-2 pt-1 border-t border-gray-100">
                        <button
                            type="button"
                            onClick={() => { reset(); setEditing(false); }}
                            className="px-3 py-1.5 text-sm text-gray-500 hover:text-gray-700 rounded-lg hover:bg-gray-50 transition"
                        >
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            disabled={processing}
                            className="inline-flex items-center gap-1.5 px-4 py-1.5 bg-[#25A7CA] text-white rounded-lg text-sm font-medium hover:bg-[#1d96b8] disabled:opacity-60 transition"
                        >
                            <Icon icon={processing ? "solar:refresh-outline" : "solar:check-circle-outline"} width={14} />
                            {processing ? "Guardando…" : "Guardar"}
                        </button>
                    </div>
                </form>
            ) : (
                <div className="p-3 flex justify-end gap-2">
                    <button
                        onClick={() => setEditing(true)}
                        className="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-gray-50 hover:bg-gray-100 text-sm text-gray-600 transition"
                    >
                        <Icon icon="solar:pen-outline" width={14} />
                        Editar
                    </button>
                    <button
                        onClick={() => onDelete(slide.id)}
                        className="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-red-50 hover:bg-red-100 text-sm text-red-600 transition"
                    >
                        <Icon icon="solar:trash-bin-trash-outline" width={14} />
                        Eliminar
                    </button>
                </div>
            )}
        </div>
    );
}

/* ── Formulario para agregar una diapositiva nueva ── */
function AddSlideForm({ sectionId, onCancel }) {
    const [type, setType] = useState("image");
    const { data, setData, post, processing, errors, reset } = useForm({
        image:     null,
        video_url: "",
        order:     "",
    });

    const submit = (e) => {
        e.preventDefault();
        post(`/admin/sections/${sectionId}/images`, {
            forceFormData: true,
            onSuccess: () => { reset(); onCancel(); },
        });
    };

    return (
        <form onSubmit={submit} className="bg-white rounded-xl border border-[#25A7CA]/30 shadow-sm p-5 space-y-4">
            <h3 className="font-semibold text-gray-700 text-sm">Nueva diapositiva</h3>

            {/* Tipo */}
            <div className="flex gap-2">
                <button
                    type="button"
                    onClick={() => { setType("image"); setData("video_url", ""); }}
                    className={`flex items-center gap-2 px-3 py-1.5 rounded-lg text-sm font-medium transition ${
                        type === "image"
                            ? "bg-[#25A7CA] text-white"
                            : "bg-gray-100 text-gray-600 hover:bg-gray-200"
                    }`}
                >
                    <Icon icon="solar:image-outline" width={15} />
                    Imagen
                </button>
                <button
                    type="button"
                    onClick={() => { setType("video"); setData("image", null); }}
                    className={`flex items-center gap-2 px-3 py-1.5 rounded-lg text-sm font-medium transition ${
                        type === "video"
                            ? "bg-[#25A7CA] text-white"
                            : "bg-gray-100 text-gray-600 hover:bg-gray-200"
                    }`}
                >
                    <Icon icon="solar:play-circle-outline" width={15} />
                    Video YouTube
                </button>
            </div>

            {/* Campo según tipo */}
            {type === "image" ? (
                <ImageUploadField
                    onChange={(f) => setData("image", f)}
                    specs={{ width: 1366, height: 400, maxMB: 5, formats: ["JPG", "PNG"] }}
                    error={errors.image}
                />
            ) : (
                <div>
                    <label className="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">
                        URL de YouTube
                    </label>
                    <input
                        type="text"
                        value={data.video_url}
                        onChange={(e) => setData("video_url", e.target.value)}
                        placeholder="https://www.youtube.com/watch?v=..."
                        className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30"
                    />
                    {errors.video_url && (
                        <p className="text-red-500 text-xs mt-1">{errors.video_url}</p>
                    )}
                </div>
            )}

            {/* Orden manual (opcional) */}
            <div>
                <label className="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">
                    Orden <span className="text-gray-400 normal-case font-normal">(opcional — se asigna automáticamente)</span>
                </label>
                <input
                    type="text"
                    value={data.order}
                    onChange={(e) => setData("order", e.target.value.toUpperCase())}
                    maxLength={20}
                    placeholder="Ej: A, B, AA, AB…"
                    className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm font-mono uppercase focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30"
                />
            </div>

            <div className="flex items-center justify-end gap-3 pt-2 border-t border-gray-100">
                <button
                    type="button"
                    onClick={onCancel}
                    className="px-4 py-2 text-sm text-gray-500 hover:text-gray-700 rounded-lg hover:bg-gray-50 transition"
                >
                    Cancelar
                </button>
                <button
                    type="submit"
                    disabled={processing}
                    className="inline-flex items-center gap-2 px-5 py-2 bg-[#25A7CA] text-white rounded-lg text-sm font-medium hover:bg-[#1d96b8] disabled:opacity-60 transition"
                >
                    <Icon
                        icon={processing ? "solar:refresh-outline" : "solar:add-circle-outline"}
                        width={15}
                    />
                    {processing ? "Agregando..." : "Agregar diapositiva"}
                </button>
            </div>
        </form>
    );
}

/* ── Página principal ── */
export default function ProductsBanner({ section }) {
    const [showAdd, setShowAdd] = useState(false);
    const slides = section?.slides ?? [];

    const handleDelete = (id) => {
        if (!confirm("¿Eliminar esta diapositiva?")) return;
        router.delete(`/admin/section-images/${id}`);
    };

    const toggleActive = () => {
        router.patch(`/admin/sections/${section.id}/toggle`, {}, { preserveScroll: true });
    };

    return (
        <AdminLayout>
            <div className="max-w-4xl">

                {/* Header */}
                <div className="mb-6 flex items-start justify-between gap-4">
                    <div>
                        <h1 className="text-2xl font-bold text-gray-800">Banner de productos</h1>
                        <p className="text-sm text-gray-500 mt-1">
                            Editá las imágenes y videos que se muestran en la portada de Productos.
                        </p>
                    </div>
                    <div className="flex items-center gap-3 flex-shrink-0">
                        {/* Toggle visible/oculto */}
                        <button
                            onClick={toggleActive}
                            className={`inline-flex items-center gap-1.5 text-xs px-2.5 py-1 rounded-full font-medium transition ${
                                section?.is_active
                                    ? "bg-green-100 text-green-700 hover:bg-green-200"
                                    : "bg-gray-100 text-gray-500 hover:bg-gray-200"
                            }`}
                        >
                            <span className={`w-1.5 h-1.5 rounded-full ${
                                section?.is_active ? "bg-green-500" : "bg-gray-400"
                            }`} />
                            {section?.is_active ? "Visible en el sitio" : "Oculta"}
                        </button>

                        {/* Botón nueva diapositiva */}
                        {!showAdd && (
                            <button
                                onClick={() => setShowAdd(true)}
                                className="inline-flex items-center gap-2 px-4 py-2 bg-[#25A7CA] text-white rounded-lg text-sm font-medium hover:bg-[#1d96b8] transition"
                            >
                                <Icon icon="solar:add-circle-outline" width={17} />
                                Nueva diapositiva
                            </button>
                        )}
                    </div>
                </div>

                {/* Formulario agregar */}
                {showAdd && (
                    <div className="mb-6">
                        <AddSlideForm sectionId={section.id} onCancel={() => setShowAdd(false)} />
                    </div>
                )}

                {/* Lista de diapositivas */}
                {slides.length === 0 && !showAdd ? (
                    <div className="bg-white rounded-xl border border-dashed border-gray-300 p-12 text-center">
                        <Icon icon="solar:gallery-wide-outline" width={40} className="mx-auto text-gray-300 mb-3" />
                        <p className="text-gray-400 text-sm mb-4">No hay diapositivas cargadas. Agregá la primera.</p>
                        <button
                            onClick={() => setShowAdd(true)}
                            className="inline-flex items-center gap-2 px-4 py-2 bg-[#25A7CA] text-white rounded-lg text-sm font-medium hover:bg-[#1d96b8] transition"
                        >
                            <Icon icon="solar:add-circle-outline" width={17} />
                            Agregar diapositiva
                        </button>
                    </div>
                ) : (
                    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        {slides.map((slide) => (
                            <SlideCard key={slide.id} slide={slide} onDelete={handleDelete} />
                        ))}
                    </div>
                )}

                {slides.length > 0 && (
                    <p className="mt-4 text-xs text-gray-400 flex items-center gap-1.5">
                        <Icon icon="solar:info-circle-outline" width={13} />
                        Las diapositivas se ordenan alfabéticamente (A, B… Z, AA, AB…). Con 2 o más diapositivas aparecen los indicadores de navegación.
                    </p>
                )}

            </div>
        </AdminLayout>
    );
}
