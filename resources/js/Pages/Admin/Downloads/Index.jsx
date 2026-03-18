import AdminLayout from "@/Layouts/AdminLayout";
import { router } from "@inertiajs/react";
import { Icon } from "@iconify/react";

export default function DownloadsIndex({ downloads }) {
    const destroy = (id) => {
        if (!confirm("¿Eliminar esta descarga?")) return;
        router.delete(`/admin/downloads/${id}`);
    };

    return (
        <AdminLayout>
            <div className="flex items-center justify-between mb-6">
                <div>
                    <h1 className="text-2xl font-bold text-gray-800">Descargas</h1>
                    <p className="text-sm text-gray-500">{downloads.length} archivos</p>
                </div>
                <button onClick={() => router.visit('/admin/downloads/create')} className="flex items-center gap-2 bg-[#25A7CA] text-white px-4 py-2 rounded-xl text-sm font-medium hover:bg-[#1d96b8] transition">
                    <Icon icon="solar:add-circle-outline" width={18} />
                    Nueva Descarga
                </button>
            </div>

            <div className="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <table className="w-full text-sm">
                    <thead>
                        <tr className="bg-gray-50 text-xs text-gray-500 uppercase tracking-wide">
                            <th className="text-left px-5 py-3">Nombre</th>
                            <th className="text-left px-5 py-3">Categoría</th>
                            <th className="px-5 py-3">Tamaño</th>
                            <th className="px-5 py-3">Estado</th>
                            <th className="px-5 py-3">Acciones</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-50">
                        {downloads.length === 0 && <tr><td colSpan={5} className="text-center py-10 text-gray-400">Sin archivos de descarga.</td></tr>}
                        {downloads.map((d) => (
                            <tr key={d.id} className="hover:bg-gray-50/50">
                                <td className="px-5 py-3">
                                    <div className="flex items-center gap-3">
                                        <div className="w-9 h-9 rounded-lg bg-[#25A7CA]/10 flex items-center justify-center flex-shrink-0">
                                            <Icon icon="solar:file-download-outline" width={16} className="text-[#25A7CA]" />
                                        </div>
                                        <div>
                                            <p className="font-medium text-gray-800">{d.name}</p>
                                            <p className="text-xs text-gray-400">{d.description || '—'}</p>
                                        </div>
                                    </div>
                                </td>
                                <td className="px-5 py-3 text-gray-500">{d.category || '—'}</td>
                                <td className="px-5 py-3 text-center text-gray-500">{d.size || '—'}</td>
                                <td className="px-5 py-3 text-center">
                                    <span className={`text-xs px-2 py-0.5 rounded-full font-medium ${d.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'}`}>{d.is_active ? 'Activo' : 'Inactivo'}</span>
                                </td>
                                <td className="px-5 py-3">
                                    <div className="flex items-center justify-center gap-2">
                                        {d.file_url && (
                                            <a href={d.file_url} target="_blank" rel="noopener noreferrer" className="p-1.5 rounded-lg hover:bg-gray-100 text-gray-600">
                                                <Icon icon="solar:download-outline" width={16} />
                                            </a>
                                        )}
                                        <button onClick={() => router.visit(`/admin/downloads/${d.id}/edit`)} className="p-1.5 rounded-lg hover:bg-gray-100 text-gray-600"><Icon icon="solar:pen-outline" width={16} /></button>
                                        <button onClick={() => destroy(d.id)} className="p-1.5 rounded-lg hover:bg-red-50 text-red-500"><Icon icon="solar:trash-bin-trash-outline" width={16} /></button>
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
