import AdminLayout from "@/Layouts/AdminLayout";
import { useForm, router } from "@inertiajs/react";
import { Icon } from "@iconify/react";
import { useState } from "react";

export default function ProductEdit({ product, brands, gearboxes }) {
    const [selectedBrand, setSelectedBrand] = useState(product.gearbox?.brand_id ?? "");
    const [newVersion, setNewVersion] = useState("");

    const existingVersions = (product.versions ?? []).map((v) => v.version);

    const { data, setData, post, processing, errors } = useForm({
        _method:                 "PUT",
        gearbox_id:              product.gearbox_id ?? "",
        figure:                  product.figure ?? "",
        cod_gs:                  product.cod_gs ?? "",
        code_oem:                product.code_oem ?? "",
        nombre:                  product.nombre?.es ?? "",
        referencia_original:     product.referencia_original ?? "",
        referencias_adicionales: Array.isArray(product.referencias_adicionales)
            ? product.referencias_adicionales.join("; ")
            : (product.referencias_adicionales ?? ""),
        description:             product.description?.es ?? "",
        nota_comercial:          product.nota_comercial ?? "",
        nota_interna:            product.nota_interna ?? "",
        is_active:               product.is_active ?? true,
        versions:                existingVersions,
    });

    const filteredGearboxes = gearboxes.filter(
        (g) => !selectedBrand || String(g.brand_id) === String(selectedBrand)
    );

    const addVersion = () => {
        const v = newVersion.trim().toUpperCase();
        if (v && !data.versions.includes(v)) {
            setData("versions", [...data.versions, v]);
        }
        setNewVersion("");
    };

    const removeVersion = (v) => {
        setData("versions", data.versions.filter((x) => x !== v));
    };

    const submit = (e) => {
        e.preventDefault();
        post(`/admin/products/${product.id}`, { forceFormData: true });
    };

    const inputClass = "w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30";
    const labelClass = "block text-sm text-gray-600 mb-1";

    return (
        <AdminLayout>
            <div className="max-w-3xl">
                <div className="flex items-center gap-3 mb-6">
                    <button onClick={() => router.visit("/admin/products")} className="p-2 rounded-lg hover:bg-gray-100 text-gray-500">
                        <Icon icon="solar:arrow-left-outline" width={18} />
                    </button>
                    <div>
                        <h1 className="text-2xl font-bold text-gray-800">Editar Producto</h1>
                        <p className="text-sm text-gray-500">{product.code_oem}</p>
                    </div>
                </div>

                <form onSubmit={submit} className="space-y-6">
                    {/* Identificación */}
                    <div className="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-4">
                        <h2 className="font-semibold text-gray-700">Identificación</h2>

                        <div className="grid grid-cols-2 gap-4">
                            <div>
                                <label className={labelClass}>Marca</label>
                                <select value={selectedBrand} onChange={(e) => { setSelectedBrand(e.target.value); setData("gearbox_id", ""); }} className={inputClass}>
                                    <option value="">Todas las marcas</option>
                                    {brands.map((b) => <option key={b.id} value={b.id}>{b.name?.es ?? b.name}</option>)}
                                </select>
                            </div>
                            <div>
                                <label className={labelClass}>Caja de cambios *</label>
                                <select value={data.gearbox_id} onChange={(e) => setData("gearbox_id", e.target.value)} className={inputClass} required>
                                    <option value="">Seleccionar...</option>
                                    {filteredGearboxes.map((g) => <option key={g.id} value={g.id}>{g.name}</option>)}
                                </select>
                                {errors.gearbox_id && <p className="text-xs text-red-500 mt-1">{errors.gearbox_id}</p>}
                            </div>
                        </div>

                        <div className="grid grid-cols-3 gap-4">
                            <div>
                                <label className={labelClass}>Figura</label>
                                <input type="text" value={data.figure} onChange={(e) => setData("figure", e.target.value)} placeholder="G60" className={inputClass} />
                            </div>
                            <div>
                                <label className={labelClass}>Cod. GS</label>
                                <input type="text" value={data.cod_gs} onChange={(e) => setData("cod_gs", e.target.value)} className={inputClass} />
                            </div>
                            <div>
                                <label className={labelClass}>Código OEM *</label>
                                <input type="text" value={data.code_oem} onChange={(e) => setData("code_oem", e.target.value)} className={inputClass} required />
                                {errors.code_oem && <p className="text-xs text-red-500 mt-1">{errors.code_oem}</p>}
                            </div>
                        </div>

                        <div>
                            <label className={labelClass}>Nombre del producto</label>
                            <input type="text" value={data.nombre} onChange={(e) => setData("nombre", e.target.value)} className={inputClass} />
                        </div>

                        <div className="grid grid-cols-2 gap-4">
                            <div>
                                <label className={labelClass}>Referencia original</label>
                                <input type="text" value={data.referencia_original} onChange={(e) => setData("referencia_original", e.target.value)} className={inputClass} />
                            </div>
                            <div>
                                <label className={labelClass}>Referencias adicionales</label>
                                <input type="text" value={data.referencias_adicionales} onChange={(e) => setData("referencias_adicionales", e.target.value)} placeholder="REF1; REF2; REF3" className={inputClass} />
                                <p className="text-xs text-gray-400 mt-0.5">Separadas por punto y coma</p>
                            </div>
                        </div>
                    </div>

                    {/* Descripción */}
                    <div className="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-4">
                        <h2 className="font-semibold text-gray-700">Descripción</h2>
                        <textarea value={data.description} onChange={(e) => setData("description", e.target.value)} rows={4} className={inputClass} placeholder="Descripción técnica del producto..." />
                    </div>

                    {/* Notas */}
                    <div className="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-4">
                        <h2 className="font-semibold text-gray-700">Notas</h2>
                        <div>
                            <label className={labelClass}>
                                <span className="inline-flex items-center gap-1.5">
                                    <Icon icon="solar:tag-outline" width={14} className="text-blue-500" />
                                    Nota comercial
                                    <span className="text-xs text-gray-400 font-normal">(visible para clientes)</span>
                                </span>
                            </label>
                            <textarea value={data.nota_comercial} onChange={(e) => setData("nota_comercial", e.target.value)} rows={2} className={inputClass} />
                        </div>
                        <div>
                            <label className={labelClass}>
                                <span className="inline-flex items-center gap-1.5">
                                    <Icon icon="solar:lock-outline" width={14} className="text-amber-500" />
                                    Nota interna
                                    <span className="text-xs text-gray-400 font-normal">(solo equipo interno)</span>
                                </span>
                            </label>
                            <textarea value={data.nota_interna} onChange={(e) => setData("nota_interna", e.target.value)} rows={2} className={inputClass} />
                        </div>
                    </div>

                    {/* Versiones */}
                    <div className="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-4">
                        <h2 className="font-semibold text-gray-700">Versiones / Variantes</h2>
                        <div className="flex gap-2">
                            <input type="text" value={newVersion} onChange={(e) => setNewVersion(e.target.value.toUpperCase())}
                                onKeyDown={(e) => e.key === "Enter" && (e.preventDefault(), addVersion())}
                                placeholder="G60, G85, G3/36..." className="flex-1 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30 uppercase" />
                            <button type="button" onClick={addVersion} className="px-3 py-2 bg-[#25A7CA] text-white rounded-lg text-sm">Agregar</button>
                        </div>
                        {data.versions.length > 0 && (
                            <div className="flex flex-wrap gap-2">
                                {data.versions.map((v) => (
                                    <span key={v} className="flex items-center gap-1 bg-[#25A7CA]/10 text-[#25A7CA] text-xs font-semibold px-2.5 py-1 rounded-full">
                                        {v}
                                        <button type="button" onClick={() => removeVersion(v)} className="ml-1 text-[#25A7CA]/60 hover:text-red-500">×</button>
                                    </span>
                                ))}
                            </div>
                        )}
                    </div>

                    {/* Imágenes existentes */}
                    {product.images?.length > 0 && (
                        <div className="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                            <h2 className="font-semibold text-gray-700 mb-3">Imágenes actuales</h2>
                            <div className="flex flex-wrap gap-3">
                                {product.images.map((img) => (
                                    <img key={img.id} src={img.url ?? `/storage/${img.path}`} className="h-20 w-20 object-cover rounded-lg border border-gray-100" alt="" />
                                ))}
                            </div>
                        </div>
                    )}

                    {/* Config */}
                    <div className="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                        <label className="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" checked={data.is_active} onChange={(e) => setData("is_active", e.target.checked)} className="w-4 h-4 rounded accent-[#25A7CA]" />
                            <span className="text-sm font-medium text-gray-700">Producto activo</span>
                        </label>
                    </div>

                    <div className="flex justify-end gap-3">
                        <button type="button" onClick={() => router.visit("/admin/products")} className="px-5 py-2.5 rounded-xl border border-gray-200 text-sm text-gray-700 hover:bg-gray-50">Cancelar</button>
                        <button type="submit" disabled={processing} className="px-5 py-2.5 rounded-xl bg-[#25A7CA] text-white text-sm font-medium hover:bg-[#1d96b8] disabled:opacity-60">
                            {processing ? "Guardando..." : "Guardar Cambios"}
                        </button>
                    </div>
                </form>
            </div>
        </AdminLayout>
    );
}
