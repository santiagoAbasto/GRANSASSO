import { useState } from "react";
import { router } from "@inertiajs/react";
import AdminLayout from "@/Layouts/AdminLayout";
import { Icon } from "@iconify/react";

export default function PaymentInfo({ bankInfo, submissions }) {
    const initial = bankInfo?.content ?? {};
    const [rows, setRows] = useState(
        Object.entries(initial).length > 0
            ? Object.entries(initial).map(([k, v]) => ({ key: k, value: v }))
            : [{ key: "", value: "" }]
    );
    const [saving, setSaving] = useState(false);

    function addRow() { setRows(r => [...r, { key: "", value: "" }]); }
    function removeRow(i) { setRows(r => r.filter((_, idx) => idx !== i)); }
    function updateRow(i, field, val) { setRows(r => r.map((row, idx) => idx === i ? { ...row, [field]: val } : row)); }

    function handleSubmit(e) {
        e.preventDefault();
        const content = {};
        rows.forEach(r => { if (r.key.trim()) content[r.key.trim()] = r.value; });
        setSaving(true);
        router.post("/admin/payment-info", { content }, {
            preserveScroll: true,
            onFinish: () => setSaving(false),
        });
    }

    return (
        <AdminLayout title="Info de Pagos">
            <div className="p-6">
                <h1 className="text-2xl font-bold text-gray-800 mb-6">Info de Pagos</h1>

                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    {/* Bank info editor */}
                    <div className="bg-white rounded-xl shadow p-6">
                        <h2 className="text-base font-semibold text-gray-700 mb-4">Datos bancarios</h2>
                        <form onSubmit={handleSubmit} className="space-y-3">
                            {rows.map((row, i) => (
                                <div key={i} className="flex items-center gap-2">
                                    <input
                                        type="text"
                                        placeholder="Campo (ej: CBU)"
                                        value={row.key}
                                        onChange={e => updateRow(i, "key", e.target.value)}
                                        className="flex-1 border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#136CBA]"
                                    />
                                    <input
                                        type="text"
                                        placeholder="Valor"
                                        value={row.value}
                                        onChange={e => updateRow(i, "value", e.target.value)}
                                        className="flex-2 border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#136CBA]"
                                    />
                                    <button type="button" onClick={() => removeRow(i)} className="text-red-400 hover:text-red-600 p-1">
                                        <Icon icon="solar:close-circle-outline" width={18} />
                                    </button>
                                </div>
                            ))}
                            <button type="button" onClick={addRow} className="flex items-center gap-1 text-sm text-[#136CBA] hover:underline font-semibold mt-1">
                                <Icon icon="solar:add-circle-outline" width={16} /> Agregar campo
                            </button>
                            <div className="flex justify-end pt-2">
                                <button type="submit" disabled={saving}
                                    className="px-6 py-2 bg-[#136CBA] text-white rounded-lg text-sm font-semibold hover:bg-[#0f5ea8] transition disabled:opacity-50">
                                    {saving ? "Guardando..." : "Guardar"}
                                </button>
                            </div>
                        </form>
                    </div>

                    {/* Preview */}
                    <div className="bg-gray-50 rounded-xl p-6 border border-gray-200">
                        <h2 className="text-base font-semibold text-gray-500 mb-4">Vista previa</h2>
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
                <div className="bg-white rounded-xl shadow overflow-hidden">
                    <div className="px-5 py-3 border-b">
                        <h2 className="text-base font-semibold text-gray-700">Pagos recibidos</h2>
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
                                    <td className="px-4 py-3 text-gray-600">{s.payment_date}</td>
                                    <td className="px-4 py-3 font-semibold text-gray-800">
                                        ${parseFloat(s.amount).toLocaleString("es-AR", { minimumFractionDigits: 2 })}
                                    </td>
                                    <td className="px-4 py-3 text-gray-600">{s.bank}</td>
                                    <td className="px-4 py-3 text-gray-500">{s.branch || "—"}</td>
                                    <td className="px-4 py-3 text-gray-500">{s.invoices_cancelled || "—"}</td>
                                    <td className="px-4 py-3">
                                        {s.file_path ? (
                                            <a href={`/storage/${s.file_path}`} target="_blank" className="text-[#136CBA] hover:underline text-xs font-semibold">
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
