import { useState } from "react";
import { usePage, router } from "@inertiajs/react";
import AdminLayout from "@/Layouts/AdminLayout";
import { Icon } from "@iconify/react";

const STATUS_LABELS = {
    pending:  { label: "Pendiente",  cls: "bg-yellow-100 text-yellow-800" },
    approved: { label: "Aprobado",   cls: "bg-green-100 text-green-800" },
    rejected: { label: "Rechazado",  cls: "bg-red-100 text-red-800" },
};

export default function Users({ users, status }) {
    const tabs = ["all", "pending", "approved", "rejected"];
    const tabLabels = { all: "Todos", pending: "Pendientes", approved: "Aprobados", rejected: "Rechazados" };

    const [editingDiscount, setEditingDiscount] = useState(null);
    const [discountForm, setDiscountForm] = useState({ discount_pct: 0, discount_extra_pct: 0 });
    const [editingPassword, setEditingPassword] = useState(null);
    const [newPassword, setNewPassword] = useState("");

    function handleApprove(id) {
        router.post(`/admin/private-users/${id}/approve`, {}, { preserveScroll: true });
    }

    function handleReject(id) {
        if (confirm("¿Rechazar este usuario?")) {
            router.post(`/admin/private-users/${id}/reject`, {}, { preserveScroll: true });
        }
    }

    function handleDelete(id) {
        if (confirm("¿Eliminar este usuario?")) {
            router.delete(`/admin/private-users/${id}`, { preserveScroll: true });
        }
    }

    function openDiscount(user) {
        setEditingDiscount(user.id);
        setDiscountForm({ discount_pct: user.discount_pct, discount_extra_pct: user.discount_extra_pct });
    }

    function saveDiscount(id) {
        router.patch(`/admin/private-users/${id}/discount`, discountForm, {
            preserveScroll: true,
            onSuccess: () => setEditingDiscount(null),
        });
    }

    function openPassword(user) {
        setEditingPassword(user.id);
        setNewPassword("");
    }

    function savePassword(id) {
        if (!newPassword || newPassword.length < 6) return;
        router.post(`/admin/private-users/${id}/password`, { password: newPassword }, {
            preserveScroll: true,
            onSuccess: () => { setEditingPassword(null); setNewPassword(""); },
        });
    }

    return (
        <AdminLayout title="Clientes — Zona Privada">
            <div className="max-w-6xl">

                {/* Header */}
                <div className="mb-6">
                    <h1 className="text-2xl font-bold text-gray-800">Clientes — Zona Privada</h1>
                    <p className="text-sm text-gray-500 mt-1">Administrá los usuarios registrados, aprobá solicitudes y configurá descuentos.</p>
                </div>

                {/* Tabs */}
                <div className="flex gap-2 mb-6">
                    {tabs.map(t => (
                        <button
                            key={t}
                            onClick={() => router.get("/admin/private-users", { status: t })}
                            className={`px-4 py-2 rounded-xl text-sm font-semibold border transition
                                ${status === t
                                    ? "bg-[#25A7CA] text-white border-[#25A7CA]"
                                    : "bg-white text-gray-600 border-gray-200 hover:bg-gray-50"}`}
                        >
                            {tabLabels[t]}
                        </button>
                    ))}
                </div>

                {/* Table */}
                <div className="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                    <table className="w-full text-sm">
                        <thead>
                            <tr className="bg-[#0D2038] text-white text-left">
                                <th className="px-4 py-3">#</th>
                                <th className="px-4 py-3">Nombre</th>
                                <th className="px-4 py-3">Email</th>
                                <th className="px-4 py-3">Estado</th>
                                <th className="px-4 py-3">Descuento</th>
                                <th className="px-4 py-3">Contraseña</th>
                                <th className="px-4 py-3">Fecha</th>
                                <th className="px-4 py-3 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            {users.data.length === 0 && (
                                <tr>
                                    <td colSpan={8} className="text-center py-10 text-gray-400">
                                        No hay usuarios.
                                    </td>
                                </tr>
                            )}
                            {users.data.map((u, i) => (
                                <tr key={u.id} className="border-b border-gray-100 hover:bg-gray-50">
                                    <td className="px-4 py-3 text-gray-400">{users.from + i}</td>
                                    <td className="px-4 py-3 font-semibold text-gray-800">
                                        {u.name} {u.lastname}
                                    </td>
                                    <td className="px-4 py-3 text-gray-600">{u.email}</td>
                                    <td className="px-4 py-3">
                                        <span className={`px-2 py-1 rounded-full text-xs font-semibold ${STATUS_LABELS[u.status]?.cls}`}>
                                            {STATUS_LABELS[u.status]?.label}
                                        </span>
                                    </td>
                                    <td className="px-4 py-3">
                                        {editingDiscount === u.id ? (
                                            <div className="flex items-center gap-2">
                                                <input
                                                    type="number"
                                                    min={0} max={100}
                                                    value={discountForm.discount_pct}
                                                    onChange={e => setDiscountForm(f => ({ ...f, discount_pct: +e.target.value }))}
                                                    className="w-16 border rounded px-2 py-1 text-sm"
                                                    placeholder="%"
                                                />
                                                <span className="text-gray-400">+</span>
                                                <input
                                                    type="number"
                                                    min={0} max={100}
                                                    value={discountForm.discount_extra_pct}
                                                    onChange={e => setDiscountForm(f => ({ ...f, discount_extra_pct: +e.target.value }))}
                                                    className="w-16 border rounded px-2 py-1 text-sm"
                                                    placeholder="%"
                                                />
                                                <button onClick={() => saveDiscount(u.id)} className="text-green-600 hover:text-green-800">
                                                    <Icon icon="solar:check-circle-outline" width={18} />
                                                </button>
                                                <button onClick={() => setEditingDiscount(null)} className="text-gray-400 hover:text-gray-600">
                                                    <Icon icon="solar:close-circle-outline" width={18} />
                                                </button>
                                            </div>
                                        ) : (
                                            <button onClick={() => openDiscount(u)} className="text-sm text-[#25A7CA] font-semibold hover:underline">
                                                {u.discount_pct}% + {u.discount_extra_pct}%
                                            </button>
                                        )}
                                    </td>
                                    <td className="px-4 py-3">
                                        {editingPassword === u.id ? (
                                            <div className="flex items-center gap-1">
                                                <input
                                                    type="text"
                                                    value={newPassword}
                                                    onChange={e => setNewPassword(e.target.value)}
                                                    placeholder="Mín. 6 chars"
                                                    className="w-28 border rounded-lg px-2 py-1 text-sm outline-none focus:ring-2 focus:ring-[#25A7CA]/30"
                                                    autoFocus
                                                />
                                                <button onClick={() => savePassword(u.id)} disabled={newPassword.length < 6}
                                                    className="text-green-600 hover:text-green-800 disabled:opacity-30">
                                                    <Icon icon="solar:check-circle-outline" width={18} />
                                                </button>
                                                <button onClick={() => setEditingPassword(null)} className="text-gray-400 hover:text-gray-600">
                                                    <Icon icon="solar:close-circle-outline" width={18} />
                                                </button>
                                            </div>
                                        ) : (
                                            <button onClick={() => openPassword(u)}
                                                className="flex items-center gap-1 text-xs text-gray-400 hover:text-[#25A7CA] transition"
                                                title="Asignar nueva contraseña">
                                                <Icon icon="solar:key-outline" width={14} />
                                                Cambiar
                                            </button>
                                        )}
                                    </td>
                                    <td className="px-4 py-3 text-gray-500 text-xs">
                                        {new Date(u.created_at).toLocaleDateString("es-AR")}
                                    </td>
                                    <td className="px-4 py-3">
                                        <div className="flex items-center gap-2 justify-end">
                                            {u.status !== "approved" && (
                                                <button
                                                    onClick={() => handleApprove(u.id)}
                                                    title="Aprobar"
                                                    className="p-1.5 rounded-lg bg-green-50 text-green-600 hover:bg-green-100 transition"
                                                >
                                                    <Icon icon="solar:check-square-outline" width={16} />
                                                </button>
                                            )}
                                            {u.status !== "rejected" && (
                                                <button
                                                    onClick={() => handleReject(u.id)}
                                                    title="Rechazar"
                                                    className="p-1.5 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition"
                                                >
                                                    <Icon icon="solar:close-square-outline" width={16} />
                                                </button>
                                            )}
                                            <button
                                                onClick={() => handleDelete(u.id)}
                                                title="Eliminar"
                                                className="p-1.5 rounded-lg bg-gray-100 text-gray-500 hover:bg-red-50 hover:text-red-500 transition"
                                            >
                                                <Icon icon="solar:trash-bin-minimalistic-outline" width={16} />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>

                {/* Pagination */}
                {users.last_page > 1 && (
                    <div className="mt-4 flex justify-center gap-2">
                        {Array.from({ length: users.last_page }, (_, k) => k + 1).map(page => (
                            <button
                                key={page}
                                onClick={() => router.get("/admin/private-users", { status, page })}
                                className={`px-3 py-1 rounded text-sm border transition
                                    ${users.current_page === page
                                        ? "bg-[#25A7CA] text-white border-[#25A7CA]"
                                        : "bg-white text-gray-600 border-gray-200 hover:bg-gray-50"}`}
                            >
                                {page}
                            </button>
                        ))}
                    </div>
                )}
            </div>
        </AdminLayout>
    );
}
