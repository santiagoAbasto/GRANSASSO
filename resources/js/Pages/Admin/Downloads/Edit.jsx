import AdminLayout from "@/Layouts/AdminLayout";
import { useForm } from "@inertiajs/react";
import { Icon } from "@iconify/react";

export default function DownloadsEdit({ download }) {
    const { data, setData, post, processing, errors } = useForm({
        _method: "PUT",
        name: download.name || "",
        category: download.category || "",
        description: download.description || "",
        order: download.order ?? 0,
        is_active: download.is_active ?? true,
        file: null,
    });

    const submit = (e) => {
        e.preventDefault();
        post(`/admin/downloads/${download.id}`, { forceFormData: true });
    };

    return (
        <AdminLayout>
            <div className="max-w-2xl">
                <div className="flex items-center gap-3 mb-6">
                    <button onClick={() => history.back()} className="p-2 rounded-lg hover:bg-gray-100 text-gray-500"><Icon icon="solar:arrow-left-outline" width={18} /></button>
                    <div>
                        <h1 className="text-2xl font-bold text-gray-800">Editar Descarga</h1>
                        <p className="text-sm text-gray-500">{download.name}</p>
                    </div>
                </div>

                <form onSubmit={submit} className="space-y-6">
                    <div className="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-4">
                        <div>
                            <label className="block text-sm text-gray-600 mb-1">Nombre *</label>
                            <input type="text" value={data.name} onChange={(e) => setData("name", e.target.value)} className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30" />
                        </div>
                        <div>
                            <label className="block text-sm text-gray-600 mb-1">Categoría</label>
                            <input type="text" value={data.category} onChange={(e) => setData("category", e.target.value)} className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30" />
                        </div>
                        <div>
                            <label className="block text-sm text-gray-600 mb-1">Descripción</label>
                            <textarea value={data.description} onChange={(e) => setData("description", e.target.value)} rows={2} className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none resize-none" />
                        </div>
                        {download.file_url && (
                            <div className="flex items-center gap-2 p-3 bg-gray-50 rounded-lg text-sm text-gray-600">
                                <Icon icon="solar:file-download-outline" width={16} className="text-[#25A7CA]" />
                                <span>Archivo actual:</span>
                                <a href={download.file_url} target="_blank" rel="noopener noreferrer" className="text-[#25A7CA] hover:underline">Ver/descargar</a>
                                {download.size && <span className="text-gray-400">({download.size})</span>}
                            </div>
                        )}
                        <div>
                            <label className="block text-sm text-gray-600 mb-1">Reemplazar archivo</label>
                            <input type="file" onChange={(e) => setData("file", e.target.files[0])} className="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-[#25A7CA]/10 file:text-[#25A7CA]" />
                        </div>
                        <div className="flex items-center gap-6">
                            <div>
                                <label className="block text-sm text-gray-600 mb-1">Orden</label>
                                <input type="number" value={data.order} onChange={(e) => setData("order", parseInt(e.target.value) || 0)} min="0" className="w-24 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none" />
                            </div>
                            <label className="flex items-center gap-2 cursor-pointer mt-5">
                                <input type="checkbox" checked={data.is_active} onChange={(e) => setData("is_active", e.target.checked)} className="w-4 h-4 rounded" />
                                <span className="text-sm text-gray-700">Activo</span>
                            </label>
                        </div>
                    </div>

                    <div className="flex justify-end gap-3">
                        <button type="button" onClick={() => history.back()} className="px-5 py-2.5 rounded-xl border border-gray-200 text-sm text-gray-700 hover:bg-gray-50">Cancelar</button>
                        <button type="submit" disabled={processing} className="px-5 py-2.5 rounded-xl bg-[#25A7CA] text-white text-sm font-medium hover:bg-[#1d96b8] disabled:opacity-60 transition">
                            {processing ? "Guardando..." : "Actualizar Descarga"}
                        </button>
                    </div>
                </form>
            </div>
        </AdminLayout>
    );
}
