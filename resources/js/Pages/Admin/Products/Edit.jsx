import AdminLayout from "@/Layouts/AdminLayout";
import { useForm, router } from "@inertiajs/react";
import { Icon } from "@iconify/react";
import { useState } from "react";
import RichTextEditor from "@/Components/Admin/RichTextEditor";
import ImageUploadField from "@/Components/Admin/ImageUploadField";

/* ── Toggle switch reutilizable ─────────────────────── */
function Toggle({ checked, onChange, label, description }) {
    return (
        <div className="flex items-center justify-between p-3 rounded-xl border border-gray-100 bg-gray-50/60">
            <div>
                <p className="text-sm font-medium text-gray-700">{label}</p>
                {description && <p className="text-xs text-gray-400 mt-0.5">{description}</p>}
            </div>
            <button
                type="button"
                onClick={() => onChange(!checked)}
                className={`relative w-11 h-6 rounded-full transition-colors focus:outline-none ${checked ? "bg-[#25A7CA]" : "bg-gray-200"
                    }`}
            >
                <span
                    className={`absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform ${checked ? "translate-x-5" : "translate-x-0"
                        }`}
                />
            </button>
        </div>
    );
}

/* ── Especificaciones técnicas ────────────────────────── */
function SpecsSection({ product }) {
    const [specs, setSpecs] = useState(product.specifications || []);
    const [newName, setNewName] = useState("");
    const [editingId, setEditingId] = useState(null);
    const [editData, setEditData] = useState({});

    const addSpec = (e) => {
        e.preventDefault();
        if (!newName.trim()) return;
        router.post(
            `/admin/products/${product.id}/specs`,
            { name: newName.trim() },
            {
                preserveScroll: true,
                onSuccess: (page) => {
                    setSpecs(page.props.product?.specifications ?? specs);
                    setNewName("");
                },
            }
        );
    };

    const startEdit = (spec) => {
        setEditingId(spec.id);
        setEditData({ name: spec.name, value: spec.value || "" });
    };

    const saveEdit = (specId) => {
        router.put(
            `/admin/products/${product.id}/specs/${specId}`,
            editData,
            {
                preserveScroll: true,
                onSuccess: (page) => {
                    setSpecs(page.props.product?.specifications ?? specs);
                    setEditingId(null);
                },
            }
        );
    };

    const deleteSpec = (specId) => {
        if (!confirm("¿Eliminar esta característica?")) return;
        router.delete(
            `/admin/products/${product.id}/specs/${specId}`,
            {
                preserveScroll: true,
                onSuccess: (page) => {
                    setSpecs(page.props.product?.specifications ?? specs.filter((s) => s.id !== specId));
                },
            }
        );
    };

    return (
        <div className="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h2 className="font-semibold text-gray-700 mb-4 flex items-center gap-2">
                <Icon icon="solar:checklist-outline" width={18} className="text-[#25A7CA]" />
                Características técnicas
            </h2>

            <div className="space-y-2 mb-4">
                {specs.length === 0 && (
                    <p className="text-sm text-gray-400 py-2">Sin características. Agregá la primera.</p>
                )}
                {specs.map((spec) => (
                    <div
                        key={spec.id}
                        className="flex items-center gap-2 p-2 rounded-lg border border-gray-100 bg-gray-50/50"
                    >
                        {editingId === spec.id ? (
                            <>
                                <input
                                    autoFocus
                                    type="text"
                                    value={editData.name}
                                    onChange={(e) => setEditData({ ...editData, name: e.target.value })}
                                    onKeyDown={(e) => { if (e.key === "Enter") { e.preventDefault(); saveEdit(spec.id); } if (e.key === "Escape") setEditingId(null); }}
                                    className="flex-1 border border-gray-200 rounded-lg px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30"
                                />
                                <button type="button" onClick={() => saveEdit(spec.id)} className="p-1.5 rounded-lg bg-[#25A7CA] text-white hover:bg-[#1d96b8]">
                                    <Icon icon="solar:check-read-outline" width={14} />
                                </button>
                                <button type="button" onClick={() => setEditingId(null)} className="p-1.5 rounded-lg hover:bg-gray-200 text-gray-500">
                                    <Icon icon="solar:close-square-outline" width={14} />
                                </button>
                            </>
                        ) : (
                            <>
                                <Icon icon="solar:check-circle-outline" width={15} className="text-[#25A7CA] flex-shrink-0" />
                                <span className="flex-1 text-sm text-gray-700">{spec.name}</span>
                                <button type="button" onClick={() => startEdit(spec)} className="p-1.5 rounded-lg hover:bg-gray-200 text-gray-500">
                                    <Icon icon="solar:pen-outline" width={14} />
                                </button>
                                <button type="button" onClick={() => deleteSpec(spec.id)} className="p-1.5 rounded-lg hover:bg-red-50 text-red-400">
                                    <Icon icon="solar:trash-bin-trash-outline" width={14} />
                                </button>
                            </>
                        )}
                    </div>
                ))}
            </div>

            <form onSubmit={addSpec} className="flex gap-2">
                <input
                    type="text"
                    value={newName}
                    onChange={(e) => setNewName(e.target.value)}
                    placeholder="Ej: Rango de medición: 0–100 bar"
                    className="flex-1 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30"
                />
                <button type="submit" className="flex items-center gap-1.5 px-4 py-2 bg-[#25A7CA] text-white rounded-lg text-sm font-medium hover:bg-[#1d96b8] transition">
                    <Icon icon="solar:add-circle-outline" width={16} />
                    Agregar
                </button>
            </form>
        </div>
    );
}

/* ── Descargas del producto ────────────────────────── */
function DownloadsSection({ product }) {
    const [downloads, setDownloads] = useState(product.downloads || []);
    const [dlFileInfo, setDlFileInfo] = useState(null);

    const { data, setData, post, processing, reset } = useForm({
        name: "",
        file: null,
        type: "",
    });

    const upload = (e) => {
        e.preventDefault();
        post(`/admin/products/${product.id}/downloads`, {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: (page) => {
                setDownloads(page.props.product?.downloads ?? downloads);
                reset();
            },
        });
    };

    const togglePrincipal = (dlId) => {
        router.patch(
            `/admin/products/${product.id}/downloads/${dlId}/toggle-principal`,
            {},
            {
                preserveScroll: true,
                onSuccess: (page) => {
                    setDownloads(
                        page.props.product?.downloads ??
                        downloads.map((d) => ({
                            ...d,
                            is_principal: d.id === dlId ? !d.is_principal : (d.is_principal && d.id !== dlId ? false : d.is_principal),
                        }))
                    );
                },
            }
        );
    };

    const deleteDownload = (dlId) => {
        if (!confirm("¿Eliminar este archivo?")) return;
        router.delete(
            `/admin/products/${product.id}/downloads/${dlId}`,
            {
                preserveScroll: true,
                onSuccess: (page) => {
                    setDownloads(page.props.product?.downloads ?? downloads.filter((d) => d.id !== dlId));
                },
            }
        );
    };

    return (
        <div className="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h2 className="font-semibold text-gray-700 mb-1 flex items-center gap-2">
                <Icon icon="solar:file-download-outline" width={18} className="text-[#25A7CA]" />
                Descargas del producto
            </h2>
            <p className="text-xs text-gray-400 mb-4">
                El archivo marcado como <strong className="text-gray-600">⭐ Principal</strong> es el que se descarga al hacer clic en "Consultar" en la web pública.
            </p>

            <div className="space-y-2 mb-5">
                {downloads.length === 0 && (
                    <p className="text-sm text-gray-400 py-2">Sin archivos. Subí el primero.</p>
                )}
                {downloads.map((dl) => (
                    <div
                        key={dl.id}
                        className={`flex items-center gap-3 p-3 rounded-xl border transition ${dl.is_principal
                            ? "border-[#25A7CA]/40 bg-[#25A7CA]/5"
                            : "border-gray-100 bg-gray-50/40"
                            }`}
                    >
                        <Icon
                            icon={/\.(mp4|webm|mov|avi)$/i.test(dl.name) ? "solar:play-circle-outline" : "solar:file-text-outline"}
                            width={18}
                            className="text-gray-400 flex-shrink-0"
                        />
                        <div className="flex-1 min-w-0">
                            <p className="text-sm font-medium text-gray-800 truncate">{dl.name}</p>
                            <p className="text-xs text-gray-400">{[dl.type, dl.size].filter(Boolean).join(" · ")}</p>
                        </div>

                        <button
                            type="button"
                            onClick={() => togglePrincipal(dl.id)}
                            title={dl.is_principal ? "Quitar como principal" : "Marcar como principal"}
                            className={`flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium transition ${dl.is_principal
                                ? "bg-[#25A7CA] text-white"
                                : "bg-gray-100 text-gray-500 hover:bg-gray-200"
                                }`}
                        >
                            <Icon icon={dl.is_principal ? "solar:star-bold" : "solar:star-outline"} width={13} />
                            Principal
                        </button>

                        <a href={dl.file_url} target="_blank" rel="noreferrer" className="p-1.5 rounded-lg hover:bg-gray-200 text-gray-500" title="Ver archivo">
                            <Icon icon="solar:eye-outline" width={15} />
                        </a>
                        <button type="button" onClick={() => deleteDownload(dl.id)} className="p-1.5 rounded-lg hover:bg-red-50 text-red-400">
                            <Icon icon="solar:trash-bin-trash-outline" width={15} />
                        </button>
                    </div>
                ))}
            </div>

            <form onSubmit={upload} className="border-t border-gray-100 pt-4 space-y-3">
                <p className="text-xs font-medium text-gray-500 mb-2">Subir nuevo archivo</p>

                {/* Spec hint */}
                <div className="flex flex-wrap items-center gap-1.5 px-3 py-2 rounded-lg bg-sky-50 border border-sky-100">
                    <Icon icon="solar:info-circle-outline" width={13} className="text-sky-400 flex-shrink-0" />
                    <span className="text-xs text-sky-700">
                        Cualquier formato (PDF, DWG, MP4, ZIP…) · máx <strong>10 MB</strong>
                    </span>
                </div>

                <div className="grid grid-cols-2 gap-3">
                    <div>
                        <label className="block text-xs text-gray-500 mb-1">Nombre del archivo *</label>
                        <input
                            type="text"
                            value={data.name}
                            onChange={(e) => setData("name", e.target.value)}
                            placeholder="Ej: Ficha técnica v2"
                            className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30"
                        />
                    </div>
                    <div>
                        <label className="block text-xs text-gray-500 mb-1">Tipo (opcional)</label>
                        <input
                            type="text"
                            value={data.type}
                            onChange={(e) => setData("type", e.target.value)}
                            placeholder="PDF, DWG, MP4…"
                            className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30"
                        />
                    </div>
                </div>
                <div className="flex items-end gap-3">
                    <div className="flex-1">
                        <label className="block text-xs text-gray-500 mb-1">Archivo *</label>
                        <input
                            type="file"
                            accept=".pdf,.doc,.docx,.xls,.xlsx,.dwg,.zip,.rar,.mp4,.webm,.mov,.avi"
                            onChange={(e) => {
                                const f = e.target.files[0];
                                if (!f) return;
                                setData("file", f);
                                const ext    = f.name.split(".").pop().toUpperCase();
                                const sizeKB = f.size / 1024;
                                setDlFileInfo({ ext, sizeKB, overMax: sizeKB / 1024 > 10 });
                            }}
                            className="block w-full text-sm text-gray-500 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-[#25A7CA]/10 file:text-[#25A7CA]"
                        />
                        {dlFileInfo && (
                            <div className={`flex flex-wrap items-center gap-x-3 gap-y-0.5 mt-1.5 px-2.5 py-1.5 rounded-lg text-xs border ${
                                dlFileInfo.overMax ? "bg-red-50 text-red-600 border-red-200" : "bg-gray-50 text-gray-500 border-gray-100"
                            }`}>
                                <Icon icon={dlFileInfo.overMax ? "solar:danger-triangle-outline" : "solar:file-check-outline"} width={12} className="flex-shrink-0" />
                                <span>Formato: <strong>{dlFileInfo.ext}</strong></span>
                                <span>Tamaño: <strong>{dlFileInfo.sizeKB >= 1024 ? `${(dlFileInfo.sizeKB/1024).toFixed(2)} MB` : `${Math.round(dlFileInfo.sizeKB)} KB`}</strong></span>
                                {dlFileInfo.overMax && <span className="font-semibold">⚠ Excede el límite de 10 MB</span>}
                            </div>
                        )}
                    </div>
                    <button
                        type="submit"
                        disabled={processing || !data.file || !data.name}
                        className="flex items-center gap-1.5 px-4 py-2 bg-[#25A7CA] text-white rounded-lg text-sm font-medium hover:bg-[#1d96b8] disabled:opacity-50 transition whitespace-nowrap"
                    >
                        <Icon icon={processing ? "solar:refresh-outline" : "solar:upload-outline"} width={16} className={processing ? "animate-spin" : ""} />
                        {processing ? "Subiendo..." : "Subir"}
                    </button>
                </div>
            </form>
        </div>
    );
}

/* ── Main Edit component ─────────────────────────────── */
export default function ProductEdit({ product, categories, subcategories }) {
    const { data, setData, post, processing, errors } = useForm({
        _method: "PUT",
        category_id: product.category_id || "",
        subcategory_id: product.subcategory_id || "",
        name: product.name || "",
        subtitle: product.subtitle || "",
        brand: product.brand || "",
        short_description: product.short_description || "",
        description: product.description || "",
        order: product.order ?? "",
        is_active: product.is_active ?? true,
        is_featured: product.is_featured ?? false,
        show_in_home: product.show_in_home ?? false,
        image: null,
        video: null,
        video_url: product.video_url || "",
        brand_logo: null,
    });

    const [preview,   setPreview]   = useState(null);
    const [previewLogo, setPreviewLogo] = useState(null);
    const [mediaInfo, setMediaInfo] = useState(null);
    const filteredSubcats = subcategories.filter(
        (s) => !data.category_id || String(s.category_id) === String(data.category_id)
    );

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

        const ext    = file.name.split(".").pop().toUpperCase();
        const sizeKB = file.size / 1024;
        const overMax = type.startsWith("video/") ? false : sizeKB / 1024 > 3;
        setMediaInfo({ ext, sizeKB, isVideo: type.startsWith("video/"), overMax });

        setPreview(URL.createObjectURL(file));

    };
    const handleLogoFile = (e) => {
        const file = e.target.files[0];
        if (!file) return;
        setData("brand_logo", file);
        setPreviewLogo(URL.createObjectURL(file));
    };

    const handleOrder = (e) => {
        const val = e.target.value.toUpperCase().replace(/[^A-Z]/g, "");
        setData("order", val);
    };

    const submit = (e) => {
        e.preventDefault();
        post(`/admin/products/${product.id}`, { forceFormData: true });
    };

    return (
        <AdminLayout>
            <div className="max-w-3xl">
                <div className="flex items-center gap-3 mb-6">
                    <button onClick={() => history.back()} className="p-2 rounded-lg hover:bg-gray-100 text-gray-500">
                        <Icon icon="solar:arrow-left-outline" width={18} />
                    </button>
                    <div>
                        <h1 className="text-2xl font-bold text-gray-800">Editar Producto</h1>
                        <p className="text-sm text-gray-500">{product.name}</p>
                    </div>
                </div>

                <form onSubmit={submit} className="space-y-6">
                    {/* Info general */}
                    <div className="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-4">
                        <h2 className="font-semibold text-gray-700">Información general</h2>

                        <div>
                            <label className="block text-sm text-gray-600 mb-1">Nombre *</label>
                            <input type="text" value={data.name} onChange={(e) => setData("name", e.target.value)} className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30" />
                            {errors.name && <p className="text-red-500 text-xs mt-1">{errors.name}</p>}
                        </div>

                        <div>
                            <label className="block text-sm text-gray-600 mb-1">Subtítulo</label>
                            <input type="text" value={data.subtitle} onChange={(e) => setData("subtitle", e.target.value)} className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30" />
                        </div>

                        <div className="grid grid-cols-2 gap-4">
                            <div>
                                <label className="block text-sm text-gray-600 mb-1">Marca (texto)</label>
                                <input type="text" value={data.brand} onChange={(e) => setData("brand", e.target.value)} className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30" />
                            </div>
                            <div>
                                <label className="block text-sm text-gray-600 mb-1">
                                    Logo de marca <span className="text-xs text-gray-400">(aparece bajo el título)</span>
                                </label>
                                {previewLogo ? (
                                    <img src={previewLogo} className="h-10 object-contain mb-1 rounded" alt="logo nuevo" />
                                ) : product.brand_logo_url ? (
                                    <img src={product.brand_logo_url} className="h-10 object-contain mb-1 rounded" alt="logo actual" />
                                ) : null}
                                <input type="file" accept="image/*" onChange={handleLogoFile} className="block w-full text-xs text-gray-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-[#25A7CA]/10 file:text-[#25A7CA]" />
                            </div>
                        </div>

                        <div className="grid grid-cols-2 gap-4">
                            <div>
                                <label className="block text-sm text-gray-600 mb-1">Categoría *</label>
                                <select value={data.category_id} onChange={(e) => setData("category_id", e.target.value)} className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30">
                                    <option value="">Seleccionar...</option>
                                    {categories.map((c) => <option key={c.id} value={c.id}>{c.name}</option>)}
                                </select>
                            </div>
                            <div>
                                <label className="block text-sm text-gray-600 mb-1">Subcategoría</label>
                                <select value={data.subcategory_id} onChange={(e) => setData("subcategory_id", e.target.value)} className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30">
                                    <option value="">Sin subcategoría</option>
                                    {filteredSubcats.map((s) => <option key={s.id} value={s.id}>{s.name}</option>)}
                                </select>
                            </div>
                        </div>

                        <div>
                            <label className="block text-sm text-gray-600 mb-1">Descripción corta</label>
                            <textarea value={data.short_description} onChange={(e) => setData("short_description", e.target.value)} rows={3} className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30 resize-none" />
                        </div>

                        <div>
                            <label className="block text-sm text-gray-600 mb-2">Descripción completa</label>
                            <RichTextEditor value={data.description} onChange={(v) => setData("description", v)} />
                        </div>
                    </div>

                    {/* Multimedia principal */}
                    <div className="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                    <h2 className="font-semibold text-gray-700 mb-4">
                        Multimedia principal
                    </h2>

                    {/* Spec hint */}
                    <div className="flex flex-wrap items-center gap-1.5 mb-3 px-3 py-2 rounded-lg bg-sky-50 border border-sky-100">
                        <Icon icon="solar:info-circle-outline" width={14} className="text-sky-400 flex-shrink-0" />
                        <span className="text-xs text-sky-700">
                            Imagen recomendada: <strong>576×576 px</strong> · PNG / JPG · máx 3 MB.
                            También podés subir un video local (MP4, WebM, MOV).
                        </span>
                    </div>

                    {product.image_url && !preview && !product.video_url && (
                        <img
                            src={product.image_url}
                            className="h-32 rounded-lg object-cover mb-3"
                            alt="actual"
                        />
                    )}

                    {product.video_url && !preview && (
                        <iframe
                            src={product.video_url.replace("watch?v=", "embed/")}
                            className="w-full h-48 rounded-lg mb-3"
                            allowFullScreen
                        />
                    )}

                    <input
                        type="file"
                        accept="image/*,video/mp4,video/webm,video/mov"
                        onChange={handleFile}
                        className="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-[#25A7CA]/10 file:text-[#25A7CA] hover:file:bg-[#25A7CA]/20"
                    />

                    {/* File info badge */}
                    {mediaInfo && (
                        <div className={`flex flex-wrap items-center gap-x-3 gap-y-1 mt-2 px-3 py-1.5 rounded-lg text-xs border ${
                            mediaInfo.overMax ? "bg-red-50 text-red-600 border-red-200" : "bg-gray-50 text-gray-500 border-gray-100"
                        }`}>
                            <Icon icon={mediaInfo.overMax ? "solar:danger-triangle-outline" : "solar:file-check-outline"} width={13} className="flex-shrink-0" />
                            <span>Tipo: <strong>{mediaInfo.isVideo ? "Video" : "Imagen"}</strong></span>
                            <span>Formato: <strong>{mediaInfo.ext}</strong></span>
                            <span>Tamaño: <strong>{mediaInfo.sizeKB >= 1024 ? `${(mediaInfo.sizeKB/1024).toFixed(2)} MB` : `${Math.round(mediaInfo.sizeKB)} KB`}</strong></span>
                            {mediaInfo.overMax && <span className="font-semibold">⚠ Excede el límite de 3 MB para imágenes</span>}
                        </div>
                    )}

                    {preview && (
                        preview.includes("video")
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
                            className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30"
                        />

                    </div>
                    </div>{/* end multimedia card */}

                    {/* Configuración */}
                    <div className="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                        <h2 className="font-semibold text-gray-700 mb-4">Configuración</h2>
                        <div className="space-y-3">
                            <div>
                                <label className="block text-sm text-gray-600 mb-1">
                                    Orden <span className="ml-1 text-xs text-gray-400">(A, AA, AB… AZ, B, BA…)</span>
                                </label>
                                <input type="text" value={data.order} onChange={handleOrder} placeholder="Ej: A, AA, BA" maxLength={10} className="w-32 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30 uppercase tracking-widest font-mono" />
                                {errors.order && <p className="text-red-500 text-xs mt-1">{errors.order}</p>}
                            </div>
                            <Toggle checked={data.is_active} onChange={(v) => setData("is_active", v)} label="Activo" description="El producto es visible en la web" />
                            <Toggle checked={data.is_featured} onChange={(v) => setData("is_featured", v)} label="Destacado" description="Aparece como producto destacado en listados" />
                            <Toggle checked={data.show_in_home} onChange={(v) => setData("show_in_home", v)} label="Mostrar en Home" description="Aparece en la sección de productos destacados del inicio" />
                        </div>
                    </div>

                    <div className="flex justify-end gap-3">
                        <button type="button" onClick={() => history.back()} className="px-5 py-2.5 rounded-xl border border-gray-200 text-sm text-gray-700 hover:bg-gray-50">Cancelar</button>
                        <button type="submit" disabled={processing} className="px-5 py-2.5 rounded-xl bg-[#25A7CA] text-white text-sm font-medium hover:bg-[#1d96b8] disabled:opacity-60 transition">
                            {processing ? "Guardando..." : "Actualizar Producto"}
                        </button>
                    </div>
                </form>

                {/* Specs — después del form principal, se guardan de forma independiente */}
                <div className="mt-6">
                    <SpecsSection product={product} />
                </div>

                {/* Downloads */}
                <div className="mt-6 mb-10">
                    <DownloadsSection product={product} />
                </div>
            </div>
        </AdminLayout>
    );
}
