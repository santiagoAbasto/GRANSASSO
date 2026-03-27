import AdminLayout from "@/Layouts/AdminLayout";
import { useForm } from "@inertiajs/react";
import { Icon } from "@iconify/react";
import ImageUploadField from "@/Components/Admin/ImageUploadField";

export default function SubcategoryEdit({ subcategory, categories }) {
    const { data, setData, post, processing, errors } = useForm({
        _method: "PUT",
        name: subcategory.name || "",
        category_id: subcategory.category_id || "",
        order: subcategory.order ?? 0,
        is_active: subcategory.is_active ?? true,
        image: null,
    });

    const [preview, setPreview] = useState(null);

    const submit = (e) => {
        e.preventDefault();
        post(`/admin/subcategories/${subcategory.id}`, { forceFormData: true });
    };

    return (
        <AdminLayout>
            <div className="max-w-2xl">
                <div className="flex items-center gap-3 mb-6">
                    <button onClick={() => history.back()} className="p-2 rounded-lg hover:bg-gray-100 text-gray-500">
                        <Icon icon="solar:arrow-left-outline" width={18} />
                    </button>
                    <div>
                        <h1 className="text-2xl font-bold text-gray-800">Editar Subcategoría</h1>
                        <p className="text-sm text-gray-500">{subcategory.name}</p>
                    </div>
                </div>

                <form onSubmit={submit} className="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-4">
                    <div>
                        <label className="block text-sm text-gray-600 mb-1">Categoría padre *</label>
                        <select value={data.category_id} onChange={(e) => setData("category_id", e.target.value)} className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30">
                            {categories.map((c) => <option key={c.id} value={c.id}>{c.name}</option>)}
                        </select>
                    </div>
                    <div>
                        <label className="block text-sm text-gray-600 mb-1">Nombre *</label>
                        <input type="text" value={data.name} onChange={(e) => setData("name", e.target.value)} className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30" />
                        {errors.name && <p className="text-red-500 text-xs mt-1">{errors.name}</p>}
                    </div>
                    <div>
                        <label className="block text-sm text-gray-600 mb-1">Imagen</label>
                        <input type="file" accept="image/*" onChange={(e) => { const f = e.target.files[0]; if(f) { setData("image", f); setPreview(URL.createObjectURL(f)); } }} className="block w-full text-sm text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-[#25A7CA]/10 file:text-[#25A7CA]" />
                        {preview && <img src={preview} className="mt-2 h-16 rounded-lg object-cover" alt="" />}
                    </div>
                    <div className="flex items-center gap-6">
                        <div>
                            <label className="block text-sm text-gray-600 mb-1">Orden</label>
                            <input type="number" value={data.order} onChange={(e) => setData("order", parseInt(e.target.value) || 0)} min="0" className="w-20 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none" />
                        </div>
                        <label className="flex items-center gap-2 cursor-pointer mt-5">
                            <input type="checkbox" checked={data.is_active} onChange={(e) => setData("is_active", e.target.checked)} className="w-4 h-4 rounded" />
                            <span className="text-sm text-gray-700">Activo</span>
                        </label>
                    </div>
                    <div className="flex justify-end gap-3 pt-2">
                        <button type="button" onClick={() => history.back()} className="px-5 py-2.5 rounded-xl border border-gray-200 text-sm text-gray-700 hover:bg-gray-50">Cancelar</button>
                        <button type="submit" disabled={processing} className="px-5 py-2.5 rounded-xl bg-[#25A7CA] text-white text-sm font-medium hover:bg-[#1d96b8] disabled:opacity-60 transition">
                            {processing ? "Guardando..." : "Actualizar"}
                        </button>
                    </div>
                </form>
            </div>
        </AdminLayout>
    );
}
