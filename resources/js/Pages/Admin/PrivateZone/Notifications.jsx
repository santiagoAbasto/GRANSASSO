import { useState } from "react";
import { router } from "@inertiajs/react";
import AdminLayout from "@/Layouts/AdminLayout";
import { Icon } from "@iconify/react";

export default function Notifications({ notification, all }) {
    const [form, setForm] = useState({
        id: notification?.id ?? "",
        title: notification?.title ?? "",
        body: notification?.body ?? "",
        is_active: notification?.is_active ?? false,
    });
    const [saving, setSaving] = useState(false);

    function handleSubmit(e) {
        e.preventDefault();
        setSaving(true);
        router.post("/admin/private-notifications", form, {
            preserveScroll: true,
            onFinish: () => setSaving(false),
        });
    }

    function loadNotification(n) {
        setForm({ id: n.id, title: n.title, body: n.body, is_active: n.is_active });
    }

    return (
        <AdminLayout title="Notificaciones — Zona Privada">
            <div className="p-6 max-w-3xl">
                <h1 className="text-2xl font-bold text-gray-800 mb-6">Notificación de popup</h1>

                <div className="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-6 text-sm text-blue-800">
                    <Icon icon="solar:info-circle-outline" width={16} className="inline mr-1" />
                    Solo puede haber <strong>una notificación activa</strong> a la vez. Al activar una nueva, las demás se desactivan.
                </div>

                <div className="bg-white rounded-xl shadow p-6 mb-6">
                    <h2 className="text-base font-semibold text-gray-700 mb-4">
                        {form.id ? "Editando notificación" : "Nueva notificación"}
                    </h2>
                    <form onSubmit={handleSubmit} className="space-y-4">
                        <div>
                            <label className="block text-xs font-semibold text-gray-600 mb-1">Título *</label>
                            <input type="text" required value={form.title}
                                onChange={e => setForm(f => ({ ...f, title: e.target.value }))}
                                className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#136CBA] focus:ring-2 focus:ring-[#136CBA]/10" />
                        </div>
                        <div>
                            <label className="block text-xs font-semibold text-gray-600 mb-1">Cuerpo *</label>
                            <textarea required rows={5} value={form.body}
                                onChange={e => setForm(f => ({ ...f, body: e.target.value }))}
                                className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#136CBA] resize-none" />
                        </div>
                        <div className="flex items-center gap-2">
                            <input type="checkbox" id="notifActive" checked={form.is_active}
                                onChange={e => setForm(f => ({ ...f, is_active: e.target.checked }))}
                                className="w-4 h-4 accent-[#136CBA]" />
                            <label htmlFor="notifActive" className="text-sm text-gray-700">Activa (mostrar en zona privada)</label>
                        </div>
                        <div className="flex justify-end">
                            <button type="submit" disabled={saving}
                                className="px-6 py-2 bg-[#136CBA] text-white rounded-lg text-sm font-semibold hover:bg-[#0f5ea8] transition disabled:opacity-50">
                                {saving ? "Guardando..." : "Guardar"}
                            </button>
                        </div>
                    </form>
                </div>

                {/* History */}
                {all.length > 0 && (
                    <div className="bg-white rounded-xl shadow overflow-hidden">
                        <div className="px-5 py-3 border-b">
                            <h3 className="text-sm font-semibold text-gray-700">Historial</h3>
                        </div>
                        <table className="w-full text-sm">
                            <thead>
                                <tr className="bg-gray-50 text-left">
                                    <th className="px-4 py-3 text-xs font-semibold text-gray-500">Título</th>
                                    <th className="px-4 py-3 text-xs font-semibold text-gray-500">Estado</th>
                                    <th className="px-4 py-3 text-xs font-semibold text-gray-500">Fecha</th>
                                    <th className="px-4 py-3 text-right text-xs font-semibold text-gray-500">Editar</th>
                                </tr>
                            </thead>
                            <tbody>
                                {all.map(n => (
                                    <tr key={n.id} className="border-b border-gray-100 hover:bg-gray-50">
                                        <td className="px-4 py-3 font-medium text-gray-700">{n.title}</td>
                                        <td className="px-4 py-3">
                                            <span className={`px-2 py-1 rounded-full text-xs font-semibold ${n.is_active ? "bg-green-100 text-green-700" : "bg-gray-100 text-gray-500"}`}>
                                                {n.is_active ? "Activa" : "Inactiva"}
                                            </span>
                                        </td>
                                        <td className="px-4 py-3 text-gray-400 text-xs">
                                            {new Date(n.created_at).toLocaleDateString("es-AR")}
                                        </td>
                                        <td className="px-4 py-3 text-right">
                                            <button onClick={() => loadNotification(n)} className="text-[#136CBA] hover:underline text-xs font-semibold">
                                                Editar
                                            </button>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                )}
            </div>
        </AdminLayout>
    );
}
