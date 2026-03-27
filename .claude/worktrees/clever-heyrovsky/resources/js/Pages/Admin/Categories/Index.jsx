import AdminLayout from "@/Layouts/AdminLayout";
import { router } from "@inertiajs/react";
import { Icon } from "@iconify/react";
import { useForm } from "@inertiajs/react";
import { useState } from "react";

function CategoryModal({ category, onClose }) {
    const isEdit = !!category;
    const { data, setData, post, processing, errors } = useForm({
        _method: isEdit ? "PUT" : undefined,
        name: category?.name || "",
        description: category?.description || "",
        order: category?.order || "A",
        is_active: category?.is_active ?? true,
        image: null,
    });

    const [preview, setPreview] = useState(null);

    const submit = (e) => {
        e.preventDefault();
        const url = isEdit ? `/admin/categories/${category.id}` : "/admin/categories";
        post(url, { forceFormData: true, onSuccess: () => onClose() });
    };

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
            <div className="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
                <div className="flex items-center justify-between mb-5">
                    <h2 className="font-semibold text-gray-800">{isEdit ? 'Editar Categoría' : 'Nueva Categoría'}</h2>
                    <button onClick={onClose} className="p-1.5 hover:bg-gray-100 rounded-lg"><Icon icon="solar:close-square-outline" width={18} /></button>
                </div>
                <form onSubmit={submit} className="space-y-4">
                    <div>
                        <label className="block text-sm text-gray-600 mb-1">Nombre *</label>
                        <input type="text" value={data.name} onChange={(e) => setData("name", e.target.value)} className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30" />
                        {errors.name && <p className="text-red-500 text-xs mt-1">{errors.name}</p>}
                    </div>
                    <div>
                        <label className="block text-sm text-gray-600 mb-1">Descripción</label>
                        <textarea value={data.description} onChange={(e) => setData("description", e.target.value)} rows={3} className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30 resize-none" />
                    </div>
                    <div>
                        <label className="block text-sm text-gray-600 mb-1">Imagen</label>
                        {category?.image_url && !preview && <img src={category.image_url} className="h-16 rounded-lg object-cover mb-2" alt="" />}
                        <input type="file" accept="image/*" onChange={(e) => { const f = e.target.files[0]; if(f) { setData("image", f); setPreview(URL.createObjectURL(f)); } }} className="block w-full text-sm text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-[#25A7CA]/10 file:text-[#25A7CA]" />
                        {preview && <img src={preview} className="mt-2 h-16 rounded-lg object-cover" alt="" />}
                    </div>
                    <div className="flex items-center gap-6">
                        <div>
                            <label className="block text-sm text-gray-600 mb-1">Orden</label>
                            <input type="text" value={data.order} onChange={(e) => setData("order", e.target.value.toUpperCase())} maxLength={10} placeholder="A" className="w-20 border border-gray-200 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30" />
                        </div>
                        <label className="flex items-center gap-2 cursor-pointer mt-5">
                            <input type="checkbox" checked={data.is_active} onChange={(e) => setData("is_active", e.target.checked)} className="w-4 h-4 rounded" />
                            <span className="text-sm text-gray-700">Activo</span>
                        </label>
                    </div>
                    <div className="flex justify-end gap-3 pt-2">
                        <button type="button" onClick={onClose} className="px-4 py-2 border border-gray-200 rounded-xl text-sm text-gray-700 hover:bg-gray-50">Cancelar</button>
                        <button type="submit" disabled={processing} className="px-4 py-2 bg-[#25A7CA] text-white rounded-xl text-sm font-medium hover:bg-[#1d96b8] disabled:opacity-60">
                            {processing ? "Guardando..." : (isEdit ? "Actualizar" : "Crear")}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
}

export default function CategoriesIndex({ categories }) {
    const [modal, setModal] = useState(null); // null | 'create' | category object

    const destroy = (id) => {
        if (!confirm("¿Eliminar esta categoría?")) return;
        router.delete(`/admin/categories/${id}`);
    };

    return (
        <AdminLayout>
            {modal !== null && <CategoryModal category={modal === 'create' ? null : modal} onClose={() => setModal(null)} />}

            <div className="flex items-center justify-between mb-6">
                <div>
                    <h1 className="text-2xl font-bold text-gray-800">Categorías de Productos</h1>
                    <p className="text-sm text-gray-500">{categories.length} categorías</p>
                </div>
                <button onClick={() => setModal('create')} className="flex items-center gap-2 bg-[#25A7CA] text-white px-4 py-2 rounded-xl text-sm font-medium hover:bg-[#1d96b8] transition">
                    <Icon icon="solar:add-circle-outline" width={18} />
                    Nueva Categoría
                </button>
            </div>

            <div className="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <table className="w-full text-sm">
                    <thead>
                        <tr className="bg-gray-50 text-xs text-gray-500 uppercase tracking-wide">
                            <th className="text-left px-5 py-3">Categoría</th>
                            <th className="text-left px-5 py-3">Descripción</th>
                            <th className="px-5 py-3">Productos</th>
                            <th className="px-5 py-3">Estado</th>
                            <th className="px-5 py-3">Acciones</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-50">
                        {categories.length === 0 && <tr><td colSpan={5} className="text-center py-10 text-gray-400">Sin categorías.</td></tr>}
                        {categories.map((c) => (
                            <tr key={c.id} className="hover:bg-gray-50/50">
                                <td className="px-5 py-3">
                                    <div className="flex items-center gap-3">
                                        {c.image_url ? <img src={c.image_url} className="w-8 h-8 rounded-lg object-cover" alt="" /> : <div className="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center"><Icon icon="solar:folder-outline" width={14} className="text-gray-400" /></div>}
                                        <span className="font-medium text-gray-800">{c.name}</span>
                                    </div>
                                </td>
                                <td className="px-5 py-3 text-gray-500 truncate max-w-[250px]">{c.description || '—'}</td>
                                <td className="px-5 py-3 text-center text-gray-500">{c.products_count}</td>
                                <td className="px-5 py-3 text-center">
                                    <span className={`text-xs px-2 py-0.5 rounded-full font-medium ${c.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'}`}>{c.is_active ? 'Activo' : 'Inactivo'}</span>
                                </td>
                                <td className="px-5 py-3">
                                    <div className="flex items-center justify-center gap-2">
                                        <button onClick={() => setModal(c)} className="p-1.5 rounded-lg hover:bg-gray-100 text-gray-600"><Icon icon="solar:pen-outline" width={16} /></button>
                                        <button onClick={() => destroy(c.id)} className="p-1.5 rounded-lg hover:bg-red-50 text-red-500"><Icon icon="solar:trash-bin-trash-outline" width={16} /></button>
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
