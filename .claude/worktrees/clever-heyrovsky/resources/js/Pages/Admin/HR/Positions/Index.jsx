import AdminLayout from "@/Layouts/AdminLayout";
import { router } from "@inertiajs/react";
import { Icon } from "@iconify/react";

export default function HrPositionsIndex({ positions, sectors }) {
    const destroy = (id) => {
        if (!confirm("¿Eliminar este puesto?")) return;
        router.delete(`/admin/hr/positions/${id}`);
    };

    return (
        <AdminLayout>
            <div className="flex items-center justify-between mb-6">
                <div>
                    <h1 className="text-2xl font-bold text-gray-800">Puestos de Trabajo</h1>
                    <p className="text-sm text-gray-500">{positions.length} puestos</p>
                </div>
                <button onClick={() => router.visit('/admin/hr/positions/create')} className="flex items-center gap-2 bg-[#25A7CA] text-white px-4 py-2 rounded-xl text-sm font-medium hover:bg-[#1d96b8] transition">
                    <Icon icon="solar:add-circle-outline" width={18} />
                    Nuevo Puesto
                </button>
            </div>

            <div className="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <table className="w-full text-sm">
                    <thead>
                        <tr className="bg-gray-50 text-xs text-gray-500 uppercase tracking-wide">
                            <th className="text-left px-5 py-3">Puesto</th>
                            <th className="text-left px-5 py-3">Sector</th>
                            <th className="px-5 py-3">Postulaciones</th>
                            <th className="px-5 py-3">Estado</th>
                            <th className="px-5 py-3">Acciones</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-50">
                        {positions.length === 0 && <tr><td colSpan={5} className="text-center py-10 text-gray-400">Sin puestos de trabajo.</td></tr>}
                        {positions.map((p) => (
                            <tr key={p.id} className="hover:bg-gray-50/50">
                                <td className="px-5 py-3 font-medium text-gray-800">{p.title}</td>
                                <td className="px-5 py-3 text-gray-500">{p.sector?.name || '—'}</td>
                                <td className="px-5 py-3 text-center text-gray-500">{p.applications_count}</td>
                                <td className="px-5 py-3 text-center">
                                    <span className={`text-xs px-2 py-0.5 rounded-full font-medium ${p.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'}`}>{p.is_active ? 'Activo' : 'Inactivo'}</span>
                                </td>
                                <td className="px-5 py-3">
                                    <div className="flex items-center justify-center gap-2">
                                        <button onClick={() => router.visit(`/admin/hr/positions/${p.id}/edit`)} className="p-1.5 rounded-lg hover:bg-gray-100 text-gray-600"><Icon icon="solar:pen-outline" width={16} /></button>
                                        <button onClick={() => destroy(p.id)} className="p-1.5 rounded-lg hover:bg-red-50 text-red-500"><Icon icon="solar:trash-bin-trash-outline" width={16} /></button>
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
