import { useRef, useState } from "react";
import AdminLayout from "@/Layouts/AdminLayout";
import { router, useForm } from "@inertiajs/react";
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
                        onChange={(value) => setData(`${field}_${lang}`, value)}
                        placeholder={`Contenido en ${lang.toUpperCase()}...`}
                    />
                </div>
            ))}
        </div>
    );
}

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

function ImageCollectionManager({
    sectionId,
    images,
    kind = "gallery",
    title,
    description,
    recommendations,
    emptyLabel,
    deleteConfirm,
    uploadLabel,
    aspectRatio,
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
    const images = section.images ?? [];
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
                    <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 mb-4">
                        {images.map((img) => (
                            <div key={img.id} className="relative group rounded-xl overflow-hidden border border-gray-100" style={{ aspectRatio: "16 / 7" }}>
                                <img src={img.image_url} alt="" className="w-full h-full object-cover" />
                                <button
                                    type="button"
                                    onClick={() => deleteImage(img.id)}
                                    className="absolute top-1.5 right-1.5 p-1 rounded-lg bg-red-500 text-white opacity-0 group-hover:opacity-100 transition"
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

function ContentSection({ section }) {
    const [editing, setEditing] = useState(false);
    const galleryImages = (section.images ?? []).filter((img) => (img.kind ?? "default") === "gallery");
    const certImg = (section.images ?? []).find((img) => (img.kind ?? "default") === "badge")
        ?? ((section.images ?? [])[0] && !(section.images ?? [])[0].kind ? (section.images ?? [])[0] : null);

    const { data, setData, post, processing } = useForm({
        _method: "PUT",
        title_es: section.title_es ?? "",
        title_en: section.title_en ?? "",
        title_pt: section.title_pt ?? "",
        content_es: section.content_es ?? "",
        content_en: section.content_en ?? "",
        content_pt: section.content_pt ?? "",
        autoplay_seconds: section.autoplay_seconds ?? 3,
    });

    const { data: certData, setData: setCertData, post: postCert, processing: certProcessing } = useForm({
        image: null,
        kind: "badge",
    });
    const [certPreview, setCertPreview] = useState(certImg?.image_url ?? null);
    const certFileRef = useRef(null);

    const handleCertFile = (e) => {
        const file = e.target.files[0];
        if (!file) return;
        setCertData("image", file);
        setCertPreview(URL.createObjectURL(file));
    };

    const submitCert = (e) => {
        e.preventDefault();
        if (!certData.image) return;

        const upload = () => {
            postCert(`/admin/sections/${section.id}/images`, {
                forceFormData: true,
                onSuccess: () => {
                    setCertData("image", null);
                    if (certFileRef.current) certFileRef.current.value = "";
                },
            });
        };

        if (certImg) {
            router.delete(`/admin/section-images/${certImg.id}`, {
                preserveScroll: true,
                onSuccess: upload,
            });
            return;
        }

        upload();
    };

    const deleteCert = () => {
        if (!certImg) return;
        if (!confirm("¿Eliminar la imagen de certificación?")) return;
        router.delete(`/admin/section-images/${certImg.id}`);
    };

    const submit = (e) => {
        e.preventDefault();
        post(`/admin/sections/${section.id}`, {
            forceFormData: true,
            onSuccess: () => setEditing(false),
        });
    };

    const saveAutoplay = () => {
        post(`/admin/sections/${section.id}`, {
            forceFormData: true,
            preserveScroll: true,
            preserveState: true,
        });
    };

    return (
        <div className="space-y-5">
            <ImageCollectionManager
                sectionId={section.id}
                images={galleryImages}
                kind="gallery"
                title="Galería principal"
                description="Estas imágenes rotan automáticamente en la columna izquierda de la página pública."
                recommendations="Recomendado: 560×560 px · JPG/PNG · máx 4 MB"
                emptyLabel="Todavía no cargaste imágenes para esta sección"
                deleteConfirm="¿Eliminar esta imagen de la galería?"
                uploadLabel="Agregar imagen a la galería"
                aspectRatio="1 / 1"
            >
                <div className="rounded-xl border border-gray-200 bg-slate-50/70 p-4">
                    <div className="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p className="text-xs font-semibold uppercase tracking-wide text-gray-500">Autoplay</p>
                            <p className="text-xs text-gray-400 mt-1">Este tiempo controla cada cuántos segundos cambia la imagen de la galería pública.</p>
                        </div>
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
            </ImageCollectionManager>

            <div className="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div className="px-5 py-4 border-b border-gray-100">
                    <p className="text-sm font-semibold text-gray-800">Certificación ISO</p>
                    <p className="text-xs text-gray-400 mt-1">Este badge se superpone sobre la galería principal en la vista pública.</p>
                </div>

                <div className="p-5">
                    <label className="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">
                        Badge ISO
                    </label>
                    <p className="text-xs text-gray-400 mb-2">Recomendado: 100×100 px · PNG/SVG · máx 5 MB</p>
                    <div className="flex items-center gap-4 mt-2">
                        {certPreview ? (
                            <div className="relative group">
                                <img src={certPreview} alt="Cert badge" className="h-16 w-16 object-contain rounded-lg border border-gray-100 bg-gray-50" />
                                <button
                                    type="button"
                                    onClick={deleteCert}
                                    className="absolute -top-1.5 -right-1.5 w-5 h-5 bg-red-500 text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition"
                                >
                                    <Icon icon="solar:close-circle-bold" width={14} />
                                </button>
                            </div>
                        ) : (
                            <div className="h-16 w-16 rounded-lg border-2 border-dashed border-gray-200 flex items-center justify-center text-gray-300 bg-gray-50">
                                <Icon icon="solar:medal-ribbons-star-outline" width={22} />
                            </div>
                        )}

                        <form onSubmit={submitCert} className="flex items-center gap-3">
                            <label className="cursor-pointer inline-flex items-center gap-2 px-3 py-1.5 border border-gray-200 rounded-lg text-xs text-gray-600 hover:bg-gray-50 transition">
                                <Icon icon="solar:upload-outline" width={13} />
                                {certPreview ? "Reemplazar" : "Subir badge"}
                                <input ref={certFileRef} type="file" accept="image/*" className="hidden" onChange={handleCertFile} />
                            </label>
                            {certData.image && (
                                <button
                                    type="submit"
                                    disabled={certProcessing}
                                    className="inline-flex items-center gap-1.5 px-3 py-1.5 bg-[#25A7CA] text-white rounded-lg text-xs font-medium hover:bg-[#1d96b8] disabled:opacity-60 transition"
                                >
                                    <Icon icon={certProcessing ? "solar:refresh-outline" : "solar:diskette-outline"} width={12} className={certProcessing ? "animate-spin" : ""} />
                                    {certProcessing ? "Subiendo..." : "Guardar badge"}
                                </button>
                            )}
                        </form>
                    </div>
                </div>
            </div>

            <div className="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div className="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                    <div className="flex items-center gap-2">
                        <div className="flex items-center justify-center w-8 h-8 rounded-lg bg-emerald-100">
                            <Icon icon="solar:document-text-outline" width={16} className="text-emerald-600" />
                        </div>
                        <div>
                            <p className="text-sm font-semibold text-gray-800">Contenido principal</p>
                            <p className="text-xs text-gray-400">Título y contenido multilenguaje</p>
                        </div>
                    </div>
                    {!editing && (
                        <button
                            type="button"
                            onClick={() => setEditing(true)}
                            className="inline-flex items-center gap-1.5 px-3 py-1.5 border border-gray-200 rounded-lg text-sm text-gray-600 hover:bg-gray-50 transition"
                        >
                            <Icon icon="solar:pen-outline" width={14} />
                            Editar
                        </button>
                    )}
                </div>

                <div className="p-5 space-y-5">
                    {editing && (
                        <form onSubmit={submit} className="space-y-5">
                            <MultiInput label="Título" field="title" data={data} setData={setData} placeholder="Ej: Calidad y Precisión Técnica" />
                            <MultiRich label="Contenido" field="content" data={data} setData={setData} />

                            <div className="flex justify-end gap-3 pt-2 border-t border-gray-100">
                                <button type="button" onClick={() => setEditing(false)} className="px-4 py-2 border border-gray-200 rounded-lg text-sm text-gray-600 hover:bg-gray-50 transition">
                                    Cancelar
                                </button>
                                <button type="submit" disabled={processing} className="inline-flex items-center gap-2 px-5 py-2 bg-[#25A7CA] text-white rounded-lg text-sm font-medium hover:bg-[#1d96b8] disabled:opacity-60 transition">
                                    <Icon icon={processing ? "solar:refresh-outline" : "solar:diskette-outline"} width={15} className={processing ? "animate-spin" : ""} />
                                    {processing ? "Guardando..." : "Guardar cambios"}
                                </button>
                            </div>
                        </form>
                    )}

                    {!editing && (
                        <div className="rounded-xl bg-gray-50 border border-gray-100 p-4">
                            <div className="flex items-center gap-2 mb-2">
                                <span className="text-xs text-gray-400">
                                    {galleryImages.length} imagen{galleryImages.length !== 1 ? "es" : ""} en la galería
                                </span>
                            </div>
                            <p className="font-semibold text-gray-800">{section.title_es || "(Sin título)"}</p>
                            {section.content_es && (
                                <div className="text-xs text-gray-400 mt-1 line-clamp-3" dangerouslySetInnerHTML={{ __html: section.content_es }} />
                            )}
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
}

function formatFileSize(bytes) {
    if (!bytes) return "–";
    if (bytes < 1024) return `${bytes} B`;
    if (bytes < 1024 * 1024) return `${Math.round(bytes / 1024)} KB`;
    return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
}

function AddDownloadForm({ onCancel }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        name_es: "", name_en: "", name_pt: "",
        logo: null,
        file: null,
        order: "",
    });
    const [logoPreview, setLogoPreview] = useState(null);
    const [fileName, setFileName] = useState(null);
    const [fileInfo, setFileInfo] = useState(null);

    const handleLogo = (e) => {
        const file = e.target.files[0];
        if (!file) return;
        setData("logo", file);
        setLogoPreview(URL.createObjectURL(file));
    };

    const handleFile = (e) => {
        const file = e.target.files[0];
        if (!file) return;
        setData("file", file);
        setFileName(file.name);
        setFileInfo(`${file.name.split(".").pop().toUpperCase()} · ${formatFileSize(file.size)}`);
    };

    const submit = (e) => {
        e.preventDefault();
        post("/admin/calidad-downloads", {
            forceFormData: true,
            onSuccess: () => {
                reset();
                onCancel();
            },
        });
    };

    return (
        <form onSubmit={submit} className="bg-white rounded-xl border border-[#25A7CA]/30 shadow-sm p-5 space-y-5">
            <h3 className="font-semibold text-gray-700 text-sm flex items-center gap-2">
                <Icon icon="solar:add-circle-outline" width={16} className="text-[#25A7CA]" />
                Nueva descarga
            </h3>

            <MultiInput label="Nombre del archivo" field="name" data={data} setData={setData} placeholder="Ej: Certificación ISO 9001:2015" />

            <div>
                <label className="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Logo / Ícono</label>
                <p className="text-xs text-gray-400 mb-2">Recomendado: 120×120 px · PNG/SVG · máx 2 MB</p>
                <div className="flex items-center gap-4">
                    {logoPreview ? (
                        <img src={logoPreview} alt="" className="w-12 h-12 rounded-full object-cover border border-gray-100" />
                    ) : (
                        <div className="w-12 h-12 rounded-full border-2 border-dashed border-gray-200 flex items-center justify-center text-gray-300">
                            <Icon icon="solar:image-outline" width={18} />
                        </div>
                    )}
                    <label className="cursor-pointer inline-flex items-center gap-2 px-3 py-1.5 border border-gray-200 rounded-lg text-xs text-gray-600 hover:bg-gray-50 transition">
                        <Icon icon="solar:upload-outline" width={13} />
                        {logoPreview ? "Cambiar" : "Subir logo"}
                        <input type="file" accept="image/*" className="hidden" onChange={handleLogo} />
                    </label>
                </div>
                {errors.logo && <p className="text-red-500 text-xs mt-1">{errors.logo}</p>}
            </div>

            <div>
                <label className="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Archivo descargable *</label>
                <p className="text-xs text-gray-400 mb-2">Recomendado: PDF · máx 30 MB</p>
                <label className="cursor-pointer flex items-center gap-3 p-3 bg-gray-50 rounded-xl border border-dashed border-gray-200 hover:border-[#25A7CA]/40 transition">
                    <Icon icon="solar:file-send-outline" width={22} className="text-gray-400 flex-shrink-0" />
                    <div className="flex-1 min-w-0">
                        {fileName ? (
                            <>
                                <p className="text-sm font-medium text-gray-700 truncate">{fileName}</p>
                                <p className="text-xs text-gray-400">{fileInfo}</p>
                            </>
                        ) : (
                            <>
                                <p className="text-sm text-gray-500">Elegir archivo...</p>
                                <p className="text-xs text-gray-400">PDF, DOCX, DWG, ZIP, etc.</p>
                            </>
                        )}
                    </div>
                    <input type="file" className="hidden" onChange={handleFile} />
                </label>
                {errors.file && <p className="text-red-500 text-xs mt-1">{errors.file}</p>}
            </div>

            <div className="flex items-center justify-end gap-3 pt-2 border-t border-gray-100">
                <button type="button" onClick={onCancel} className="px-4 py-2 text-sm text-gray-500 hover:text-gray-700 rounded-lg hover:bg-gray-50 transition">
                    Cancelar
                </button>
                <button
                    type="submit"
                    disabled={!data.file || processing}
                    className="inline-flex items-center gap-2 px-5 py-2 bg-[#25A7CA] text-white rounded-lg text-sm font-medium hover:bg-[#1d96b8] disabled:opacity-60 transition"
                >
                    <Icon icon={processing ? "solar:refresh-outline" : "solar:add-circle-outline"} width={15} className={processing ? "animate-spin" : ""} />
                    {processing ? "Subiendo..." : "Agregar descarga"}
                </button>
            </div>
        </form>
    );
}

function DownloadCard({ item, onDelete }) {
    const [editing, setEditing] = useState(false);
    const { data, setData, post, processing } = useForm({
        _method: "PUT",
        name_es: item.name_es ?? "",
        name_en: item.name_en ?? "",
        name_pt: item.name_pt ?? "",
        logo: null,
        file: null,
        order: item.order ?? "",
        is_active: item.is_active,
    });
    const [logoPreview, setLogoPreview] = useState(item.logo_url ?? null);
    const [newFileName, setNewFileName] = useState(null);
    const [newFileInfo, setNewFileInfo] = useState(null);

    const handleLogo = (e) => {
        const file = e.target.files[0];
        if (!file) return;
        setData("logo", file);
        setLogoPreview(URL.createObjectURL(file));
    };

    const handleFile = (e) => {
        const file = e.target.files[0];
        if (!file) return;
        setData("file", file);
        setNewFileName(file.name);
        setNewFileInfo(`${file.name.split(".").pop().toUpperCase()} · ${formatFileSize(file.size)}`);
    };

    const submit = (e) => {
        e.preventDefault();
        post(`/admin/calidad-downloads/${item.id}`, {
            forceFormData: true,
            onSuccess: () => setEditing(false),
        });
    };

    if (editing) {
        return (
            <div className="bg-white rounded-xl border border-[#25A7CA]/30 shadow-sm p-4 space-y-4">
                <div className="flex items-center justify-between">
                    <p className="text-sm font-semibold text-gray-700">Editando descarga</p>
                    <button type="button" onClick={() => setEditing(false)} className="text-gray-400 hover:text-gray-600">
                        <Icon icon="solar:close-circle-outline" width={18} />
                    </button>
                </div>
                <form onSubmit={submit} className="space-y-4">
                    <MultiInput label="Nombre" field="name" data={data} setData={setData} />
                    <div>
                        <label className="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Logo / Ícono</label>
                        <p className="text-xs text-gray-400 mb-2">Recomendado: 120×120 px · PNG/SVG · máx 2 MB</p>
                        <div className="flex items-center gap-3">
                            {logoPreview ? (
                                <img src={logoPreview} alt="" className="w-10 h-10 rounded-full object-cover border border-gray-100" />
                            ) : (
                                <div className="w-10 h-10 rounded-full border-2 border-dashed border-gray-200 flex items-center justify-center text-gray-300">
                                    <Icon icon="solar:image-outline" width={14} />
                                </div>
                            )}
                            <label className="cursor-pointer inline-flex items-center gap-2 px-3 py-1.5 border border-gray-200 rounded-lg text-xs text-gray-600 hover:bg-gray-50 transition">
                                <Icon icon="solar:upload-outline" width={12} />
                                Cambiar logo
                                <input type="file" accept="image/*" className="hidden" onChange={handleLogo} />
                            </label>
                        </div>
                    </div>
                    <div>
                        <label className="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Reemplazar archivo (opcional)</label>
                        <p className="text-xs text-gray-400 mb-2">Recomendado: PDF · máx 30 MB</p>
                        <label className="cursor-pointer flex items-center gap-3 p-3 bg-gray-50 rounded-xl border border-dashed border-gray-200 hover:border-[#25A7CA]/40 transition">
                            <Icon icon="solar:file-send-outline" width={18} className="text-gray-400 flex-shrink-0" />
                            <div className="flex-1 min-w-0">
                                {newFileName ? (
                                    <>
                                        <p className="text-xs font-medium text-gray-700 truncate">{newFileName}</p>
                                        <p className="text-xs text-gray-400">{newFileInfo}</p>
                                    </>
                                ) : (
                                    <p className="text-xs text-gray-400">
                                        Archivo actual: {item.file_format} · {formatFileSize(item.file_size)}
                                    </p>
                                )}
                            </div>
                            <input type="file" className="hidden" onChange={handleFile} />
                        </label>
                    </div>
                    <div className="flex items-center gap-4">
                        <div>
                            <label className="block text-xs text-gray-500 mb-1">Orden</label>
                            <input
                                type="text"
                                maxLength={20}
                                value={data.order}
                                onChange={(e) => setData("order", e.target.value.toUpperCase())}
                                className="w-20 border border-gray-200 rounded-lg px-3 py-1.5 text-sm font-mono uppercase focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30"
                            />
                        </div>
                        <label className="flex items-center gap-2 cursor-pointer">
                            <input
                                type="checkbox"
                                checked={data.is_active}
                                onChange={(e) => setData("is_active", e.target.checked)}
                                className="w-4 h-4 rounded accent-[#25A7CA]"
                            />
                            <span className="text-sm text-gray-700">Activo</span>
                        </label>
                    </div>
                    <div className="flex justify-end gap-3 pt-2 border-t border-gray-100">
                        <button type="button" onClick={() => setEditing(false)} className="px-4 py-2 border border-gray-200 rounded-lg text-sm text-gray-600 hover:bg-gray-50 transition">
                            Cancelar
                        </button>
                        <button type="submit" disabled={processing} className="inline-flex items-center gap-2 px-5 py-2 bg-[#25A7CA] text-white rounded-lg text-sm font-medium hover:bg-[#1d96b8] disabled:opacity-60 transition">
                            <Icon icon={processing ? "solar:refresh-outline" : "solar:diskette-outline"} width={15} />
                            {processing ? "Guardando..." : "Guardar"}
                        </button>
                    </div>
                </form>
            </div>
        );
    }

    return (
        <div className="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex items-center gap-4">
            {item.logo_url ? (
                <img src={item.logo_url} alt="" className="w-10 h-10 rounded-full object-cover border border-gray-100 flex-shrink-0" />
            ) : (
                <div className="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center flex-shrink-0">
                    <Icon icon="solar:file-outline" width={18} className="text-gray-400" />
                </div>
            )}
            <div className="flex-1 min-w-0">
                <p className="font-semibold text-gray-800 truncate">{item.name_es || "(Sin nombre)"}</p>
                <p className="text-xs text-gray-400 mt-0.5 flex items-center gap-1.5">
                    {item.file_format && (
                        <span className="bg-gray-100 text-gray-600 px-1.5 py-0.5 rounded text-[10px] font-medium">{item.file_format}</span>
                    )}
                    {formatFileSize(item.file_size)}
                    <span className="font-mono text-gray-300 ml-1">{item.order}</span>
                </p>
            </div>
            <span className={`text-xs px-2 py-0.5 rounded-full font-medium flex-shrink-0 ${item.is_active ? "bg-green-100 text-green-700" : "bg-gray-100 text-gray-500"}`}>
                {item.is_active ? "Activo" : "Inactivo"}
            </span>
            <div className="flex items-center gap-1 flex-shrink-0">
                <button type="button" onClick={() => setEditing(true)} className="p-2 text-gray-400 hover:text-[#25A7CA] hover:bg-blue-50 rounded-lg transition">
                    <Icon icon="solar:pen-outline" width={15} />
                </button>
                <button type="button" onClick={() => onDelete(item.id)} className="p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition">
                    <Icon icon="solar:trash-bin-trash-outline" width={15} />
                </button>
            </div>
        </div>
    );
}

function DownloadsSection({ downloads }) {
    const [showAdd, setShowAdd] = useState(false);

    const handleDelete = (id) => {
        if (!confirm("¿Eliminar esta descarga?")) return;
        router.delete(`/admin/calidad-downloads/${id}`);
    };

    return (
        <div className="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div className="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <div className="flex items-center gap-2">
                    <div className="flex items-center justify-center w-8 h-8 rounded-lg bg-orange-100">
                        <Icon icon="solar:download-minimalistic-outline" width={16} className="text-orange-600" />
                    </div>
                    <div>
                        <p className="text-sm font-semibold text-gray-800">Descargas</p>
                        <p className="text-xs text-gray-400">
                            {downloads.length === 0
                                ? "Sin archivos — la sección no se mostrará"
                                : `${downloads.length} archivo${downloads.length !== 1 ? "s" : ""} disponible${downloads.length !== 1 ? "s" : ""}`}
                        </p>
                    </div>
                </div>
                {!showAdd && (
                    <button
                        type="button"
                        onClick={() => setShowAdd(true)}
                        className="inline-flex items-center gap-2 px-3 py-1.5 bg-[#25A7CA] text-white rounded-lg text-sm font-medium hover:bg-[#1d96b8] transition"
                    >
                        <Icon icon="solar:add-circle-outline" width={15} />
                        Agregar
                    </button>
                )}
            </div>
            <div className="p-5 space-y-3">
                {showAdd && <AddDownloadForm onCancel={() => setShowAdd(false)} />}
                {downloads.length === 0 && !showAdd ? (
                    <div className="flex items-center justify-center h-24 rounded-xl border-2 border-dashed border-gray-200 text-gray-300">
                        <div className="text-center">
                            <Icon icon="solar:folder-open-outline" width={28} className="mx-auto mb-1" />
                            <p className="text-xs">Sin descargas todavía</p>
                        </div>
                    </div>
                ) : (
                    downloads.map((item) => (
                        <DownloadCard key={item.id} item={item} onDelete={handleDelete} />
                    ))
                )}
            </div>
        </div>
    );
}

export default function Calidad({ sections, downloads }) {
    const banner = sections.find((section) => section.type === "banner");
    const content = sections.find((section) => section.type === "calidad_main");

    return (
        <AdminLayout>
            <div className="max-w-4xl space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold text-gray-800">Calidad</h1>
                        <p className="text-sm text-gray-500">Editá el contenido, las imágenes y las descargas.</p>
                    </div>
                    <a
                        href="/calidad"
                        target="_blank"
                        rel="noopener noreferrer"
                        className="inline-flex items-center gap-2 px-4 py-2 border border-gray-200 text-gray-600 rounded-lg text-sm hover:bg-gray-50 transition"
                    >
                        <Icon icon="solar:eye-outline" width={16} />
                        Ver página
                    </a>
                </div>

                {banner && <BannerSection section={banner} />}
                {content && <ContentSection section={content} />}
                <DownloadsSection downloads={downloads ?? []} />
            </div>
        </AdminLayout>
    );
}
