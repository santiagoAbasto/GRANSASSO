import AdminLayout from "@/Layouts/AdminLayout";
import { router } from "@inertiajs/react";
import { Icon } from "@iconify/react";

export default function SectorsIndex({ industries }) {
    const destroy = (id) => {
        if (!confirm("¿Eliminar este sector?")) return;
        router.delete(`/admin/sectors/${id}`);
    };

    return (
        <AdminLayout>
            <div className="flex items-center justify-between mb-6">
                <div>
                    <h1 className="text-2xl font-bold text-gray-800">Sectores</h1>
                    <p className="text-sm text-gray-500">{industries.length} sectores</p>
                </div>
                <button onClick={() => router.visit('/admin/sectors/create')} className="flex items-center gap-2 bg-[#25A7CA] text-white px-4 py-2 rounded-xl text-sm font-medium hover:bg-[#1d96b8] transition">
                    <Icon icon="solar:add-circle-outline" width={18} />
                    Nuevo Sector
                </button>
            </div>

            <div className="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <table className="w-full text-sm">
                    <thead>
                        <tr className="bg-gray-50 text-xs text-gray-500 uppercase tracking-wide">
                            <th className="text-left px-5 py-3">Sector</th>
                            <th className="px-5 py-3">Orden</th>
                            <th className="px-5 py-3">Estado</th>
                            <th className="px-5 py-3">Acciones</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-50">
                        {industries.length === 0 && <tr><td colSpan={4} className="text-center py-10 text-gray-400">Sin sectores.</td></tr>}
                        {industries.map((i) => (
                            <tr key={i.id} className="hover:bg-gray-50/50">
                                <td className="px-5 py-3">
                                    <div className="flex items-center gap-3">
                                        {i.image_url ? <img src={i.image_url} className="w-9 h-9 rounded-lg object-cover" alt="" /> : <div className="w-9 h-9 rounded-lg bg-gray-100 flex items-center justify-center"><Icon icon="solar:buildings-outline" width={16} className="text-gray-400" /></div>}
                                        <div>
                                            <p className="font-medium text-gray-800">{i.name}</p>
                                            {i.title && <p className="text-xs text-gray-400">{i.title}</p>}
                                        </div>
                                    </div>
                                </td>
                                <td className="px-5 py-3 text-center text-gray-500">{i.order}</td>
                                <td className="px-5 py-3 text-center">
                                    <span className={`text-xs px-2 py-0.5 rounded-full font-medium ${i.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'}`}>{i.is_active ? 'Activo' : 'Inactivo'}</span>
                                </td>
                                <td className="px-5 py-3">
                                    <div className="flex items-center justify-center gap-2">
                                        <button onClick={() => router.visit(`/admin/sectors/${i.id}/edit`)} className="p-1.5 rounded-lg hover:bg-gray-100 text-gray-600"><Icon icon="solar:pen-outline" width={16} /></button>
                                        <button onClick={() => destroy(i.id)} className="p-1.5 rounded-lg hover:bg-red-50 text-red-500"><Icon icon="solar:trash-bin-trash-outline" width={16} /></button>
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
