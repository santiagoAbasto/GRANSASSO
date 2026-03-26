import AdminLayout from "@/Layouts/AdminLayout";
import { useForm, router } from "@inertiajs/react";
import { Icon } from "@iconify/react";
import { useState } from "react";
import RichTextEditor from "@/Components/Admin/RichTextEditor";
import ImageUploadField from "@/Components/Admin/ImageUploadField";

const LOCALES = [
    { code: "es", label: "Español", flag: "🇦🇷" },
    { code: "en", label: "English", flag: "🇺🇸" },
    { code: "pt", label: "Português", flag: "🇧🇷" },
];

export default function HomeCalidad({ page, section }) {
    const [activeLocale, setActiveLocale] = useState("es");

    // Icono de certificación existente (primer imagen — igual que el blade: sortBy('order')->first())
    const existingCertIcon = section?.images?.[0] ?? null;

    const { data, setData, post, processing, errors } = useForm({
        // Textos multiidioma
        title_es:       section?.title?.es       ?? "",
        title_en:       section?.title?.en       ?? "",
        title_pt:       section?.title?.pt       ?? "",
        content_es:     section?.content?.es     ?? "",
        content_en:     section?.content?.en     ?? "",
        content_pt:     section?.content?.pt     ?? "",
        button_text_es: section?.button_text?.es ?? "",
        button_text_en: section?.button_text?.en ?? "",
        button_text_pt: section?.button_text?.pt ?? "",

        // Compartidos
        button_link: section?.button_link ?? "",
        is_active:   section?.is_active   ?? true,

        // Imágenes
        image:   null,  // fondo
        image_1: null,  // icono de certificación (ISO badge)
    });

    const submit = (e) => {
        e.preventDefault();
        post("/admin/home-calidad", { forceFormData: true });
    };

    const deleteCertIcon = () => {
        if (!existingCertIcon) return;
        if (!confirm("¿Eliminar el icono de certificación?")) return;
        router.delete(`/admin/home-calidad/images/${existingCertIcon.id}`);
    };

    return (
        <AdminLayout>
            <div className="max-w-3xl">

                {/* ── HEADER ─────────────────────────────────────────────── */}
                <div className="mb-6">
                    <h1 className="text-2xl font-bold text-gray-800">Home – Calidad</h1>
                    <p className="text-sm text-gray-500 mt-1">
                        Edita el bloque "Calidad" que aparece en la página de inicio.
                    </p>
                </div>

                <form onSubmit={submit} className="space-y-6">

                    {/* ── IMAGEN FONDO ────────────────────────────────────── */}
                    <div className="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-4">
                        <h2 className="font-semibold text-gray-700 flex items-center gap-2">
                            <Icon icon="solar:gallery-wide-outline" width={18} className="text-[#25A7CA]" />
                            Imagen de fondo
                        </h2>
                        <ImageUploadField
                            label="Fondo de la sección"
                            currentUrl={section?.image_url}
                            onChange={(f) => setData("image", f)}
                            specs={{ width: 1920, height: 500, maxMB: 5, formats: ["JPG", "PNG"] }}
                            error={errors.image}
                        />
                    </div>

                    {/* ── ICONO DE CERTIFICACIÓN ──────────────────────────── */}
                    <div className="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-4">
                        <h2 className="font-semibold text-gray-700 flex items-center gap-2">
                            <Icon icon="solar:medal-ribbons-star-outline" width={18} className="text-[#25A7CA]" />
                            Icono de certificación
                            <span className="text-xs text-gray-400 font-normal">(ej: badge ISO 9001)</span>
                        </h2>

                        <p className="text-xs text-gray-500">
                            Este icono aparece a la derecha del texto en la sección calidad del inicio.
                            Recomendado: PNG con fondo transparente, aprox. 220 × 220 px.
                        </p>

                        {/* Icono actual */}
                        {existingCertIcon && (
                            <div className="flex items-center gap-4 p-3 bg-gray-50 rounded-xl border border-gray-100">
                                <img
                                    src={existingCertIcon.image_url}
                                    className="h-24 w-auto rounded-lg object-contain"
                                    alt="Icono de certificación actual"
                                />
                                <div className="flex flex-col gap-2">
                                    <span className="text-xs text-gray-500">Icono actual</span>
                                    <button
                                        type="button"
                                        onClick={deleteCertIcon}
                                        className="flex items-center gap-1 text-xs text-red-500 hover:text-red-700 transition"
                                    >
                                        <Icon icon="solar:trash-bin-trash-outline" width={13} />
                                        Eliminar icono
                                    </button>
                                </div>
                            </div>
                        )}

                        <ImageUploadField
                            label={existingCertIcon ? "Reemplazar icono" : "Subir icono de certificación"}
                            onChange={(f) => setData("image_1", f)}
                            specs={{ maxMB: 3, formats: ["PNG", "JPG", "SVG"] }}
                            error={errors.image_1}
                        />
                    </div>

                    {/* ── TABS MULTIIDIOMA ────────────────────────────────── */}
                    <div className="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

                        {/* Tab selector */}
                        <div className="flex border-b border-gray-100">
                            {LOCALES.map((loc) => (
                                <button
                                    key={loc.code}
                                    type="button"
                                    onClick={() => setActiveLocale(loc.code)}
                                    className={`flex-1 flex items-center justify-center gap-2 py-3 text-sm font-medium transition ${
                                        activeLocale === loc.code
                                            ? "bg-[#25A7CA]/5 text-[#25A7CA] border-b-2 border-[#25A7CA]"
                                            : "text-gray-500 hover:bg-gray-50"
                                    }`}
                                >
                                    <span>{loc.flag}</span>
                                    <span>{loc.label}</span>
                                </button>
                            ))}
                        </div>

                        {/* Tab content */}
                        {LOCALES.map((loc) => (
                            <div
                                key={loc.code}
                                className={`p-6 space-y-5 ${activeLocale !== loc.code ? "hidden" : ""}`}
                            >
                                {/* Título */}
                                <div>
                                    <label className="block text-xs font-medium text-gray-500 mb-1">
                                        Título
                                        {loc.code === "es" && <span className="text-red-400 ml-1">*</span>}
                                    </label>
                                    <input
                                        type="text"
                                        value={data[`title_${loc.code}`]}
                                        onChange={(e) => setData(`title_${loc.code}`, e.target.value)}
                                        placeholder={loc.code === "es" ? "Ej: Calidad" : loc.code === "en" ? "Eg: Quality" : "Ex: Qualidade"}
                                        className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30"
                                    />
                                    {errors[`title_${loc.code}`] && (
                                        <p className="text-xs text-red-500 mt-1">{errors[`title_${loc.code}`]}</p>
                                    )}
                                </div>

                                {/* Contenido (rich text) */}
                                <div>
                                    <label className="block text-xs font-medium text-gray-500 mb-1">
                                        Contenido
                                        {loc.code === "es" && <span className="text-red-400 ml-1">*</span>}
                                    </label>
                                    <RichTextEditor
                                        value={data[`content_${loc.code}`]}
                                        onChange={(v) => setData(`content_${loc.code}`, v)}
                                        placeholder={
                                            loc.code === "es"
                                                ? "Describe los estándares de calidad..."
                                                : loc.code === "en"
                                                ? "Describe quality standards..."
                                                : "Descreva os padrões de qualidade..."
                                        }
                                    />
                                    {errors[`content_${loc.code}`] && (
                                        <p className="text-xs text-red-500 mt-1">{errors[`content_${loc.code}`]}</p>
                                    )}
                                </div>

                                {/* Texto del botón */}
                                <div>
                                    <label className="block text-xs font-medium text-gray-500 mb-1">
                                        Texto del botón
                                    </label>
                                    <input
                                        type="text"
                                        value={data[`button_text_${loc.code}`]}
                                        onChange={(e) => setData(`button_text_${loc.code}`, e.target.value)}
                                        placeholder={
                                            loc.code === "es"
                                                ? "Mas información"
                                                : loc.code === "en"
                                                ? "More information"
                                                : "Mais informações"
                                        }
                                        className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30"
                                    />
                                </div>
                            </div>
                        ))}
                    </div>

                    {/* ── BOTÓN URL + ESTADO ──────────────────────────────── */}
                    <div className="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-4">
                        <h2 className="font-semibold text-gray-700 flex items-center gap-2">
                            <Icon icon="solar:link-outline" width={18} className="text-[#25A7CA]" />
                            Configuración del botón
                        </h2>

                        <div>
                            <label className="block text-xs font-medium text-gray-500 mb-1">
                                URL del botón
                            </label>
                            <input
                                type="text"
                                value={data.button_link}
                                onChange={(e) => setData("button_link", e.target.value)}
                                placeholder="/calidad"
                                className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30"
                            />
                        </div>

                        <div className="flex items-center justify-between pt-1">
                            <label className="flex items-center gap-2 cursor-pointer">
                                <input
                                    type="checkbox"
                                    checked={data.is_active}
                                    onChange={(e) => setData("is_active", e.target.checked)}
                                    className="w-4 h-4 rounded accent-[#25A7CA]"
                                />
                                <span className="text-sm text-gray-700">Sección activa</span>
                            </label>

                            <span className={`inline-flex items-center gap-1 text-xs px-2.5 py-1 rounded-full font-medium ${
                                data.is_active
                                    ? "bg-green-100 text-green-700"
                                    : "bg-gray-100 text-gray-500"
                            }`}>
                                <span className={`w-1.5 h-1.5 rounded-full ${data.is_active ? "bg-green-500" : "bg-gray-400"}`} />
                                {data.is_active ? "Visible en el sitio" : "Oculta"}
                            </span>
                        </div>
                    </div>

                    {/* ── SUBMIT ──────────────────────────────────────────── */}
                    <div className="flex justify-end gap-3 pb-6">
                        <button
                            type="button"
                            onClick={() => history.back()}
                            className="px-5 py-2.5 rounded-xl border border-gray-200 text-sm text-gray-700 hover:bg-gray-50 transition"
                        >
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            disabled={processing}
                            className="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-[#25A7CA] text-white text-sm font-medium hover:bg-[#1d96b8] disabled:opacity-60 transition"
                        >
                            <Icon
                                icon={processing ? "solar:refresh-outline" : "solar:diskette-outline"}
                                width={15}
                                className={processing ? "animate-spin" : ""}
                            />
                            {processing ? "Guardando..." : "Guardar cambios"}
                        </button>
                    </div>

                </form>
            </div>
        </AdminLayout>
    );
}
