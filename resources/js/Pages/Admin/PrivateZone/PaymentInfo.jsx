import { useState, useRef } from "react";
import { router } from "@inertiajs/react";
import AdminLayout from "@/Layouts/AdminLayout";
import { Icon } from "@iconify/react";

export default function PaymentInfo({ bankInfo, submissions }) {
    const initial = bankInfo?.content ?? [];
    const [rows, setRows] = useState(() => {
        if (Array.isArray(initial) && initial.length > 0) {
            return initial.map(([k, v]) => ({ key: k, value: v }));
        }
        if (typeof initial === "object" && !Array.isArray(initial) && Object.keys(initial).length > 0) {
            return Object.entries(initial).map(([k, v]) => ({ key: k, value: v }));
        }
        return [{ key: "", value: "" }];
    });
    const [saving, setSaving] = useState(false);
    const dragItem = useRef(null);
    const dragOverItem = useRef(null);

    function addRow() { setRows(r => [...r, { key: "", value: "" }]); }
    function removeRow(i) { setRows(r => r.filter((_, idx) => idx !== i)); }
    function updateRow(i, field, val) { setRows(r => r.map((row, idx) => idx === i ? { ...row, [field]: val } : row)); }

    function handleDragStart(i) { dragItem.current = i; }
    function handleDragEnter(i) { dragOverItem.current = i; }
    function handleDragEnd() {
        if (dragItem.current === null || dragOverItem.current === null) return;
        const copy = [...rows];
        const dragged = copy.splice(dragItem.current, 1)[0];
        copy.splice(dragOverItem.current, 0, dragged);
        dragItem.current = null;
        dragOverItem.current = null;
        setRows(copy);
    }

    function moveRow(from, to) {
        if (to < 0 || to >= rows.length) return;
        const copy = [...rows];
        const [item] = copy.splice(from, 1);
        copy.splice(to, 0, item);
        setRows(copy);
    }

    function handleSubmit(e) {
        e.preventDefault();
        const content = rows.filter(r => r.key.trim()).map(r => [r.key.trim(), r.value]);
        setSaving(true);
        router.post("/admin/payment-info", { content }, {
            preserveScroll: true,
            onFinish: () => setSaving(false),
        });
    }

    return (
        <AdminLayout title="Info de Pagos">
            <div>

                {/* Header */}
                <div className="mb-6">
                    <h1 className="text-2xl font-bold text-gray-800">Info de Pagos</h1>
                    <p className="text-sm text-gray-500 mt-1">Gestioná los datos bancarios y visualizá los pagos recibidos.</p>
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    {/* Bank info editor */}
                    <div className="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                        <h2 className="font-semibold text-gray-700 flex items-center gap-2 mb-4">
                            <Icon icon="solar:bank-outline" width={18} className="text-[#25A7CA]" />
                            Datos bancarios
                        </h2>
                        <form onSubmit={handleSubmit} className="space-y-1">
                            {rows.map((row, i) => (
                                <div
                                    key={i}
                                    draggable
                                    onDragStart={() => handleDragStart(i)}
                                    onDragEnter={() => handleDragEnter(i)}
                                    onDragEnd={handleDragEnd}
                                    onDragOver={e => e.preventDefault()}
                                    className="flex items-center gap-2 py-1.5 px-1 rounded-lg hover:bg-gray-50 transition cursor-grab active:cursor-grabbing"
                                >
                                    <div className="flex flex-col gap-0.5 flex-shrink-0">
                                        <button type="button" onClick={() => moveRow(i, i - 1)} disabled={i === 0}
                                            className="text-gray-300 hover:text-gray-600 disabled:opacity-20 disabled:cursor-not-allowed leading-none">
                                            <Icon icon="solar:alt-arrow-up-outline" width={14} />
                                        </button>
                                        <button type="button" onClick={() => moveRow(i, i + 1)} disabled={i === rows.length - 1}
                                            className="text-gray-300 hover:text-gray-600 disabled:opacity-20 disabled:cursor-not-allowed leading-none">
                                            <Icon icon="solar:alt-arrow-down-outline" width={14} />
                                        </button>
                                    </div>
                                    <Icon icon="solar:hamburger-menu-outline" width={16} className="text-gray-300 flex-shrink-0" />
                                    <input
                                        type="text"
                                        placeholder="Campo (ej: CBU)"
                                        value={row.key}
                                        onChange={e => updateRow(i, "key", e.target.value)}
                                        className="flex-1 border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-[#25A7CA]/30 outline-none"
                                    />
                                    <input
                                        type="text"
                                        placeholder="Valor"
                                        value={row.value}
                                        onChange={e => updateRow(i, "value", e.target.value)}
                                        className="flex-[2] border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-[#25A7CA]/30 outline-none"
                                    />
                                    <button type="button" onClick={() => removeRow(i)} className="text-red-400 hover:text-red-600 p-1">
                                        <Icon icon="solar:close-circle-outline" width={18} />
                                    </button>
                                </div>
                            ))}
                            <button type="button" onClick={addRow} className="flex items-center gap-1 text-sm text-[#25A7CA] hover:underline font-semibold mt-2">
                                <Icon icon="solar:add-circle-outline" width={16} /> Agregar campo
                            </button>
                            <div className="flex justify-end pt-2">
                                <button type="submit" disabled={saving}
                                    className="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-[#25A7CA] text-white text-sm font-medium hover:bg-[#1d96b8] disabled:opacity-60 transition">
                                    {saving ? "Guardando..." : "Guardar"}
                                </button>
                            </div>
                        </form>
                    </div>

                    {/* Preview */}
                    <div className="bg-gray-50 rounded-2xl border border-gray-200 p-6">
                        <h2 className="font-semibold text-gray-500 flex items-center gap-2 mb-4">
                            <Icon icon="solar:eye-outline" width={18} className="text-gray-400" />
                            Vista previa
                        </h2>
                        {rows.filter(r => r.key.trim()).length === 0 ? (
                            <p className="text-sm text-gray-400">Sin datos.</p>
                        ) : (
                            <div className="space-y-3">
                                {rows.filter(r => r.key.trim()).map((row, i) => (
                                    <div key={i} className="flex flex-col">
                                        <span className="text-xs font-semibold text-gray-400 uppercase tracking-wide">{row.key}</span>
                                        <span className="text-base font-semibold text-gray-800">{row.value || "—"}</span>
                                    </div>
                                ))}
                            </div>
                        )}
                    </div>
                </div>

                {/* Submissions table */}
                <div className="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                    <div className="px-5 py-4 border-b border-gray-100 flex items-center gap-2">
                        <Icon icon="solar:card-recive-outline" width={18} className="text-[#25A7CA]" />
                        <h2 className="font-semibold text-gray-700">Pagos recibidos</h2>
                    </div>
                    <table className="w-full text-sm">
                        <thead>
                            <tr className="bg-[#0D2038] text-white text-left">
                                <th className="px-4 py-3">Cliente</th>
                                <th className="px-4 py-3">Fecha</th>
                                <th className="px-4 py-3">Importe</th>
                                <th className="px-4 py-3">Banco</th>
                                <th className="px-4 py-3">Sucursal</th>
                                <th className="px-4 py-3">Facturas</th>
                                <th className="px-4 py-3">Archivo</th>
                            </tr>
                        </thead>
                        <tbody>
                            {submissions.data.length === 0 && (
                                <tr><td colSpan={7} className="text-center py-10 text-gray-400">Sin pagos recibidos.</td></tr>
                            )}
                            {submissions.data.map(s => (
                                <tr key={s.id} className="border-b border-gray-100 hover:bg-gray-50">
                                    <td className="px-4 py-3 font-semibold text-gray-800">
                                        {s.private_user?.name} {s.private_user?.lastname}
                                        <div className="text-xs text-gray-400 font-normal">{s.private_user?.email}</div>
                                    </td>
                                    <td className="px-4 py-3 text-gray-600">
                                        {new Date(s.payment_date).toLocaleDateString("es-AR", { day: "2-digit", month: "2-digit", year: "numeric", timeZone: "America/Argentina/Buenos_Aires" })}
                                        <div className="text-xs text-gray-400">
                                            {new Date(s.created_at).toLocaleString("es-AR", { day: "2-digit", month: "2-digit", year: "numeric", hour: "2-digit", minute: "2-digit", timeZone: "America/Argentina/Buenos_Aires" })}
                                        </div>
                                    </td>
                                    <td className="px-4 py-3 font-semibold text-gray-800">
                                        ${parseFloat(s.amount).toLocaleString("es-AR", { minimumFractionDigits: 2 })}
                                    </td>
                                    <td className="px-4 py-3 text-gray-600">{s.bank}</td>
                                    <td className="px-4 py-3 text-gray-500">{s.branch || "—"}</td>
                                    <td className="px-4 py-3 text-gray-500">{s.invoices_cancelled || "—"}</td>
                                    <td className="px-4 py-3">
                                        {s.file_path ? (
                                            <a href={`/storage/${s.file_path}`} target="_blank" className="text-[#25A7CA] hover:underline text-xs font-semibold">
                                                Ver
                                            </a>
                                        ) : "—"}
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

