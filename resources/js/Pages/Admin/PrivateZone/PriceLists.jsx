import { useState } from "react";
import { router } from "@inertiajs/react";
import AdminLayout from "@/Layouts/AdminLayout";
import { Icon } from "@iconify/react";

function formatBytes(bytes) {
    if (!bytes) return "—";
    if (bytes >= 1048576) return (bytes / 1048576).toFixed(2) + " MB";
    if (bytes >= 1024) return (bytes / 1024).toFixed(1) + " KB";
    return bytes + " B";
}

export default function PriceLists({ priceLists }) {
    const [form, setForm] = useState({ name: "", file: null });
    const [uploading, setUploading] = useState(false);
    const [error, setError] = useState("");

    function handleSubmit(e) {
        e.preventDefault();
        if (!form.name || !form.file) {
            setError("Completá el nombre y seleccioná un PDF.");
            return;
        }
        setError("");
        setUploading(true);
        const fd = new FormData();
        fd.append("name", form.name);
        fd.append("file", form.file);
        router.post("/admin/price-lists", fd, {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                setForm({ name: "", file: null });
                setUploading(false);
                document.getElementById("plFileInput").value = "";
            },
            onError: () => setUploading(false),
        });
    }

    function handleDelete(id) {
        if (confirm("¿Eliminar esta lista de precios?")) {
            router.delete(`/admin/price-lists/${id}`, { preserveScroll: true });
        }
    }

    return (
        <AdminLayout title="Listas de Precios">
            <div className="p-6">
                <h1 className="text-2xl font-bold text-gray-800 mb-6">Listas de Precios</h1>

                {/* Upload form */}
                <div className="bg-white rounded-xl shadow p-6 mb-6">
                    <h2 className="text-base font-semibold text-gray-700 mb-4">Subir nueva lista</h2>
                    {error && (
                        <div className="bg-red-50 border border-red-200 text-red-600 text-sm rounded-lg px-4 py-3 mb-4">
                            {error}
                        </div>
                    )}
                    <form onSubmit={handleSubmit} className="flex flex-col sm:flex-row gap-4 items-end">
                        <div className="flex-1">
                            <label className="block text-xs font-semibold text-gray-600 mb-1">Nombre *</label>
                            <input
                                type="text"
                                value={form.name}
                                onChange={e => setForm(f => ({ ...f, name: e.target.value }))}
                                className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:border-[#136CBA] focus:ring-2 focus:ring-[#136CBA]/10 outline-none"
                                placeholder="Ej: Lista de precios Enero 2026"
                            />
                        </div>
                        <div className="flex-1">
                            <label className="block text-xs font-semibold text-gray-600 mb-1">Archivo PDF *</label>
                            <input
                                id="plFileInput"
                                type="file"
                                accept=".pdf"
                                onChange={e => setForm(f => ({ ...f, file: e.target.files[0] || null }))}
                                className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm"
                            />
                        </div>
                        <button
                            type="submit"
                            disabled={uploading}
                            className="px-6 py-2 bg-[#136CBA] text-white rounded-lg text-sm font-semibold hover:bg-[#0f5ea8] transition disabled:opacity-50 flex-shrink-0"
                        >
                            {uploading ? "Subiendo..." : "Subir"}
                        </button>
                    </form>
                </div>

                {/* Table */}
                <div className="bg-white rounded-xl shadow overflow-hidden">
                    <table className="w-full text-sm">
                        <thead>
                            <tr className="bg-[#0D2038] text-white text-left">
                                <th className="px-4 py-3">Nombre</th>
                                <th className="px-4 py-3">Tamaño</th>
                                <th className="px-4 py-3">Subido el</th>
                                <th className="px-4 py-3 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            {priceLists.length === 0 && (
                                <tr>
                                    <td colSpan={4} className="text-center py-10 text-gray-400">
                                        No hay listas de precios.
                                    </td>
                                </tr>
                            )}
                            {priceLists.map(pl => (
                                <tr key={pl.id} className="border-b border-gray-100 hover:bg-gray-50">
                                    <td className="px-4 py-3 font-semibold text-gray-800">
                                        <div className="flex items-center gap-2">
                                            <Icon icon="solar:file-text-outline" width={18} className="text-[#136CBA]" />
                                            {pl.name}
                                        </div>
                                    </td>
                                    <td className="px-4 py-3 text-gray-500">{formatBytes(pl.file_size)}</td>
                                    <td className="px-4 py-3 text-gray-500 text-xs">
                                        {new Date(pl.created_at).toLocaleDateString("es-AR")}
                                    </td>
                                    <td className="px-4 py-3 text-right">
                                        <button
                                            onClick={() => handleDelete(pl.id)}
                                            className="p-1.5 rounded-lg bg-red-50 text-red-500 hover:bg-red-100 transition"
                                            title="Eliminar"
                                        >
                                            <Icon icon="solar:trash-bin-minimalistic-outline" width={16} />
                                        </button>
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
