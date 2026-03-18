import AdminLayout from "@/Layouts/AdminLayout";
import { router, useForm } from "@inertiajs/react";
import { Icon } from "@iconify/react";
import { useState } from "react";

export default function NewsletterIndex({ subscribers, total, total_active }) {
    const [showSend, setShowSend] = useState(false);

    const { data, setData, post, processing, reset, errors } = useForm({
        subject: "",
        body: "",
    });

    const destroy = (id) => {
        if (!confirm("¿Eliminar este suscriptor?")) return;
        router.delete(`/admin/newsletter/${id}`);
    };

    const handleSend = (e) => {
        e.preventDefault();
        post("/admin/newsletter/send", {
            onSuccess: () => { reset(); setShowSend(false); },
        });
    };

    return (
        <AdminLayout>
            {/* Header */}
            <div className="flex items-center justify-between mb-6">
                <div>
                    <h1 className="text-2xl font-bold text-gray-800">Newsletter</h1>
                    <p className="text-sm text-gray-500">{total} suscriptores · {total_active} activos</p>
                </div>
                <button
                    onClick={() => setShowSend(!showSend)}
                    className="flex items-center gap-2 bg-[#25A7CA] text-white px-4 py-2 rounded-xl text-sm font-medium hover:bg-[#1d96b8] transition"
                >
                    <Icon icon="solar:letter-outline" width={18} />
                    Enviar Email
                </button>
            </div>

            {/* Send Campaign Panel */}
            {showSend && (
                <div className="bg-white rounded-2xl border border-[#25A7CA]/30 shadow-sm p-6 mb-6">
                    <div className="flex items-center justify-between mb-4">
                        <div>
                            <h2 className="font-semibold text-gray-800 flex items-center gap-2">
                                <Icon icon="solar:letter-outline" width={18} className="text-[#25A7CA]" />
                                Enviar campaña
                            </h2>
                            <p className="text-xs text-gray-500 mt-0.5">
                                Se enviará a los <strong>{total_active}</strong> suscriptores activos via Mailtrap
                            </p>
                        </div>
                        <button onClick={() => setShowSend(false)} className="p-1.5 hover:bg-gray-100 rounded-lg text-gray-400">
                            <Icon icon="solar:close-square-outline" width={18} />
                        </button>
                    </div>
                    <form onSubmit={handleSend} className="space-y-4">
                        <div>
                            <label className="block text-sm text-gray-600 mb-1">Asunto *</label>
                            <input
                                type="text"
                                value={data.subject}
                                onChange={(e) => setData("subject", e.target.value)}
                                placeholder="Ej: Novedades de marzo 2026"
                                className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30"
                            />
                            {errors.subject && <p className="text-red-500 text-xs mt-1">{errors.subject}</p>}
                        </div>
                        <div>
                            <label className="block text-sm text-gray-600 mb-1">Cuerpo del mensaje *</label>
                            <textarea
                                value={data.body}
                                onChange={(e) => setData("body", e.target.value)}
                                rows={6}
                                placeholder="Escribí el contenido del email aquí..."
                                className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30 resize-none"
                            />
                            {errors.body && <p className="text-red-500 text-xs mt-1">{errors.body}</p>}
                        </div>
                        <div className="flex items-center justify-between">
                            <p className="text-xs text-gray-400 flex items-center gap-1">
                                <Icon icon="solar:shield-check-outline" width={14} className="text-green-500" />
                                Enviando a través de Mailtrap (entorno de prueba)
                            </p>
                            <div className="flex gap-3">
                                <button
                                    type="button"
                                    onClick={() => setShowSend(false)}
                                    className="px-4 py-2 border border-gray-200 rounded-xl text-sm text-gray-700 hover:bg-gray-50"
                                >
                                    Cancelar
                                </button>
                                <button
                                    type="submit"
                                    disabled={processing || total_active === 0}
                                    className="flex items-center gap-2 px-5 py-2 bg-[#25A7CA] text-white rounded-xl text-sm font-medium hover:bg-[#1d96b8] disabled:opacity-60 transition"
                                >
                                    <Icon icon={processing ? "solar:refresh-outline" : "solar:plain-2-outline"} width={16} className={processing ? "animate-spin" : ""} />
                                    {processing ? "Enviando..." : `Enviar a ${total_active} suscriptores`}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            )}

            {/* Subscribers Table */}
            <div className="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <table className="w-full text-sm">
                    <thead>
                        <tr className="bg-gray-50 text-xs text-gray-500 uppercase tracking-wide">
                            <th className="text-left px-5 py-3">#</th>
                            <th className="text-left px-5 py-3">Email</th>
                            <th className="px-5 py-3">Estado</th>
                            <th className="px-5 py-3">Fecha suscripción</th>
                            <th className="px-5 py-3">Acciones</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-50">
                        {subscribers.data.length === 0 && (
                            <tr><td colSpan={5} className="text-center py-10 text-gray-400">Sin suscriptores.</td></tr>
                        )}
                        {subscribers.data.map((s, i) => (
                            <tr key={s.id} className="hover:bg-gray-50/50">
                                <td className="px-5 py-3 text-gray-400">{subscribers.from + i}</td>
                                <td className="px-5 py-3 font-medium text-gray-800">{s.email}</td>
                                <td className="px-5 py-3 text-center">
                                    <span className={`text-xs px-2 py-0.5 rounded-full font-medium ${s.is_active !== false ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'}`}>
                                        {s.is_active !== false ? 'Activo' : 'Inactivo'}
                                    </span>
                                </td>
                                <td className="px-5 py-3 text-center text-gray-500">{s.created_at}</td>
                                <td className="px-5 py-3 text-center">
                                    <button onClick={() => destroy(s.id)} className="p-1.5 rounded-lg hover:bg-red-50 text-red-500">
                                        <Icon icon="solar:trash-bin-trash-outline" width={16} />
                                    </button>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
                {subscribers.last_page > 1 && (
                    <div className="flex items-center justify-between px-5 py-3 border-t border-gray-100 text-sm text-gray-500">
                        <span>Página {subscribers.current_page} de {subscribers.last_page}</span>
                        <div className="flex gap-2">
                            {subscribers.prev_page_url && (
                                <button onClick={() => router.visit(subscribers.prev_page_url)} className="px-3 py-1.5 rounded-lg border border-gray-200 hover:bg-gray-50">
                                    Anterior
                                </button>
                            )}
                            {subscribers.next_page_url && (
                                <button onClick={() => router.visit(subscribers.next_page_url)} className="px-3 py-1.5 rounded-lg border border-gray-200 hover:bg-gray-50">
                                    Siguiente
                                </button>
                            )}
                        </div>
                    </div>
                )}
            </div>
        </AdminLayout>
    );
}
