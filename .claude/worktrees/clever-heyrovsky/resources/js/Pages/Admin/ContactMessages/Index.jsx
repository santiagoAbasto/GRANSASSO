import AdminLayout from "@/Layouts/AdminLayout";
import { router } from "@inertiajs/react";
import { Icon } from "@iconify/react";

export default function ContactMessagesIndex({ messages }) {
    const destroy = (id) => {
        if (!confirm("¿Eliminar este mensaje?")) return;
        router.delete(`/admin/contact-messages/${id}`);
    };

    return (
        <AdminLayout>
            <div className="mb-6">
                <h1 className="text-2xl font-bold text-gray-800">Mensajes de Contacto</h1>
                <p className="text-sm text-gray-500">{messages.total} mensajes recibidos</p>
            </div>

            <div className="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <table className="w-full text-sm">
                    <thead>
                        <tr className="bg-gray-50 text-xs text-gray-500 uppercase tracking-wide">
                            <th className="text-left px-5 py-3">Remitente</th>
                            <th className="text-left px-5 py-3">Email</th>
                            <th className="text-left px-5 py-3">Empresa</th>
                            <th className="px-5 py-3">Fecha</th>
                            <th className="px-5 py-3">Estado</th>
                            <th className="px-5 py-3">Acciones</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-50">
                        {messages.data.length === 0 && <tr><td colSpan={6} className="text-center py-10 text-gray-400">Sin mensajes.</td></tr>}
                        {messages.data.map((m) => (
                            <tr key={m.id} className={`hover:bg-gray-50/50 ${!m.is_read ? 'bg-blue-50/30' : ''}`}>
                                <td className="px-5 py-3">
                                    <div className="flex items-center gap-2">
                                        {!m.is_read && <span className="w-2 h-2 rounded-full bg-[#25A7CA] flex-shrink-0" />}
                                        <span className="font-medium text-gray-800">{m.name}</span>
                                    </div>
                                </td>
                                <td className="px-5 py-3 text-gray-500">{m.email}</td>
                                <td className="px-5 py-3 text-gray-500">{m.company || '—'}</td>
                                <td className="px-5 py-3 text-center text-gray-500">{m.created_at}</td>
                                <td className="px-5 py-3 text-center">
                                    <span className={`text-xs px-2 py-0.5 rounded-full font-medium ${m.is_read ? 'bg-gray-100 text-gray-500' : 'bg-[#25A7CA]/10 text-[#25A7CA]'}`}>{m.is_read ? 'Leído' : 'Nuevo'}</span>
                                </td>
                                <td className="px-5 py-3">
                                    <div className="flex items-center justify-center gap-2">
                                        <button onClick={() => router.visit(`/admin/contact-messages/${m.id}`)} className="p-1.5 rounded-lg hover:bg-gray-100 text-gray-600"><Icon icon="solar:eye-outline" width={16} /></button>
                                        <button onClick={() => destroy(m.id)} className="p-1.5 rounded-lg hover:bg-red-50 text-red-500"><Icon icon="solar:trash-bin-trash-outline" width={16} /></button>
                                    </div>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
                {messages.last_page > 1 && (
                    <div className="flex items-center justify-between px-5 py-3 border-t border-gray-100 text-sm text-gray-500">
                        <span>Página {messages.current_page} de {messages.last_page}</span>
                        <div className="flex gap-2">
                            {messages.prev_page_url && <button onClick={() => router.visit(messages.prev_page_url)} className="px-3 py-1.5 rounded-lg border border-gray-200 hover:bg-gray-50">Anterior</button>}
                            {messages.next_page_url && <button onClick={() => router.visit(messages.next_page_url)} className="px-3 py-1.5 rounded-lg border border-gray-200 hover:bg-gray-50">Siguiente</button>}
                        </div>
                    </div>
                )}
            </div>
        </AdminLayout>
    );
}
