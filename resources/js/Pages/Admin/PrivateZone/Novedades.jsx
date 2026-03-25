import { useState } from "react";
import { router } from "@inertiajs/react";
import AdminLayout from "@/Layouts/AdminLayout";
import { Icon } from "@iconify/react";

const emptyForm = { title: "", category: "", excerpt: "", body: "", is_active: true, image: null };

export default function Novedades({ novedades }) {
    const [showForm, setShowForm] = useState(false);
    const [editing, setEditing] = useState(null);
    const [form, setForm] = useState(emptyForm);
    const [saving, setSaving] = useState(false);

    function openNew() {
        setEditing(null);
        setForm(emptyForm);
        setShowForm(true);
    }

    function openEdit(n) {
        setEditing(n.id);
        setForm({ title: n.title, category: n.category || "", excerpt: n.excerpt || "", body: n.body || "", is_active: !!n.is_active, image: null });
        setShowForm(true);
    }

    function handleSubmit(e) {
        e.preventDefault();
        setSaving(true);
        const fd = new FormData();
        Object.keys(form).forEach(k => {
            if (k === "image") { if (form.image) fd.append("image", form.image); }
            else if (k === "is_active") fd.append("is_active", form.is_active ? "1" : "0");
            else fd.append(k, form[k] ?? "");
        });
        const url = editing ? `/admin/private-novedades/${editing}` : "/admin/private-novedades";
        if (editing) fd.append("_method", "PUT");
        router.post(url, fd, {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => { setShowForm(false); setSaving(false); },
            onError: () => setSaving(false),
        });
    }

    function handleDelete(id) {
        if (confirm("¿Eliminar esta novedad?")) {
            router.delete(`/admin/private-novedades/${id}`, { preserveScroll: true });
        }
    }

    return (
        <AdminLayout title="Novedades — Zona Privada">
            <div className="p-6">
                <div className="flex items-center justify-between mb-6">
                    <h1 className="text-2xl font-bold text-gray-800">Novedades</h1>
                    <button
                        onClick={openNew}
                        className="flex items-center gap-2 px-4 py-2 bg-[#136CBA] text-white rounded-lg text-sm font-semibold hover:bg-[#0f5ea8] transition"
                    >
                        <Icon icon="solar:add-circle-outline" width={18} />
                        Nueva novedad
                    </button>
                </div>

                {/* Form modal */}
                {showForm && (
                    <div className="fixed inset-0 bg-black/40 z-50 flex items-start justify-center overflow-y-auto py-8">
                        <div className="bg-white rounded-xl shadow-xl w-full max-w-2xl mx-4">
                            <div className="flex items-center justify-between px-6 py-4 border-b">
                                <h2 className="font-semibold text-gray-800">
                                    {editing ? "Editar novedad" : "Nueva novedad"}
                                </h2>
                                <button onClick={() => setShowForm(false)} className="text-gray-400 hover:text-gray-600">
                                    <Icon icon="solar:close-circle-outline" width={22} />
                                </button>
                            </div>
                            <form onSubmit={handleSubmit} className="p-6 space-y-4">
                                <div>
                                    <label className="block text-xs font-semibold text-gray-600 mb-1">Título *</label>
                                    <input type="text" required value={form.title}
                                        onChange={e => setForm(f => ({ ...f, title: e.target.value }))}
                                        className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#136CBA]" />
                                </div>
                                <div>
                                    <label className="block text-xs font-semibold text-gray-600 mb-1">Categoría</label>
                                    <input type="text" value={form.category}
                                        onChange={e => setForm(f => ({ ...f, category: e.target.value }))}
                                        className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#136CBA]" />
                                </div>
                                <div>
                                    <label className="block text-xs font-semibold text-gray-600 mb-1">Extracto</label>
                                    <textarea rows={3} value={form.excerpt}
                                        onChange={e => setForm(f => ({ ...f, excerpt: e.target.value }))}
                                        className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#136CBA] resize-none" />
                                </div>
                                <div>
                                    <label className="block text-xs font-semibold text-gray-600 mb-1">Cuerpo</label>
                                    <textarea rows={6} value={form.body}
                                        onChange={e => setForm(f => ({ ...f, body: e.target.value }))}
                                        className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#136CBA] resize-y" />
                                </div>
                                <div>
                                    <label className="block text-xs font-semibold text-gray-600 mb-1">Imagen</label>
                                    <input type="file" accept="image/*"
                                        onChange={e => setForm(f => ({ ...f, image: e.target.files[0] || null }))}
                                        className="text-sm" />
                                </div>
                                <div className="flex items-center gap-2">
                                    <input type="checkbox" id="isActiveCheck" checked={form.is_active}
                                        onChange={e => setForm(f => ({ ...f, is_active: e.target.checked }))}
                                        className="w-4 h-4 accent-[#136CBA]" />
                                    <label htmlFor="isActiveCheck" className="text-sm text-gray-700">Activa</label>
                                </div>
                                <div className="flex justify-end gap-3 pt-2">
                                    <button type="button" onClick={() => setShowForm(false)}
                                        className="px-4 py-2 border border-gray-200 rounded-lg text-sm text-gray-600 hover:bg-gray-50 transition">
                                        Cancelar
                                    </button>
                                    <button type="submit" disabled={saving}
                                        className="px-6 py-2 bg-[#136CBA] text-white rounded-lg text-sm font-semibold hover:bg-[#0f5ea8] transition disabled:opacity-50">
                                        {saving ? "Guardando..." : "Guardar"}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                )}

                {/* Table */}
                <div className="bg-white rounded-xl shadow overflow-hidden">
                    <table className="w-full text-sm">
                        <thead>
                            <tr className="bg-[#0D2038] text-white text-left">
                                <th className="px-4 py-3">Título</th>
                                <th className="px-4 py-3">Categoría</th>
                                <th className="px-4 py-3">Estado</th>
                                <th className="px-4 py-3">Fecha</th>
                                <th className="px-4 py-3 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            {novedades.length === 0 && (
                                <tr><td colSpan={5} className="text-center py-10 text-gray-400">No hay novedades.</td></tr>
                            )}
                            {novedades.map(n => (
                                <tr key={n.id} className="border-b border-gray-100 hover:bg-gray-50">
                                    <td className="px-4 py-3 font-semibold text-gray-800">{n.title}</td>
                                    <td className="px-4 py-3 text-gray-500">{n.category || "—"}</td>
                                    <td className="px-4 py-3">
                                        <span className={`px-2 py-1 rounded-full text-xs font-semibold ${n.is_active ? "bg-green-100 text-green-700" : "bg-gray-100 text-gray-500"}`}>
                                            {n.is_active ? "Activa" : "Inactiva"}
                                        </span>
                                    </td>
                                    <td className="px-4 py-3 text-gray-400 text-xs">
                                        {new Date(n.created_at).toLocaleDateString("es-AR")}
                                    </td>
                                    <td className="px-4 py-3">
                                        <div className="flex items-center gap-2 justify-end">
                                            <button onClick={() => openEdit(n)} className="p-1.5 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition" title="Editar">
                                                <Icon icon="solar:pen-outline" width={15} />
                                            </button>
                                            <button onClick={() => handleDelete(n.id)} className="p-1.5 rounded-lg bg-red-50 text-red-500 hover:bg-red-100 transition" title="Eliminar">
                                                <Icon icon="solar:trash-bin-minimalistic-outline" width={15} />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>
        </AdminLayout>
    );
}
