import AdminLayout from "@/Layouts/AdminLayout";
import { router } from "@inertiajs/react";
import { Icon } from "@iconify/react";
import { useState } from "react";

export default function ProductsIndex({ products, categories, filters }) {
    const [search, setSearch]   = useState(filters?.search || "");
    const [category, setCategory] = useState(filters?.category || "");

    const applyFilters = () => {
        router.get("/admin/products", { search, category }, { preserveState: true });
    };

    const destroy = (id) => {
        if (!confirm("¿Eliminar este producto?")) return;
        router.delete(`/admin/products/${id}`);
    };

    return (
        <AdminLayout>
            <div className="flex items-center justify-between mb-6">
                <div>
                    <h1 className="text-2xl font-bold text-gray-800">Productos</h1>
                    <p className="text-sm text-gray-500">{products.total} productos en total</p>
                </div>
                <button
                    onClick={() => router.visit("/admin/products/create")}
                    className="flex items-center gap-2 bg-[#25A7CA] text-white px-4 py-2 rounded-xl text-sm font-medium hover:bg-[#1d96b8] transition"
                >
                    <Icon icon="solar:add-circle-outline" width={18} />
                    Nuevo Producto
                </button>
            </div>

            {/* Filters */}
            <div className="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 mb-5 flex flex-wrap gap-3">
                <input
                    type="text"
                    value={search}
                    onChange={(e) => setSearch(e.target.value)}
                    onKeyDown={(e) => e.key === "Enter" && applyFilters()}
                    placeholder="Buscar producto..."
                    className="flex-1 min-w-48 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30"
                />
                <select
                    value={category}
                    onChange={(e) => setCategory(e.target.value)}
                    className="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30"
                >
                    <option value="">Todas las categorías</option>
                    {categories.map((c) => (
                        <option key={c.id} value={c.id}>{c.name}</option>
                    ))}
                </select>
                <button
                    onClick={applyFilters}
                    className="px-4 py-2 bg-[#25A7CA] text-white rounded-lg text-sm hover:bg-[#1d96b8] transition"
                >
                    Filtrar
                </button>
            </div>

            {/* Table */}
            <div className="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <table className="w-full text-sm">
                    <thead>
                        <tr className="bg-gray-50 text-xs text-gray-500 uppercase tracking-wide">
                            <th className="text-left px-5 py-3">Producto</th>
                            <th className="text-left px-5 py-3">Categoría</th>
                            <th className="text-left px-5 py-3">Marca</th>
                            <th className="px-5 py-3 text-center">Estado</th>
                            <th className="px-5 py-3 text-center">Home</th>
                            <th className="px-5 py-3 text-center">Orden</th>
                            <th className="px-5 py-3 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-50">
                        {products.data.length === 0 && (
                            <tr>
                                <td colSpan={7} className="text-center py-12 text-gray-400">
                                    No se encontraron productos.
                                </td>
                            </tr>
                        )}
                        {products.data.map((p) => (
                            <tr key={p.id} className="hover:bg-gray-50/50">
                                {/* Producto */}
                                <td className="px-5 py-3">
                                    <div className="flex items-center gap-3">
                                        {p.image_url ? (
                                            <img
                                                src={p.image_url}
                                                className="w-9 h-9 rounded-lg object-cover flex-shrink-0"
                                                alt={p.name}
                                            />
                                        ) : (
                                            <div className="w-9 h-9 rounded-lg bg-gray-100 flex items-center justify-center flex-shrink-0">
                                                <Icon icon="solar:box-outline" width={16} className="text-gray-400" />
                                            </div>
                                        )}
                                        <div className="min-w-0">
                                            <p className="font-medium text-gray-800 truncate max-w-[180px]">{p.name}</p>
                                            <p className="text-xs text-gray-400">{p.slug}</p>
                                        </div>
                                    </div>
                                </td>

                                {/* Categoría */}
                                <td className="px-5 py-3 text-gray-600">
                                    <div>
                                        <span>{p.category?.name || "—"}</span>
                                        {p.subcategory && (
                                            <p className="text-xs text-gray-400">{p.subcategory.name}</p>
                                        )}
                                    </div>
                                </td>

                                {/* Marca */}
                                <td className="px-5 py-3 text-gray-600">{p.brand || "—"}</td>

                                {/* Estado */}
                                <td className="px-5 py-3 text-center">
                                    <span
                                        className={`inline-block text-xs px-2 py-0.5 rounded-full font-medium ${
                                            p.is_active
                                                ? "bg-green-100 text-green-700"
                                                : "bg-gray-100 text-gray-500"
                                        }`}
                                    >
                                        {p.is_active ? "Activo" : "Inactivo"}
                                    </span>
                                </td>

                                {/* Home */}
                                <td className="px-5 py-3 text-center">
                                    {p.show_in_home ? (
                                        <span className="inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded-full font-medium bg-[#25A7CA]/10 text-[#25A7CA]">
                                            <Icon icon="solar:home-2-bold" width={12} />
                                            Home
                                        </span>
                                    ) : (
                                        <span className="text-gray-300">—</span>
                                    )}
                                </td>

                                {/* Orden */}
                                <td className="px-5 py-3 text-center">
                                    <span className="font-mono text-xs font-semibold text-gray-500 bg-gray-100 px-2 py-0.5 rounded">
                                        {p.order || "—"}
                                    </span>
                                </td>

                                {/* Acciones */}
                                <td className="px-5 py-3">
                                    <div className="flex items-center justify-center gap-2">
                                        <button
                                            onClick={() => router.visit(`/admin/products/${p.id}/edit`)}
                                            className="p-1.5 rounded-lg hover:bg-gray-100 text-gray-600 transition"
                                        >
                                            <Icon icon="solar:pen-outline" width={16} />
                                        </button>
                                        <button
                                            onClick={() => destroy(p.id)}
                                            className="p-1.5 rounded-lg hover:bg-red-50 text-red-500 transition"
                                        >
                                            <Icon icon="solar:trash-bin-trash-outline" width={16} />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>

                {/* Pagination */}
                {products.last_page > 1 && (
                    <div className="flex items-center justify-between px-5 py-3 border-t border-gray-100 text-sm text-gray-500">
                        <span>
                            Página {products.current_page} de {products.last_page}
                        </span>
                        <div className="flex gap-2">
                            {products.prev_page_url && (
                                <button
                                    onClick={() => router.visit(products.prev_page_url)}
                                    className="px-3 py-1.5 rounded-lg border border-gray-200 hover:bg-gray-50"
                                >
                                    Anterior
                                </button>
                            )}
                            {products.next_page_url && (
                                <button
                                    onClick={() => router.visit(products.next_page_url)}
                                    className="px-3 py-1.5 rounded-lg border border-gray-200 hover:bg-gray-50"
                                >
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
