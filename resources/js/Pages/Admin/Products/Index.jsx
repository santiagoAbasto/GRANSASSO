import AdminLayout from "@/Layouts/AdminLayout";
import { router } from "@inertiajs/react";
import { Icon } from "@iconify/react";
import { useState } from "react";

export default function ProductsIndex({ products, brands, gearboxes }) {
    const params  = new URLSearchParams(window.location.search);
    const [search,    setSearch]  = useState(params.get("search")     || "");
    const [brandId,   setBrandId] = useState(params.get("brand_id")   || "");
    const [gearboxId, setGbId]    = useState(params.get("gearbox_id") || "");

    const applyFilters = () => {
        router.get("/admin/products", { search, brand_id: brandId, gearbox_id: gearboxId }, { preserveState: true });
    };

    const destroy = (id) => {
        if (!confirm("¿Eliminar este producto y todos sus items?")) return;
        router.delete(`/admin/products/${id}`);
    };

    const thClass = "text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide";

    return (
        <AdminLayout>
            <div>
                {/* Header */}
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
                        placeholder="OEM, Cod. GS, Figura..."
                        className="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30 w-52"
                    />
                    <select
                        value={brandId}
                        onChange={(e) => { setBrandId(e.target.value); setGbId(""); }}
                        className="border border-gray-200 rounded-lg px-3 py-2 text-sm"
                    >
                        <option value="">Todas las marcas</option>
                        {brands.map((b) => (
                            <option key={b.id} value={b.id}>{b.name?.es ?? b.name}</option>
                        ))}
                    </select>
                    <select
                        value={gearboxId}
                        onChange={(e) => setGbId(e.target.value)}
                        className="border border-gray-200 rounded-lg px-3 py-2 text-sm"
                    >
                        <option value="">Todas las cajas</option>
                        {gearboxes.map((g) => (
                            <option key={g.id} value={g.id}>{g.name}</option>
                        ))}
                    </select>
                    <button
                        onClick={applyFilters}
                        className="px-4 py-2 bg-gray-100 rounded-lg text-sm text-gray-700 hover:bg-gray-200"
                    >
                        Filtrar
                    </button>
                </div>

                {/* Tabla */}
                <div className="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-x-auto">
                    <table className="w-full text-sm">
                        <thead>
                            <tr className="border-b border-gray-100 bg-gray-50/60">
                                {/* Imagen */}
                                <th className="w-12 px-3 py-3"></th>
                                {/* Fig. */}
                                <th className={`${thClass} w-20`}>Fig.</th>
                                {/* Cod. GS */}
                                <th className={`${thClass} w-24`}>Cod. GS</th>
                                {/* Código OEM */}
                                <th className={thClass}>Código OEM</th>
                                {/* Caja de cambios */}
                                <th className={`${thClass} hidden md:table-cell`}>Caja de cambios</th>
                                {/* Descripción */}
                                <th className={`${thClass} hidden lg:table-cell`}>Descripción</th>
                                {/* Variantes */}
                                <th className={`${thClass} hidden lg:table-cell`}>Variantes</th>
                                {/* Acciones */}
                                <th className="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            {products.data.map((product, i) => (
                                <tr
                                    key={product.id}
                                    className={`border-b border-gray-50 last:border-0 ${i % 2 === 0 ? "" : "bg-gray-50/30"}`}
                                >
                                    {/* Imagen */}
                                    <td className="px-3 py-2">
                                        {product.primary_image ? (
                                            <img
                                                src={product.primary_image.url}
                                                className="w-10 h-10 object-cover rounded-lg border border-gray-100"
                                                alt=""
                                            />
                                        ) : (
                                            <div className="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center">
                                                <Icon icon="solar:gallery-outline" width={16} className="text-gray-300" />
                                            </div>
                                        )}
                                    </td>

                                    {/* Fig. */}
                                    <td className="px-4 py-3 text-gray-600">
                                        {product.figure
                                            ? <span className="font-mono text-xs">{product.figure}</span>
                                            : <span className="text-gray-300">—</span>
                                        }
                                    </td>

                                    {/* Cod. GS */}
                                    <td className="px-4 py-3">
                                        {product.code_gs
                                            ? <span className="font-semibold text-gray-800">{product.code_gs}</span>
                                            : <span className="text-gray-300">—</span>
                                        }
                                        {product.items_count > 1 && (
                                            <div className="mt-0.5">
                                                <span className="text-[10px] bg-amber-100 text-amber-700 px-1.5 py-0.5 rounded-full font-medium">
                                                    {product.items_count} items
                                                </span>
                                            </div>
                                        )}
                                    </td>

                                    {/* Código OEM */}
                                    <td className="px-4 py-3">
                                        {product.oem_codes?.length > 0
                                            ? (
                                                <div className="flex flex-col gap-0.5">
                                                    {product.oem_codes.map((oem) => (
                                                        <span key={oem} className="font-mono text-xs text-gray-700">{oem}</span>
                                                    ))}
                                                </div>
                                            )
                                            : <span className="text-gray-300">—</span>
                                        }
                                    </td>

                                    {/* Caja de cambios */}
                                    <td className="px-4 py-3 hidden md:table-cell max-w-[220px]">
                                        <div className="text-xs text-gray-400 uppercase tracking-wide">
                                            {product.gearbox?.brand?.name?.es ?? product.gearbox?.brand?.name ?? ""}
                                        </div>
                                        <div className="text-sm text-gray-700 font-medium">
                                            {product.gearbox?.name}
                                        </div>
                                        {product.gearbox?.compatible_vehicles && (
                                            <div
                                                className="text-[11px] text-gray-400 mt-0.5 leading-tight truncate"
                                                title={product.gearbox.compatible_vehicles}
                                            >
                                                {product.gearbox.compatible_vehicles}
                                            </div>
                                        )}
                                    </td>

                                    {/* Descripción */}
                                    <td className="px-4 py-3 text-gray-600 hidden lg:table-cell max-w-xs">
                                        <div className="truncate">{product.name_es || "—"}</div>
                                    </td>

                                    {/* Variantes */}
                                    <td className="px-4 py-3 hidden lg:table-cell">
                                        <div className="flex flex-wrap gap-1">
                                            {(product.variants ?? []).slice(0, 5).map((v) => (
                                                <span
                                                    key={v}
                                                    className="text-[10px] bg-[#25A7CA]/10 text-[#25A7CA] px-1.5 py-0.5 rounded-full font-medium uppercase"
                                                >
                                                    {v}
                                                </span>
                                            ))}
                                            {product.variants?.length > 5 && (
                                                <span className="text-[10px] text-gray-400">
                                                    +{product.variants.length - 5}
                                                </span>
                                            )}
                                        </div>
                                    </td>

                                    {/* Acciones */}
                                    <td className="px-4 py-3">
                                        <div className="flex items-center justify-end gap-2">
                                            <span className={`text-[10px] px-2 py-0.5 rounded-full font-medium ${product.is_active ? "bg-green-100 text-green-700" : "bg-gray-100 text-gray-500"}`}>
                                                {product.is_active ? "Activo" : "Inactivo"}
                                            </span>
                                            <button
                                                onClick={() => router.visit(`/admin/products/${product.id}/edit`)}
                                                className="p-1.5 rounded-lg hover:bg-gray-100 text-gray-600"
                                                title="Editar"
                                            >
                                                <Icon icon="solar:pen-outline" width={15} />
                                            </button>
                                            <button
                                                onClick={() => destroy(product.id)}
                                                className="p-1.5 rounded-lg hover:bg-red-50 text-red-400"
                                                title="Eliminar"
                                            >
                                                <Icon icon="solar:trash-bin-trash-outline" width={15} />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            ))}

                            {products.data.length === 0 && (
                                <tr>
                                    <td colSpan={8} className="px-5 py-12 text-center text-sm text-gray-400">
                                        Sin productos. Usá el importador Excel o creá uno manualmente.
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>

                {/* Paginación */}
                {products.last_page > 1 && (
                    <div className="flex items-center justify-between mt-4 text-sm text-gray-500">
                        <span>Página {products.current_page} de {products.last_page} ({products.total} productos)</span>
                        <div className="flex gap-2">
                            {products.prev_page_url && (
                                <button
                                    onClick={() => router.get(products.prev_page_url)}
                                    className="px-3 py-1.5 border border-gray-200 rounded-lg hover:bg-gray-50"
                                >
                                    Anterior
                                </button>
                            )}
                            {products.next_page_url && (
                                <button
                                    onClick={() => router.get(products.next_page_url)}
                                    className="px-3 py-1.5 border border-gray-200 rounded-lg hover:bg-gray-50"
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
