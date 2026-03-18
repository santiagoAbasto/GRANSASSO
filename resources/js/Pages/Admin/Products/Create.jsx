import AdminLayout from "@/Layouts/AdminLayout";
import { useForm } from "@inertiajs/react";
import { Icon } from "@iconify/react";
import { useState, useEffect } from "react";
import RichTextEditor from "@/Components/Admin/RichTextEditor";

/* ── Toggle switch reutilizable ─────────────────────── */
function Toggle({ checked, onChange, label, description }) {
    return (
        <div className="flex items-center justify-between p-3 rounded-xl border border-gray-100 bg-gray-50/60">
            <div>
                <p className="text-sm font-medium text-gray-700">{label}</p>
                {description && (
                    <p className="text-xs text-gray-400 mt-0.5">{description}</p>
                )}
            </div>

            <button
                type="button"
                onClick={() => onChange(!checked)}
                className={`relative w-11 h-6 rounded-full transition-colors ${
                    checked ? "bg-[#25A7CA]" : "bg-gray-200"
                }`}
            >
                <span
                    className={`absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform ${
                        checked ? "translate-x-5" : "translate-x-0"
                    }`}
                />
            </button>
        </div>
    );
}

/* ── Main Create component ───────────────────────────── */
export default function ProductCreate({ categories, subcategories }) {

    const { data, setData, post, processing, errors } = useForm({

        category_id: "",
        subcategory_id: "",

        name: "",
        subtitle: "",
        brand: "",

        short_description: "",
        description: "",

        order: "",

        is_active: true,
        is_featured: false,
        show_in_home: false,

        image: null,
        video: null,
        video_url: "",

        brand_logo: null,

    });

    const [preview, setPreview] = useState(null);
    const [previewLogo, setPreviewLogo] = useState(null);
    const [mediaInfo, setMediaInfo] = useState(null);

    const filteredSubcats = subcategories.filter(
        (s) => !data.category_id || String(s.category_id) === String(data.category_id)
    );

    /* ── cleanup objectURL ── */
    useEffect(() => {
        return () => {
            if (preview) URL.revokeObjectURL(preview);
            if (previewLogo) URL.revokeObjectURL(previewLogo);
        };
    }, [preview, previewLogo]);

    /* ── FILE MEDIA ── */

    const handleFile = (e) => {

        const file = e.target.files[0];
        if (!file) return;

        const type = file.type;

        if (type.startsWith("video/")) {
            setData("video", file);
            setData("image", null);
        } else {
            setData("image", file);
            setData("video", null);
        }

        const ext = file.name.split(".").pop().toUpperCase();
        const sizeKB = file.size / 1024;
        const overMax = type.startsWith("video/") ? false : sizeKB / 1024 > 3;

        setMediaInfo({
            ext,
            sizeKB,
            isVideo: type.startsWith("video/"),
            overMax
        });

        setPreview(URL.createObjectURL(file));
    };

    /* ── BRAND LOGO ── */

    const handleLogoFile = (e) => {

        const file = e.target.files[0];
        if (!file) return;

        setData("brand_logo", file);
        setPreviewLogo(URL.createObjectURL(file));
    };

    /* ── ORDER ── */

    const handleOrder = (e) => {

        const val = e.target.value
            .toUpperCase()
            .replace(/[^A-Z]/g, "");

        setData("order", val);
    };

    /* ── SUBMIT ── */

    const submit = (e) => {

        e.preventDefault();

        post("/admin/products", {
            forceFormData: true,
        });
    };

    /* ── field helper ── */

    const field = (label, key) => (
        <div>
            <label className="block text-sm text-gray-600 mb-1">{label}</label>

            <input
                type="text"
                value={data[key]}
                onChange={(e) => setData(key, e.target.value)}
                className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30"
            />

            {errors[key] && (
                <p className="text-red-500 text-xs mt-1">{errors[key]}</p>
            )}
        </div>
    );

    return (
        <AdminLayout>

            <div className="max-w-3xl">

                {/* HEADER */}

                <div className="flex items-center gap-3 mb-6">

                    <button
                        onClick={() => history.back()}
                        className="p-2 rounded-lg hover:bg-gray-100 text-gray-500"
                    >
                        <Icon icon="solar:arrow-left-outline" width={18} />
                    </button>

                    <h1 className="text-2xl font-bold text-gray-800">
                        Nuevo Producto
                    </h1>

                </div>


                <form onSubmit={submit} className="space-y-6">

                    {/* INFO GENERAL */}

                    <div className="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-4">

                        <h2 className="font-semibold text-gray-700">
                            Información general
                        </h2>

                        {field("Nombre *", "name")}
                        {field("Subtítulo", "subtitle")}

                        {/* Marca */}

                        <div className="grid grid-cols-2 gap-4">

                            {field("Marca", "brand")}

                            <div>

                                <label className="block text-sm text-gray-600 mb-1">
                                    Logo de marca
                                </label>

                                {previewLogo && (
                                    <img
                                        src={previewLogo}
                                        className="h-10 object-contain mb-1 rounded"
                                        alt="logo preview"
                                    />
                                )}

                                <input
                                    type="file"
                                    accept="image/*"
                                    onChange={handleLogoFile}
                                    className="block w-full text-xs text-gray-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-[#25A7CA]/10 file:text-[#25A7CA]"
                                />

                            </div>

                        </div>


                        {/* CATEGORY */}

                        <div className="grid grid-cols-2 gap-4">

                            <div>

                                <label className="block text-sm text-gray-600 mb-1">
                                    Categoría *
                                </label>

                                <select
                                    value={data.category_id}
                                    onChange={(e) => setData("category_id", e.target.value)}
                                    className="w-full border border-gray-200 rounded-lg px-3 py-2"
                                >
                                    <option value="">Seleccionar...</option>

                                    {categories.map((c) => (
                                        <option key={c.id} value={c.id}>
                                            {c.name}
                                        </option>
                                    ))}

                                </select>

                            </div>

                            <div>

                                <label className="block text-sm text-gray-600 mb-1">
                                    Subcategoría
                                </label>

                                <select
                                    value={data.subcategory_id}
                                    onChange={(e) => setData("subcategory_id", e.target.value)}
                                    className="w-full border border-gray-200 rounded-lg px-3 py-2"
                                >
                                    <option value="">Sin subcategoría</option>

                                    {filteredSubcats.map((s) => (
                                        <option key={s.id} value={s.id}>
                                            {s.name}
                                        </option>
                                    ))}

                                </select>

                            </div>

                        </div>


                        {/* SHORT DESCRIPTION */}

                        <div>

                            <label className="block text-sm text-gray-600 mb-1">
                                Descripción corta
                            </label>

                            <textarea
                                value={data.short_description}
                                onChange={(e) => setData("short_description", e.target.value)}
                                rows={3}
                                className="w-full border border-gray-200 rounded-lg px-3 py-2"
                            />

                        </div>


                        {/* DESCRIPTION */}

                        <div>

                            <label className="block text-sm text-gray-600 mb-2">
                                Descripción completa
                            </label>

                            <RichTextEditor
                                value={data.description}
                                onChange={(v) => setData("description", v)}
                            />

                        </div>

                    </div>


                    {/* MULTIMEDIA */}

                    <div className="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">

                        <h2 className="font-semibold text-gray-700 mb-4">
                            Multimedia
                        </h2>

                        <div className="flex flex-wrap items-center gap-1.5 mb-3 px-3 py-2 rounded-lg bg-sky-50 border border-sky-100">
                            <Icon icon="solar:info-circle-outline" width={14} className="text-sky-400" />
                            <span className="text-xs text-sky-700">
                                Imagen recomendada: <strong>576×576 px</strong> · PNG/JPG · máx 3MB.
                                También podés subir video (MP4/WebM).
                            </span>
                        </div>

                        <input
                            type="file"
                            accept="image/*,video/mp4,video/webm,video/mov"
                            onChange={handleFile}
                            className="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-[#25A7CA]/10 file:text-[#25A7CA]"
                        />

                        {mediaInfo && (

                            <div className={`flex flex-wrap items-center gap-x-3 gap-y-1 mt-2 px-3 py-1.5 rounded-lg text-xs border ${
                                mediaInfo.overMax
                                    ? "bg-red-50 text-red-600 border-red-200"
                                    : "bg-gray-50 text-gray-500 border-gray-100"
                            }`}>

                                <Icon icon="solar:file-check-outline" width={13} />

                                <span>
                                    Tipo: <strong>{mediaInfo.isVideo ? "Video" : "Imagen"}</strong>
                                </span>

                                <span>
                                    Formato: <strong>{mediaInfo.ext}</strong>
                                </span>

                                <span>
                                    Tamaño: <strong>{mediaInfo.sizeKB >= 1024 ? `${(mediaInfo.sizeKB/1024).toFixed(2)} MB` : `${Math.round(mediaInfo.sizeKB)} KB`}</strong>
                                </span>

                            </div>

                        )}

                        {preview && (

                            mediaInfo?.isVideo
                                ? (
                                    <video
                                        src={preview}
                                        controls
                                        className="mt-3 h-40 rounded-lg"
                                    />
                                )
                                : (
                                    <img
                                        src={preview}
                                        className="mt-3 h-40 rounded-lg object-cover"
                                        alt="preview"
                                    />
                                )

                        )}

                        <div className="mt-4">

                            <label className="block text-sm text-gray-600 mb-1">
                                Video YouTube (opcional)
                            </label>

                            <input
                                type="text"
                                value={data.video_url}
                                onChange={(e) => setData("video_url", e.target.value)}
                                placeholder="https://youtube.com/watch?v=..."
                                className="w-full border border-gray-200 rounded-lg px-3 py-2"
                            />

                        </div>

                    </div>


                    {/* CONFIG */}

                    <div className="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">

                        <h2 className="font-semibold text-gray-700 mb-4">
                            Configuración
                        </h2>

                        <div className="space-y-3">

                            <input
                                type="text"
                                value={data.order}
                                onChange={handleOrder}
                                placeholder="A, AA, AB"
                                className="w-32 border border-gray-200 rounded-lg px-3 py-2 uppercase"
                            />

                            <Toggle
                                checked={data.is_active}
                                onChange={(v) => setData("is_active", v)}
                                label="Activo"
                            />

                            <Toggle
                                checked={data.is_featured}
                                onChange={(v) => setData("is_featured", v)}
                                label="Destacado"
                            />

                            <Toggle
                                checked={data.show_in_home}
                                onChange={(v) => setData("show_in_home", v)}
                                label="Mostrar en Home"
                            />

                        </div>

                    </div>


                    {/* BUTTONS */}

                    <div className="flex justify-end gap-3">

                        <button
                            type="button"
                            onClick={() => history.back()}
                            className="px-5 py-2.5 rounded-xl border border-gray-200"
                        >
                            Cancelar
                        </button>

                        <button
                            type="submit"
                            disabled={processing}
                            className="px-5 py-2.5 rounded-xl bg-[#25A7CA] text-white"
                        >
                            {processing ? "Guardando..." : "Crear Producto"}
                        </button>

                    </div>

                </form>

            </div>

        </AdminLayout>
    );
}
