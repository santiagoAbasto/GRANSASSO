import AdminLayout from "@/Layouts/AdminLayout";
import { router } from "@inertiajs/react";
import { Icon } from "@iconify/react";
import { useState } from "react";

export default function HrApplicationsIndex({ applications, positions, filters }) {
    const [position, setPosition] = useState(filters?.position || "");

    const applyFilters = () => {
        router.get("/admin/hr/applications", { position }, { preserveState: true });
    };

    const destroy = (id) => {
        if (!confirm("¿Eliminar esta postulación?")) return;
        router.delete(`/admin/hr/applications/${id}`);
    };

    return (
        <AdminLayout>
            <div className="mb-6">
                <h1 className="text-2xl font-bold text-gray-800">Postulaciones</h1>
                <p className="text-sm text-gray-500">{applications.total} postulaciones recibidas</p>
            </div>

            <div className="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 mb-5 flex gap-3">
                <select value={position} onChange={(e) => setPosition(e.target.value)} className="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30">
                    <option value="">Todos los puestos</option>
                    {positions.map((p) => <option key={p.id} value={p.id}>{p.title}</option>)}
                </select>
                <button onClick={applyFilters} className="px-4 py-2 bg-[#25A7CA] text-white rounded-lg text-sm hover:bg-[#1d96b8]">Filtrar</button>
            </div>

            <div className="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <table className="w-full text-sm">
                    <thead>
                        <tr className="bg-gray-50 text-xs text-gray-500 uppercase tracking-wide">
                            <th className="text-left px-5 py-3">Candidato</th>
                            <th className="text-left px-5 py-3">Puesto</th>
                            <th className="text-left px-5 py-3">Email</th>
                            <th className="px-5 py-3">Fecha</th>
                            <th className="px-5 py-3">Estado</th>
                            <th className="px-5 py-3">Acciones</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-50">
                        {applications.data.length === 0 && <tr><td colSpan={6} className="text-center py-10 text-gray-400">Sin postulaciones.</td></tr>}
                        {applications.data.map((a) => (
                            <tr key={a.id} className={`hover:bg-gray-50/50 ${!a.is_read ? 'bg-blue-50/30' : ''}`}>
                                <td className="px-5 py-3">
                                    <div className="flex items-center gap-2">
                                        {!a.is_read && <span className="w-2 h-2 rounded-full bg-[#25A7CA] flex-shrink-0" />}
                                        <span className="font-medium text-gray-800">{a.name}</span>
                                    </div>
                                </td>
                                <td className="px-5 py-3 text-gray-500">{a.position?.title || '—'}</td>
                                <td className="px-5 py-3 text-gray-500">{a.email}</td>
                                <td className="px-5 py-3 text-center text-gray-500">{a.created_at}</td>
                                <td className="px-5 py-3 text-center">
                                    <span className={`text-xs px-2 py-0.5 rounded-full font-medium ${a.is_read ? 'bg-gray-100 text-gray-500' : 'bg-[#25A7CA]/10 text-[#25A7CA]'}`}>{a.is_read ? 'Leída' : 'Nueva'}</span>
                                </td>
                                <td className="px-5 py-3">
                                    <div className="flex items-center justify-center gap-2">
                                        <button onClick={() => router.visit(`/admin/hr/applications/${a.id}`)} className="p-1.5 rounded-lg hover:bg-gray-100 text-gray-600"><Icon icon="solar:eye-outline" width={16} /></button>
                                        <button onClick={() => destroy(a.id)} className="p-1.5 rounded-lg hover:bg-red-50 text-red-500"><Icon icon="solar:trash-bin-trash-outline" width={16} /></button>
                                    </div>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
                {applications.last_page > 1 && (
                    <div className="flex items-center justify-between px-5 py-3 border-t border-gray-100 text-sm text-gray-500">
                        <span>Página {applications.current_page} de {applications.last_page}</span>
                        <div className="flex gap-2">
                            {applications.prev_page_url && <button onClick={() => router.visit(applications.prev_page_url)} className="px-3 py-1.5 rounded-lg border border-gray-200 hover:bg-gray-50">Anterior</button>}
                            {applications.next_page_url && <button onClick={() => router.visit(applications.next_page_url)} className="px-3 py-1.5 rounded-lg border border-gray-200 hover:bg-gray-50">Siguiente</button>}
                        </div>
                    </div>
                )}
            </div>
        </AdminLayout>
    );
}
