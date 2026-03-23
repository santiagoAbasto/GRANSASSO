import { useState, useRef } from "react";
import AdminLayout from "@/Layouts/AdminLayout";
import { router } from "@inertiajs/react";
import { useForm } from "@inertiajs/react";
import { Icon } from "@iconify/react";

/* inline order input shared by both sections */
function OrderInput({ img }) {
    const [val, setVal] = useState(img.order ?? "");
    const [saving, setSaving] = useState(false);

    const save = () => {
        const trimmed = val.toString().trim();
        if (trimmed === (img.order ?? "").toString()) return;
        setSaving(true);
        router.patch(
            `/admin/section-images/${img.id}`,
            { order: trimmed },
            {
                preserveScroll: true,
                onFinish: () => setSaving(false),
            }
        );
    };

    return (
        <div className="absolute bottom-1 left-1 right-1 flex items-center gap-1">
            <input
                type="text"
                value={val}
                onChange={(e) => setVal(e.target.value)}
                onBlur={save}
                onKeyDown={(e) => e.key === "Enter" && e.target.blur()}
                className="w-full text-[10px] font-bold text-center bg-black/60 text-white border border-white/30 rounded px-1 py-0.5 outline-none focus:border-white"
                title="Orden (ej: A, B, AA, AB…)"
            />
            {saving && <Icon icon="solar:refresh-outline" width={10} className="text-white animate-spin flex-shrink-0" />}
        </div>
    );
}

/* ─────────────────────────────────────────────────────────────────────────
   BANNER SECTION — gestión de imágenes slider
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
            <div className="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <div className="flex items-center gap-2">
                    <div className="flex items-center justify-center w-8 h-8 rounded-lg bg-[#25A7CA]/10">
                        <Icon icon="solar:gallery-wide-outline" width={16} className="text-[#25A7CA]" />
                    </div>
                    <div>
                        <p className="text-sm font-semibold text-gray-800">Banner / Slider</p>
                        <p className="text-xs text-gray-400">
                            {images.length === 0
                                ? "Sin imágenes — el banner no se mostrará"
                                : images.length === 1
                                ? "1 imagen — banner estático (sin dots)"
                                : `${images.length} imágenes — slider automático con dots`}
                        </p>
                    </div>
                </div>
                <span className={`text-xs px-2 py-0.5 rounded-full font-medium ${section.is_active ? "bg-green-100 text-green-700" : "bg-gray-100 text-gray-500"}`}>
                    {section.is_active ? "Activo" : "Inactivo"}
                </span>
            </div>

            <div className="p-5">
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
                                <button
                                    onClick={() => deleteImage(img.id)}
                                    className="absolute top-1.5 right-1.5 p-1 rounded-lg bg-red-500 text-white opacity-0 group-hover:opacity-100 transition"
                                    title="Eliminar"
                                >
                                    <Icon icon="solar:trash-bin-trash-outline" width={13} />
                                </button>
                                <OrderInput img={img} />
                            </div>
                        ))}
                    </div>
                )}

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
   GALLERY SECTION — "Trabajos realizados"
───────────────────────────────────────────────────────────────────────── */
function GallerySection({ section }) {
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
        if (!confirm("¿Eliminar esta imagen?")) return;
        router.delete(`/admin/section-images/${id}`);
    };

    const images = section.images ?? [];

    return (
        <div className="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div className="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <div className="flex items-center gap-2">
                    <div className="flex items-center justify-center w-8 h-8 rounded-lg bg-amber-100">
                        <Icon icon="solar:gallery-minimalistic-outline" width={16} className="text-amber-600" />
                    </div>
                    <div>
                        <p className="text-sm font-semibold text-gray-800">Galería — Trabajos realizados</p>
                        <p className="text-xs text-gray-400">
                            {images.length === 0
                                ? "Sin imágenes — la sección no se mostrará"
                                : `${images.length} imagen${images.length !== 1 ? "es" : ""} — cuadrícula 4 columnas`}
                        </p>
                    </div>
                </div>
                <span className={`text-xs px-2 py-0.5 rounded-full font-medium ${section.is_active ? "bg-green-100 text-green-700" : "bg-gray-100 text-gray-500"}`}>
                    {section.is_active ? "Activo" : "Inactivo"}
                </span>
            </div>

            <div className="p-5">
                {images.length === 0 ? (
                    <div className="flex items-center justify-center h-28 rounded-xl border-2 border-dashed border-gray-200 text-gray-300 mb-4">
                        <div className="text-center">
                            <Icon icon="solar:gallery-outline" width={28} className="mx-auto mb-1" />
                            <p className="text-xs">Sin imágenes todavía</p>
                        </div>
                    </div>
                ) : (
                    <div className="grid grid-cols-3 sm:grid-cols-4 lg:grid-cols-6 gap-3 mb-4">
                        {images.map((img) => (
                            <div key={img.id} className="relative group rounded-xl overflow-hidden border border-gray-100" style={{ aspectRatio: "1/1" }}>
                                <img src={img.image_url} alt="" className="w-full h-full object-cover" />
                                <button
                                    onClick={() => deleteImage(img.id)}
                                    className="absolute top-1 right-1 p-1 rounded-lg bg-red-500 text-white opacity-0 group-hover:opacity-100 transition"
                                    title="Eliminar"
                                >
                                    <Icon icon="solar:trash-bin-trash-outline" width={12} />
                                </button>
                                <OrderInput img={img} />
                            </div>
                        ))}
                    </div>
                )}

                <form onSubmit={submit} className="flex flex-col sm:flex-row items-start sm:items-end gap-3 p-4 bg-gray-50 rounded-xl border border-dashed border-gray-200">
                    <div className="flex-1">
                        <label className="block text-xs font-medium text-gray-500 mb-1">Agregar imagen a la galería</label>
                        <p className="text-xs text-gray-400 mb-2">JPG/PNG · máx 5 MB · proporción cuadrada recomendada</p>
                        <input
                            ref={fileRef}
                            type="file"
                            accept="image/*"
                            onChange={handleFile}
                            className="block w-full text-sm text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100"
                        />
                        {preview && (
                            <img src={preview} className="mt-2 h-16 rounded-lg object-cover border border-gray-100" alt="preview" />
                        )}
                    </div>
                    <button
                        type="submit"
                        disabled={!data.image || processing}
                        className="inline-flex items-center gap-1.5 px-4 py-2 bg-amber-600 text-white rounded-lg text-sm font-medium hover:bg-amber-700 disabled:opacity-40 transition whitespace-nowrap"
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
   PAGE PRINCIPAL
───────────────────────────────────────────────────────────────────────── */
export default function Desarrollos({ page, sections }) {
    const banner  = sections.find((s) => s.type === "banner");
    const gallery = sections.find((s) => s.type === "desarrollos_gallery");

    return (
        <AdminLayout>
            <div className="max-w-4xl space-y-6">

                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold text-gray-800">CMS – Desarrollos especiales</h1>
                        <p className="text-sm text-gray-500">Gestión del banner y la galería de trabajos realizados</p>
                    </div>
                    <a
                        href="/desarrollos-especiales"
                        target="_blank"
                        rel="noopener noreferrer"
                        className="inline-flex items-center gap-2 px-4 py-2 border border-gray-200 text-gray-600 rounded-lg text-sm hover:bg-gray-50 transition"
                    >
                        <Icon icon="solar:eye-outline" width={16} />
                        Ver página
                    </a>
                </div>

                {/* Info card */}
                <div className="bg-[#25A7CA]/5 border border-[#25A7CA]/20 rounded-xl p-4">
                    <div className="flex items-start gap-3">
                        <Icon icon="solar:info-circle-outline" width={18} className="text-[#25A7CA] mt-0.5 flex-shrink-0" />
                        <div className="text-sm text-gray-600">
                            <p className="font-medium text-gray-700 mb-1">Contenido de la página</p>
                            <p>El texto principal ("Desarrollo y fabricación a medida") está fijo en el código. Desde aquí podés gestionar las imágenes del <strong>banner</strong> y la <strong>galería de trabajos realizados</strong>.</p>
                        </div>
                    </div>
                </div>

                {/* Banner */}
                {banner && <BannerSection section={banner} />}

                {/* Gallery */}
                {gallery && <GallerySection section={gallery} />}

            </div>
        </AdminLayout>
    );
}
