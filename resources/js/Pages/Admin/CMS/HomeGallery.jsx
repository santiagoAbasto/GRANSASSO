import { useState } from "react";
import AdminLayout from "@/Layouts/AdminLayout";
import { useForm, router } from "@inertiajs/react";
import { Icon } from "@iconify/react";
import ImageUploadField from "@/Components/Admin/ImageUploadField";

function GalleryCard({ image, onDelete }) {
    const [editing, setEditing] = useState(false);
    const { data, setData, patch, processing, errors, reset } = useForm({
        order: image.order ?? "",
        image: null,
    });

    const submit = (e) => {
        e.preventDefault();
        patch(`/admin/section-images/${image.id}`, {
            forceFormData: true,
            onSuccess: () => setEditing(false),
        });
    };

    return (
        <div className="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <div className="relative aspect-square bg-gray-100">
                <img src={image.image_url} alt="" className="w-full h-full object-cover" />
                <span className="absolute top-3 right-3 bg-black/45 text-white text-xs px-2 py-0.5 rounded-full font-mono font-bold tracking-widest">
                    {image.order}
                </span>
            </div>

            {editing ? (
                <form onSubmit={submit} className="p-4 space-y-3 border-t border-gray-100">
                    <div>
                        <label className="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">
                            Orden
                        </label>
                        <input
                            type="text"
                            value={data.order}
                            onChange={(e) => setData("order", e.target.value.toUpperCase())}
                            maxLength={20}
                            className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm font-mono uppercase focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30"
                        />
                        {errors.order && <p className="text-red-500 text-xs mt-1">{errors.order}</p>}
                    </div>

                    <ImageUploadField
                        onChange={(file) => setData("image", file)}
                        specs={{ width: 900, height: 900, maxMB: 4, formats: ["JPG", "PNG", "WEBP"] }}
                        error={errors.image}
                    />

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
                            {processing ? "Guardando..." : "Guardar"}
                        </button>
                    </div>
                </form>
            ) : (
                <div className="p-3 flex justify-end gap-2">
                    <button
                        type="button"
                        onClick={() => setEditing(true)}
                        className="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-gray-50 hover:bg-gray-100 text-sm text-gray-600 transition"
                    >
                        <Icon icon="solar:pen-outline" width={14} />
                        Editar
                    </button>
                    <button
                        type="button"
                        onClick={() => onDelete(image.id)}
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

function AddImageForm({ sectionId, onCancel }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        image: null,
        order: "",
    });

    const submit = (e) => {
        e.preventDefault();
        post(`/admin/sections/${sectionId}/images`, {
            forceFormData: true,
            onSuccess: () => {
                reset();
                onCancel();
            },
        });
    };

    return (
        <form onSubmit={submit} className="bg-white rounded-xl border border-[#25A7CA]/30 shadow-sm p-5 space-y-4">
            <h3 className="font-semibold text-gray-700 text-sm">Nueva imagen</h3>

            <ImageUploadField
                onChange={(file) => setData("image", file)}
                specs={{ width: 900, height: 900, maxMB: 4, formats: ["JPG", "PNG", "WEBP"] }}
                error={errors.image}
            />

            <div>
                <label className="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">
                    Orden <span className="text-gray-400 normal-case font-normal">(opcional)</span>
                </label>
                <input
                    type="text"
                    value={data.order}
                    onChange={(e) => setData("order", e.target.value.toUpperCase())}
                    maxLength={20}
                    placeholder="Ej: A, B, C"
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
                    <Icon icon={processing ? "solar:refresh-outline" : "solar:add-circle-outline"} width={15} />
                    {processing ? "Agregando..." : "Agregar imagen"}
                </button>
            </div>
        </form>
    );
}

export default function HomeGallery({ section }) {
    const [showAdd, setShowAdd] = useState(false);
    const images = section?.images ?? [];
    const maxImagesReached = images.length >= 3;

    const handleDelete = (id) => {
        if (!confirm("¿Eliminar esta imagen?")) return;
        router.delete(`/admin/section-images/${id}`);
    };

    const toggleActive = () => {
        router.patch(`/admin/sections/${section.id}/toggle`, {}, { preserveScroll: true });
    };

    return (
        <AdminLayout>
            <div className="max-w-4xl">
                <div className="mb-6 flex items-start justify-between gap-4">
                    <div>
                        <h1 className="text-2xl font-bold text-gray-800">Imágenes en inicio</h1>
                        <p className="text-sm text-gray-500 mt-1">
                            Estas 3 imágenes reemplazan el buscador y el bloque de productos debajo del banner principal.
                        </p>
                    </div>
                    <div className="flex items-center gap-3 flex-shrink-0">
                        <button
                            type="button"
                            onClick={toggleActive}
                            className={`inline-flex items-center gap-1.5 text-xs px-2.5 py-1 rounded-full font-medium transition ${
                                section?.is_active
                                    ? "bg-green-100 text-green-700 hover:bg-green-200"
                                    : "bg-gray-100 text-gray-500 hover:bg-gray-200"
                            }`}
                        >
                            <span className={`w-1.5 h-1.5 rounded-full ${section?.is_active ? "bg-green-500" : "bg-gray-400"}`} />
                            {section?.is_active ? "Visible en el sitio" : "Oculta"}
                        </button>

                        {!showAdd && !maxImagesReached && (
                            <button
                                type="button"
                                onClick={() => setShowAdd(true)}
                                className="inline-flex items-center gap-2 px-4 py-2 bg-[#25A7CA] text-white rounded-lg text-sm font-medium hover:bg-[#1d96b8] transition"
                            >
                                <Icon icon="solar:add-circle-outline" width={17} />
                                Nueva imagen
                            </button>
                        )}
                    </div>
                </div>

                <div className="mb-5 rounded-2xl border border-sky-100 bg-sky-50 px-4 py-3 text-sm text-sky-800">
                    Se muestran hasta 3 imágenes, en una fila, justo debajo del banner principal del inicio.
                </div>

                {showAdd && (
                    <div className="mb-6">
                        <AddImageForm sectionId={section.id} onCancel={() => setShowAdd(false)} />
                    </div>
                )}

                {images.length === 0 && !showAdd ? (
                    <div className="bg-white rounded-xl border border-dashed border-gray-300 p-12 text-center">
                        <Icon icon="solar:gallery-outline" width={40} className="mx-auto text-gray-300 mb-3" />
                        <p className="text-gray-400 text-sm mb-4">No hay imágenes cargadas todavía.</p>
                        <button
                            type="button"
                            onClick={() => setShowAdd(true)}
                            className="inline-flex items-center gap-2 px-4 py-2 bg-[#25A7CA] text-white rounded-lg text-sm font-medium hover:bg-[#1d96b8] transition"
                        >
                            <Icon icon="solar:add-circle-outline" width={17} />
                            Agregar imagen
                        </button>
                    </div>
                ) : (
                    <div className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                        {images.map((image) => (
                            <GalleryCard key={image.id} image={image} onDelete={handleDelete} />
                        ))}
                    </div>
                )}

                <p className="mt-4 text-xs text-gray-400 flex items-center gap-1.5">
                    <Icon icon="solar:info-circle-outline" width={13} />
                    Si querés cambiar una imagen, podés editarla sin perder el orden actual.
                </p>
            </div>
        </AdminLayout>
    );
}
