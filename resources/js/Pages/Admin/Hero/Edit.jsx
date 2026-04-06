import AdminLayout from "@/Layouts/AdminLayout";
import { useForm, router } from "@inertiajs/react";
import { Icon } from "@iconify/react";
import ImageUploadField from "@/Components/Admin/ImageUploadField";

const LANGS = ["es", "en", "pt"];

function LangBadge({ lang }) {
    const colors = { es: "bg-red-100 text-red-600", en: "bg-blue-100 text-blue-600", pt: "bg-green-100 text-green-600" };
    return (
        <span className={`inline-block text-[10px] font-bold px-1.5 py-0.5 rounded uppercase leading-none ${colors[lang]}`}>
            {lang}
        </span>
    );
}

function MultiInput({ label, field, data, setData, placeholder = "", type = "input" }) {
    return (
        <div>
            <label className="block text-sm font-medium text-gray-600 mb-2">{label}</label>
            <div className={type === "textarea" ? "space-y-2" : "grid grid-cols-3 gap-3"}>
                {LANGS.map((lang) => (
                    <div key={lang} className="flex flex-col gap-1">
                        <LangBadge lang={lang} />
                        {type === "textarea" ? (
                            <textarea
                                value={data[`${field}_${lang}`]}
                                onChange={(e) => setData(`${field}_${lang}`, e.target.value)}
                                rows={3}
                                placeholder={placeholder}
                                className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30 resize-y"
                            />
                        ) : (
                            <input
                                type="text"
                                value={data[`${field}_${lang}`]}
                                onChange={(e) => setData(`${field}_${lang}`, e.target.value)}
                                placeholder={placeholder}
                                className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30"
                            />
                        )}
                    </div>
                ))}
            </div>
        </div>
    );
}

export default function HeroEdit({ slide }) {
    const { data, setData, post, processing, errors } = useForm({
        _method:        "PUT",
        title_es:       slide.title_es       ?? "",
        title_en:       slide.title_en       ?? "",
        title_pt:       slide.title_pt       ?? "",
        subtitle_es:    slide.subtitle_es    ?? "",
        subtitle_en:    slide.subtitle_en    ?? "",
        subtitle_pt:    slide.subtitle_pt    ?? "",
        button_text_es: slide.button_text_es ?? "",
        button_text_en: slide.button_text_en ?? "",
        button_text_pt: slide.button_text_pt ?? "",
        button_link:    slide.button_link    ?? "",
        media_type:     slide.media_type     ?? "image",
        image:          null,
        video_url:      slide.video_url      ?? "",
        video_file:     null,
        order:          slide.order          ?? "A",
        is_active:      slide.is_active      ?? true,
    });

    const submit = (e) => {
        e.preventDefault();
        post(`/admin/hero/${slide.id}`, { forceFormData: true });
    };

    return (
        <AdminLayout>
            <div className="max-w-3xl">
                <div className="flex items-center gap-3 mb-6">
                    <button onClick={() => router.visit("/admin/hero")} className="p-2 rounded-lg hover:bg-gray-100 text-gray-500">
                        <Icon icon="solar:arrow-left-outline" width={18} />
                    </button>
                    <div>
                        <h1 className="text-2xl font-bold text-gray-800">Editar Slide</h1>
                        <p className="text-sm text-gray-500">Slide #{slide.id}</p>
                    </div>
                </div>

                <form onSubmit={submit} className="space-y-6">
                    {/* Media */}
                    <div className="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                        <h2 className="font-semibold text-gray-700 mb-4">Media del slide</h2>

                        <div className="grid grid-cols-1 md:grid-cols-3 gap-3 mb-5">
                            {[
                                { value: "image", label: "Imagen" },
                                { value: "youtube", label: "YouTube embebido" },
                                { value: "video", label: "Video local" },
                            ].map((option) => (
                                <label key={option.value} className="flex items-center gap-2 border border-gray-200 rounded-xl px-4 py-3 cursor-pointer">
                                    <input
                                        type="radio"
                                        name="media_type"
                                        value={option.value}
                                        checked={data.media_type === option.value}
                                        onChange={() => setData("media_type", option.value)}
                                    />
                                    <span className="text-sm text-gray-700">{option.label}</span>
                                </label>
                            ))}
                        </div>

                        {slide.media_url && (
                            <div className="mb-4 rounded-xl overflow-hidden h-40 bg-gray-100">
                                {slide.media_type === "image" ? (
                                    <img src={slide.media_url} alt="Slide actual" className="w-full h-full object-cover" />
                                ) : slide.media_type === "video" ? (
                                    <video src={slide.media_url} className="w-full h-full object-cover" muted playsInline />
                                ) : (
                                    <div className="w-full h-full flex items-center justify-center bg-gray-800 text-white text-sm px-4 text-center">
                                        Video de YouTube configurado
                                    </div>
                                )}
                            </div>
                        )}

                        {data.media_type === "image" && (
                            <ImageUploadField
                                label="Reemplazar imagen"
                                onChange={(f) => setData("image", f)}
                                specs={{ width: 1440, height: 680, maxMB: 5, formats: ["JPG", "PNG"] }}
                                error={errors.image}
                            />
                        )}

                        {data.media_type === "youtube" && (
                            <div>
                                <label className="block text-sm font-medium text-gray-600 mb-1">Link de YouTube</label>
                                <input
                                    type="url"
                                    value={data.video_url}
                                    onChange={(e) => setData("video_url", e.target.value)}
                                    placeholder="https://www.youtube.com/watch?v=..."
                                    className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30"
                                />
                                {errors.video_url && <p className="text-red-500 text-xs mt-1">{errors.video_url}</p>}
                            </div>
                        )}

                        {data.media_type === "video" && (
                            <ImageUploadField
                                label="Reemplazar video local"
                                onChange={(f) => setData("video_file", f)}
                                accept="video/mp4,video/quicktime,video/webm"
                                showPreview={false}
                                specs={{ maxMB: 50, formats: ["MP4", "MOV", "WEBM"] }}
                                error={errors.video_file}
                            />
                        )}
                    </div>

                    {/* Contenido multilenguaje */}
                    <div className="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-5">
                        <h2 className="font-semibold text-gray-700">Contenido multilenguaje</h2>

                        <MultiInput label="Título" field="title" data={data} setData={setData} placeholder="Título del slide" />
                        <MultiInput label="Subtítulo" field="subtitle" data={data} setData={setData} placeholder="Subtítulo" />
                        <MultiInput label="Texto del botón" field="button_text" data={data} setData={setData} placeholder="Ver productos" />

                        <div>
                            <label className="block text-sm font-medium text-gray-600 mb-1">URL del botón</label>
                            <input
                                type="text"
                                value={data.button_link}
                                onChange={(e) => setData("button_link", e.target.value)}
                                placeholder="/productos"
                                className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30"
                            />
                        </div>
                    </div>

                    {/* Config */}
                    <div className="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                        <h2 className="font-semibold text-gray-700 mb-4">Configuración</h2>
                        <div className="flex items-center gap-6">
                            <div>
                                <label className="block text-sm text-gray-600 mb-1">Orden</label>
                                <input type="text" value={data.order} onChange={(e) => setData("order", e.target.value.toUpperCase())}
                                    className="w-24 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none" />
                            </div>
                            <label className="flex items-center gap-2 cursor-pointer mt-5">
                                <input type="checkbox" checked={data.is_active} onChange={(e) => setData("is_active", e.target.checked)} className="w-4 h-4 rounded" />
                                <span className="text-sm text-gray-700">Activo</span>
                            </label>
                        </div>
                    </div>

                    <div className="flex justify-end gap-3">
                        <button type="button" onClick={() => router.visit("/admin/hero")} className="px-5 py-2.5 rounded-xl border border-gray-200 text-sm text-gray-700 hover:bg-gray-50">Cancelar</button>
                        <button type="submit" disabled={processing} className="px-5 py-2.5 rounded-xl bg-[#25A7CA] text-white text-sm font-medium hover:bg-[#1d96b8] disabled:opacity-60 transition">
                            {processing ? "Guardando..." : "Guardar Cambios"}
                        </button>
                    </div>
                </form>
            </div>
        </AdminLayout>
    );
}
