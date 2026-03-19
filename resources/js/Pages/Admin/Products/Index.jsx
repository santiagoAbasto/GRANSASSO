import AdminLayout from "@/Layouts/AdminLayout";
import { router } from "@inertiajs/react";
import { Icon } from "@iconify/react";
import { useState } from "react";

export default function ProductsIndex({ products, brands, gearboxes }) {
    const [search, setSearch]     = useState(new URLSearchParams(window.location.search).get("search") || "");
    const [brandId, setBrandId]   = useState(new URLSearchParams(window.location.search).get("brand_id") || "");
    const [gearboxId, setGbId]    = useState(new URLSearchParams(window.location.search).get("gearbox_id") || "");

    const applyFilters = () => {
        router.get("/admin/products", { search, brand_id: brandId, gearbox_id: gearboxId }, { preserveState: true });
    };

    const destroy = (id) => {
        if (!confirm("¿Eliminar este producto?")) return;
        router.delete(`/admin/products/${id}`);
    };

    return (
        <AdminLayout>
            <div>
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

                {/* Filtros */}
                <div className="flex flex-wrap gap-3 mb-5">
                    <input
                        type="text"
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        onKeyDown={(e) => e.key === "Enter" && applyFilters()}
                        placeholder="Buscar por OEM, Cod GS..."
                        className="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30 w-52"
                    />
                    <select value={brandId} onChange={(e) => setBrandId(e.target.value)} className="border border-gray-200 rounded-lg px-3 py-2 text-sm">
                        <option value="">Todas las marcas</option>
                        {brands.map((b) => <option key={b.id} value={b.id}>{b.name?.es ?? b.name}</option>)}
                    </select>
                    <select value={gearboxId} onChange={(e) => setGbId(e.target.value)} className="border border-gray-200 rounded-lg px-3 py-2 text-sm">
                        <option value="">Todas las cajas</option>
                        {gearboxes.map((g) => <option key={g.id} value={g.id}>{g.name}</option>)}
                    </select>
                    <button onClick={applyFilters} className="px-4 py-2 bg-gray-100 rounded-lg text-sm text-gray-700 hover:bg-gray-200">Filtrar</button>
                </div>

                {/* Tabla */}
                <div className="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                    <table className="w-full text-sm">
                        <thead>
                            <tr className="border-b border-gray-100 bg-gray-50/60">
                                <th className="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">OEM / Figura</th>
                                <th className="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide hidden md:table-cell">Caja</th>
                                <th className="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide hidden lg:table-cell">Nombre</th>
                                <th className="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide hidden lg:table-cell">Variantes</th>
                                <th className="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            {products.data.map((product, i) => (
                                <tr key={product.id} className={`border-b border-gray-50 last:border-0 ${i % 2 === 0 ? "" : "bg-gray-50/30"}`}>
                                    <td className="px-5 py-3">
                                        <div className="font-medium text-gray-800">{product.code_oem}</div>
                                        {product.figure && <div className="text-xs text-gray-400">{product.figure}</div>}
                                        {product.cod_gs  && <div className="text-xs text-gray-400">{product.cod_gs}</div>}
                                    </td>
                                    <td className="px-5 py-3 hidden md:table-cell">
                                        <div className="text-xs text-gray-500">{product.gearbox?.brand?.name?.es ?? "—"}</div>
                                        <div className="text-sm text-gray-700">{product.gearbox?.name}</div>
                                    </td>
                                    <td className="px-5 py-3 text-gray-600 hidden lg:table-cell max-w-xs truncate">
                                        {product.nombre?.es || "—"}
                                    </td>
                                    <td className="px-5 py-3 hidden lg:table-cell">
                                        <div className="flex flex-wrap gap-1">
                                            {(product.versions ?? []).slice(0, 3).map((v) => (
                                                <span key={v.id ?? v.version} className="text-[10px] bg-[#25A7CA]/10 text-[#25A7CA] px-1.5 py-0.5 rounded-full font-medium">{v.version}</span>
                                            ))}
                                            {product.versions?.length > 3 && <span className="text-[10px] text-gray-400">+{product.versions.length - 3}</span>}
                                        </div>
                                    </td>
                                    <td className="px-5 py-3">
                                        <div className="flex items-center justify-end gap-2">
                                            <button onClick={() => router.visit(`/admin/products/${product.id}/edit`)}
                                                className="p-1.5 rounded-lg hover:bg-gray-100 text-gray-600" title="Editar">
                                                <Icon icon="solar:pen-outline" width={15} />
                                            </button>
                                            <button onClick={() => destroy(product.id)}
                                                className="p-1.5 rounded-lg hover:bg-red-50 text-red-500" title="Eliminar">
                                                <Icon icon="solar:trash-bin-trash-outline" width={15} />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            ))}
                            {products.data.length === 0 && (
                                <tr><td colSpan={5} className="px-5 py-12 text-center text-sm text-gray-400">Sin productos. Usá el importador de Excel o creá uno manualmente.</td></tr>
                            )}
                        </tbody>
                    </table>
                </div>

                {/* Paginación */}
                {products.last_page > 1 && (
                    <div className="flex items-center justify-between mt-4 text-sm text-gray-500">
                        <span>Página {products.current_page} de {products.last_page}</span>
                        <div className="flex gap-2">
                            {products.prev_page_url && (
                                <button onClick={() => router.get(products.prev_page_url)} className="px-3 py-1.5 border border-gray-200 rounded-lg hover:bg-gray-50">Anterior</button>
                            )}
                            {products.next_page_url && (
                                <button onClick={() => router.get(products.next_page_url)} className="px-3 py-1.5 border border-gray-200 rounded-lg hover:bg-gray-50">Siguiente</button>
                            )}
                        </div>
                    </div>
                )}
            </div>
        </AdminLayout>
    );
}
