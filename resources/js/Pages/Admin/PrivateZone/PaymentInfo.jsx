import { useRef, useState } from "react";
import { router } from "@inertiajs/react";
import AdminLayout from "@/Layouts/AdminLayout";
import { Icon } from "@iconify/react";

function formatCurrency(amount) {
    return Number(amount || 0).toLocaleString("es-AR", {
        style: "currency",
        currency: "ARS",
        minimumFractionDigits: 2,
    });
}

function StatCard({ icon, label, value, tone = "sky" }) {
    const tones = {
        sky: "bg-sky-50 text-sky-600 border-sky-100",
        emerald: "bg-emerald-50 text-emerald-600 border-emerald-100",
        amber: "bg-amber-50 text-amber-600 border-amber-100",
    };

    return (
        <div className="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
            <div className={`mb-3 flex h-10 w-10 items-center justify-center rounded-xl border ${tones[tone]}`}>
                <Icon icon={icon} width={18} />
            </div>
            <p className="text-xs font-semibold uppercase tracking-wide text-gray-400">{label}</p>
            <p className="mt-1 text-2xl font-semibold text-gray-800">{value}</p>
        </div>
    );
}

function SearchBar({ value, onChange, onSubmit, onClear }) {
    return (
        <form onSubmit={onSubmit} className="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
            <div className="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div className="relative w-full lg:max-w-xl">
                    <Icon icon="solar:magnifer-outline" width={18} className="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" />
                    <input
                        type="text"
                        value={value}
                        onChange={(e) => onChange(e.target.value)}
                        placeholder="Buscar por cliente, correo, banco, sucursal o factura..."
                        className="w-full rounded-2xl border border-gray-200 py-3 pl-10 pr-4 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30"
                    />
                </div>

                <div className="flex flex-wrap gap-2">
                    {value.trim() && (
                        <button
                            type="button"
                            onClick={onClear}
                            className="rounded-xl border border-gray-200 px-4 py-2.5 text-sm text-gray-600 transition hover:bg-gray-50"
                        >
                            Limpiar
                        </button>
                    )}
                    <button
                        type="submit"
                        className="inline-flex items-center gap-2 rounded-xl bg-[#25A7CA] px-4 py-2.5 text-sm font-medium text-white transition hover:bg-[#1d96b8]"
                    >
                        <Icon icon="solar:magnifer-outline" width={16} />
                        Buscar
                    </button>
                </div>
            </div>
        </form>
    );
}

function BankInfoEditor({ rows, setRows, onSubmit, saving }) {
    const dragItem = useRef(null);
    const dragOverItem = useRef(null);

    function addRow() {
        setRows((current) => [...current, { key: "", value: "" }]);
    }

    function removeRow(index) {
        setRows((current) => current.filter((_, currentIndex) => currentIndex !== index));
    }

    function updateRow(index, field, value) {
        setRows((current) => current.map((row, currentIndex) => (currentIndex === index ? { ...row, [field]: value } : row)));
    }

    function moveRow(from, to) {
        if (to < 0 || to >= rows.length) return;
        const copy = [...rows];
        const [item] = copy.splice(from, 1);
        copy.splice(to, 0, item);
        setRows(copy);
    }

    function handleDragStart(index) {
        dragItem.current = index;
    }

    function handleDragEnter(index) {
        dragOverItem.current = index;
    }

    function handleDragEnd() {
        if (dragItem.current === null || dragOverItem.current === null) return;
        moveRow(dragItem.current, dragOverItem.current);
        dragItem.current = null;
        dragOverItem.current = null;
    }

    return (
        <div className="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
            <div className="mb-5 flex items-start gap-3">
                <div className="flex h-11 w-11 items-center justify-center rounded-2xl bg-[#25A7CA]/10 text-[#25A7CA]">
                    <Icon icon="solar:bank-outline" width={20} />
                </div>
                <div>
                    <h2 className="text-base font-semibold text-gray-800">Datos bancarios</h2>
                    <p className="mt-1 text-sm text-gray-500">Ordená y editá los campos como los verá el cliente cuando necesite pagar.</p>
                </div>
            </div>

            <form onSubmit={onSubmit} className="space-y-3">
                {rows.map((row, index) => (
                    <div
                        key={index}
                        draggable
                        onDragStart={() => handleDragStart(index)}
                        onDragEnter={() => handleDragEnter(index)}
                        onDragEnd={handleDragEnd}
                        onDragOver={(e) => e.preventDefault()}
                        className="grid gap-3 rounded-2xl border border-gray-100 bg-gray-50/70 p-3 lg:grid-cols-[auto_auto_1fr_1.4fr_auto]"
                    >
                        <div className="flex flex-col justify-center gap-1">
                            <button
                                type="button"
                                onClick={() => moveRow(index, index - 1)}
                                disabled={index === 0}
                                className="rounded-lg p-1 text-gray-300 transition hover:bg-white hover:text-gray-600 disabled:cursor-not-allowed disabled:opacity-20"
                            >
                                <Icon icon="solar:alt-arrow-up-outline" width={14} />
                            </button>
                            <button
                                type="button"
                                onClick={() => moveRow(index, index + 1)}
                                disabled={index === rows.length - 1}
                                className="rounded-lg p-1 text-gray-300 transition hover:bg-white hover:text-gray-600 disabled:cursor-not-allowed disabled:opacity-20"
                            >
                                <Icon icon="solar:alt-arrow-down-outline" width={14} />
                            </button>
                        </div>

                        <div className="flex items-center justify-center text-gray-300">
                            <Icon icon="solar:hamburger-menu-outline" width={18} />
                        </div>

                        <input
                            type="text"
                            placeholder="Campo (ej: CBU)"
                            value={row.key}
                            onChange={(e) => updateRow(index, "key", e.target.value)}
                            className="w-full rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30"
                        />

                        <input
                            type="text"
                            placeholder="Valor"
                            value={row.value}
                            onChange={(e) => updateRow(index, "value", e.target.value)}
                            className="w-full rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30"
                        />

                        <button
                            type="button"
                            onClick={() => removeRow(index)}
                            className="inline-flex items-center justify-center rounded-xl border border-rose-100 bg-white px-3 py-2 text-rose-500 transition hover:bg-rose-50"
                        >
                            <Icon icon="solar:trash-bin-trash-outline" width={16} />
                        </button>
                    </div>
                ))}

                <div className="flex flex-col gap-3 border-t border-gray-100 pt-4 sm:flex-row sm:items-center sm:justify-between">
                    <button
                        type="button"
                        onClick={addRow}
                        className="inline-flex items-center gap-2 rounded-xl border border-[#25A7CA]/20 bg-[#25A7CA]/10 px-4 py-2.5 text-sm font-medium text-[#25A7CA] transition hover:bg-[#25A7CA]/20"
                    >
                        <Icon icon="solar:add-circle-outline" width={16} />
                        Agregar campo
                    </button>

                    <button
                        type="submit"
                        disabled={saving}
                        className="inline-flex items-center justify-center gap-2 rounded-xl bg-[#25A7CA] px-5 py-2.5 text-sm font-medium text-white transition hover:bg-[#1d96b8] disabled:opacity-60"
                    >
                        <Icon icon={saving ? "solar:refresh-outline" : "solar:diskette-outline"} width={16} className={saving ? "animate-spin" : ""} />
                        {saving ? "Guardando..." : "Guardar datos bancarios"}
                    </button>
                </div>
            </form>
        </div>
    );
}

function BankInfoPreview({ rows }) {
    const visibleRows = rows.filter((row) => row.key.trim());

    return (
        <div className="rounded-3xl border border-gray-200 bg-gray-50 p-6">
            <div className="mb-5 flex items-start gap-3">
                <div className="flex h-11 w-11 items-center justify-center rounded-2xl bg-white text-gray-500 shadow-sm">
                    <Icon icon="solar:eye-outline" width={20} />
                </div>
                <div>
                    <h2 className="text-base font-semibold text-gray-700">Vista previa</h2>
                    <p className="mt-1 text-sm text-gray-500">Así se ordenan los datos mientras vas editando.</p>
                </div>
            </div>

            {visibleRows.length === 0 ? (
                <div className="rounded-2xl border border-dashed border-gray-200 bg-white px-5 py-10 text-center text-sm text-gray-400">
                    No hay datos cargados todavía.
                </div>
            ) : (
                <div className="space-y-3">
                    {visibleRows.map((row, index) => (
                        <div key={`${row.key}-${index}`} className="rounded-2xl border border-white bg-white px-4 py-3 shadow-sm">
                            <p className="text-[11px] font-semibold uppercase tracking-wide text-gray-400">{row.key}</p>
                            <p className="mt-1 text-base font-semibold text-gray-800 break-all">{row.value || "—"}</p>
                        </div>
                    ))}
                </div>
            )}
        </div>
    );
}

function SubmissionCard({ submission }) {
    return (
        <div className="rounded-3xl border border-gray-100 bg-white p-5 shadow-sm transition hover:shadow-md">
            <div className="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                <div className="min-w-0 flex-1">
                    <div className="flex flex-wrap items-center gap-2">
                        <h3 className="text-lg font-semibold text-gray-800">{submission.customer_name || "Cliente sin nombre"}</h3>
                        <span className="rounded-full bg-sky-50 px-2.5 py-1 text-xs font-semibold text-sky-700">
                            {formatCurrency(submission.amount)}
                        </span>
                    </div>

                    <p className="mt-1 break-all text-sm text-gray-500">{submission.customer_email || "Sin correo"}</p>

                    <div className="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                        <div className="rounded-2xl border border-gray-100 bg-gray-50/70 px-4 py-3">
                            <p className="text-[11px] font-semibold uppercase tracking-wide text-gray-400">Fecha del pago</p>
                            <p className="mt-1 text-sm font-medium text-gray-700">{submission.payment_date_label || "—"}</p>
                        </div>
                        <div className="rounded-2xl border border-gray-100 bg-gray-50/70 px-4 py-3">
                            <p className="text-[11px] font-semibold uppercase tracking-wide text-gray-400">Banco / sucursal</p>
                            <p className="mt-1 text-sm font-medium text-gray-700">
                                {submission.bank || "—"}
                                {submission.branch ? ` · ${submission.branch}` : ""}
                            </p>
                        </div>
                        <div className="rounded-2xl border border-gray-100 bg-gray-50/70 px-4 py-3">
                            <p className="text-[11px] font-semibold uppercase tracking-wide text-gray-400">Facturas canceladas</p>
                            <p className="mt-1 text-sm font-medium text-gray-700">{submission.invoices_cancelled || "—"}</p>
                        </div>
                        <div className="rounded-2xl border border-gray-100 bg-gray-50/70 px-4 py-3">
                            <p className="text-[11px] font-semibold uppercase tracking-wide text-gray-400">Recibido en sistema</p>
                            <p className="mt-1 text-sm font-medium text-gray-700">{submission.created_at_label || "—"}</p>
                        </div>
                    </div>

                    {submission.observations && (
                        <div className="mt-4 rounded-2xl border border-amber-100 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                            <span className="font-semibold">Observaciones:</span> {submission.observations}
                        </div>
                    )}
                </div>

                <div className="flex w-full flex-col gap-2 xl:w-auto xl:min-w-[170px]">
                    {submission.download_url ? (
                        <a
                            href={submission.download_url}
                            className="inline-flex items-center justify-center gap-2 rounded-xl border border-[#25A7CA]/20 bg-[#25A7CA]/10 px-4 py-2.5 text-sm font-medium text-[#25A7CA] transition hover:bg-[#25A7CA]/20"
                        >
                            <Icon icon="solar:file-download-outline" width={15} />
                            Descargar comprobante
                        </a>
                    ) : (
                        <div className="rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-center text-sm text-gray-400">
                            Sin archivo adjunto
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
}

export default function PaymentInfo({ bankInfo, submissions, q, stats }) {
    const initial = bankInfo?.content ?? [];
    const [rows, setRows] = useState(() => {
        if (Array.isArray(initial) && initial.length > 0) {
            return initial.map(([key, value]) => ({ key, value }));
        }
        if (typeof initial === "object" && !Array.isArray(initial) && Object.keys(initial).length > 0) {
            return Object.entries(initial).map(([key, value]) => ({ key, value }));
        }
        return [{ key: "", value: "" }];
    });
    const [saving, setSaving] = useState(false);
    const [search, setSearch] = useState(q ?? "");

    function handleSubmit(e) {
        e.preventDefault();
        const content = rows
            .filter((row) => row.key.trim())
            .map((row) => [row.key.trim(), row.value]);

        setSaving(true);
        router.post("/admin/payment-info", { content }, {
            preserveScroll: true,
            onFinish: () => setSaving(false),
        });
    }

    function navigate(page = 1, nextQuery = search) {
        const params = {};
        if (page > 1) params.page = page;
        if (nextQuery.trim()) params.q = nextQuery.trim();

        router.get("/admin/payment-info", params, {
            preserveScroll: true,
            preserveState: true,
            replace: true,
        });
    }

    return (
        <AdminLayout title="Info de Pagos">
            <div className="max-w-6xl space-y-6">
                <div className="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <h1 className="text-2xl font-bold text-gray-800">Info de Pagos</h1>
                        <p className="mt-1 text-sm text-gray-500">Ordená mejor los datos bancarios y revisá los comprobantes con menos fricción.</p>
                    </div>
                </div>

                <div className="grid gap-4 md:grid-cols-3">
                    <StatCard icon="solar:card-recive-outline" label="Pagos encontrados" value={stats?.count ?? 0} />
                    <StatCard icon="solar:wallet-money-outline" label="Importe total" value={formatCurrency(stats?.total_amount ?? 0)} tone="emerald" />
                    <StatCard icon="solar:bank-outline" label="Campos bancarios" value={rows.filter((row) => row.key.trim()).length} tone="amber" />
                </div>

                <div className="grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">
                    <BankInfoEditor rows={rows} setRows={setRows} onSubmit={handleSubmit} saving={saving} />
                    <BankInfoPreview rows={rows} />
                </div>

                <SearchBar
                    value={search}
                    onChange={setSearch}
                    onSubmit={(e) => {
                        e.preventDefault();
                        navigate(1, search);
                    }}
                    onClear={() => {
                        setSearch("");
                        navigate(1, "");
                    }}
                />

                <div className="rounded-3xl border border-gray-100 bg-white p-5 shadow-sm">
                    <div className="mb-5 flex items-start gap-3">
                        <div className="flex h-11 w-11 items-center justify-center rounded-2xl bg-[#25A7CA]/10 text-[#25A7CA]">
                            <Icon icon="solar:card-recive-outline" width={20} />
                        </div>
                        <div>
                            <h2 className="text-base font-semibold text-gray-800">Pagos recibidos</h2>
                            <p className="mt-1 text-sm text-gray-500">Cada tarjeta resume al cliente, el importe, la referencia de pago y el comprobante adjunto.</p>
                        </div>
                    </div>

                    <div className="space-y-3">
                        {submissions.data.length === 0 ? (
                            <div className="rounded-2xl border border-dashed border-gray-200 bg-gray-50 px-6 py-14 text-center">
                                <div className="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-gray-400 shadow-sm">
                                    <Icon icon="solar:card-recive-outline" width={24} />
                                </div>
                                <p className="text-base font-semibold text-gray-700">Sin pagos recibidos</p>
                                <p className="mt-1 text-sm text-gray-400">Cuando lleguen comprobantes desde zona privada, van a aparecer acá.</p>
                            </div>
                        ) : (
                            submissions.data.map((submission) => <SubmissionCard key={submission.id} submission={submission} />)
                        )}
                    </div>
                </div>

                {submissions.last_page > 1 && (
                    <div className="flex flex-wrap justify-center gap-2">
                        {Array.from({ length: submissions.last_page }, (_, index) => index + 1).map((page) => (
                            <button
                                key={page}
                                type="button"
                                onClick={() => navigate(page, search)}
                                className={`rounded-xl border px-3 py-2 text-sm font-medium transition ${
                                    submissions.current_page === page
                                        ? "border-[#25A7CA] bg-[#25A7CA] text-white"
                                        : "border-gray-200 bg-white text-gray-600 hover:bg-gray-50"
                                }`}
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
