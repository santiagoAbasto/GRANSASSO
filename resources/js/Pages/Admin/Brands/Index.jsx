import { useState } from "react";
import AdminLayout from "@/Layouts/AdminLayout";
import { useForm, router } from "@inertiajs/react";
import { Icon } from "@iconify/react";

const LANGS = ["es", "en", "pt"];
const LANG_COLORS = {
    es: "bg-red-50 text-red-600 border-red-200",
    en: "bg-blue-50 text-blue-600 border-blue-200",
    pt: "bg-green-50 text-green-600 border-green-200",
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

function InicioSwitch({ brand }) {
    const active = brand.show_in_home;
    return (
        <div className="flex flex-col items-center gap-1">
            <button
                type="button"
                onClick={() => router.patch(`/admin/brands/${brand.id}/toggle-home`)}
                className={`relative inline-flex h-5 w-9 flex-shrink-0 rounded-full border-2 transition-colors focus:outline-none ${
                    active ? "bg-[#25A7CA] border-[#25A7CA]" : "bg-gray-200 border-gray-200"
                }`}
                title={active ? "Ocultar del inicio" : "Mostrar en el inicio"}
            >
                <span className={`inline-block h-3.5 w-3.5 rounded-full bg-white shadow transition-transform ${
                    active ? "translate-x-3.5" : "translate-x-0.5"
                }`} />
            </button>
            <span className="text-[10px] text-gray-400 leading-none">{active ? "Inicio" : "Oculta"}</span>
        </div>
    );
}

function BrandForm({ initial = null, onCancel }) {
    const isNew = !initial;
    const { data, setData, post, processing, errors, reset } = useForm(
        initial
            ? {
                _method: "PUT",
                name_es: initial.name_es,
                name_en: initial.name_en,
                name_pt: initial.name_pt,
                logo: null,
                order: initial.order ?? "A",
                is_active: initial.is_active,
                show_in_home: initial.show_in_home ?? true,
            }
            : {
                name_es: "", name_en: "", name_pt: "",
                logo: null,
                order: "A",
                is_active: true,
                show_in_home: true,
            }
    );

    const [preview, setPreview] = useState(initial?.logo_url ?? null);

    const handleLogo = (e) => {
        const file = e.target.files[0];
        if (!file) return;
        setData("logo", file);
        setPreview(URL.createObjectURL(file));
    };

    const submit = (e) => {
        e.preventDefault();
        const url = isNew ? "/admin/brands" : `/admin/brands/${initial.id}`;
        post(url, { forceFormData: true, onSuccess: () => { reset(); onCancel(); } });
    };

    return (
        <form onSubmit={submit} className="bg-white rounded-xl border border-gray-100 shadow-sm p-6 space-y-5">
            <h3 className="font-semibold text-gray-700 text-base">
                {isNew ? "Nueva marca" : `Editar: ${initial.name_es}`}
            </h3>

            {/* Logo */}
            <div>
                <label className="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Logo</label>
                <p className="text-xs text-gray-400 mb-2">Recomendado: 286×215 px · JPG/PNG · máx 2 MB</p>
                <div className="flex items-center gap-4">
                    {preview ? (
                        <img src={preview} alt="logo" className="h-16 w-auto object-contain rounded border border-gray-100 p-1 bg-gray-50" />
                    ) : (
                        <div className="h-16 w-16 rounded border-2 border-dashed border-gray-200 flex items-center justify-center text-gray-300">
                            <Icon icon="solar:image-outline" width={24} />
                        </div>
                    )}
                    <label className="cursor-pointer inline-flex items-center gap-2 px-4 py-2 border border-gray-200 rounded-lg text-sm text-gray-600 hover:bg-gray-50 transition">
                        <Icon icon="solar:upload-outline" width={15} />
                        {preview ? "Cambiar logo" : "Subir logo"}
                        <input type="file" accept="image/*" className="hidden" onChange={handleLogo} />
                    </label>
                </div>
                {errors.logo && <p className="text-red-500 text-xs mt-1">{errors.logo}</p>}
            </div>

            {/* Nombre multilingual */}
            <MultiInput label="Nombre de la marca" field="name" data={data} setData={setData} placeholder="Ej: Gran Sasso" />
            {errors.name_es && <p className="text-red-500 text-xs -mt-3">{errors.name_es}</p>}

            {/* Orden alfanumérico + activo */}
            <div className="flex items-end gap-4 pt-1">
                <div>
                    <label className="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">
                        Orden <span className="text-gray-400 font-normal normal-case">(A, AB, B, BA…)</span>
                    </label>
                    <input
                        type="text"
                        maxLength={20}
                        value={data.order}
                        onChange={(e) => setData("order", e.target.value.toUpperCase())}
                        placeholder="A"
                        className="w-24 border border-gray-200 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30 uppercase"
                    />
                </div>
                <label className="flex items-center gap-2 cursor-pointer mb-2">
                    <input
                        type="checkbox"
                        checked={!!data.is_active}
                        onChange={(e) => setData("is_active", e.target.checked)}
                        className="w-4 h-4 rounded accent-[#25A7CA]"
                    />
                    <span className="text-sm text-gray-700">Activa</span>
                </label>
                <div className="flex flex-col gap-1 mb-2">
                    <label className="block text-xs font-semibold text-gray-500 uppercase tracking-wide">Visible en inicio</label>
                    <button
                        type="button"
                        onClick={() => setData("show_in_home", !data.show_in_home)}
                        className={`relative inline-flex h-5 w-9 flex-shrink-0 rounded-full border-2 transition-colors focus:outline-none ${
                            data.show_in_home ? "bg-[#25A7CA] border-[#25A7CA]" : "bg-gray-200 border-gray-200"
                        }`}
                    >
                        <span className={`inline-block h-3.5 w-3.5 rounded-full bg-white shadow transition-transform ${
                            data.show_in_home ? "translate-x-3.5" : "translate-x-0.5"
                        }`} />
                    </button>
                    <span className="text-xs text-gray-400">{data.show_in_home ? "Se muestra en el inicio" : "Oculta en el inicio"}</span>
                </div>
            </div>

            {/* Acciones */}
            <div className="flex items-center justify-end gap-3 pt-2 border-t border-gray-100">
                <button
                    type="button"
                    onClick={onCancel}
                    className="px-4 py-2 text-sm text-gray-500 hover:text-gray-700 rounded-lg hover:bg-gray-50 transition"
                >
                    Cancelar
                </button>
                <button
                    type="submit"
                    disabled={processing}
                    className="inline-flex items-center gap-2 px-5 py-2 bg-[#25A7CA] text-white rounded-lg text-sm font-medium hover:bg-[#1d96b8] disabled:opacity-60 transition"
                >
                    <Icon icon={processing ? "solar:refresh-outline" : "solar:diskette-outline"} width={15} />
                    {processing ? "Guardando..." : "Guardar"}
                </button>
            </div>
        </form>
    );
}

export default function BrandsIndex({ brands }) {
    const [showCreate, setShowCreate] = useState(false);
    const [editingId, setEditingId] = useState(null);

    const handleDelete = (brand) => {
        if (!confirm(`¿Eliminar la marca "${brand.name_es}"? Esta acción no se puede deshacer.`)) return;
        router.delete(`/admin/brands/${brand.id}`);
    };

    return (
        <AdminLayout>
            <div className="max-w-4xl">
                {/* Header */}
                <div className="flex items-center justify-between mb-6">
                    <div>
                        <h1 className="text-2xl font-bold text-gray-800">Marcas</h1>
                        <p className="text-sm text-gray-500 mt-0.5">Administrá el catálogo de marcas.</p>
                    </div>
                    {!showCreate && (
                        <button
                            onClick={() => { setShowCreate(true); setEditingId(null); }}
                            className="inline-flex items-center gap-2 px-4 py-2 bg-[#25A7CA] text-white rounded-lg text-sm font-medium hover:bg-[#1d96b8] transition"
                        >
                            <Icon icon="solar:add-circle-outline" width={17} />
                            Nueva marca
                        </button>
                    )}
                </div>

                {/* Formulario crear */}
                {showCreate && (
                    <div className="mb-6">
                        <BrandForm onCancel={() => setShowCreate(false)} />
                    </div>
                )}

                {/* Lista */}
                {brands.length === 0 && !showCreate ? (
                    <div className="bg-white rounded-xl border border-gray-100 shadow-sm p-12 text-center">
                        <Icon icon="solar:box-minimalistic-outline" width={40} className="mx-auto text-gray-300 mb-3" />
                        <p className="text-gray-400 text-sm">No hay marcas creadas todavía.</p>
                    </div>
                ) : (
                    <div className="space-y-3">
                        {brands.map((brand) =>
                            editingId === brand.id ? (
                                <BrandForm
                                    key={brand.id}
                                    initial={brand}
                                    onCancel={() => setEditingId(null)}
                                />
                            ) : (
                                <div
                                    key={brand.id}
                                    className="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex items-center gap-4"
                                >
                                    {/* Orden badge */}
                                    <div className="w-8 text-center">
                                        <span className="text-xs font-mono font-bold text-gray-400">{brand.order}</span>
                                    </div>

                                    {/* Logo */}
                                    <div className="w-14 h-14 flex-shrink-0 rounded-lg border border-gray-100 bg-gray-50 flex items-center justify-center overflow-hidden">
                                        {brand.logo_url ? (
                                            <img src={brand.logo_url} alt={brand.name_es} className="h-full w-full object-contain p-1" />
                                        ) : (
                                            <Icon icon="solar:image-outline" width={20} className="text-gray-300" />
                                        )}
                                    </div>

                                    {/* Info */}
                                    <div className="flex-1 min-w-0">
                                        <div className="flex items-center gap-2 flex-wrap">
                                            <span className="font-semibold text-gray-800">{brand.name_es}</span>
                                            {brand.name_en && brand.name_en !== brand.name_es && (
                                                <span className="text-xs text-gray-400">/ {brand.name_en}</span>
                                            )}
                                            {brand.name_pt && brand.name_pt !== brand.name_es && (
                                                <span className="text-xs text-gray-400">/ {brand.name_pt}</span>
                                            )}
                                            <span className={`text-xs px-2 py-0.5 rounded-full font-medium ${brand.is_active ? "bg-green-50 text-green-600" : "bg-gray-100 text-gray-400"}`}>
                                                {brand.is_active ? "Activa" : "Inactiva"}
                                            </span>
                                        </div>
                                        <p className="text-xs text-gray-400 mt-0.5">slug: {brand.slug}</p>
                                    </div>

                                    {/* Inicio */}
                                    <InicioSwitch brand={brand} />

                                    {/* Acciones */}
                                    <div className="flex items-center gap-1 flex-shrink-0">
                                        <button
                                            onClick={() => { setEditingId(brand.id); setShowCreate(false); }}
                                            className="p-2 text-gray-400 hover:text-[#25A7CA] hover:bg-blue-50 rounded-lg transition"
                                            title="Editar"
                                        >
                                            <Icon icon="solar:pen-outline" width={17} />
                                        </button>
                                        <button
                                            onClick={() => handleDelete(brand)}
                                            className="p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition"
                                            title="Eliminar"
                                        >
                                            <Icon icon="solar:trash-bin-trash-outline" width={17} />
                                        </button>
                                    </div>
                                </div>
                            )
                        )}
                    </div>
                )}
            </div>
        </AdminLayout>
    );
}
