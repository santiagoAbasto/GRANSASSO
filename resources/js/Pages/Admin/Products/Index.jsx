import AdminLayout from "@/Layouts/AdminLayout";
import { router } from "@inertiajs/react";
import { Icon } from "@iconify/react";
import { useState } from "react";

/** Convierte texto ALL-CAPS a Title Case suave */
function toTitleCase(str) {
    if (!str) return str;
    return str
        .toLowerCase()
        .replace(/\b\w/g, (c) => c.toUpperCase());
}

export default function ProductsIndex({ products, brands, gearboxes }) {
    const params         = new URLSearchParams(window.location.search);
    const urlGearboxId   = params.get("gearbox_id") || "";
    const [search,    setSearch]  = useState(params.get("search")     || "");
    const [brandId,   setBrandId] = useState(params.get("brand_id")   || "");
    const [gearboxId, setGbId]    = useState(urlGearboxId);
    const [oemExact,  setOemExact]  = useState(params.get("oem_exact")  || "");
    const [codGs,     setCodGs]     = useState(params.get("cod_gs")     || "");
    const [bulkFiles,      setBulkFiles]    = useState([]);
    const [bulkPrimary,    setBulkPrimary]  = useState(0);
    const [bulkPreviews,   setBulkPreviews] = useState([]);
    const [bulkUploading,  setBulkUploading] = useState(false);
    const selectedGearbox = gearboxes.find((g) => String(g.id) === String(gearboxId)) || null;

    const applyFilters = () => {
        router.get(
            "/admin/products",
            {
                search,
                brand_id:   brandId,
                gearbox_id: gearboxId,
                oem_exact:  oemExact,
                cod_gs:     codGs,
            },
            { preserveState: true }
        );
    };

    const clearFilters = () => {
        setSearch(""); setBrandId(""); setGbId("");
        setOemExact(""); setCodGs("");
        router.get("/admin/products");
    };

    const hasFilters = search || brandId || gearboxId || oemExact || codGs;

    const onBulkFilesChange = (e) => {
        const files = Array.from(e.target.files);
        setBulkFiles(files);
        setBulkPrimary(0);
        // Generate previews
        const previews = files.map((f) => URL.createObjectURL(f));
        setBulkPreviews(previews);
    };

    const submitBulkImage = () => {
        if (bulkFiles.length === 0 || !gearboxId) return;
        const ids = products.data.map((p) => p.id);
        if (ids.length === 0) return alert("No hay productos en esta caja.");
        if (!confirm(`¿Asignar ${bulkFiles.length} imagen(es) a ${ids.length} producto(s)? Se reemplazarán las imágenes actuales.`)) return;
        setBulkUploading(true);
        const fd = new FormData();
        ids.forEach((id) => fd.append("product_ids[]", id));
        bulkFiles.forEach((f) => fd.append("images[]", f));
        fd.append("primary_index", bulkPrimary);
        router.post("/admin/products/bulk-image", fd, {
            forceFormData: true,
            onFinish: () => {
                setBulkUploading(false);
                setBulkFiles([]);
                setBulkPreviews([]);
                setBulkPrimary(0);
            },
        });
    };

    const destroy = (id) => {
        if (!confirm("¿Eliminar este producto y todos sus items?")) return;
        router.delete(`/admin/products/${id}`);
    };

    const destroyByGearbox = () => {
        if (!gearboxId || !selectedGearbox) return;
        const total = products.total ?? products.data.length;
        if (!confirm(`¿Eliminar todos los productos de "${selectedGearbox.name}"? La caja se conservará, pero se borrarán ${total} producto(s).`)) return;
        router.delete(`/admin/gearboxes/${selectedGearbox.id}/products`);
    };

    const Th = ({ children, className = "" }) => (
        <th className={`px-3 py-3 bg-gray-50/80 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider ${className}`}>
            {children}
        </th>
    );

    return (
        <AdminLayout>
            <div>

                {/* ── Header ─────────────────────────────────────────────── */}
                <div className="flex items-center justify-between mb-6">
                    <div>
                        <h1 className="text-2xl font-bold text-gray-800">Productos</h1>
                        <p className="text-sm text-gray-400 mt-0.5">{products.total} productos</p>
                    </div>
                    <button
                        onClick={() => router.visit("/admin/products/create")}
                        className="flex items-center gap-2 bg-[#25A7CA] text-white px-4 py-2 rounded-xl text-sm font-medium hover:bg-[#1d96b8] transition"
                    >
                        <Icon icon="solar:add-circle-outline" width={18} />
                        Nuevo Producto
                    </button>
                </div>

                {/* ── Filtros ─────────────────────────────────────────────── */}
                <div className="bg-white border border-gray-100 rounded-2xl shadow-sm p-4 mb-5">
                    <p className="text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-3">Filtros</p>
                    <div className="flex flex-wrap gap-2">

                        {/* Texto libre */}
                        <div className="relative">
                            <Icon icon="solar:magnifer-outline" className="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" width={14} />
                            <input
                                type="text"
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                onKeyDown={(e) => e.key === "Enter" && applyFilters()}
                                placeholder="Descripción..."
                                className="border border-gray-200 rounded-lg pl-8 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30 w-44"
                            />
                        </div>

                        {/* OEM exacto */}
                        <div className="relative">
                            <Icon icon="solar:code-outline" className="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" width={14} />
                            <input
                                type="text"
                                value={oemExact}
                                onChange={(e) => setOemExact(e.target.value)}
                                onKeyDown={(e) => e.key === "Enter" && applyFilters()}
                                placeholder="OEM exacto..."
                                className="border border-gray-200 rounded-lg pl-8 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30 w-36"
                            />
                        </div>

                        {/* Cod GS */}
                        <div className="relative">
                            <Icon icon="solar:hashtag-circle-outline" className="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" width={14} />
                            <input
                                type="text"
                                value={codGs}
                                onChange={(e) => setCodGs(e.target.value)}
                                onKeyDown={(e) => e.key === "Enter" && applyFilters()}
                                placeholder="Cod. GS..."
                                className="border border-gray-200 rounded-lg pl-8 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30 w-32"
                            />
                        </div>

                        {/* Marca */}
                        <select
                            value={brandId}
                            onChange={(e) => {
                                const val = e.target.value;
                                setBrandId(val);
                                setGbId("");
                                // Navegar inmediatamente para que el servidor devuelva las cajas de esta marca
                                router.get("/admin/products", { brand_id: val, search, oem_exact: oemExact, cod_gs: codGs }, { preserveState: true });
                            }}
                            className="border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-600 focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30"
                        >
                            <option value="">Todas las marcas</option>
                            {brands.map((b) => (
                                <option key={b.id} value={b.id}>{b.name?.es ?? b.name}</option>
                            ))}
                        </select>

                        {/* Caja — se puebla automáticamente al seleccionar marca */}
                        <select
                            value={gearboxId}
                            onChange={(e) => {
                                setGbId(e.target.value);
                                router.get("/admin/products", { brand_id: brandId, gearbox_id: e.target.value, search, oem_exact: oemExact, cod_gs: codGs }, { preserveState: true });
                            }}
                            className="border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-600 focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30"
                            disabled={!brandId}
                        >
                            <option value="">{brandId ? "Todas las cajas" : "— primero elegí marca —"}</option>
                            {gearboxes.map((g) => (
                                <option key={g.id} value={g.id}>{g.name}</option>
                            ))}
                        </select>

                        <button
                            onClick={applyFilters}
                            className="px-4 py-2 bg-[#25A7CA] text-white rounded-lg text-sm font-medium hover:bg-[#1d96b8] transition"
                        >
                            Filtrar
                        </button>

                        {hasFilters && (
                            <button
                                onClick={clearFilters}
                                className="px-3 py-2 text-sm text-gray-400 hover:text-gray-600 flex items-center gap-1"
                            >
                                <Icon icon="solar:close-circle-outline" width={14} />
                                Limpiar
                            </button>
                        )}
                    </div>
                </div>

                {/* ── Imagen masiva por caja ──────────────────────────────── */}
                {urlGearboxId && (
                    <div className="bg-white border border-amber-200 rounded-2xl p-5 mb-5 shadow-sm">
                        <div className="flex items-center gap-2 mb-4">
                            <Icon icon="solar:gallery-add-outline" width={20} className="text-amber-500" />
                            <div>
                                <p className="text-sm font-semibold text-gray-700">Imagen masiva para la caja</p>
                                <p className="text-xs text-gray-400">Se asignarán a los {products.data.length} productos filtrados · reemplaza imágenes existentes</p>
                            </div>
                        </div>

                        {/* File input */}
                        <p className="text-xs text-gray-400 mb-2">Recomendado: 288×209 px · JPG/PNG · máx 8 MB por imagen</p>
                        <input
                            type="file"
                            accept="image/*"
                            multiple
                            onChange={onBulkFilesChange}
                            className="block w-full text-sm text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-amber-50 file:text-amber-700 mb-3"
                        />

                        {/* Previews + primary selector */}
                        {bulkPreviews.length > 0 && (
                            <div className="mb-4">
                                <p className="text-xs text-gray-400 mb-2">Seleccioná cuál será la imagen del card (★ = principal):</p>
                                <div className="flex flex-wrap gap-3">
                                    {bulkPreviews.map((src, i) => (
                                        <button
                                            key={i}
                                            type="button"
                                            onClick={() => setBulkPrimary(i)}
                                            className={`relative rounded-xl overflow-hidden border-2 transition ${bulkPrimary === i ? "border-[#25A7CA]" : "border-gray-200 hover:border-gray-300"}`}
                                        >
                                            <img src={src} className="h-20 w-20 object-cover" alt="" />
                                            {bulkPrimary === i && (
                                                <span className="absolute top-1 left-1 bg-[#25A7CA] text-white text-[9px] font-bold px-1.5 py-0.5 rounded-full leading-none">
                                                    CARD
                                                </span>
                                            )}
                                            {bulkPrimary !== i && (
                                                <span className="absolute top-1 left-1 bg-black/40 text-white text-[9px] px-1.5 py-0.5 rounded-full leading-none">
                                                    detalle
                                                </span>
                                            )}
                                        </button>
                                    ))}
                                </div>
                            </div>
                        )}

                        <button
                            onClick={submitBulkImage}
                            disabled={bulkFiles.length === 0 || bulkUploading}
                            className="px-5 py-2 bg-amber-500 hover:bg-amber-600 disabled:opacity-40 text-white rounded-xl text-sm font-semibold transition"
                        >
                            {bulkUploading ? "Subiendo…" : `Aplicar a ${products.data.length} productos`}
                        </button>
                    </div>
                )}

                {urlGearboxId && (
                    <div className="bg-white border border-red-200 rounded-2xl p-5 mb-5 shadow-sm">
                        <div className="flex items-start gap-3">
                            <div className="w-10 h-10 rounded-xl bg-red-50 text-red-500 flex items-center justify-center flex-shrink-0">
                                <Icon icon="solar:danger-triangle-outline" width={19} />
                            </div>
                            <div className="flex-1 min-w-0">
                                <p className="text-sm font-semibold text-gray-700">Eliminar productos de la caja</p>
                                <p className="text-xs text-gray-400 mt-1">
                                    Se eliminarán los {products.total ?? products.data.length} producto(s) de {selectedGearbox?.name ?? "la caja seleccionada"}.
                                    La caja, su marca y sus campos extra se conservan.
                                </p>
                            </div>
                            <button
                                onClick={destroyByGearbox}
                                disabled={!selectedGearbox || (products.total ?? products.data.length) === 0}
                                className="px-4 py-2 bg-red-500 hover:bg-red-600 disabled:opacity-40 text-white rounded-xl text-sm font-semibold transition"
                            >
                                Eliminar todo
                            </button>
                        </div>
                    </div>
                )}

                {/* ── Tabla ───────────────────────────────────────────────── */}
                <div className="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-x-auto">
                    <table className="w-full text-sm border-collapse">
                        <thead>
                            <tr className="border-b border-gray-100">
                                {/* img */}
                                <th className="w-14 px-3 py-3 bg-gray-50/80 rounded-tl-2xl" />
                                <Th className="w-12">Fig.</Th>
                                <Th className="w-20">Cod. GS</Th>
                                <Th>Código OEM</Th>
                                <Th className="hidden lg:table-cell w-36">Caja</Th>
                                <Th className="hidden lg:table-cell w-60">Modelos</Th>
                                <Th className="hidden md:table-cell">Descripción</Th>
                                <Th className="w-28">Variantes</Th>
                                <th className="px-3 py-3 bg-gray-50/80 text-right text-[11px] font-semibold text-gray-400 uppercase tracking-wider rounded-tr-2xl">
                                    Acciones
                                </th>
                            </tr>
                        </thead>

                        <tbody className="divide-y divide-gray-50">
                            {products.data.map((product) => (
                                <tr key={product.id} className="hover:bg-slate-50/60 transition-colors">

                                    {/* ── Imagen ────────────────────────── */}
                                    <td className="px-3 py-2.5">
                                        {product.primary_image ? (
                                            <img
                                                src={product.primary_image.url}
                                                className="w-10 h-10 object-cover rounded-xl border border-gray-100"
                                                alt=""
                                            />
                                        ) : (
                                            <div className="w-10 h-10 bg-gray-50 rounded-xl border border-dashed border-gray-200 flex items-center justify-center">
                                                <Icon icon="solar:box-outline" width={15} className="text-gray-300" />
                                            </div>
                                        )}
                                    </td>

                                    {/* ── Fig. ─────────────────────────── */}
                                    <td className="px-3 py-2.5">
                                        {product.figure ? (
                                            <span className="font-mono text-xs font-semibold text-gray-600">{product.figure}</span>
                                        ) : (
                                            <span className="text-gray-200">—</span>
                                        )}
                                    </td>

                                    {/* ── Cod. GS ───────────────────────── */}
                                    <td className="px-3 py-2.5">
                                        {product.code_gs ? (
                                            <div className="flex flex-col items-start gap-1">
                                                <span className="font-bold text-gray-800">{product.code_gs}</span>
                                                {/* Badge SOLO si tiene más de 1 item (variantes reales) */}
                                                {product.items_count > 1 && (
                                                    <span className="inline-flex items-center gap-0.5 text-[10px] bg-amber-50 text-amber-500 border border-amber-100 px-1.5 py-0.5 rounded font-medium">
                                                        <Icon icon="solar:layers-minimalistic-outline" width={9} />
                                                        {product.items_count} vars
                                                    </span>
                                                )}
                                            </div>
                                        ) : (
                                            <span className="text-gray-200">—</span>
                                        )}
                                    </td>

                                    {/* ── Código OEM ────────────────────── */}
                                    <td className="px-3 py-2.5">
                                        {product.oem_codes?.length > 0 ? (
                                            <div className="flex flex-col gap-1">
                                                {product.oem_codes.map((oem) => (
                                                    <code
                                                        key={oem}
                                                        className="inline-block font-mono text-[12px] text-gray-400 bg-gray-50 border border-gray-100 px-2 py-0.5 rounded-md"
                                                    >
                                                        {oem}
                                                    </code>
                                                ))}
                                            </div>
                                        ) : (
                                            <span className="text-gray-200">—</span>
                                        )}
                                    </td>

                                    {/* ── Caja ──────────────────────────── */}
                                    <td className="px-3 py-2.5 hidden lg:table-cell">
                                        <div className="text-[10px] text-gray-400 uppercase tracking-wide font-medium">
                                            {product.gearbox?.brand?.name?.es ?? product.gearbox?.brand?.name ?? ""}
                                        </div>
                                        <div className="text-sm font-semibold text-gray-700 mt-0.5">
                                            {product.gearbox?.name ?? <span className="text-gray-300">—</span>}
                                        </div>
                                        {product.secondary_gearboxes?.length > 0 && (
                                            <div className="mt-1 text-[11px] leading-relaxed text-slate-400">
                                                También en:{" "}
                                                {product.secondary_gearboxes
                                                    .map((gearbox) => `${gearbox.brand} / ${gearbox.name}`)
                                                    .join(" · ")}
                                            </div>
                                        )}
                                    </td>

                                    {/* ── Modelos ── completo, hace wrap ─── */}
                                    <td className="px-3 py-2.5 hidden lg:table-cell">
                                        {product.gearbox?.compatible_vehicles ? (
                                            <span className="text-xs text-gray-400 leading-relaxed whitespace-normal">
                                                {product.gearbox.compatible_vehicles}
                                            </span>
                                        ) : (
                                            <span className="text-gray-200">—</span>
                                        )}
                                    </td>

                                    {/* ── Descripción ── Title Case + semibold */}
                                    <td className="px-3 py-2.5 hidden md:table-cell">
                                        {product.name_es ? (
                                            <span
                                                className="text-xs font-semibold text-gray-700 line-clamp-2 leading-snug"
                                                title={product.name_es}
                                            >
                                                {toTitleCase(product.name_es)}
                                            </span>
                                        ) : (
                                            <span className="text-gray-200">—</span>
                                        )}
                                    </td>

                                    {/* ── Variantes ─────────────────────── */}
                                    <td className="px-3 py-2.5">
                                        <div className="flex flex-wrap gap-1">
                                            {(product.variants ?? []).slice(0, 4).map((v) => (
                                                <span
                                                    key={v}
                                                    className="inline-block text-[11px] font-semibold bg-[#25A7CA]/10 text-[#1d96b8] px-2 py-0.5 rounded-md uppercase"
                                                >
                                                    {v}
                                                </span>
                                            ))}
                                            {product.variants?.length > 4 && (
                                                <span className="text-[11px] text-gray-400">+{product.variants.length - 4}</span>
                                            )}
                                            {(!product.variants || product.variants.length === 0) && (
                                                <span className="text-[11px] text-gray-300 italic">Genérico</span>
                                            )}
                                        </div>
                                    </td>

                                    {/* ── Acciones ──────────────────────── */}
                                    <td className="px-3 py-2.5">
                                        <div className="flex items-center justify-end gap-1">
                                            <span className={`text-[10px] px-2 py-0.5 rounded-full font-semibold border ${
                                                product.is_active
                                                    ? "bg-emerald-50 text-emerald-600 border-emerald-100"
                                                    : "bg-gray-50 text-gray-400 border-gray-200"
                                            }`}>
                                                {product.is_active ? "Activo" : "Inactivo"}
                                            </span>
                                            <button
                                                onClick={() => router.visit(`/admin/products/${product.id}/edit`)}
                                                className="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-gray-700 transition-colors"
                                                title="Editar"
                                            >
                                                <Icon icon="solar:pen-outline" width={14} />
                                            </button>
                                            <button
                                                onClick={() => destroy(product.id)}
                                                className="p-1.5 rounded-lg hover:bg-red-50 text-gray-300 hover:text-red-400 transition-colors"
                                                title="Eliminar"
                                            >
                                                <Icon icon="solar:trash-bin-trash-outline" width={14} />
                                            </button>
                                        </div>
                                    </td>

                                </tr>
                            ))}

                            {products.data.length === 0 && (
                                <tr>
                                    <td colSpan={9} className="py-16 text-center">
                                        <Icon icon="solar:box-outline" width={36} className="text-gray-200 mx-auto mb-2" />
                                        <p className="text-sm text-gray-400">No hay productos cargados. Ve al importador para subir el Excel o crea uno manualmente.</p>
                                        <button
                                            onClick={() => router.visit("/admin/import")}
                                            className="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-[#25A7CA] text-white rounded-xl text-sm font-medium hover:bg-[#1d96b8] transition"
                                        >
                                            <Icon icon="solar:upload-outline" width={16} />
                                            Ir al importador
                                        </button>
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>

                {/* ── Paginación ──────────────────────────────────────────── */}
                {products.last_page > 1 && (
                    <div className="flex items-center justify-between mt-4 text-sm text-gray-400">
                        <span>Página {products.current_page} de {products.last_page}</span>
                        <div className="flex gap-1.5">
                            {products.prev_page_url && (
                                <button
                                    onClick={() => router.get(products.prev_page_url)}
                                    className="flex items-center gap-1 px-3 py-1.5 border border-gray-200 rounded-lg hover:bg-gray-50 text-gray-600 transition"
                                >
                                    <Icon icon="solar:arrow-left-outline" width={13} /> Anterior
                                </button>
                            )}
                            {products.next_page_url && (
                                <button
                                    onClick={() => router.get(products.next_page_url)}
                                    className="flex items-center gap-1 px-3 py-1.5 border border-gray-200 rounded-lg hover:bg-gray-50 text-gray-600 transition"
                                >
                                    Siguiente <Icon icon="solar:arrow-right-outline" width={13} />
                                </button>
                            )}
                        </div>
                    </div>
                )}

            </div>
        </AdminLayout>
    );
}
