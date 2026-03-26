import { useState } from "react";
import { router } from "@inertiajs/react";
import AdminLayout from "@/Layouts/AdminLayout";
import { Icon } from "@iconify/react";

export default function Notifications({ notification, all, users }) {
    const [form, setForm] = useState({
        id: notification?.id ?? "",
        title: notification?.title ?? "",
        body: notification?.body ?? "",
        is_active: notification?.is_active ?? false,
        user_ids: notification?.users?.map(u => u.id) ?? [],
    });
    const [saving, setSaving] = useState(false);
    const [search, setSearch] = useState("");

    const allSelected = form.user_ids.length === users.length;

    function toggleUser(id) {
        setForm(f => ({
            ...f,
            user_ids: f.user_ids.includes(id)
                ? f.user_ids.filter(uid => uid !== id)
                : [...f.user_ids, id],
        }));
    }

    function toggleAll() {
        setForm(f => ({
            ...f,
            user_ids: allSelected ? [] : users.map(u => u.id),
        }));
    }

    const filteredUsers = users.filter(u => {
        const q = search.toLowerCase();
        return `${u.name} ${u.lastname} ${u.email}`.toLowerCase().includes(q);
    });

    function handleSubmit(e) {
        e.preventDefault();
        setSaving(true);
        router.post("/admin/private-notifications", form, {
            preserveScroll: true,
            onFinish: () => setSaving(false),
        });
    }

    function loadNotification(n) {
        setForm({
            id: n.id,
            title: n.title,
            body: n.body,
            is_active: n.is_active,
            user_ids: n.users?.map(u => u.id) ?? [],
        });
    }

    return (
        <AdminLayout title="Notificaciones — Zona Privada">
            <div className="max-w-4xl">

                {/* Header */}
                <div className="mb-6">
                    <h1 className="text-2xl font-bold text-gray-800">Notificación de popup</h1>
                    <p className="text-sm text-gray-500 mt-1">Gestioná las notificaciones que aparecen en la zona privada de los clientes.</p>
                </div>

                <div className="bg-blue-50 border border-blue-200 rounded-2xl p-4 mb-6 text-sm text-blue-800 flex items-start gap-2">
                    <Icon icon="solar:info-circle-outline" width={16} className="mt-0.5 flex-shrink-0" />
                    Solo puede haber <strong>una notificación activa</strong> a la vez. Seleccioná a qué usuarios les aparecerá.
                </div>

                <div className="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 mb-6 space-y-4">
                    <h2 className="font-semibold text-gray-700 flex items-center gap-2">
                        <Icon icon="solar:bell-outline" width={18} className="text-[#25A7CA]" />
                        {form.id ? "Editando notificación" : "Nueva notificación"}
                    </h2>
                    <form onSubmit={handleSubmit} className="space-y-4">
                        <div>
                            <label className="block text-xs font-medium text-gray-500 mb-1">Título *</label>
                            <input type="text" required value={form.title}
                                onChange={e => setForm(f => ({ ...f, title: e.target.value }))}
                                className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-[#25A7CA]/30" />
                        </div>
                        <div>
                            <label className="block text-xs font-medium text-gray-500 mb-1">Cuerpo *</label>
                            <textarea required rows={5} value={form.body}
                                onChange={e => setForm(f => ({ ...f, body: e.target.value }))}
                                className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-[#25A7CA]/30 resize-none" />
                        </div>

                        {/* User selector */}
                        <div>
                            <label className="block text-xs font-medium text-gray-500 mb-2">
                                Usuarios destinatarios
                                <span className="ml-2 font-normal text-gray-400">
                                    ({form.user_ids.length} de {users.length} seleccionados)
                                </span>
                            </label>
                            <div className="border border-gray-200 rounded-lg overflow-hidden">
                                <div className="flex items-center gap-2 px-3 py-2 bg-gray-50 border-b border-gray-200">
                                    <Icon icon="solar:magnifer-outline" width={16} className="text-gray-400" />
                                    <input type="text" placeholder="Buscar usuario..."
                                        value={search} onChange={e => setSearch(e.target.value)}
                                        className="flex-1 bg-transparent text-sm outline-none" />
                                    <button type="button" onClick={toggleAll}
                                        className="text-xs font-semibold text-[#25A7CA] hover:underline whitespace-nowrap">
                                        {allSelected ? "Deseleccionar todos" : "Seleccionar todos"}
                                    </button>
                                </div>
                                <div className="max-h-48 overflow-y-auto divide-y divide-gray-100">
                                    {filteredUsers.map(u => (
                                        <label key={u.id}
                                            className="flex items-center gap-3 px-3 py-2 hover:bg-gray-50 cursor-pointer transition">
                                            <input type="checkbox"
                                                checked={form.user_ids.includes(u.id)}
                                                onChange={() => toggleUser(u.id)}
                                                className="w-4 h-4 accent-[#25A7CA] flex-shrink-0" />
                                            <div className="min-w-0">
                                                <span className="text-sm font-medium text-gray-800 block truncate">
                                                    {u.name} {u.lastname}
                                                </span>
                                                <span className="text-xs text-gray-400 block truncate">{u.email}</span>
                                            </div>
                                        </label>
                                    ))}
                                    {filteredUsers.length === 0 && (
                                        <p className="text-center py-4 text-sm text-gray-400">Sin resultados</p>
                                    )}
                                </div>
                            </div>
                        </div>

                        <div className="flex items-center gap-2">
                            <input type="checkbox" id="notifActive" checked={form.is_active}
                                onChange={e => setForm(f => ({ ...f, is_active: e.target.checked }))}
                                className="w-4 h-4 accent-[#25A7CA]" />
                            <label htmlFor="notifActive" className="text-sm text-gray-700">Activa (mostrar en zona privada)</label>
                        </div>
                        <div className="flex justify-end">
                            <button type="submit" disabled={saving}
                                className="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-[#25A7CA] text-white text-sm font-medium hover:bg-[#1d96b8] disabled:opacity-60 transition">
                                <Icon icon={saving ? "solar:refresh-outline" : "solar:diskette-outline"} width={15} className={saving ? "animate-spin" : ""} />
                                {saving ? "Guardando..." : "Guardar"}
                            </button>
                        </div>
                    </form>
                </div>

                {/* History */}
                {all.length > 0 && (
                    <div className="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                        <div className="px-5 py-4 border-b border-gray-100 flex items-center gap-2">
                            <Icon icon="solar:history-outline" width={18} className="text-[#25A7CA]" />
                            <h3 className="font-semibold text-gray-700">Historial</h3>
                        </div>
                        <table className="w-full text-sm">
                            <thead>
                                <tr className="bg-gray-50 text-left">
                                    <th className="px-4 py-3 text-xs font-semibold text-gray-500">Título</th>
                                    <th className="px-4 py-3 text-xs font-semibold text-gray-500">Usuarios</th>
                                    <th className="px-4 py-3 text-xs font-semibold text-gray-500">Estado</th>
                                    <th className="px-4 py-3 text-xs font-semibold text-gray-500">Fecha</th>
                                    <th className="px-4 py-3 text-right text-xs font-semibold text-gray-500">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                {all.map(n => (
                                    <tr key={n.id} className="border-b border-gray-100 hover:bg-gray-50">
                                        <td className="px-4 py-3 font-medium text-gray-700">{n.title}</td>
                                        <td className="px-4 py-3 text-gray-500 text-xs">
                                            {n.users?.length === users.length
                                                ? "Todos"
                                                : n.users?.length === 0
                                                    ? "Ninguno"
                                                    : `${n.users?.length} usuarios`}
                                        </td>
                                        <td className="px-4 py-3">
                                            <span className={`px-2 py-1 rounded-full text-xs font-semibold ${n.is_active ? "bg-green-100 text-green-700" : "bg-gray-100 text-gray-500"}`}>
                                                {n.is_active ? "Activa" : "Inactiva"}
                                            </span>
                                        </td>
                                        <td className="px-4 py-3 text-gray-400 text-xs">
                                            {new Date(n.created_at).toLocaleDateString("es-AR")}
                                        </td>
                                        <td className="px-4 py-3 text-right">
                                            <button onClick={() => loadNotification(n)} className="text-[#25A7CA] hover:underline text-xs font-semibold mr-3">
                                                Editar
                                            </button>
                                            <button onClick={() => { if (confirm("¿Eliminar esta notificación?")) router.delete(`/admin/private-notifications/${n.id}`, { preserveScroll: true }); }} className="text-red-500 hover:underline text-xs font-semibold">
                                                Eliminar
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
