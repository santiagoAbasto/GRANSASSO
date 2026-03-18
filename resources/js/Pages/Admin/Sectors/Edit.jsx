import AdminLayout from "@/Layouts/AdminLayout";
import { useForm } from "@inertiajs/react";
import { Icon } from "@iconify/react";

import RichTextEditor from "@/Components/Admin/RichTextEditor";
import ImageUploadField from "@/Components/Admin/ImageUploadField";

export default function SectorsEdit({ industry }) {
    const { data, setData, post, processing, errors } = useForm({
        _method: "PUT",
        title: industry.title || "",
        name: industry.name || "",
        description: industry.description || "",
        order: industry.order || "A",
        is_active: industry.is_active ?? true,
        image: null,
    });

    const submit = (e) => {
        e.preventDefault();
        post(`/admin/sectors/${industry.id}`, { forceFormData: true });
    };

    return (
        <AdminLayout>
            <div className="max-w-2xl">
                <div className="flex items-center gap-3 mb-6">
                    <button onClick={() => history.back()} className="p-2 rounded-lg hover:bg-gray-100 text-gray-500"><Icon icon="solar:arrow-left-outline" width={18} /></button>
                    <div>
                        <h1 className="text-2xl font-bold text-gray-800">Editar Sector</h1>
                        <p className="text-sm text-gray-500">{industry.name}</p>
                    </div>
                </div>
                <form onSubmit={submit} className="space-y-6">
                    <div className="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-4">
                        <div>
                            <label className="block text-sm text-gray-600 mb-1">Nombre *</label>
                            <input type="text" value={data.name} onChange={(e) => setData("name", e.target.value)} className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30" />
                        </div>
                        <div>
                            <label className="block text-sm text-gray-600 mb-1">Título (etiqueta)</label>
                            <input type="text" value={data.title} onChange={(e) => setData("title", e.target.value)} className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30" />
                        </div>
                        <div>
                            <label className="block text-sm text-gray-600 mb-2">Descripción</label>
                            <RichTextEditor value={data.description} onChange={(v) => setData("description", v)} />
                        </div>
                    </div>

                    <div className="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                        <h2 className="font-semibold text-gray-700 mb-4">Imagen</h2>
                        <ImageUploadField
                            currentUrl={industry.image_url}
                            onChange={(f) => setData("image", f)}
                            specs={{ width: 500, height: 500, maxMB: 3, formats: ["PNG", "JPG"] }}
                        />
                    </div>

                    <div className="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 flex items-center gap-6">
                        <div>
                            <label className="block text-sm text-gray-600 mb-1">Orden</label>
                            <input type="text" value={data.order} onChange={(e) => setData("order", e.target.value.toUpperCase())} maxLength={10} placeholder="A" className="w-24 border border-gray-200 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30" />
                        </div>
                        <label className="flex items-center gap-2 cursor-pointer mt-5">
                            <input type="checkbox" checked={data.is_active} onChange={(e) => setData("is_active", e.target.checked)} className="w-4 h-4 rounded" />
                            <span className="text-sm text-gray-700">Activo</span>
                        </label>
                    </div>

                    <div className="flex justify-end gap-3">
                        <button type="button" onClick={() => history.back()} className="px-5 py-2.5 rounded-xl border border-gray-200 text-sm text-gray-700 hover:bg-gray-50">Cancelar</button>
                        <button type="submit" disabled={processing} className="px-5 py-2.5 rounded-xl bg-[#25A7CA] text-white text-sm font-medium hover:bg-[#1d96b8] disabled:opacity-60 transition">
                            {processing ? "Guardando..." : "Actualizar Sector"}
                        </button>
                    </div>
                </form>
            </div>
        </AdminLayout>
    );
}
