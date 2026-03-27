import AdminLayout from "@/Layouts/AdminLayout";
import { router } from "@inertiajs/react";
import { Icon } from "@iconify/react";

export default function QuotationsIndex({ quotations }) {
    const destroy = (id) => {
        if (!confirm("¿Eliminar esta cotización?")) return;
        router.delete(`/admin/comercial/quotations/${id}`);
    };

    return (
        <AdminLayout>
            <div className="mb-6">
                <h1 className="text-2xl font-bold text-gray-800">Cotizaciones</h1>
                <p className="text-sm text-gray-500">{quotations.total} cotizaciones recibidas</p>
            </div>

            <div className="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <table className="w-full text-sm">
                    <thead>
                        <tr className="bg-gray-50 text-xs text-gray-500 uppercase tracking-wide">
                            <th className="text-left px-5 py-3">Solicitante</th>
                            <th className="text-left px-5 py-3">Empresa</th>
                            <th className="text-left px-5 py-3">Email</th>
                            <th className="px-5 py-3">Fecha</th>
                            <th className="px-5 py-3">Estado</th>
                            <th className="px-5 py-3">Acciones</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-50">
                        {quotations.data.length === 0 && <tr><td colSpan={6} className="text-center py-10 text-gray-400">Sin cotizaciones.</td></tr>}
                        {quotations.data.map((q) => (
                            <tr key={q.id} className={`hover:bg-gray-50/50 ${!q.is_read ? 'bg-blue-50/30' : ''}`}>
                                <td className="px-5 py-3">
                                    <div className="flex items-center gap-2">
                                        {!q.is_read && <span className="w-2 h-2 rounded-full bg-[#25A7CA] flex-shrink-0" />}
                                        <span className="font-medium text-gray-800">{q.name}</span>
                                    </div>
                                </td>
                                <td className="px-5 py-3 text-gray-500">{q.company || '—'}</td>
                                <td className="px-5 py-3 text-gray-500">{q.email}</td>
                                <td className="px-5 py-3 text-center text-gray-500">{q.created_at}</td>
                                <td className="px-5 py-3 text-center">
                                    <span className={`text-xs px-2 py-0.5 rounded-full font-medium ${q.is_read ? 'bg-gray-100 text-gray-500' : 'bg-[#25A7CA]/10 text-[#25A7CA]'}`}>{q.is_read ? 'Leída' : 'Nueva'}</span>
                                </td>
                                <td className="px-5 py-3">
                                    <div className="flex items-center justify-center gap-2">
                                        <button onClick={() => router.visit(`/admin/comercial/quotations/${q.id}`)} className="p-1.5 rounded-lg hover:bg-gray-100 text-gray-600"><Icon icon="solar:eye-outline" width={16} /></button>
                                        <button onClick={() => destroy(q.id)} className="p-1.5 rounded-lg hover:bg-red-50 text-red-500"><Icon icon="solar:trash-bin-trash-outline" width={16} /></button>
                                    </div>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
                {quotations.last_page > 1 && (
                    <div className="flex items-center justify-between px-5 py-3 border-t border-gray-100 text-sm text-gray-500">
                        <span>Página {quotations.current_page} de {quotations.last_page}</span>
                        <div className="flex gap-2">
                            {quotations.prev_page_url && <button onClick={() => router.visit(quotations.prev_page_url)} className="px-3 py-1.5 rounded-lg border border-gray-200 hover:bg-gray-50">Anterior</button>}
                            {quotations.next_page_url && <button onClick={() => router.visit(quotations.next_page_url)} className="px-3 py-1.5 rounded-lg border border-gray-200 hover:bg-gray-50">Siguiente</button>}
                        </div>
                    </div>
                )}
            </div>
        </AdminLayout>
    );
}
