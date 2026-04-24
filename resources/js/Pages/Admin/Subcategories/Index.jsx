import AdminLayout from "@/Layouts/AdminLayout";
import { router, useForm } from "@inertiajs/react";
import { Icon } from "@iconify/react";
import { useState } from "react";

/* ── Toggle switch ───────────────────────────────────── */
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
                className={`relative w-11 h-6 rounded-full transition-colors focus:outline-none ${
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

/* ── Modal crear / editar ─────────────────────────────── */
function SubcategoryModal({ subcategory, categories, onClose }) {
    const isEdit = !!subcategory;
    const { data, setData, post, processing, errors } = useForm({
        _method:       isEdit ? "PUT" : undefined,
        name:          subcategory?.name || "",
        category_id:   subcategory?.category?.id || subcategory?.category_id || (categories[0]?.id ?? ""),
        order:         subcategory?.order ?? "",
        is_active:     subcategory?.is_active ?? true,
        show_in_home:  subcategory?.show_in_home ?? false,
        image:         null,
    });

    const [preview, setPreview] = useState(null);

    const handleOrder = (e) => {
        const val = e.target.value.toUpperCase().replace(/[^A-Z]/g, "");
        setData("order", val);
    };

    const submit = (e) => {
        e.preventDefault();
        const url = isEdit
            ? `/admin/subcategories/${subcategory.id}`
            : "/admin/subcategories";
        post(url, { forceFormData: true, onSuccess: () => onClose() });
    };

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
            <div className="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 max-h-[90vh] overflow-y-auto">
                <div className="flex items-center justify-between mb-5">
                    <h2 className="font-semibold text-gray-800">
                        {isEdit ? "Editar Subcategoría" : "Nueva Subcategoría"}
                    </h2>
                    <button onClick={onClose} className="p-1.5 hover:bg-gray-100 rounded-lg">
                        <Icon icon="solar:close-square-outline" width={18} />
                    </button>
                </div>

                <form onSubmit={submit} className="space-y-4">
                    {/* Categoría */}
                    <div>
                        <label className="block text-sm text-gray-600 mb-1">Categoría padre *</label>
                        <select
                            value={data.category_id}
                            onChange={(e) => setData("category_id", e.target.value)}
                            className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30"
                        >
                            {categories.map((c) => (
                                <option key={c.id} value={c.id}>{c.name}</option>
                            ))}
                        </select>
                        {errors.category_id && (
                            <p className="text-red-500 text-xs mt-1">{errors.category_id}</p>
                        )}
                    </div>

                    {/* Nombre */}
                    <div>
                        <label className="block text-sm text-gray-600 mb-1">Nombre *</label>
                        <input
                            type="text"
                            value={data.name}
                            onChange={(e) => setData("name", e.target.value)}
                            className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30"
                        />
                        {errors.name && <p className="text-red-500 text-xs mt-1">{errors.name}</p>}
                    </div>

                    {/* Imagen */}
                    <div>
                        <label className="block text-sm text-gray-600 mb-1">Imagen</label>
                        <input
                            type="file"
                            accept="image/*"
                            onChange={(e) => {
                                const f = e.target.files[0];
                                if (f) {
                                    setData("image", f);
                                    setPreview(URL.createObjectURL(f));
                                }
                            }}
                            className="block w-full text-sm text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-[#25A7CA]/10 file:text-[#25A7CA]"
                        />
                        {preview && (
                            <img src={preview} className="mt-2 h-16 rounded-lg object-cover" alt="" />
                        )}
                    </div>

                    {/* Orden */}
                    <div>
                        <label className="block text-sm text-gray-600 mb-1">
                            Orden
                            <span className="ml-1 text-xs text-gray-400">(A, AA, AB… AZ, B, BA…)</span>
                        </label>
                        <input
                            type="text"
                            value={data.order}
                            onChange={handleOrder}
                            placeholder="Ej: A, AA, BA"
                            maxLength={10}
                            className="w-32 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30 uppercase tracking-widest font-mono"
                        />
                        {errors.order && <p className="text-red-500 text-xs mt-1">{errors.order}</p>}
                    </div>

                    {/* Toggles */}
                    <Toggle
                        checked={data.is_active}
                        onChange={(v) => setData("is_active", v)}
                        label="Activo"
                        description="Visible en la web"
                    />
                    <Toggle
                        checked={data.show_in_home}
                        onChange={(v) => setData("show_in_home", v)}
                        label="Mostrar en inicio"
                        description="Aparece como producto destacado en la página principal"
                    />

                    <div className="flex justify-end gap-3 pt-2">
                        <button
                            type="button"
                            onClick={onClose}
                            className="px-4 py-2 border border-gray-200 rounded-xl text-sm text-gray-700 hover:bg-gray-50"
                        >
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            disabled={processing}
                            className="px-4 py-2 bg-[#25A7CA] text-white rounded-xl text-sm font-medium hover:bg-[#1d96b8] disabled:opacity-60"
                        >
                            {processing ? "Guardando..." : isEdit ? "Actualizar" : "Crear"}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
}

/* ── Vista principal ──────────────────────────────────── */
export default function SubcategoriesIndex({ subcategories, categories }) {
    const [modal, setModal] = useState(null);

    const destroy = (id) => {
        if (!confirm("¿Eliminar esta subcategoría?")) return;
        router.delete(`/admin/subcategories/${id}`);
    };

    return (
        <AdminLayout>
            {modal !== null && (
                <SubcategoryModal
                    subcategory={modal === "create" ? null : modal}
                    categories={categories}
                    onClose={() => setModal(null)}
                />
            )}

            <div className="flex items-center justify-between mb-6">
                <div>
                    <h1 className="text-2xl font-bold text-gray-800">Subcategorías</h1>
                    <p className="text-sm text-gray-500">{subcategories.length} subcategorías</p>
                </div>
                <button
                    onClick={() => setModal("create")}
                    className="flex items-center gap-2 bg-[#25A7CA] text-white px-4 py-2 rounded-xl text-sm font-medium hover:bg-[#1d96b8] transition"
                >
                    <Icon icon="solar:add-circle-outline" width={18} />
                    Nueva Subcategoría
                </button>
            </div>

            <div className="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <table className="w-full text-sm">
                    <thead>
                        <tr className="bg-gray-50 text-xs text-gray-500 uppercase tracking-wide">
                            <th className="text-left px-5 py-3">Subcategoría</th>
                            <th className="text-left px-5 py-3">Categoría</th>
                            <th className="px-5 py-3 text-center">Productos</th>
                            <th className="px-5 py-3 text-center">Estado</th>
                            <th className="px-5 py-3 text-center">Inicio</th>
                            <th className="px-5 py-3 text-center">Orden</th>
                            <th className="px-5 py-3 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-50">
                        {subcategories.length === 0 && (
                            <tr>
                                <td colSpan={7} className="text-center py-10 text-gray-400">
                                    Sin subcategorías.
                                </td>
                            </tr>
                        )}
                        {subcategories.map((s) => (
                            <tr key={s.id} className="hover:bg-gray-50/50">
                                {/* Nombre */}
                                <td className="px-5 py-3">
                                    <span className="font-medium text-gray-800">{s.name}</span>
                                </td>

                                {/* Categoría */}
                                <td className="px-5 py-3 text-gray-500">{s.category?.name || "—"}</td>

                                {/* Productos */}
                                <td className="px-5 py-3 text-center text-gray-500">{s.products_count}</td>

                                {/* Estado */}
                                <td className="px-5 py-3 text-center">
                                    <span
                                        className={`text-xs px-2 py-0.5 rounded-full font-medium ${
                                            s.is_active
                                                ? "bg-green-100 text-green-700"
                                                : "bg-gray-100 text-gray-500"
                                        }`}
                                    >
                                        {s.is_active ? "Activo" : "Inactivo"}
                                    </span>
                                </td>

                                {/* Inicio */}
                                <td className="px-5 py-3 text-center">
                                    {s.show_in_home ? (
                                        <span className="inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded-full font-medium bg-[#25A7CA]/10 text-[#25A7CA]">
                                            <Icon icon="solar:home-2-bold" width={12} />
                                            Inicio
                                        </span>
                                    ) : (
                                        <span className="text-gray-300">—</span>
                                    )}
                                </td>

                                {/* Orden */}
                                <td className="px-5 py-3 text-center">
                                    <span className="font-mono text-xs font-semibold text-gray-500 bg-gray-100 px-2 py-0.5 rounded">
                                        {s.order || "—"}
                                    </span>
                                </td>

                                {/* Acciones */}
                                <td className="px-5 py-3">
                                    <div className="flex items-center justify-center gap-2">
                                        <button
                                            onClick={() => setModal(s)}
                                            className="p-1.5 rounded-lg hover:bg-gray-100 text-gray-600"
                                        >
                                            <Icon icon="solar:pen-outline" width={16} />
                                        </button>
                                        <button
                                            onClick={() => destroy(s.id)}
                                            className="p-1.5 rounded-lg hover:bg-red-50 text-red-500"
                                        >
                                            <Icon icon="solar:trash-bin-trash-outline" width={16} />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </AdminLayout>
    );
}
