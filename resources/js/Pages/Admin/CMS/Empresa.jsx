import { useRef, useState } from "react";
import AdminLayout from "@/Layouts/AdminLayout";
import { useForm, router } from "@inertiajs/react";
import { Icon } from "@iconify/react";
import RichTextEditor from "@/Components/Admin/RichTextEditor";
import ImageUploadField from "@/Components/Admin/ImageUploadField";

const LANGS = ["es", "en", "pt"];
const LANG_LABELS = { es: "🇦🇷 ES", en: "🇺🇸 EN", pt: "🇧🇷 PT" };

function OrderInput({ img }) {
    const [val, setVal] = useState(img.order ?? "");
    const [saving, setSaving] = useState(false);

    const save = () => {
        const trimmed = val.toString().trim().toUpperCase();
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
                value={val}
                onChange={(e) => setVal(e.target.value.toUpperCase())}
                onBlur={save}
                onKeyDown={(e) => e.key === "Enter" && e.target.blur()}
                className="w-full text-[10px] font-bold text-center bg-black/60 text-white border border-white/30 rounded px-1 py-0.5 outline-none focus:border-white"
                title="Orden (ej: A, B, AA...)"
            />
            {saving && <Icon icon="solar:refresh-outline" width={10} className="text-white animate-spin flex-shrink-0" />}
        </div>
    );
}

function GalleryManager({
    sectionId,
    images,
    title,
    description,
    recommendations,
    emptyLabel,
    deleteConfirm,
    uploadLabel,
    kind = "gallery",
    aspectRatio = "1 / 1",
    children = null,
}) {
    const { data, setData, post, processing } = useForm({ images: [], kind });
    const fileRef = useRef(null);
    const [previews, setPreviews] = useState([]);

    const handleFile = (e) => {
        const files = Array.from(e.target.files || []);
        if (!files.length) return;
        setData("images", files);
        setPreviews(files.map((file) => ({
            name: file.name,
            url: URL.createObjectURL(file),
        })));
    };

    const submit = (e) => {
        e.preventDefault();
        if (!data.images.length) return;

        post(`/admin/sections/${sectionId}/images`, {
            forceFormData: true,
            onSuccess: () => {
                setData("images", []);
                setPreviews([]);
                if (fileRef.current) fileRef.current.value = "";
            },
        });
    };

    const deleteImage = (id) => {
        if (!confirm(deleteConfirm)) return;
        router.delete(`/admin/section-images/${id}`);
    };

    return (
        <div className="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <div className="px-5 py-4 border-b border-gray-100">
                <p className="text-sm font-semibold text-gray-800">{title}</p>
                <p className="text-xs text-gray-400 mt-1">{description}</p>
            </div>

            <div className="p-5 space-y-4">
                {children}

                {images.length === 0 ? (
                    <div className="flex items-center justify-center h-28 rounded-xl border-2 border-dashed border-gray-200 text-gray-300">
                        <div className="text-center">
                            <Icon icon="solar:gallery-outline" width={28} className="mx-auto mb-1" />
                            <p className="text-xs">{emptyLabel}</p>
                        </div>
                    </div>
                ) : (
                    <div className="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        {images.map((img) => (
                            <div key={img.id} className="relative group rounded-xl overflow-hidden border border-gray-100 bg-gray-50" style={{ aspectRatio }}>
                                {img.image_url ? (
                                    <img src={img.image_url} alt="" className="w-full h-full object-cover" />
                                ) : (
                                    <div className="w-full h-full flex items-center justify-center text-gray-300">
                                        <Icon icon="solar:image-outline" width={22} />
                                    </div>
                                )}
                                <button
                                    type="button"
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
                        <label className="block text-xs font-medium text-gray-500 mb-1">{uploadLabel}</label>
                        <p className="text-xs text-gray-400 mb-2">{recommendations}</p>
                        <input
                            ref={fileRef}
                            type="file"
                            multiple
                            accept="image/*"
                            onChange={handleFile}
                            className="block w-full text-sm text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-[#25A7CA]/10 file:text-[#25A7CA] hover:file:bg-[#25A7CA]/20"
                        />
                        {previews.length > 0 && (
                            <div className="mt-3 grid grid-cols-2 sm:grid-cols-3 gap-2">
                                {previews.map((preview) => (
                                    <div key={`${preview.name}-${preview.url}`} className="rounded-lg overflow-hidden border border-gray-100 bg-white">
                                        <img src={preview.url} className="h-20 w-full object-cover" alt={preview.name} />
                                        <p className="px-2 py-1 text-[11px] text-gray-500 truncate">{preview.name}</p>
                                    </div>
                                ))}
                            </div>
                        )}
                    </div>
                    <button
                        type="submit"
                        disabled={!data.images.length || processing}
                        className="inline-flex items-center gap-1.5 px-4 py-2 bg-[#25A7CA] text-white rounded-lg text-sm font-medium hover:bg-[#1d96b8] disabled:opacity-40 transition whitespace-nowrap"
                    >
                        <Icon icon={processing ? "solar:refresh-outline" : "solar:add-circle-outline"} width={15} className={processing ? "animate-spin" : ""} />
                        {processing ? "Subiendo..." : data.images.length > 1 ? `Agregar ${data.images.length} imágenes` : "Agregar imagen"}
                    </button>
                </form>
            </div>
        </div>
    );
}

function BannerSection({ section }) {
    const { data, setData, post, processing } = useForm({ images: [] });
    const fileRef = useRef(null);
    const [previews, setPreviews] = useState([]);

    const handleFile = (e) => {
        const files = Array.from(e.target.files || []);
        if (!files.length) return;
        setData("images", files);
        setPreviews(files.map((file) => ({
            name: file.name,
            url: URL.createObjectURL(file),
        })));
    };

    const submit = (e) => {
        e.preventDefault();
        if (!data.images.length) return;
        post(`/admin/sections/${section.id}/images`, {
            forceFormData: true,
            onSuccess: () => {
                setData("images", []);
                setPreviews([]);
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
                {images.length === 0 ? (
                    <div className="flex items-center justify-center h-28 rounded-xl border-2 border-dashed border-gray-200 text-gray-300 mb-4">
                        <div className="text-center">
                            <Icon icon="solar:gallery-outline" width={28} className="mx-auto mb-1" />
                            <p className="text-xs">Sin imágenes todavía</p>
                        </div>
                    </div>
                ) : (
                    <div className="space-y-3 mb-4">
                        {images.map((img, index) => (
                            <div key={img.id} className="relative group overflow-hidden rounded-2xl border border-gray-200 bg-gradient-to-br from-slate-100 to-slate-50 shadow-sm">
                                <div className="absolute inset-x-0 top-0 z-10 flex items-center justify-between p-3">
                                    <div className="inline-flex items-center gap-2 rounded-full bg-black/55 px-3 py-1 text-[11px] font-semibold text-white backdrop-blur-sm">
                                        <span>Banner #{index + 1}</span>
                                        <span className="rounded-full bg-white/15 px-2 py-0.5 font-mono">{img.order}</span>
                                    </div>
                                    <button
                                        type="button"
                                        onClick={() => deleteImage(img.id)}
                                        className="rounded-xl bg-red-500/95 p-2 text-white opacity-0 shadow-sm transition group-hover:opacity-100"
                                        title="Eliminar"
                                    >
                                        <Icon icon="solar:trash-bin-trash-outline" width={14} />
                                    </button>
                                </div>

                                <div style={{ aspectRatio: "1920 / 521" }}>
                                    <img src={img.image_url} alt="" className="h-full w-full object-cover object-center" />
                                </div>
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
                            multiple
                            accept="image/*"
                            onChange={handleFile}
                            className="block w-full text-sm text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-[#25A7CA]/10 file:text-[#25A7CA] hover:file:bg-[#25A7CA]/20"
                        />
                        {previews.length > 0 && (
                            <div className="mt-4 space-y-3">
                                <div className="flex items-center gap-2 text-xs font-medium text-sky-700">
                                    <Icon icon="solar:eye-outline" width={15} />
                                    <span>Vista previa antes de subir</span>
                                </div>

                                {previews.map((preview, index) => (
                                    <div key={`${preview.name}-${preview.url}`} className="overflow-hidden rounded-2xl border border-sky-100 bg-white shadow-sm">
                                        <div className="flex items-center justify-between border-b border-sky-100 bg-sky-50/70 px-3 py-2">
                                            <span className="text-xs font-semibold text-sky-800">Nueva imagen #{index + 1}</span>
                                            <span className="max-w-[60%] truncate text-[11px] text-sky-700">{preview.name}</span>
                                        </div>
                                        <div style={{ aspectRatio: "1920 / 521" }}>
                                            <img src={preview.url} className="h-full w-full object-cover object-center" alt={preview.name} />
                                        </div>
                                    </div>
                                ))}
                            </div>
                        )}
                    </div>
                    <button
                        type="submit"
                        disabled={!data.images.length || processing}
                        className="inline-flex items-center gap-1.5 px-4 py-2 bg-[#25A7CA] text-white rounded-lg text-sm font-medium hover:bg-[#1d96b8] disabled:opacity-40 transition whitespace-nowrap"
                    >
                        <Icon icon={processing ? "solar:refresh-outline" : "solar:add-circle-outline"} width={15} className={processing ? "animate-spin" : ""} />
                        {processing ? "Subiendo..." : data.images.length > 1 ? `Agregar ${data.images.length} imágenes` : "Agregar imagen"}
                    </button>
                </form>
            </div>
        </div>
    );
}

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
                button_link: "",
                autoplay_seconds: 3,
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
                autoplay_seconds: initial.autoplay_seconds ?? 3,
                order: initial.order ?? "A",
                is_active: initial.is_active ?? true,
                image: null,
            }
    );

    const isAboutMain = isNew ? data.type === "about_main" : initial?.type === "about_main";
    const galleryImages = (initial?.images ?? []).filter((img) => (img.kind ?? "gallery") === "gallery");

    const submit = (e) => {
        e.preventDefault();
        const url = isNew ? `/admin/pages/${pageId}/sections` : `/admin/sections/${initial.id}`;
        post(url, { forceFormData: true, onSuccess });
    };

    const saveAutoplay = () => {
        if (!initial?.id) return;
        post(`/admin/sections/${initial.id}`, {
            forceFormData: true,
            preserveScroll: true,
            preserveState: true,
        });
    };

    return (
        <div className="space-y-5">
            {isAboutMain && (
                isNew ? (
                    <form onSubmit={submit} className="space-y-5">
                        <div className="bg-white rounded-xl border border-[#25A7CA]/40 shadow-sm p-5">
                            <p className="text-sm font-semibold text-gray-700 mb-1">Primera imagen del carrusel</p>
                            <p className="text-xs text-gray-400 mb-3">Después de crear la sección vas a poder sumar más imágenes, reordenarlas y editar el tiempo del autoplay.</p>
                            <ImageUploadField
                                currentUrl={null}
                                onChange={(f) => setData("image", f)}
                                specs={{ width: 600, height: 600, maxMB: 4, formats: ["JPG", "PNG"] }}
                                error={errors.image}
                            />
                        </div>

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
                                {processing ? "Guardando..." : "Crear sección"}
                            </button>
                        </div>
                    </form>
                ) : (
                    <GalleryManager
                        sectionId={initial.id}
                        images={galleryImages}
                        kind="gallery"
                        title="Galería de imágenes"
                        description="Estas imágenes se muestran en la columna izquierda y rotan automáticamente en la vista pública."
                        recommendations="Recomendado: 600×600 px · JPG/PNG · máx 4 MB"
                        emptyLabel="Todavía no cargaste imágenes para esta sección"
                        deleteConfirm="¿Eliminar esta imagen de la galería?"
                        uploadLabel="Agregar imagen a la galería"
                    >
                        <div className="rounded-xl border border-gray-200 bg-slate-50/70 p-4">
                            <div className="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                                <div>
                                    <p className="text-xs font-semibold uppercase tracking-wide text-gray-500">Autoplay</p>
                                    <p className="text-xs text-gray-400 mt-1">Este tiempo controla cada cuántos segundos cambia la imagen de la galería pública.</p>
                                </div>
                                <div className="flex items-end gap-3">
                                    <div>
                                        <label className="block text-xs text-gray-500 mb-1">Tiempo entre imágenes (segundos)</label>
                                        <input
                                            type="number"
                                            min="1"
                                            max="30"
                                            value={data.autoplay_seconds}
                                            onChange={(e) => setData("autoplay_seconds", e.target.value)}
                                            className="w-28 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30 bg-white"
                                        />
                                    </div>
                                    <button
                                        type="button"
                                        onClick={saveAutoplay}
                                        disabled={processing}
                                        className="inline-flex items-center justify-center gap-2 px-4 py-2 bg-[#25A7CA] text-white rounded-lg text-sm font-medium hover:bg-[#1d96b8] disabled:opacity-60 transition whitespace-nowrap"
                                    >
                                        <Icon icon={processing ? "solar:refresh-outline" : "solar:diskette-outline"} width={15} className={processing ? "animate-spin" : ""} />
                                        {processing ? "Guardando..." : "Guardar autoplay"}
                                    </button>
                                </div>
                            </div>
                            {errors.autoplay_seconds && <p className="text-red-500 text-xs mt-2">{errors.autoplay_seconds}</p>}
                        </div>
                    </GalleryManager>
                )
            )}

            {!isNew && (
                <form onSubmit={submit} className="space-y-5">
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
                            {processing ? "Guardando..." : "Guardar cambios"}
                        </button>
                    </div>
                </form>
            )}
        </div>
    );
}

const TYPE_LABELS = {
    about_main: "Quiénes somos",
    about_mision: "Misión",
    about_vision: "Visión",
    about_calidad: "Valores / Calidad",
};

function SectionCard({ section, onDelete }) {
    const [editing, setEditing] = useState(false);
    const galleryPreview = (section.images ?? []).find((img) => (img.kind ?? "gallery") === "gallery")?.image_url ?? section.image_url;

    if (editing) {
        return (
            <div className="bg-white rounded-xl border border-[#25A7CA]/30 shadow-sm p-5">
                <div className="flex items-center justify-between mb-4">
                    <p className="font-semibold text-gray-700 text-sm">
                        Editando: {TYPE_LABELS[section.type] ?? section.type}
                    </p>
                    <button type="button" onClick={() => setEditing(false)} className="text-gray-400 hover:text-gray-600">
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

            {section.type === "about_main" && (
                galleryPreview ? (
                    <img src={galleryPreview} alt="" className="h-14 w-20 object-cover rounded-lg flex-shrink-0 border border-gray-100" />
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
                    {section.type === "about_main" && (
                        <span className="text-xs px-2 py-0.5 rounded-full font-medium bg-sky-50 text-sky-700">
                            {section.autoplay_seconds ?? 3}s
                        </span>
                    )}
                </div>
                <p className="font-semibold text-gray-800 truncate">{section.title_es || "(Sin título)"}</p>
                {section.content_es && (
                    <p className="text-xs text-gray-400 mt-0.5 line-clamp-2" dangerouslySetInnerHTML={{ __html: section.content_es }} />
                )}
            </div>

            <div className="flex items-center gap-1 flex-shrink-0">
                <button type="button" onClick={() => setEditing(true)} className="p-2 text-gray-400 hover:text-[#25A7CA] hover:bg-blue-50 rounded-lg transition" title="Editar">
                    <Icon icon="solar:pen-outline" width={16} />
                </button>
                <button type="button" onClick={() => onDelete(section.id)} className="p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition" title="Eliminar">
                    <Icon icon="solar:trash-bin-trash-outline" width={16} />
                </button>
            </div>
        </div>
    );
}

export default function Empresa({ page, sections }) {
    const [showCreate, setShowCreate] = useState(false);

    const banner = sections.find((s) => s.type === "banner");
    const contentSections = sections.filter((s) => s.type !== "banner");

    const deleteSection = (id) => {
        if (!confirm("¿Eliminar esta sección?")) return;
        router.delete(`/admin/sections/${id}`);
    };

    return (
        <AdminLayout>
            <div className="max-w-4xl space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold text-gray-800">Empresa</h1>
                        <p className="text-sm text-gray-500">Editá el contenido de la página Empresa.</p>
                    </div>
                    {!showCreate && (
                        <button
                            type="button"
                            onClick={() => setShowCreate(true)}
                            className="inline-flex items-center gap-2 px-4 py-2 bg-[#25A7CA] text-white rounded-lg text-sm font-medium hover:bg-[#1d96b8] transition"
                        >
                            <Icon icon="solar:add-circle-outline" width={17} />
                            Nueva sección
                        </button>
                    )}
                </div>

                {banner && <BannerSection section={banner} />}

                {showCreate && (
                    <div className="bg-white rounded-xl border border-dashed border-[#25A7CA]/50 p-5">
                        <div className="flex items-center justify-between mb-4">
                            <p className="font-semibold text-gray-700 flex items-center gap-2">
                                <Icon icon="solar:add-circle-outline" width={17} className="text-[#25A7CA]" />
                                Nueva sección
                            </p>
                            <button type="button" onClick={() => setShowCreate(false)} className="text-gray-400 hover:text-gray-600">
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

                {contentSections.length === 0 && !showCreate ? (
                    <div className="bg-white rounded-xl border border-dashed border-gray-300 p-12 text-center">
                        <Icon icon="solar:document-text-outline" width={40} className="text-gray-300 mx-auto mb-3" />
                        <p className="text-gray-400 text-sm">No hay secciones de contenido.</p>
                        <button type="button" onClick={() => setShowCreate(true)} className="mt-3 text-sm text-[#25A7CA] hover:underline">
                            Agregar primera sección
                        </button>
                    </div>
                ) : (
                    <div className="space-y-3">
                        {contentSections.map((section) => (
                            <SectionCard key={section.id} section={section} onDelete={deleteSection} />
                        ))}
                    </div>
                )}
            </div>
        </AdminLayout>
    );
}
