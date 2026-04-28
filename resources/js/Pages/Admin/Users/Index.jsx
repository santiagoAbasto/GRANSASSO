import AdminLayout from "@/Layouts/AdminLayout";
import { useForm, usePage } from "@inertiajs/react";
import { Icon } from "@iconify/react";
import { useState } from "react";

function initialsFor(name = "") {
    return name
        .split(" ")
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part[0]?.toUpperCase() ?? "")
        .join("");
}

function generatePassword() {
    const alphabet = "ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789!@#$%";
    return Array.from({ length: 12 }, () => alphabet[Math.floor(Math.random() * alphabet.length)]).join("");
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

function UserModal({ user, onClose }) {
    const isEdit = !!user;
    const [showPassword, setShowPassword] = useState(false);
    const [showConfirmPassword, setShowConfirmPassword] = useState(false);
    const [changePassword, setChangePassword] = useState(!isEdit);

    const { data, setData, post, put, processing, errors, reset } = useForm({
        name: user?.name || "",
        email: user?.email || "",
        password: "",
        password_confirmation: "",
    });

    const applyGeneratedPassword = () => {
        const nextPassword = generatePassword();
        setChangePassword(true);
        setShowPassword(true);
        setShowConfirmPassword(true);
        setData("password", nextPassword);
        setData("password_confirmation", nextPassword);
    };

    const submit = (e) => {
        e.preventDefault();
        if (isEdit) {
            put(`/admin/users/${user.id}`, {
                onSuccess: () => {
                    reset();
                    onClose();
                },
            });
            return;
        }

        post("/admin/users", {
            onSuccess: () => {
                reset();
                onClose();
            },
        });
    };

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/40 p-4 backdrop-blur-sm">
            <div className="w-full max-w-2xl overflow-hidden rounded-3xl bg-white shadow-2xl">
                <div className="flex items-start justify-between border-b border-gray-100 px-6 py-5">
                    <div>
                        <h2 className="text-lg font-semibold text-gray-800">
                            {isEdit ? "Editar acceso del usuario" : "Nuevo usuario administrador"}
                        </h2>
                        <p className="mt-1 text-sm text-gray-500">
                            {isEdit
                                ? "Actualizá sus datos de acceso o generá una nueva contraseña si necesitás reiniciar credenciales."
                                : "Creá un acceso nuevo al panel y dejá la contraseña lista para compartirla con el equipo."}
                        </p>
                    </div>
                    <button
                        type="button"
                        onClick={onClose}
                        className="rounded-full p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600"
                    >
                        <Icon icon="solar:close-circle-outline" width={22} />
                    </button>
                </div>

                <form onSubmit={submit} className="space-y-5 px-6 py-6">
                    <div className="grid gap-5 lg:grid-cols-[1.1fr_0.9fr]">
                        <div className="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                            <div className="mb-4 flex items-center gap-2">
                                <div className="flex h-9 w-9 items-center justify-center rounded-xl bg-[#25A7CA]/10 text-[#25A7CA]">
                                    <Icon icon="solar:user-id-outline" width={18} />
                                </div>
                                <div>
                                    <p className="text-sm font-semibold text-gray-800">Datos de acceso</p>
                                    <p className="text-xs text-gray-400">Nombre visible y correo de ingreso.</p>
                                </div>
                            </div>

                            <div className="space-y-4">
                                <div>
                                    <label className="mb-1 block text-sm font-medium text-gray-700">Nombre</label>
                                    <input
                                        type="text"
                                        value={data.name}
                                        onChange={(e) => setData("name", e.target.value)}
                                        placeholder="Nombre completo"
                                        className="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30"
                                    />
                                    {errors.name && <p className="mt-1 text-xs text-red-500">{errors.name}</p>}
                                </div>

                                <div>
                                    <label className="mb-1 block text-sm font-medium text-gray-700">Correo electrónico</label>
                                    <input
                                        type="email"
                                        value={data.email}
                                        onChange={(e) => setData("email", e.target.value)}
                                        placeholder="admin@empresa.com"
                                        className="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30"
                                    />
                                    {errors.email && <p className="mt-1 text-xs text-red-500">{errors.email}</p>}
                                </div>
                            </div>
                        </div>

                        <div className="rounded-2xl border border-gray-100 bg-slate-50/70 p-5">
                            <div className="mb-4 flex items-center gap-2">
                                <div className="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-100 text-amber-600">
                                    <Icon icon="solar:shield-keyhole-outline" width={18} />
                                </div>
                                <div>
                                    <p className="text-sm font-semibold text-gray-800">Seguridad</p>
                                    <p className="text-xs text-gray-400">Gestioná la contraseña sin confundir pasos.</p>
                                </div>
                            </div>

                            {isEdit && !changePassword ? (
                                <div className="rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                                    La contraseña actual se conserva. Solo activá este bloque si necesitás cambiarla.
                                </div>
                            ) : (
                                <div className="rounded-2xl border border-amber-100 bg-amber-50 px-4 py-3 text-sm text-amber-700">
                                    Usá una contraseña segura y compartila por un canal confiable.
                                </div>
                            )}

                            {isEdit && (
                                <button
                                    type="button"
                                    onClick={() => {
                                        setChangePassword((current) => !current);
                                        setData("password", "");
                                        setData("password_confirmation", "");
                                    }}
                                    className={`mt-4 inline-flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-medium transition ${
                                        changePassword
                                            ? "bg-slate-200 text-slate-700 hover:bg-slate-300"
                                            : "bg-[#25A7CA] text-white hover:bg-[#1d96b8]"
                                    }`}
                                >
                                    <Icon icon={changePassword ? "solar:close-circle-outline" : "solar:key-outline"} width={16} />
                                    {changePassword ? "Mantener contraseña actual" : "Cambiar contraseña"}
                                </button>
                            )}

                            {(changePassword || !isEdit) && (
                                <div className="mt-4 space-y-4">
                                    <div className="flex flex-wrap gap-2">
                                        <button
                                            type="button"
                                            onClick={applyGeneratedPassword}
                                            className="inline-flex items-center gap-2 rounded-xl border border-[#25A7CA]/20 bg-[#25A7CA]/10 px-3 py-2 text-xs font-semibold text-[#25A7CA] transition hover:bg-[#25A7CA]/20"
                                        >
                                            <Icon icon="solar:magic-stick-3-outline" width={14} />
                                            Generar segura
                                        </button>
                                        <button
                                            type="button"
                                            onClick={() => {
                                                setShowPassword((current) => {
                                                    setShowConfirmPassword(!current);
                                                    return !current;
                                                });
                                            }}
                                            className="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-600 transition hover:bg-gray-50"
                                        >
                                            <Icon icon={showPassword ? "solar:eye-closed-outline" : "solar:eye-outline"} width={14} />
                                            {showPassword ? "Ocultar" : "Mostrar"}
                                        </button>
                                    </div>

                                    <div>
                                        <label className="mb-1 block text-sm font-medium text-gray-700">Contraseña</label>
                                        <input
                                            type={showPassword ? "text" : "password"}
                                            value={data.password}
                                            onChange={(e) => {
                                                setChangePassword(true);
                                                setData("password", e.target.value);
                                            }}
                                            placeholder="Mínimo 8 caracteres"
                                            className="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30"
                                        />
                                        {errors.password && <p className="mt-1 text-xs text-red-500">{errors.password}</p>}
                                    </div>

                                    <div>
                                        <label className="mb-1 block text-sm font-medium text-gray-700">Confirmar contraseña</label>
                                        <input
                                            type={showConfirmPassword ? "text" : "password"}
                                            value={data.password_confirmation}
                                            onChange={(e) => setData("password_confirmation", e.target.value)}
                                            placeholder="Repetir contraseña"
                                            className="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30"
                                        />
                                    </div>
                                </div>
                            )}
                        </div>
                    </div>

                    <div className="flex justify-end gap-3 border-t border-gray-100 pt-4">
                        <button
                            type="button"
                            onClick={onClose}
                            className="rounded-xl border border-gray-200 px-4 py-2 text-sm text-gray-600 transition hover:bg-gray-50"
                        >
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            disabled={processing}
                            className="inline-flex items-center gap-2 rounded-xl bg-[#25A7CA] px-5 py-2.5 text-sm font-medium text-white transition hover:bg-[#1d96b8] disabled:opacity-60"
                        >
                            <Icon icon={processing ? "svg-spinners:ring-resize" : "solar:check-circle-outline"} width={15} />
                            {processing ? "Guardando..." : isEdit ? "Guardar cambios" : "Crear usuario"}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
}

function ConfirmDelete({ user, onConfirm, onCancel, processing }) {
    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/40 p-4 backdrop-blur-sm">
            <div className="w-full max-w-md rounded-3xl bg-white p-6 shadow-2xl">
                <div className="mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-red-100 text-red-500">
                    <Icon icon="solar:trash-bin-trash-bold" width={22} />
                </div>
                <h3 className="text-lg font-semibold text-gray-800">Eliminar acceso administrador</h3>
                <p className="mt-2 text-sm leading-relaxed text-gray-500">
                    Vas a quitar el acceso de <strong>{user.name}</strong>. Ya no podrá entrar al panel con
                    <span className="font-medium text-gray-700"> {user.email}</span>.
                </p>

                <div className="mt-4 rounded-2xl border border-red-100 bg-red-50 px-4 py-3 text-sm text-red-700">
                    Esta acción no se puede deshacer desde esta pantalla.
                </div>

                <div className="mt-6 flex gap-3">
                    <button
                        type="button"
                        onClick={onCancel}
                        className="flex-1 rounded-xl border border-gray-200 py-2.5 text-sm text-gray-600 transition hover:bg-gray-50"
                    >
                        Cancelar
                    </button>
                    <button
                        type="button"
                        onClick={onConfirm}
                        disabled={processing}
                        className="flex-1 rounded-xl bg-red-500 py-2.5 text-sm font-medium text-white transition hover:bg-red-600 disabled:opacity-60"
                    >
                        {processing ? "Eliminando..." : "Eliminar acceso"}
                    </button>
                </div>
            </div>
        </div>
    );
}

function UserCard({ user, isCurrentUser, onEdit, onDelete }) {
    const canDelete = !user.is_principal && !isCurrentUser;

    return (
        <div className="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm transition hover:border-[#25A7CA]/20 hover:shadow-md">
            <div className="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div className="flex items-start gap-4">
                    <div className="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-2xl bg-[#25A7CA]/10 text-sm font-bold text-[#25A7CA]">
                        {initialsFor(user.name) || <Icon icon="solar:user-bold" width={18} />}
                    </div>

                    <div className="min-w-0">
                        <div className="flex flex-wrap items-center gap-2">
                            <p className="text-base font-semibold text-gray-800">{user.name}</p>
                            {user.is_principal && (
                                <span className="rounded-full border border-[#25A7CA]/20 bg-[#25A7CA]/10 px-2.5 py-1 text-[11px] font-semibold text-[#25A7CA]">
                                    Principal
                                </span>
                            )}
                            {isCurrentUser && (
                                <span className="rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-[11px] font-semibold text-emerald-700">
                                    Sesión actual
                                </span>
                            )}
                        </div>

                        <p className="mt-1 break-all text-sm text-gray-500">{user.email}</p>

                        <div className="mt-3 flex flex-wrap gap-3 text-xs text-gray-400">
                            <span className="inline-flex items-center gap-1.5">
                                <Icon icon="solar:calendar-outline" width={14} />
                                Alta: {user.created_at}
                            </span>
                            <span className="inline-flex items-center gap-1.5">
                                <Icon icon="solar:shield-user-outline" width={14} />
                                Acceso administrador activo
                            </span>
                            <span className={`inline-flex items-center gap-1.5 ${canDelete ? "text-amber-600" : "text-gray-400"}`}>
                                <Icon icon={canDelete ? "solar:trash-bin-minimalistic-outline" : "solar:lock-outline"} width={14} />
                                {canDelete ? "Se puede eliminar" : user.is_principal ? "Cuenta protegida" : "No podés eliminarte"}
                            </span>
                        </div>
                    </div>
                </div>

                <div className="flex flex-col gap-2 sm:flex-row lg:flex-col xl:flex-row">
                    <button
                        type="button"
                        onClick={onEdit}
                        className="inline-flex items-center justify-center gap-2 rounded-xl border border-[#25A7CA]/20 bg-[#25A7CA]/10 px-4 py-2.5 text-sm font-medium text-[#25A7CA] transition hover:bg-[#25A7CA]/20"
                    >
                        <Icon icon="solar:pen-outline" width={15} />
                        Editar acceso
                    </button>

                    {canDelete ? (
                        <button
                            type="button"
                            onClick={onDelete}
                            className="inline-flex items-center justify-center gap-2 rounded-xl border border-red-100 px-4 py-2.5 text-sm font-medium text-red-500 transition hover:bg-red-50"
                        >
                            <Icon icon="solar:trash-bin-trash-outline" width={15} />
                            Eliminar acceso
                        </button>
                    ) : (
                        <div className="inline-flex items-center justify-center gap-2 rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-400">
                            <Icon icon="solar:lock-outline" width={15} />
                            Protegido
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
}

export default function UsersIndex({ users }) {
    const { auth } = usePage().props;
    const currentUserId = auth?.user?.id;

    const [showCreate, setShowCreate] = useState(false);
    const [editUser, setEditUser] = useState(null);
    const [deleteUser, setDeleteUser] = useState(null);
    const [deleting, setDeleting] = useState(false);
    const [search, setSearch] = useState("");

    const { delete: destroy } = useForm();

    const normalizedSearch = search.trim().toLowerCase();
    const filteredUsers = users.filter((user) => {
        if (!normalizedSearch) return true;
        return (
            user.name.toLowerCase().includes(normalizedSearch) ||
            user.email.toLowerCase().includes(normalizedSearch)
        );
    });

    const principalCount = users.filter((user) => user.is_principal).length;
    const editableCount = users.filter((user) => !user.is_principal).length;

    const handleDelete = () => {
        setDeleting(true);
        destroy(`/admin/users/${deleteUser.id}`, {
            onFinish: () => {
                setDeleting(false);
                setDeleteUser(null);
            },
        });
    };

    return (
        <AdminLayout>
            <div className="max-w-5xl space-y-6">
                <div className="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <h1 className="text-2xl font-bold text-gray-800">Usuarios Admin</h1>
                        <p className="mt-1 text-sm text-gray-500">
                            Gestioná de forma simple quién entra al panel y qué accesos conviene mantener protegidos.
                        </p>
                    </div>

                    <button
                        type="button"
                        onClick={() => setShowCreate(true)}
                        className="inline-flex items-center justify-center gap-2 rounded-2xl bg-[#25A7CA] px-4 py-3 text-sm font-medium text-white transition hover:bg-[#1d96b8]"
                    >
                        <Icon icon="solar:user-plus-rounded-outline" width={17} />
                        Nuevo usuario admin
                    </button>
                </div>

                <div className="grid gap-4 md:grid-cols-3">
                    <StatCard icon="solar:users-group-two-rounded-outline" label="Usuarios con acceso" value={users.length} />
                    <StatCard icon="solar:shield-user-outline" label="Cuentas editables" value={editableCount} tone="emerald" />
                    <StatCard icon="solar:lock-keyhole-outline" label="Cuentas protegidas" value={principalCount} tone="amber" />
                </div>

                <div className="rounded-2xl border border-amber-100 bg-amber-50 px-5 py-4">
                    <div className="flex items-start gap-3">
                        <Icon icon="solar:shield-keyhole-outline" width={18} className="mt-0.5 flex-shrink-0 text-amber-500" />
                        <p className="text-sm leading-relaxed text-amber-800">
                            La cuenta <strong>principal</strong> queda protegida para evitar bloqueos del panel.
                            También te marcamos tu <strong>sesión actual</strong> para que no borres el acceso con el que estás trabajando.
                        </p>
                    </div>
                </div>

                <div className="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
                    <div className="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div className="relative w-full lg:max-w-md">
                            <Icon icon="solar:magnifer-outline" width={17} className="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" />
                            <input
                                type="text"
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                placeholder="Buscar por nombre o correo..."
                                className="w-full rounded-2xl border border-gray-200 py-3 pl-10 pr-4 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30"
                            />
                        </div>

                        <div className="flex flex-wrap items-center gap-2 text-xs text-gray-500">
                            <span className="rounded-full bg-gray-100 px-3 py-1.5 font-medium">
                                {filteredUsers.length} resultado{filteredUsers.length === 1 ? "" : "s"}
                            </span>
                            {normalizedSearch && (
                                <button
                                    type="button"
                                    onClick={() => setSearch("")}
                                    className="rounded-full border border-gray-200 px-3 py-1.5 font-medium text-gray-600 transition hover:bg-gray-50"
                                >
                                    Limpiar búsqueda
                                </button>
                            )}
                        </div>
                    </div>
                </div>

                <div className="space-y-3">
                    {filteredUsers.length > 0 ? (
                        filteredUsers.map((user) => (
                            <UserCard
                                key={user.id}
                                user={user}
                                isCurrentUser={Number(currentUserId) === Number(user.id)}
                                onEdit={() => setEditUser(user)}
                                onDelete={() => setDeleteUser(user)}
                            />
                        ))
                    ) : (
                        <div className="rounded-2xl border border-dashed border-gray-200 bg-white px-6 py-14 text-center shadow-sm">
                            <div className="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-gray-100 text-gray-400">
                                <Icon icon="solar:magnifer-outline" width={22} />
                            </div>
                            <p className="text-base font-semibold text-gray-700">No encontramos usuarios con ese criterio</p>
                            <p className="mt-1 text-sm text-gray-400">
                                Probá con otro nombre o correo, o limpiá la búsqueda para volver a ver todos.
                            </p>
                        </div>
                    )}
                </div>
            </div>

            {showCreate && <UserModal onClose={() => setShowCreate(false)} />}
            {editUser && <UserModal user={editUser} onClose={() => setEditUser(null)} />}
            {deleteUser && (
                <ConfirmDelete
                    user={deleteUser}
                    onConfirm={handleDelete}
                    onCancel={() => setDeleteUser(null)}
                    processing={deleting}
                />
            )}
        </AdminLayout>
    );
}
