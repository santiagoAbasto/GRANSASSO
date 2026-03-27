import AdminLayout from "@/Layouts/AdminLayout";
import { router } from "@inertiajs/react";
import { Icon } from "@iconify/react";
import { useState } from "react";

/* Switch "En Home" */
function HomeSwitch({ postId, value }) {
    const [active, setActive] = useState(value);
    const [loading, setLoading] = useState(false);

    const toggle = () => {
        setLoading(true);
        const next = !active;
        setActive(next);
        router.patch(
            `/admin/posts/${postId}/toggle-home`,
            {},
            {
                preserveState: true,
                preserveScroll: true,
                onFinish: () => setLoading(false),
                onError: () => setActive(!next),
            }
        );
    };

    return (
        <button
            onClick={toggle}
            disabled={loading}
            title={active ? "Quitar del home" : "Mostrar en home"}
            className={`relative inline-flex h-5 w-9 flex-shrink-0 items-center rounded-full transition-colors duration-200 focus:outline-none
                ${active ? "bg-[#25A7CA]" : "bg-gray-200"}
                ${loading ? "opacity-60 cursor-wait" : "cursor-pointer"}`}
        >
            <span className={`inline-block h-3.5 w-3.5 transform rounded-full bg-white shadow transition-transform duration-200
                ${active ? "translate-x-[18px]" : "translate-x-[2px]"}`}
            />
        </button>
    );
}

/* Campo orden inline — click para editar, Enter/blur para guardar */
function OrderBadge({ postId, value }) {
    const [editing, setEditing] = useState(false);
    const [val, setVal] = useState(value || "A");
    const [saved, setSaved] = useState(value || "A");

    const save = () => {
        const clean = val.trim().toUpperCase() || "A";
        setVal(clean);
        setEditing(false);
        if (clean === saved) return;
        setSaved(clean);
        router.patch(
            `/admin/posts/${postId}/update-order`,
            { order: clean },
            { preserveState: true, preserveScroll: true }
        );
    };

    if (editing) {
        return (
            <input
                autoFocus
                value={val}
                onChange={(e) => setVal(e.target.value.toUpperCase())}
                onBlur={save}
                onKeyDown={(e) => { if (e.key === "Enter") save(); if (e.key === "Escape") { setVal(saved); setEditing(false); } }}
                maxLength={10}
                className="w-14 text-center border border-[#25A7CA] rounded-md px-1 py-0.5 text-xs font-mono focus:outline-none"
            />
        );
    }

    return (
        <button
            onClick={() => setEditing(true)}
            title="Click para editar orden"
            className="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-gray-100 hover:bg-[#25A7CA]/10 text-gray-600 hover:text-[#25A7CA] text-xs font-mono transition group"
        >
            {saved}
            <Icon icon="solar:pen-outline" width={10} className="opacity-0 group-hover:opacity-100 transition" />
        </button>
    );
}

export default function PostsIndex({ posts, filters }) {
    const [search, setSearch] = useState(filters?.search || "");

    const applyFilters = () => {
        router.get("/admin/posts", { search }, { preserveState: true });
    };

    const destroy = (id) => {
        if (!confirm("¿Eliminar esta novedad?")) return;
        router.delete(`/admin/posts/${id}`);
    };

    return (
        <AdminLayout>
            <div className="flex items-center justify-between mb-6">
                <div>
                    <h1 className="text-2xl font-bold text-gray-800">Novedades</h1>
                    <p className="text-sm text-gray-500">{posts.total} publicaciones</p>
                </div>
                <button
                    onClick={() => router.visit("/admin/posts/create")}
                    className="flex items-center gap-2 bg-[#25A7CA] text-white px-4 py-2 rounded-xl text-sm font-medium hover:bg-[#1d96b8] transition"
                >
                    <Icon icon="solar:add-circle-outline" width={18} />
                    Nueva Novedad
                </button>
            </div>

            {/* BUSCADOR */}
            <div className="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 mb-5 flex gap-3">
                <input
                    type="text"
                    value={search}
                    onChange={(e) => setSearch(e.target.value)}
                    onKeyDown={(e) => e.key === "Enter" && applyFilters()}
                    placeholder="Buscar..."
                    className="flex-1 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30"
                />
                <button onClick={applyFilters} className="px-4 py-2 bg-[#25A7CA] text-white rounded-lg text-sm hover:bg-[#1d96b8] transition">
                    Buscar
                </button>
            </div>

            {/* TABLA */}
            <div className="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <table className="w-full text-sm">
                    <thead>
                        <tr className="bg-gray-50 text-xs text-gray-500 uppercase tracking-wide">
                            <th className="px-4 py-3 text-center w-16">
                                <div className="flex items-center justify-center gap-1" title="Orden alfanumérico — A, B, C, AA, AB...">
                                    <Icon icon="solar:sort-by-alphabet-outline" width={13} />
                                    Orden
                                </div>
                            </th>
                            <th className="text-left px-5 py-3">Novedad</th>
                            <th className="text-left px-5 py-3">Categoría</th>
                            <th className="px-5 py-3">Publicado</th>
                            <th className="px-5 py-3">Estado</th>
                            <th className="px-5 py-3">
                                <div className="flex items-center justify-center gap-1">
                                    <Icon icon="solar:home-smile-outline" width={13} />
                                    En Home
                                </div>
                            </th>
                            <th className="px-5 py-3">Acciones</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-50">
                        {posts.data.length === 0 && (
                            <tr><td colSpan={7} className="text-center py-10 text-gray-400">Sin novedades.</td></tr>
                        )}
                        {posts.data.map((p) => (
                            <tr key={p.id} className="hover:bg-gray-50/50">

                                {/* Orden */}
                                <td className="px-4 py-3 text-center">
                                    <OrderBadge postId={p.id} value={p.order} />
                                </td>

                                {/* Novedad */}
                                <td className="px-5 py-3">
                                    <div className="flex items-center gap-3">
                                        {p.image_url ? (
                                            <img src={p.image_url} className="w-9 h-9 rounded-lg object-cover flex-shrink-0" alt="" />
                                        ) : (
                                            <div className="w-9 h-9 rounded-lg bg-gray-100 flex items-center justify-center flex-shrink-0">
                                                <Icon icon="solar:document-text-outline" width={16} className="text-gray-400" />
                                            </div>
                                        )}
                                        <div className="min-w-0">
                                            <p className="font-medium text-gray-800 truncate max-w-[260px]">{p.title}</p>
                                            <p className="text-xs text-gray-400">{p.slug}</p>
                                        </div>
                                    </div>
                                </td>

                                {/* Categoría */}
                                <td className="px-5 py-3 text-gray-500">{p.category || "—"}</td>

                                {/* Publicado */}
                                <td className="px-5 py-3 text-center text-gray-500">{p.published_at || "—"}</td>

                                {/* Estado */}
                                <td className="px-5 py-3 text-center">
                                    <span className={`text-xs px-2 py-0.5 rounded-full font-medium ${
                                        p.is_active ? "bg-green-100 text-green-700" : "bg-gray-100 text-gray-500"
                                    }`}>
                                        {p.is_active ? "Activo" : "Inactivo"}
                                    </span>
                                </td>

                                {/* En Home */}
                                <td className="px-5 py-3 text-center">
                                    <div className="flex justify-center">
                                        <HomeSwitch postId={p.id} value={p.is_featured} />
                                    </div>
                                </td>

                                {/* Acciones */}
                                <td className="px-5 py-3">
                                    <div className="flex items-center justify-center gap-2">
                                        <button onClick={() => router.visit(`/admin/posts/${p.id}/edit`)} className="p-1.5 rounded-lg hover:bg-gray-100 text-gray-600">
                                            <Icon icon="solar:pen-outline" width={16} />
                                        </button>
                                        <button onClick={() => destroy(p.id)} className="p-1.5 rounded-lg hover:bg-red-50 text-red-500">
                                            <Icon icon="solar:trash-bin-trash-outline" width={16} />
                                        </button>
                                    </div>
                                </td>

                            </tr>
                        ))}
                    </tbody>
                </table>

                {posts.last_page > 1 && (
                    <div className="flex items-center justify-between px-5 py-3 border-t border-gray-100 text-sm text-gray-500">
                        <span>Página {posts.current_page} de {posts.last_page}</span>
                        <div className="flex gap-2">
                            {posts.prev_page_url && <button onClick={() => router.visit(posts.prev_page_url)} className="px-3 py-1.5 rounded-lg border border-gray-200 hover:bg-gray-50">Anterior</button>}
                            {posts.next_page_url && <button onClick={() => router.visit(posts.next_page_url)} className="px-3 py-1.5 rounded-lg border border-gray-200 hover:bg-gray-50">Siguiente</button>}
                        </div>
                    </div>
                )}
            </div>

            {/* LEYENDA */}
            <p className="text-xs text-gray-400 mt-3 flex items-center gap-1">
                <Icon icon="solar:info-circle-outline" width={13} />
                El orden es alfanumérico: A, B, C… Z, AA, AB… — Click en el badge para editar.
            </p>
        </AdminLayout>
    );
}
