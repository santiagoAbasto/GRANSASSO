import { useState } from "react";
import AdminLayout from "@/Layouts/AdminLayout";
import { useForm, router } from "@inertiajs/react";
import { Icon } from "@iconify/react";

function StatusBadge({ active }) {
    return (
        <span className={`inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded-full font-medium ${
            active ? "bg-green-50 text-green-600" : "bg-gray-100 text-gray-400"
        }`}>
            <span className={`w-1.5 h-1.5 rounded-full ${active ? "bg-green-500" : "bg-gray-300"}`} />
            {active ? "Activa" : "Inactiva"}
        </span>
    );
}

function FormRow({ data, setData, brands, errors, processing, onCancel, isNew }) {
    const [preview, setPreview] = useState(data.image_url ?? null);

    const handleImage = (e) => {
        const file = e.target.files[0];
        if (!file) return;
        setData("image", file);
        setPreview(URL.createObjectURL(file));
    };

    return (
        <div className="bg-white rounded-xl border border-[#25A7CA]/30 shadow-sm p-5 space-y-4">
            <div className="grid grid-cols-2 gap-4">
                {/* Marca */}
                <div>
                    <label className="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Marca *</label>
                    <select
                        value={data.brand_id}
                        onChange={(e) => setData("brand_id", e.target.value)}
                        className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30"
                    >
                        <option value="">Seleccionar…</option>
                        {brands.map((b) => (
                            <option key={b.id} value={b.id}>{b.name}</option>
                        ))}
                    </select>
                    {errors.brand_id && <p className="text-red-500 text-xs mt-1">{errors.brand_id}</p>}
                </div>

                {/* Nombre */}
                <div>
                    <label className="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Nombre *</label>
                    <input
                        type="text"
                        value={data.name}
                        onChange={(e) => setData("name", e.target.value)}
                        placeholder="Ej: 6HP26"
                        className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30"
                    />
                    {errors.name && <p className="text-red-500 text-xs mt-1">{errors.name}</p>}
                </div>

                {/* Vehículos compatibles */}
                <div className="col-span-2">
                    <label className="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Vehículos compatibles</label>
                    <input
                        type="text"
                        value={data.compatible_vehicles ?? ""}
                        onChange={(e) => setData("compatible_vehicles", e.target.value)}
                        placeholder="Ej: BMW Serie 5, 6, 7 (2001–2009)"
                        className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30"
                    />
                </div>

                {/* Orden + Estado */}
                <div className="flex items-end gap-4">
                    <div>
                        <label className="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Orden</label>
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
                            checked={!!data.is_active}
                            onChange={(e) => setData("is_active", e.target.checked)}
                            className="w-4 h-4 rounded accent-[#25A7CA]"
                        />
                        <span className="text-sm text-gray-700">Activa</span>
                    </label>
                </div>

                {/* Imagen */}
                <div>
                    <label className="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Imagen</label>
                    <div className="flex items-center gap-3">
                        {preview ? (
                            <img src={preview} alt="" className="h-12 w-auto object-contain rounded border border-gray-100 p-1 bg-gray-50" />
                        ) : (
                            <div className="h-12 w-12 rounded border-2 border-dashed border-gray-200 flex items-center justify-center text-gray-300">
                                <Icon icon="solar:image-outline" width={18} />
                            </div>
                        )}
                        <label className="cursor-pointer inline-flex items-center gap-2 px-3 py-1.5 border border-gray-200 rounded-lg text-xs text-gray-600 hover:bg-gray-50 transition">
                            <Icon icon="solar:upload-outline" width={13} />
                            {preview ? "Cambiar" : "Subir"}
                            <input type="file" accept="image/*" className="hidden" onChange={handleImage} />
                        </label>
                    </div>
                </div>
            </div>

            <div className="flex items-center justify-end gap-3 pt-2 border-t border-gray-100">
                <button
                    type="button"
                    onClick={onCancel}
                    className="px-4 py-2 text-sm text-gray-500 hover:text-gray-700 rounded-lg hover:bg-gray-50 transition"
                >
                    Cancelar
                </button>
                <button
                    type="button"
                    disabled={processing}
                    onClick={onCancel.submit}
                    className="inline-flex items-center gap-2 px-5 py-2 bg-[#25A7CA] text-white rounded-lg text-sm font-medium hover:bg-[#1d96b8] disabled:opacity-60 transition"
                >
                    <Icon icon={processing ? "solar:refresh-outline" : "solar:diskette-outline"} width={15} />
                    {processing ? "Guardando..." : "Guardar"}
                </button>
            </div>
        </div>
    );
}

function CreateForm({ brands, onDone }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        brand_id: "",
        name: "",
        compatible_vehicles: "",
        image: null,
        is_active: true,
        order: "A",
    });

    const submit = () => {
        post("/admin/gearboxes", {
            forceFormData: true,
            onSuccess: () => { reset(); onDone(); },
        });
    };

    const cancelWithSubmit = () => onDone();
    cancelWithSubmit.submit = submit;

    return (
        <div className="mb-6">
            <h3 className="font-semibold text-gray-700 text-sm mb-3">Nueva caja de cambios</h3>
            <FormRow
                data={data}
                setData={setData}
                brands={brands}
                errors={errors}
                processing={processing}
                onCancel={cancelWithSubmit}
                isNew
            />
        </div>
    );
}

function EditForm({ gearbox, brands, onDone }) {
    const { data, setData, post, processing, errors } = useForm({
        _method: "PUT",
        brand_id: gearbox.brand_id,
        name: gearbox.name,
        compatible_vehicles: gearbox.compatible_vehicles ?? "",
        image: null,
        image_url: gearbox.image_url,
        is_active: gearbox.is_active,
        order: gearbox.order ?? "A",
    });

    const submit = () => {
        post(`/admin/gearboxes/${gearbox.id}`, {
            forceFormData: true,
            onSuccess: onDone,
        });
    };

    const cancelWithSubmit = () => onDone();
    cancelWithSubmit.submit = submit;

    return (
        <FormRow
            data={data}
            setData={setData}
            brands={brands}
            errors={errors}
            processing={processing}
            onCancel={cancelWithSubmit}
        />
    );
}

export default function GearboxesIndex({ gearboxes, brands }) {
    const [showCreate, setShowCreate] = useState(false);
    const [editingId, setEditingId] = useState(null);

    const handleDelete = (g) => {
        if (!confirm(`¿Eliminar "${g.name}"? Esta acción no se puede deshacer.`)) return;
        router.delete(`/admin/gearboxes/${g.id}`);
    };

    const handleDeleteProducts = (g) => {
        if ((g.products_count ?? 0) === 0) return;
        if (!confirm(`¿Eliminar todos los productos de "${g.name}"? La caja se conservará, pero se borrarán sus ${g.products_count} producto(s).`)) return;
        router.delete(`/admin/gearboxes/${g.id}/products`);
    };

    return (
        <AdminLayout>
            <div className="max-w-5xl">
                {/* Header */}
                <div className="flex items-center justify-between mb-6">
                    <div>
                        <h1 className="text-2xl font-bold text-gray-800">Cajas de cambios</h1>
                        <p className="text-sm text-gray-500 mt-0.5">Administrá el catálogo de cajas de cambios.</p>
                    </div>
                    {!showCreate && (
                        <button
                            onClick={() => { setShowCreate(true); setEditingId(null); }}
                            className="inline-flex items-center gap-2 px-4 py-2 bg-[#25A7CA] text-white rounded-lg text-sm font-medium hover:bg-[#1d96b8] transition"
                        >
                            <Icon icon="solar:add-circle-outline" width={17} />
                            Nueva caja
                        </button>
                    )}
                </div>

                {/* Formulario crear */}
                {showCreate && (
                    <CreateForm brands={brands} onDone={() => setShowCreate(false)} />
                )}

                {/* Lista */}
                {gearboxes.length === 0 && !showCreate ? (
                    <div className="bg-white rounded-xl border border-gray-100 shadow-sm p-12 text-center">
                        <Icon icon="solar:settings-minimalistic-outline" width={40} className="mx-auto text-gray-300 mb-3" />
                        <p className="text-gray-400 text-sm">No hay cajas de cambios creadas todavía.</p>
                    </div>
                ) : (
                    <div className="space-y-3">
                        {gearboxes.map((g) =>
                            editingId === g.id ? (
                                <EditForm
                                    key={g.id}
                                    gearbox={g}
                                    brands={brands}
                                    onDone={() => setEditingId(null)}
                                />
                            ) : (
                                <div
                                    key={g.id}
                                    className="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex items-center gap-4"
                                >
                                    {/* Orden */}
                                    <span className="text-xs font-mono font-bold text-gray-400 w-8 text-center">{g.order}</span>

                                    {/* Imagen */}
                                    <div className="w-12 h-12 flex-shrink-0 rounded-lg border border-gray-100 bg-gray-50 flex items-center justify-center overflow-hidden">
                                        {g.image_url ? (
                                            <img src={g.image_url} alt={g.name} className="h-full w-full object-contain p-1" />
                                        ) : (
                                            <Icon icon="solar:settings-minimalistic-outline" width={18} className="text-gray-300" />
                                        )}
                                    </div>

                                    {/* Info */}
                                    <div className="flex-1 min-w-0">
                                        <div className="flex items-center gap-2 flex-wrap">
                                            <span className="text-xs font-semibold text-[#25A7CA] uppercase">{g.brand_name}</span>
                                            <span className="font-semibold text-gray-800">{g.name}</span>
                                            <StatusBadge active={g.is_active} />
                                            <span className="inline-flex items-center gap-1 text-[11px] px-2 py-0.5 rounded-full bg-gray-50 text-gray-500 border border-gray-100">
                                                <Icon icon="solar:box-outline" width={12} />
                                                {g.products_count ?? 0} productos
                                            </span>
                                        </div>
                                        {g.compatible_vehicles && (
                                            <p className="text-xs text-gray-400 mt-0.5 truncate">{g.compatible_vehicles}</p>
                                        )}
                                    </div>

                                    {/* Acciones */}
                                    <div className="flex items-center gap-1 flex-shrink-0">
                                        <button
                                            onClick={() => { setEditingId(g.id); setShowCreate(false); }}
                                            className="p-2 text-gray-400 hover:text-[#25A7CA] hover:bg-blue-50 rounded-lg transition"
                                            title="Editar"
                                        >
                                            <Icon icon="solar:pen-outline" width={17} />
                                        </button>
                                        {(g.products_count ?? 0) > 0 && (
                                            <button
                                                onClick={() => handleDeleteProducts(g)}
                                                className="inline-flex items-center gap-1.5 px-2.5 py-2 text-xs font-medium text-amber-700 bg-amber-50 hover:bg-amber-100 rounded-lg transition"
                                                title="Vaciar productos de la caja"
                                            >
                                                <Icon icon="solar:box-outline" width={14} />
                                                Vaciar
                                            </button>
                                        )}
                                        <button
                                            onClick={() => handleDelete(g)}
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
