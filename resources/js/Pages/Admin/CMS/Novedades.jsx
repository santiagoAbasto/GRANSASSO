import { useRef, useState } from "react";
import { router, useForm } from "@inertiajs/react";
import { Icon } from "@iconify/react";
import AdminLayout from "@/Layouts/AdminLayout";
import RichTextEditor from "@/Components/Admin/RichTextEditor";
import NovedadesManager from "@/Components/Admin/NovedadesManager";

const LOCALES = [
    { code: "es", label: "Español", flag: "🇦🇷" },
    { code: "en", label: "English", flag: "🇺🇸" },
    { code: "pt", label: "Português", flag: "🇧🇷" },
];

function OrderInput({ img }) {
    const [value, setValue] = useState(img.order ?? "");
    const [saving, setSaving] = useState(false);

    const save = () => {
        const trimmed = value.toString().trim();
        if (trimmed === (img.order ?? "").toString()) return;

        setSaving(true);
        router.patch(`/admin/section-images/${img.id}`, { order: trimmed }, {
            preserveScroll: true,
            onFinish: () => setSaving(false),
        });
    };

    return (
        <div className="absolute bottom-1 left-1 right-1 flex items-center gap-1">
            <input
                type="text"
                value={value}
                onChange={(e) => setValue(e.target.value)}
                onBlur={save}
                onKeyDown={(e) => e.key === "Enter" && e.currentTarget.blur()}
                className="w-full rounded border border-white/30 bg-black/60 px-1 py-0.5 text-center text-[10px] font-bold text-white outline-none focus:border-white"
                title="Orden"
            />
            {saving && <Icon icon="solar:refresh-outline" width={10} className="animate-spin text-white" />}
        </div>
    );
}

function BannerSection({ section }) {
    const [open, setOpen] = useState(false);
    const fileRef = useRef(null);
    const [preview, setPreview] = useState(null);
    const { data, setData, post, processing } = useForm({ image: null });

    const images = section.images ?? [];

    const handleFile = (event) => {
        const file = event.target.files[0];
        if (!file) return;

        setData("image", file);
        setPreview(URL.createObjectURL(file));
    };

    const submit = (event) => {
        event.preventDefault();
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

    const removeImage = (id) => {
        if (!confirm("¿Eliminar esta imagen del banner?")) return;
        router.delete(`/admin/section-images/${id}`, { preserveScroll: true });
    };

    return (
        <div className="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
            <button
                type="button"
                onClick={() => setOpen((current) => !current)}
                className="flex w-full items-center justify-between px-5 py-4 text-left"
            >
                <div className="flex items-center gap-2">
                    <div className="flex h-8 w-8 items-center justify-center rounded-lg bg-[#25A7CA]/10">
                        <Icon icon="solar:gallery-wide-outline" width={16} className="text-[#25A7CA]" />
                    </div>
                    <div>
                        <p className="text-sm font-semibold text-gray-800">Banner principal</p>
                        <p className="text-xs text-gray-400">
                            {images.length === 0
                                ? "Sin imágenes todavía"
                                : images.length === 1
                                    ? "1 imagen cargada"
                                    : `${images.length} imágenes cargadas`}
                        </p>
                    </div>
                </div>
                <div className="flex items-center gap-3">
                    <span className={`rounded-full px-2 py-0.5 text-xs font-medium ${section.is_active ? "bg-green-100 text-green-700" : "bg-gray-100 text-gray-500"}`}>
                        {section.is_active ? "Activo" : "Inactivo"}
                    </span>
                    <Icon icon={open ? "solar:alt-arrow-up-outline" : "solar:alt-arrow-down-outline"} width={18} className="text-gray-400" />
                </div>
            </button>

            {open && <div className="space-y-4 border-t border-gray-100 p-5">
                {images.length > 0 ? (
                    <div className="grid grid-cols-2 gap-3 lg:grid-cols-3">
                        {images.map((img) => (
                            <div key={img.id} className="group relative overflow-hidden rounded-xl border border-gray-100" style={{ aspectRatio: "16 / 7" }}>
                                <img src={img.image_url} alt="" className="h-full w-full object-cover" />
                                <button
                                    type="button"
                                    onClick={() => removeImage(img.id)}
                                    className="absolute right-1.5 top-1.5 rounded-lg bg-red-500 p-1 text-white opacity-0 transition group-hover:opacity-100"
                                >
                                    <Icon icon="solar:trash-bin-trash-outline" width={13} />
                                </button>
                                <OrderInput img={img} />
                            </div>
                        ))}
                    </div>
                ) : (
                    <div className="flex h-28 items-center justify-center rounded-xl border-2 border-dashed border-gray-200 text-gray-300">
                        <div className="text-center">
                            <Icon icon="solar:gallery-outline" width={28} className="mx-auto mb-1" />
                            <p className="text-xs">Sin imágenes todavía</p>
                        </div>
                    </div>
                )}

                <form onSubmit={submit} className="flex flex-col items-start gap-3 rounded-xl border border-dashed border-gray-200 bg-gray-50 p-4 sm:flex-row sm:items-end">
                    <div className="flex-1">
                        <label className="mb-1 block text-xs font-medium text-gray-500">Agregar imagen al banner</label>
                        <p className="mb-2 text-xs text-gray-400">Recomendado: 1920×521 px · JPG/PNG · máx 5 MB</p>
                        <input
                            ref={fileRef}
                            type="file"
                            accept="image/*"
                            onChange={handleFile}
                            className="block w-full text-sm text-gray-500 file:mr-3 file:rounded-lg file:border-0 file:bg-[#25A7CA]/10 file:px-3 file:py-1.5 file:text-xs file:font-medium file:text-[#25A7CA] hover:file:bg-[#25A7CA]/20"
                        />
                        {preview && <img src={preview} alt="Vista previa" className="mt-2 h-16 rounded-lg border border-gray-100 object-cover" />}
                    </div>
                    <button
                        type="submit"
                        disabled={!data.image || processing}
                        className="inline-flex items-center gap-1.5 whitespace-nowrap rounded-lg bg-[#25A7CA] px-4 py-2 text-sm font-medium text-white transition hover:bg-[#1d96b8] disabled:opacity-40"
                    >
                        <Icon icon={processing ? "solar:refresh-outline" : "solar:add-circle-outline"} width={15} className={processing ? "animate-spin" : ""} />
                        {processing ? "Subiendo..." : "Agregar imagen"}
                    </button>
                </form>
            </div>}
        </div>
    );
}

function IntroSection({ section }) {
    const [open, setOpen] = useState(false);
    const [showTranslations, setShowTranslations] = useState(false);
    const { data, setData, post, processing } = useForm({
        _method: "PUT",
        title_es: section?.title_es ?? "",
        title_en: section?.title_en ?? "",
        title_pt: section?.title_pt ?? "",
        content_es: section?.content_es ?? "",
        content_en: section?.content_en ?? "",
        content_pt: section?.content_pt ?? "",
        is_active: section?.is_active ?? true,
    });

    const submit = (event) => {
        event.preventDefault();
        post(`/admin/sections/${section.id}`, { preserveScroll: true });
    };

    const primaryLocale = LOCALES[0];
    const secondaryLocales = LOCALES.slice(1);

    return (
        <div className="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
            <button
                type="button"
                onClick={() => setOpen((current) => !current)}
                className="flex w-full items-center justify-between px-5 py-4 text-left"
            >
                <div className="flex items-center gap-2">
                    <div className="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-100">
                        <Icon icon="solar:document-text-outline" width={16} className="text-emerald-600" />
                    </div>
                    <div>
                        <p className="text-sm font-semibold text-gray-800">Texto introductorio</p>
                        <p className="text-xs text-gray-400">
                            {data.title_es?.trim() || "Sin título en español"} · {data.content_es?.replace(/<[^>]+>/g, "").trim().slice(0, 80) || "Sin contenido todavía"}
                        </p>
                    </div>
                </div>
                <div className="flex items-center gap-3">
                    <span className={`rounded-full px-2 py-0.5 text-xs font-medium ${data.is_active ? "bg-green-100 text-green-700" : "bg-gray-100 text-gray-500"}`}>
                        {data.is_active ? "Activo" : "Inactivo"}
                    </span>
                    <Icon icon={open ? "solar:alt-arrow-up-outline" : "solar:alt-arrow-down-outline"} width={18} className="text-gray-400" />
                </div>
            </button>

            {open && <form onSubmit={submit} className="space-y-5 border-t border-gray-100 p-5">
                <div className="space-y-3 rounded-xl border border-gray-100 p-4">
                    <div className="flex items-center gap-2">
                        <span>{primaryLocale.flag}</span>
                        <span className="text-sm font-semibold text-gray-700">{primaryLocale.label}</span>
                    </div>

                    <div>
                        <label className="mb-1 block text-xs font-medium text-gray-500">Título principal</label>
                        <input
                            type="text"
                            value={data.title_es}
                            onChange={(e) => setData("title_es", e.target.value)}
                            className="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30"
                            placeholder="Ej: Últimas novedades"
                        />
                    </div>

                    <div>
                        <label className="mb-1 block text-xs font-medium text-gray-500">Texto principal</label>
                        <RichTextEditor
                            value={data.content_es}
                            onChange={(value) => setData("content_es", value)}
                            placeholder="Ej: Conocé lanzamientos, disponibilidad, incorporaciones y novedades destacadas de Gran Sasso."
                        />
                    </div>
                </div>

                <div className="rounded-xl border border-dashed border-gray-200 bg-gray-50">
                    <button
                        type="button"
                        onClick={() => setShowTranslations((current) => !current)}
                        className="flex w-full items-center justify-between px-4 py-3 text-left"
                    >
                        <div>
                            <p className="text-sm font-semibold text-gray-700">Otros idiomas</p>
                            <p className="text-xs text-gray-400">English y Português quedan ocultos hasta que los necesites editar.</p>
                        </div>
                        <Icon
                            icon={showTranslations ? "solar:alt-arrow-up-outline" : "solar:alt-arrow-down-outline"}
                            width={18}
                            className="text-gray-400"
                        />
                    </button>

                    {showTranslations && (
                        <div className="space-y-4 border-t border-gray-200 px-4 py-4">
                            {secondaryLocales.map((locale) => (
                                <div key={locale.code} className="space-y-3 rounded-xl border border-gray-100 bg-white p-4">
                                    <div className="flex items-center gap-2">
                                        <span>{locale.flag}</span>
                                        <span className="text-sm font-semibold text-gray-700">{locale.label}</span>
                                    </div>

                                    <div>
                                        <label className="mb-1 block text-xs font-medium text-gray-500">Título</label>
                                        <input
                                            type="text"
                                            value={data[`title_${locale.code}`]}
                                            onChange={(e) => setData(`title_${locale.code}`, e.target.value)}
                                            className="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30"
                                            placeholder={locale.code === "en" ? "Eg: Latest news" : "Ex: Últimas novidades"}
                                        />
                                    </div>

                                    <div>
                                        <label className="mb-1 block text-xs font-medium text-gray-500">Contenido</label>
                                        <RichTextEditor
                                            value={data[`content_${locale.code}`]}
                                            onChange={(value) => setData(`content_${locale.code}`, value)}
                                            placeholder="Escribí el contenido..."
                                        />
                                    </div>
                                </div>
                            ))}
                        </div>
                    )}
                </div>

                <div className="flex items-center justify-between border-t border-gray-100 pt-2">
                    <label className="flex cursor-pointer items-center gap-2">
                        <input
                            type="checkbox"
                            checked={!!data.is_active}
                            onChange={(e) => setData("is_active", e.target.checked)}
                            className="h-4 w-4 rounded accent-[#25A7CA]"
                        />
                        <span className="text-sm text-gray-700">Sección activa</span>
                    </label>

                    <button
                        type="submit"
                        disabled={processing}
                        className="inline-flex items-center gap-2 rounded-lg bg-[#25A7CA] px-5 py-2 text-sm font-medium text-white transition hover:bg-[#1d96b8] disabled:opacity-60"
                    >
                        <Icon icon={processing ? "solar:refresh-outline" : "solar:diskette-outline"} width={15} className={processing ? "animate-spin" : ""} />
                        {processing ? "Guardando..." : "Guardar texto"}
                    </button>
                </div>
            </form>}
        </div>
    );
}

export default function Novedades({ page, sections, novedades }) {
    const banner = sections.find((section) => section.type === "banner");
    const intro = sections.find((section) => section.type === "novedades_intro");

    return (
        <AdminLayout>
            <div className="max-w-4xl space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold text-gray-800">Novedades</h1>
                        <p className="text-sm text-gray-500">Configurá el banner, el texto y las novedades de la página pública.</p>
                    </div>
                    <a
                        href="/novedades"
                        target="_blank"
                        rel="noopener noreferrer"
                        className="inline-flex items-center gap-2 rounded-lg border border-gray-200 px-4 py-2 text-sm text-gray-600 transition hover:bg-gray-50"
                    >
                        <Icon icon="solar:eye-outline" width={16} />
                        Ver página
                    </a>
                </div>

                <div className="rounded-xl border border-[#25A7CA]/20 bg-[#25A7CA]/5 p-4">
                    <div className="flex items-start gap-3">
                        <Icon icon="solar:info-circle-outline" width={18} className="mt-0.5 flex-shrink-0 text-[#25A7CA]" />
                        <div className="text-sm text-gray-600">
                            <p className="mb-1 font-medium text-gray-700">Cómo funciona esta página</p>
                            <p>Desde acá editás el encabezado y también las novedades públicas. Este módulo es independiente de <strong>Zona Privada &gt; Novedades</strong>.</p>
                        </div>
                    </div>
                </div>

                {banner && <BannerSection section={banner} />}
                {intro && <IntroSection section={intro} />}

                <NovedadesManager
                    novedades={novedades}
                    baseUrl="/admin/public-novedades"
                    title="Novedades públicas"
                    description="Estas tarjetas se muestran en el slug público /novedades y no comparten contenido con la zona privada."
                    createLabel="Nueva novedad pública"
                    emptyLabel="No hay novedades públicas todavía."
                />
            </div>
        </AdminLayout>
    );
}
