import AdminLayout from "@/Layouts/AdminLayout";
import { useForm } from "@inertiajs/react";
import { Icon } from "@iconify/react";

import RichTextEditor from "@/Components/Admin/RichTextEditor";
import ImageUploadField from "@/Components/Admin/ImageUploadField";

export default function HeroEdit({ slide }) {
    const { data, setData, post, processing, errors } = useForm({
        _method: "PUT",
        title: slide.title || "",
        media_type: slide.media_type || "image",
        media: null,
        video_url: slide.video_url || "",
        button_text: slide.button_text || "",
        button_link: slide.button_link || "",
        order: slide.order ?? "A",
        is_active: slide.is_active ?? true,
    });

    const handleFile = (file) => {
        setData("media", file);
    };

    /** Convert any YouTube URL → embed URL */
    function toYouTubeEmbed(url) {
        if (!url) return null;
        let videoId = null;
        try {
            const u = new URL(url);
            if (u.hostname.includes('youtube.com') && u.pathname === '/watch') {
                videoId = u.searchParams.get('v');
            } else if (u.hostname === 'youtu.be') {
                videoId = u.pathname.slice(1);
            } else if (u.hostname.includes('youtube.com') && u.pathname.startsWith('/embed/')) {
                return url; // already embed
            }
        } catch { return null; }
        return videoId ? `https://www.youtube.com/embed/${videoId}` : null;
    }

    const submit = (e) => {
        e.preventDefault();
        post(`/admin/hero/${slide.id}`, { forceFormData: true });
    };

    const embedUrl = toYouTubeEmbed(data.video_url);

    return (
        <AdminLayout>
            <div className="max-w-3xl">
                <div className="flex items-center gap-3 mb-6">
                    <button onClick={() => history.back()} className="p-2 rounded-lg hover:bg-gray-100 text-gray-500">
                        <Icon icon="solar:arrow-left-outline" width={18} />
                    </button>
                    <div>
                        <h1 className="text-2xl font-bold text-gray-800">Editar Slide</h1>
                        <p className="text-sm text-gray-500">{slide.title || 'Sin título'}</p>
                    </div>
                </div>

                <form onSubmit={submit} className="space-y-6">
                    {/* Tipo de media */}
                    <div className="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                        <h2 className="font-semibold text-gray-700 mb-4">Tipo de Media</h2>
                        <div className="flex gap-3">
                            {["image", "video"].map((type) => (
                                <label key={type} className={`flex-1 flex items-center gap-3 p-4 rounded-xl border-2 cursor-pointer transition ${data.media_type === type ? 'border-[#25A7CA] bg-[#25A7CA]/5' : 'border-gray-200 hover:border-gray-300'}`}>
                                    <input type="radio" name="media_type" value={type} checked={data.media_type === type} onChange={() => setData("media_type", type)} className="sr-only" />
                                    <Icon icon={type === "image" ? "solar:gallery-outline" : "solar:play-circle-outline"} width={20} className={data.media_type === type ? 'text-[#25A7CA]' : 'text-gray-400'} />
                                    <span className={`font-medium text-sm ${data.media_type === type ? 'text-[#25A7CA]' : 'text-gray-600'}`}>{type === "image" ? "Imagen" : "Video"}</span>
                                </label>
                            ))}
                        </div>

                        <div className="mt-4">
                            {data.media_type === "image" ? (
                                <div>
                                    <ImageUploadField
                                        label="Nueva imagen (opcional)"
                                        currentUrl={slide.media_url}
                                        onChange={handleFile}
                                        specs={{ width: 1224, height: 720, maxMB: 3, formats: ["PNG"] }}
                                    />
                                </div>
                            ) : (
                                <div>
                                    <label className="block text-sm text-gray-600 mb-2">URL de YouTube</label>
                                    <input
                                        type="text"
                                        placeholder="https://www.youtube.com/watch?v=..."
                                        value={data.video_url}
                                        onChange={(e) => setData("video_url", e.target.value)}
                                        className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30"
                                    />
                                    <p className="text-xs text-gray-400 mt-1">Soporta: youtube.com/watch?v=..., youtu.be/..., youtube.com/embed/...</p>
                                    {/* Video preview */}
                                    {embedUrl && (
                                        <div className="mt-3 rounded-lg overflow-hidden" style={{ aspectRatio: '16/9', maxHeight: '200px' }}>
                                            <iframe
                                                src={embedUrl}
                                                title="Vista previa"
                                                className="w-full h-full"
                                                frameBorder="0"
                                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                                allowFullScreen
                                            />
                                        </div>
                                    )}
                                </div>
                            )}
                        </div>
                    </div>

                    {/* Título */}
                    <div className="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-4">
                        <h2 className="font-semibold text-gray-700">Título (texto enriquecido)</h2>
                        <input
                            type="text"
                            value={data.title}
                            onChange={(e) => setData("title", e.target.value)}
                            className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30"
                            placeholder="Título del slide"
                        />
                        {errors.title && <p className="text-red-500 text-xs mt-1">{errors.title}</p>}
                    </div>

                    {/* CTA */}
                    <div className="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-4">
                        <h2 className="font-semibold text-gray-700">Botón (CTA)</h2>
                        <div className="grid grid-cols-2 gap-4">
                            <div>
                                <label className="block text-sm text-gray-600 mb-1">Texto</label>
                                <input type="text" value={data.button_text} onChange={(e) => setData("button_text", e.target.value)} className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30" />
                            </div>
                            <div>
                                <label className="block text-sm text-gray-600 mb-1">URL</label>
                                <input type="text" value={data.button_link} onChange={(e) => setData("button_link", e.target.value)} className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30" />
                            </div>
                        </div>
                    </div>

                    {/* Config */}
                    <div className="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                        <h2 className="font-semibold text-gray-700 mb-4">Configuración</h2>
                        <div className="flex items-center gap-6">
                            <div>
                                <label className="block text-sm text-gray-600 mb-1">Orden (ej: A, AA, AB, B, BA...)</label>
                                <input
                                    type="text"
                                    value={data.order}
                                    onChange={(e) => setData("order", e.target.value.toUpperCase())}
                                    maxLength={10}
                                    className="w-24 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30 uppercase"
                                    placeholder="A"
                                />
                            </div>
                            <label className="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" checked={data.is_active} onChange={(e) => setData("is_active", e.target.checked)} className="w-4 h-4 rounded border-gray-300 text-[#25A7CA] focus:ring-[#25A7CA]" />
                                <span className="text-sm text-gray-700">Activo</span>
                            </label>
                        </div>
                    </div>

                    <div className="flex justify-end gap-3">
                        <button type="button" onClick={() => history.back()} className="px-5 py-2.5 rounded-xl border border-gray-200 text-sm text-gray-700 hover:bg-gray-50">Cancelar</button>
                        <button type="submit" disabled={processing} className="px-5 py-2.5 rounded-xl bg-[#25A7CA] text-white text-sm font-medium hover:bg-[#1d96b8] disabled:opacity-60 transition">
                            {processing ? "Guardando..." : "Actualizar Slide"}
                        </button>
                    </div>
                </form>
            </div>
        </AdminLayout>
    );
}
