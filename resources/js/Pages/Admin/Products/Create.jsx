import AdminLayout from "@/Layouts/AdminLayout";
import { useForm, router } from "@inertiajs/react";
import { Icon } from "@iconify/react";
import { useState } from "react";

export default function ProductCreate({ brands, gearboxes }) {
    const [selectedBrand, setSelectedBrand] = useState("");
    const [variantInput,  setVariantInput]  = useState("");

    const { data, setData, post, processing, errors } = useForm({
        // Producto base
        gearbox_id:              "",
        additional_gearbox_ids:  [],
        figure:                  "",
        code_gs:                 "",
        name_es:                 "",
        description_es:          "",
        is_active:               true,
        // Item principal
        code_oem:                "",
        referencia_original:     "",
        referencias_adicionales: "",
        notes:                   "",
        application:             "",
        application_type:        "generic",
        variants:                [],
        // Imágenes
        images:                  [],
    });

    const filteredGearboxes = gearboxes.filter(
        (g) => !selectedBrand || String(g.brand_id) === String(selectedBrand)
    );

    const groupedGearboxes = brands
        .map((brand) => ({
            id: brand.id,
            name: brand.name?.es ?? brand.name,
            gearboxes: gearboxes.filter((gearbox) => String(gearbox.brand_id) === String(brand.id)),
        }))
        .filter((group) => group.gearboxes.length > 0);

    const addVariant = () => {
        const v = variantInput.trim().toUpperCase();
        if (v && !data.variants.includes(v)) {
            setData("variants", [...data.variants, v]);
        }
        setVariantInput("");
    };

    const removeVariant = (v) => setData("variants", data.variants.filter((x) => x !== v));

    const toggleAdditionalGearbox = (gearboxId) => {
        const id = Number(gearboxId);
        const current = Array.isArray(data.additional_gearbox_ids) ? data.additional_gearbox_ids : [];
        const exists = current.some((value) => Number(value) === id);

        setData(
            "additional_gearbox_ids",
            exists
                ? current.filter((value) => Number(value) !== id)
                : [...current, id]
        );
    };

    const submit = (e) => {
        e.preventDefault();
        post("/admin/products", { forceFormData: true });
    };

    const ic = "w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30";
    const lc = "block text-sm text-gray-600 mb-1";

    return (
        <AdminLayout>
            <div className="max-w-3xl">
                <div className="flex items-center gap-3 mb-6">
                    <button onClick={() => router.visit("/admin/products")} className="p-2 rounded-lg hover:bg-gray-100 text-gray-500">
                        <Icon icon="solar:arrow-left-outline" width={18} />
                    </button>
                    <div>
                        <h1 className="text-2xl font-bold text-gray-800">Nuevo Producto</h1>
                        <p className="text-sm text-gray-500">Agregar producto al catálogo</p>
                    </div>
                </div>

                <form onSubmit={submit} className="space-y-6">

                    {/* ── Producto base ───────────────────────────────────── */}
                    <div className="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-4">
                        <h2 className="font-semibold text-gray-700 flex items-center gap-2">
                            <Icon icon="solar:box-outline" width={18} className="text-[#25A7CA]" />
                            Producto base
                        </h2>

                        <div className="grid grid-cols-2 gap-4">
                            <div>
                                <label className={lc}>Marca</label>
                                <select
                                    value={selectedBrand}
                                    onChange={(e) => { setSelectedBrand(e.target.value); setData("gearbox_id", ""); }}
                                    className={ic}
                                >
                                    <option value="">Seleccionar marca…</option>
                                    {brands.map((b) => (
                                        <option key={b.id} value={b.id}>{b.name?.es ?? b.name}</option>
                                    ))}
                                </select>
                            </div>
                            <div>
                                <label className={lc}>Caja de cambios *</label>
                                <select
                                    value={data.gearbox_id}
                                    onChange={(e) => {
                                        const nextGearboxId = e.target.value;
                                        setData((current) => ({
                                            ...current,
                                            gearbox_id: nextGearboxId,
                                            additional_gearbox_ids: (current.additional_gearbox_ids || []).filter(
                                                (value) => String(value) !== String(nextGearboxId)
                                            ),
                                        }));
                                    }}
                                    className={ic}
                                    required
                                >
                                    <option value="">Seleccionar caja…</option>
                                    {filteredGearboxes.map((g) => (
                                        <option key={g.id} value={g.id}>{g.name}</option>
                                    ))}
                                </select>
                                {errors.gearbox_id && <p className="text-xs text-red-500 mt-1">{errors.gearbox_id}</p>}
                            </div>
                        </div>

                        <div className="rounded-xl border border-slate-200 bg-slate-50/70 p-4">
                            <div className="flex items-start gap-2 mb-3">
                                <Icon icon="solar:share-outline" width={18} className="text-[#25A7CA] mt-0.5" />
                                <div>
                                    <p className="text-sm font-semibold text-slate-700">También mostrar en otras marcas o cajas</p>
                                    <p className="text-xs text-slate-500 mt-1">
                                        La caja principal sigue siendo la del catálogo y del Excel. Acá solo elegís dónde más querés que aparezca este mismo producto.
                                    </p>
                                </div>
                            </div>

                            <div className="grid gap-3 md:grid-cols-2">
                                {groupedGearboxes.map((group) => (
                                    <div key={group.id} className="rounded-xl border border-white bg-white p-3">
                                        <p className="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">
                                            {group.name}
                                        </p>
                                        <div className="space-y-2">
                                            {group.gearboxes.map((gearbox) => {
                                                const isPrimary = String(data.gearbox_id) === String(gearbox.id);
                                                const isChecked = data.additional_gearbox_ids.some((value) => String(value) === String(gearbox.id));

                                                return (
                                                    <label
                                                        key={gearbox.id}
                                                        className={`flex items-center gap-2 text-sm ${isPrimary ? "text-slate-400" : "text-slate-600"} ${isPrimary ? "cursor-not-allowed" : "cursor-pointer"}`}
                                                    >
                                                        <input
                                                            type="checkbox"
                                                            checked={isPrimary ? false : isChecked}
                                                            disabled={isPrimary}
                                                            onChange={() => toggleAdditionalGearbox(gearbox.id)}
                                                            className="accent-[#25A7CA]"
                                                        />
                                                        <span>{gearbox.name}</span>
                                                        {isPrimary && (
                                                            <span className="rounded-full bg-[#25A7CA]/10 px-2 py-0.5 text-[10px] font-semibold text-[#25A7CA]">
                                                                Principal
                                                            </span>
                                                        )}
                                                    </label>
                                                );
                                            })}
                                        </div>
                                    </div>
                                ))}
                            </div>
                            {errors.additional_gearbox_ids && <p className="text-xs text-red-500 mt-2">{errors.additional_gearbox_ids}</p>}
                        </div>

                        <div className="grid grid-cols-2 gap-4">
                            <div>
                                <label className={lc}>Figura</label>
                                <input type="text" value={data.figure} onChange={(e) => setData("figure", e.target.value)} placeholder="7, 9, 236…" className={ic} />
                            </div>
                            <div>
                                <label className={lc}>Cod. GS</label>
                                <input type="text" value={data.code_gs} onChange={(e) => setData("code_gs", e.target.value)} placeholder="800, 812…" className={ic} />
                            </div>
                        </div>

                        <div>
                            <label className={lc}>Nombre del producto</label>
                            <input type="text" value={data.name_es} onChange={(e) => setData("name_es", e.target.value)} className={ic} />
                        </div>

                        <div>
                            <label className={lc}>Descripción</label>
                            <textarea value={data.description_es} onChange={(e) => setData("description_es", e.target.value)} rows={3} className={ic} />
                        </div>
                    </div>

                    {/* ── Item principal (OEM + refs + variantes) ─────────── */}
                    <div className="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-4">
                        <h2 className="font-semibold text-gray-700 flex items-center gap-2">
                            <Icon icon="solar:tag-outline" width={18} className="text-[#25A7CA]" />
                            Código OEM y referencias
                        </h2>

                        <div>
                            <label className={lc}>Código OEM</label>
                            <input type="text" value={data.code_oem} onChange={(e) => setData("code_oem", e.target.value)} className={ic} placeholder="694-262-0002" />
                        </div>

                        <div className="grid grid-cols-2 gap-4">
                            <div>
                                <label className={lc}>Referencia original</label>
                                <input type="text" value={data.referencia_original} onChange={(e) => setData("referencia_original", e.target.value)} className={ic} />
                            </div>
                            <div>
                                <label className={lc}>Referencias adicionales</label>
                                <input type="text" value={data.referencias_adicionales} onChange={(e) => setData("referencias_adicionales", e.target.value)} placeholder="REF1; REF2; REF3" className={ic} />
                                <p className="text-xs text-gray-400 mt-0.5">Separadas por punto y coma</p>
                            </div>
                        </div>

                        <div>
                            <label className={lc}>Notas</label>
                            <textarea value={data.notes} onChange={(e) => setData("notes", e.target.value)} rows={2} className={ic} />
                        </div>

                        {/* Variantes */}
                        <div>
                            <label className={lc}>Variantes compatibles (G60, G85, G3/36…)</label>
                            <div className="flex gap-2">
                                <input
                                    type="text"
                                    value={variantInput}
                                    onChange={(e) => setVariantInput(e.target.value.toUpperCase())}
                                    onKeyDown={(e) => e.key === "Enter" && (e.preventDefault(), addVariant())}
                                    placeholder="G60"
                                    className="flex-1 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30"
                                />
                                <button type="button" onClick={addVariant} className="px-3 py-2 bg-[#25A7CA] text-white rounded-lg text-sm">
                                    Agregar
                                </button>
                            </div>
                            {data.variants.length > 0 && (
                                <div className="flex flex-wrap gap-2 mt-2">
                                    {data.variants.map((v) => (
                                        <span key={v} className="flex items-center gap-1 bg-[#25A7CA]/10 text-[#25A7CA] text-xs font-semibold px-2.5 py-1 rounded-full">
                                            {v}
                                            <button type="button" onClick={() => removeVariant(v)} className="ml-1 text-[#25A7CA]/60 hover:text-red-500">×</button>
                                        </span>
                                    ))}
                                </div>
                            )}
                        </div>

                        {/* Aplicación */}
                        <div>
                            <label className={lc}>Tipo de aplicación</label>
                            <div className="flex gap-4 mt-1">
                                <label className="flex items-center gap-2 cursor-pointer text-sm text-gray-600">
                                    <input
                                        type="radio"
                                        value="generic"
                                        checked={data.application_type === "generic"}
                                        onChange={() => setData("application_type", "generic")}
                                        className="accent-[#25A7CA]"
                                    />
                                    Genérico (aplica a toda la caja)
                                </label>
                                <label className="flex items-center gap-2 cursor-pointer text-sm text-gray-600">
                                    <input
                                        type="radio"
                                        value="specific"
                                        checked={data.application_type === "specific"}
                                        onChange={() => setData("application_type", "specific")}
                                        className="accent-[#25A7CA]"
                                    />
                                    Específico (modelos de vehículo)
                                </label>
                            </div>
                            {data.application_type === "specific" && (
                                <input
                                    type="text"
                                    value={data.application}
                                    onChange={(e) => setData("application", e.target.value)}
                                    placeholder="Mercedes L1218, L1518…"
                                    className={`${ic} mt-2`}
                                />
                            )}
                        </div>
                    </div>

                    {/* ── Imágenes ────────────────────────────────────────── */}
                    <div className="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-3">
                        <h2 className="font-semibold text-gray-700 flex items-center gap-2">
                            <Icon icon="solar:gallery-outline" width={18} className="text-[#25A7CA]" />
                            Imágenes
                        </h2>
                        <p className="text-xs text-gray-400">Recomendado: 288×209 px · JPG/PNG · máx 4 MB por imagen</p>
                        <input
                            type="file"
                            multiple
                            accept="image/*"
                            onChange={(e) => setData("images", Array.from(e.target.files))}
                            className="block w-full text-sm text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-[#25A7CA]/10 file:text-[#25A7CA]"
                        />
                        <p className="text-xs text-gray-400">Podés subir múltiples imágenes. La primera será la principal.</p>
                    </div>

                    {/* ── Config ──────────────────────────────────────────── */}
                    <div className="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                        <label className="flex items-center gap-2 cursor-pointer">
                            <input
                                type="checkbox"
                                checked={data.is_active}
                                onChange={(e) => setData("is_active", e.target.checked)}
                                className="w-4 h-4 rounded accent-[#25A7CA]"
                            />
                            <span className="text-sm font-medium text-gray-700">Producto activo</span>
                        </label>
                    </div>

                    <div className="flex justify-end gap-3">
                        <button type="button" onClick={() => router.visit("/admin/products")} className="px-5 py-2.5 rounded-xl border border-gray-200 text-sm text-gray-700 hover:bg-gray-50">
                            Cancelar
                        </button>
                        <button type="submit" disabled={processing} className="px-5 py-2.5 rounded-xl bg-[#25A7CA] text-white text-sm font-medium hover:bg-[#1d96b8] disabled:opacity-60">
                            {processing ? "Guardando…" : "Crear Producto"}
                        </button>
                    </div>
                </form>
            </div>
        </AdminLayout>
    );
}
