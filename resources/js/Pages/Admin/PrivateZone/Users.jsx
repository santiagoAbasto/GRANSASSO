import { useState } from "react";
import { router } from "@inertiajs/react";
import AdminLayout from "@/Layouts/AdminLayout";
import { Icon } from "@iconify/react";

const STATUS_CONFIG = {
    pending: {
        label: "Pendiente",
        badge: "bg-amber-100 text-amber-800 border-amber-200",
        card: "bg-amber-50 text-amber-700 border-amber-100",
        icon: "solar:clock-circle-outline",
    },
    approved: {
        label: "Aprobado",
        badge: "bg-emerald-100 text-emerald-800 border-emerald-200",
        card: "bg-emerald-50 text-emerald-700 border-emerald-100",
        icon: "solar:shield-check-outline",
    },
    rejected: {
        label: "Rechazado",
        badge: "bg-rose-100 text-rose-800 border-rose-200",
        card: "bg-rose-50 text-rose-700 border-rose-100",
        icon: "solar:close-circle-outline",
    },
};

const TABS = ["all", "pending", "approved", "rejected"];

const TAB_LABELS = {
    all: "Todos",
    pending: "Pendientes",
    approved: "Aprobados",
    rejected: "Rechazados",
};

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
                        placeholder="Buscar por nombre, apellido o correo..."
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

function StatCard({ icon, label, value, tone = "sky" }) {
    const tones = {
        sky: "bg-sky-50 text-sky-600 border-sky-100",
        amber: "bg-amber-50 text-amber-600 border-amber-100",
        emerald: "bg-emerald-50 text-emerald-600 border-emerald-100",
        rose: "bg-rose-50 text-rose-600 border-rose-100",
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

function TabBar({ status, stats, onChange }) {
    return (
        <div className="flex flex-wrap gap-2">
            {TABS.map((tab) => (
                <button
                    key={tab}
                    type="button"
                    onClick={() => onChange(tab)}
                    className={`inline-flex items-center gap-2 rounded-2xl border px-4 py-2.5 text-sm font-semibold transition ${
                        status === tab
                            ? "border-[#25A7CA] bg-[#25A7CA] text-white"
                            : "border-gray-200 bg-white text-gray-600 hover:bg-gray-50"
                    }`}
                >
                    <span>{TAB_LABELS[tab]}</span>
                    <span className={`rounded-full px-2 py-0.5 text-xs ${status === tab ? "bg-white/20 text-white" : "bg-gray-100 text-gray-500"}`}>
                        {stats?.[tab] ?? 0}
                    </span>
                </button>
            ))}
        </div>
    );
}

function ActionButton({ icon, label, onClick, tone = "default", disabled = false }) {
    const tones = {
        default: "border-gray-200 text-gray-600 hover:bg-gray-50",
        sky: "border-[#25A7CA]/20 bg-[#25A7CA]/10 text-[#25A7CA] hover:bg-[#25A7CA]/20",
        emerald: "border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100",
        rose: "border-rose-200 bg-rose-50 text-rose-700 hover:bg-rose-100",
    };

    return (
        <button
            type="button"
            onClick={onClick}
            disabled={disabled}
            className={`inline-flex items-center justify-center gap-2 rounded-xl border px-3 py-2 text-sm font-medium transition disabled:cursor-not-allowed disabled:opacity-50 ${tones[tone]}`}
        >
            <Icon icon={icon} width={15} />
            {label}
        </button>
    );
}

function DiscountEditor({ value, onChange, onSave, onCancel, user }) {
    const total = Number(value.discount_pct || 0) + Number(value.discount_extra_pct || 0);

    return (
        <div className="rounded-2xl border border-sky-100 bg-sky-50/70 p-4">
            <div className="mb-4 flex items-start gap-3">
                <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-white text-[#25A7CA] shadow-sm">
                    <Icon icon="solar:ticket-sale-outline" width={18} />
                </div>
                <div>
                    <p className="text-sm font-semibold text-slate-800">Editar descuento de {user.full_name}</p>
                    <p className="text-xs text-slate-500">Configurá descuento base y extra. El total actual quedará en {total}%.</p>
                </div>
            </div>

            <div className="grid gap-4 md:grid-cols-3">
                <div>
                    <label className="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Descuento base</label>
                    <input
                        type="number"
                        min={0}
                        max={100}
                        value={value.discount_pct}
                        onChange={(e) => onChange((current) => ({ ...current, discount_pct: Number(e.target.value) }))}
                        className="w-full rounded-xl border border-sky-200 bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30"
                    />
                </div>
                <div>
                    <label className="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Descuento extra</label>
                    <input
                        type="number"
                        min={0}
                        max={100}
                        value={value.discount_extra_pct}
                        onChange={(e) => onChange((current) => ({ ...current, discount_extra_pct: Number(e.target.value) }))}
                        className="w-full rounded-xl border border-sky-200 bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30"
                    />
                </div>
                <div className="rounded-2xl border border-sky-100 bg-white px-4 py-3">
                    <p className="text-xs font-semibold uppercase tracking-wide text-slate-400">Descuento total</p>
                    <p className="mt-1 text-2xl font-semibold text-slate-800">{total}%</p>
                </div>
            </div>

            <div className="mt-4 flex flex-wrap justify-end gap-2">
                <ActionButton icon="solar:close-circle-outline" label="Cancelar" onClick={onCancel} />
                <ActionButton icon="solar:diskette-outline" label="Guardar descuento" onClick={onSave} tone="sky" />
            </div>
        </div>
    );
}

function PasswordEditor({ value, onChange, onGenerate, onSave, onCancel, user }) {
    return (
        <div className="rounded-2xl border border-amber-100 bg-amber-50/80 p-4">
            <div className="mb-4 flex items-start gap-3">
                <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-white text-amber-600 shadow-sm">
                    <Icon icon="solar:key-outline" width={18} />
                </div>
                <div>
                    <p className="text-sm font-semibold text-slate-800">Resetear contraseña de {user.full_name}</p>
                    <p className="text-xs text-slate-500">Podés escribir una nueva contraseña o generar una segura para compartirle al cliente.</p>
                </div>
            </div>

            <div className="grid gap-3 lg:grid-cols-[1fr_auto]">
                <div>
                    <label className="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Nueva contraseña</label>
                    <input
                        type="text"
                        value={value}
                        onChange={(e) => onChange(e.target.value)}
                        placeholder="Mínimo 6 caracteres"
                        className="w-full rounded-xl border border-amber-200 bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-200"
                        autoFocus
                    />
                </div>
                <div className="flex items-end">
                    <button
                        type="button"
                        onClick={onGenerate}
                        className="inline-flex items-center gap-2 rounded-xl border border-amber-200 bg-white px-4 py-2.5 text-sm font-medium text-amber-700 transition hover:bg-amber-100"
                    >
                        <Icon icon="solar:magic-stick-3-outline" width={15} />
                        Generar
                    </button>
                </div>
            </div>

            <div className="mt-4 flex flex-wrap justify-end gap-2">
                <ActionButton icon="solar:close-circle-outline" label="Cancelar" onClick={onCancel} />
                <ActionButton icon="solar:key-minimalistic-square-outline" label="Guardar nueva clave" onClick={onSave} tone="emerald" disabled={value.length < 6} />
            </div>
        </div>
    );
}

function UserCard({
    user,
    onApprove,
    onReject,
    onDelete,
    onOpenDiscount,
    onOpenPassword,
    isEditingDiscount,
    discountForm,
    setDiscountForm,
    saveDiscount,
    closeDiscount,
    isEditingPassword,
    newPassword,
    setNewPassword,
    generatePassword,
    savePassword,
    closePassword,
}) {
    const statusConfig = STATUS_CONFIG[user.status];
    const totalDiscount = user.discount_pct + user.discount_extra_pct;

    return (
        <div className="rounded-3xl border border-gray-100 bg-white p-5 shadow-sm transition hover:shadow-md">
            <div className="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
                <div className="min-w-0 flex-1">
                    <div className="flex flex-wrap items-center gap-2">
                        <h3 className="text-lg font-semibold text-gray-800">{user.full_name}</h3>
                        <span className={`inline-flex items-center gap-1 rounded-full border px-2.5 py-1 text-xs font-semibold ${statusConfig.badge}`}>
                            <Icon icon={statusConfig.icon} width={13} />
                            {statusConfig.label}
                        </span>
                    </div>

                    <p className="mt-1 break-all text-sm text-gray-500">{user.email}</p>

                    <div className="mt-4 grid gap-3 sm:grid-cols-3">
                        <div className="rounded-2xl border border-gray-100 bg-gray-50/70 px-4 py-3">
                            <p className="text-[11px] font-semibold uppercase tracking-wide text-gray-400">Alta</p>
                            <p className="mt-1 text-sm font-medium text-gray-700">{user.created_at_label}</p>
                        </div>
                        <div className="rounded-2xl border border-gray-100 bg-gray-50/70 px-4 py-3">
                            <p className="text-[11px] font-semibold uppercase tracking-wide text-gray-400">Último acceso</p>
                            <p className="mt-1 text-sm font-medium text-gray-700">{user.last_login_label || "Todavía no ingresó"}</p>
                        </div>
                        <div className="rounded-2xl border border-gray-100 bg-gray-50/70 px-4 py-3">
                            <p className="text-[11px] font-semibold uppercase tracking-wide text-gray-400">Descuento actual</p>
                            <p className="mt-1 text-sm font-medium text-gray-700">
                                {user.discount_pct}% base + {user.discount_extra_pct}% extra
                                <span className="ml-2 text-[#25A7CA]">= {totalDiscount}%</span>
                            </p>
                        </div>
                    </div>
                </div>

                <div className="flex w-full flex-col gap-2 xl:w-auto xl:min-w-[280px]">
                    {user.status !== "approved" && (
                        <ActionButton icon="solar:check-square-outline" label="Aprobar cliente" onClick={() => onApprove(user.id)} tone="emerald" />
                    )}
                    {user.status !== "rejected" && (
                        <ActionButton icon="solar:close-square-outline" label="Rechazar cliente" onClick={() => onReject(user.id)} tone="rose" />
                    )}
                    <ActionButton icon="solar:ticket-sale-outline" label="Editar descuento" onClick={() => onOpenDiscount(user)} tone="sky" />
                    <ActionButton icon="solar:key-outline" label="Resetear contraseña" onClick={() => onOpenPassword(user)} />
                    <ActionButton icon="solar:trash-bin-minimalistic-outline" label="Eliminar cliente" onClick={() => onDelete(user.id)} tone="rose" />
                </div>
            </div>

            {isEditingDiscount && (
                <div className="mt-5 border-t border-gray-100 pt-5">
                    <DiscountEditor
                        user={user}
                        value={discountForm}
                        onChange={setDiscountForm}
                        onSave={() => saveDiscount(user.id)}
                        onCancel={closeDiscount}
                    />
                </div>
            )}

            {isEditingPassword && (
                <div className="mt-5 border-t border-gray-100 pt-5">
                    <PasswordEditor
                        user={user}
                        value={newPassword}
                        onChange={setNewPassword}
                        onGenerate={generatePassword}
                        onSave={() => savePassword(user.id)}
                        onCancel={closePassword}
                    />
                </div>
            )}
        </div>
    );
}

export default function Users({ users, status, q, stats }) {
    const [search, setSearch] = useState(q ?? "");
    const [editingDiscount, setEditingDiscount] = useState(null);
    const [discountForm, setDiscountForm] = useState({ discount_pct: 0, discount_extra_pct: 0 });
    const [editingPassword, setEditingPassword] = useState(null);
    const [newPassword, setNewPassword] = useState("");

    function navigate(nextStatus = status, page = 1, nextQuery = search) {
        const params = { status: nextStatus };
        if (page > 1) params.page = page;
        if (nextQuery.trim()) params.q = nextQuery.trim();

        router.get("/admin/private-users", params, {
            preserveScroll: true,
            preserveState: true,
            replace: true,
        });
    }

    function handleApprove(id) {
        router.post(`/admin/private-users/${id}/approve`, {}, { preserveScroll: true });
    }

    function handleReject(id) {
        if (!confirm("¿Rechazar este cliente?")) return;
        router.post(`/admin/private-users/${id}/reject`, {}, { preserveScroll: true });
    }

    function handleDelete(id) {
        if (!confirm("¿Eliminar este cliente privado?")) return;
        router.delete(`/admin/private-users/${id}`, { preserveScroll: true });
    }

    function openDiscount(user) {
        setEditingPassword(null);
        setNewPassword("");
        setEditingDiscount(user.id);
        setDiscountForm({
            discount_pct: user.discount_pct,
            discount_extra_pct: user.discount_extra_pct,
        });
    }

    function saveDiscount(id) {
        router.patch(`/admin/private-users/${id}/discount`, discountForm, {
            preserveScroll: true,
            onSuccess: () => setEditingDiscount(null),
        });
    }

    function openPassword(user) {
        setEditingDiscount(null);
        setEditingPassword(user.id);
        setNewPassword("");
    }

    function createGeneratedPassword() {
        const alphabet = "ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789!@#$%";
        const value = Array.from({ length: 10 }, () => alphabet[Math.floor(Math.random() * alphabet.length)]).join("");
        setNewPassword(value);
    }

    function savePassword(id) {
        if (!newPassword || newPassword.length < 6) return;

        router.post(`/admin/private-users/${id}/password`, { password: newPassword }, {
            preserveScroll: true,
            onSuccess: () => {
                setEditingPassword(null);
                setNewPassword("");
            },
        });
    }

    return (
        <AdminLayout title="Clientes — Zona Privada">
            <div className="max-w-6xl space-y-6">
                <div className="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <h1 className="text-2xl font-bold text-gray-800">Clientes — Zona Privada</h1>
                        <p className="mt-1 text-sm text-gray-500">
                            Gestioná aprobaciones, descuentos y credenciales desde una vista más rápida para atención comercial.
                        </p>
                    </div>
                </div>

                <div className="grid gap-4 md:grid-cols-4">
                    <StatCard icon="solar:users-group-rounded-outline" label="Clientes" value={stats?.all ?? 0} />
                    <StatCard icon="solar:user-check-outline" label="Pendientes" value={stats?.pending ?? 0} tone="amber" />
                    <StatCard icon="solar:shield-check-outline" label="Aprobados" value={stats?.approved ?? 0} tone="emerald" />
                    <StatCard icon="solar:user-block-outline" label="Rechazados" value={stats?.rejected ?? 0} tone="rose" />
                </div>

                <SearchBar
                    value={search}
                    onChange={setSearch}
                    onSubmit={(e) => {
                        e.preventDefault();
                        navigate(status, 1, search);
                    }}
                    onClear={() => {
                        setSearch("");
                        navigate(status, 1, "");
                    }}
                />

                <div className="rounded-2xl border border-[#25A7CA]/20 bg-[#25A7CA]/5 p-4">
                    <div className="flex items-start gap-3">
                        <Icon icon="solar:info-circle-outline" width={18} className="mt-0.5 flex-shrink-0 text-[#25A7CA]" />
                        <div className="text-sm text-gray-600">
                            <p className="mb-1 font-medium text-gray-700">Cómo trabajar más rápido</p>
                            <p>
                                Usá los filtros por estado para priorizar aprobaciones, y en cada tarjeta vas a encontrar acceso directo para descuento, contraseña y eliminación sin perder contexto del cliente.
                            </p>
                        </div>
                    </div>
                </div>

                <TabBar status={status} stats={stats} onChange={(nextStatus) => navigate(nextStatus, 1, search)} />

                <div className="space-y-4">
                    {users.data.length === 0 ? (
                        <div className="rounded-3xl border border-dashed border-gray-200 bg-white px-6 py-14 text-center shadow-sm">
                            <div className="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-gray-100 text-gray-400">
                                <Icon icon="solar:users-group-rounded-outline" width={24} />
                            </div>
                            <p className="text-base font-semibold text-gray-700">No hay clientes para mostrar</p>
                            <p className="mt-1 text-sm text-gray-400">Probá con otro filtro o limpiá la búsqueda actual.</p>
                        </div>
                    ) : (
                        users.data.map((user) => (
                            <UserCard
                                key={user.id}
                                user={user}
                                onApprove={handleApprove}
                                onReject={handleReject}
                                onDelete={handleDelete}
                                onOpenDiscount={openDiscount}
                                onOpenPassword={openPassword}
                                isEditingDiscount={editingDiscount === user.id}
                                discountForm={discountForm}
                                setDiscountForm={setDiscountForm}
                                saveDiscount={saveDiscount}
                                closeDiscount={() => setEditingDiscount(null)}
                                isEditingPassword={editingPassword === user.id}
                                newPassword={newPassword}
                                setNewPassword={setNewPassword}
                                generatePassword={createGeneratedPassword}
                                savePassword={savePassword}
                                closePassword={() => {
                                    setEditingPassword(null);
                                    setNewPassword("");
                                }}
                            />
                        ))
                    )}
                </div>

                {users.last_page > 1 && (
                    <div className="flex flex-wrap justify-center gap-2">
                        {Array.from({ length: users.last_page }, (_, index) => index + 1).map((page) => (
                            <button
                                key={page}
                                type="button"
                                onClick={() => navigate(status, page, search)}
                                className={`rounded-xl border px-3 py-2 text-sm font-medium transition ${
                                    users.current_page === page
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
