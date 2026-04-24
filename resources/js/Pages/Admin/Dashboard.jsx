import AdminLayout from "@/Layouts/AdminLayout";
import { Icon } from "@iconify/react";
import { router } from "@inertiajs/react";

function TrendBadge({ value, positiveLabel = "vs. período anterior" }) {
    if (value === null || value === undefined) {
        return (
            <span className="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-1 text-[11px] font-semibold text-gray-500">
                Sin base comparativa
            </span>
        );
    }

    const isPositive = value >= 0;

    return (
        <span
            className={`inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-[11px] font-semibold ${
                isPositive ? "bg-emerald-100 text-emerald-700" : "bg-rose-100 text-rose-700"
            }`}
        >
            <Icon icon={isPositive ? "solar:arrow-up-outline" : "solar:arrow-down-outline"} width={12} />
            {Math.abs(value)}% {positiveLabel}
        </span>
    );
}

function MetricCard({ title, value, caption, icon, tone = "blue", onClick }) {
    const tones = {
        blue: "bg-[#25A7CA]/10 text-[#25A7CA]",
        emerald: "bg-emerald-100 text-emerald-600",
        amber: "bg-amber-100 text-amber-600",
        violet: "bg-violet-100 text-violet-600",
        rose: "bg-rose-100 text-rose-600",
    };

    return (
        <button
            onClick={onClick}
            className="w-full rounded-2xl border border-gray-100 bg-white p-5 text-left shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
        >
            <div className="mb-4 flex items-start justify-between gap-3">
                <div className={`flex h-11 w-11 items-center justify-center rounded-2xl ${tones[tone]}`}>
                    <Icon icon={icon} width={20} />
                </div>
                <Icon icon="solar:arrow-right-up-outline" width={16} className="text-gray-300" />
            </div>
            <p className="text-sm font-medium text-gray-500">{title}</p>
            <p className="mt-1 text-3xl font-bold tracking-tight text-gray-900">{value ?? 0}</p>
            {caption && <p className="mt-2 text-xs text-gray-400">{caption}</p>}
        </button>
    );
}

function MiniBarChart({ title, subtitle, data, color = "#25A7CA" }) {
    const max = Math.max(...data.map((item) => item.value), 1);

    return (
        <div className="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
            <div className="mb-5">
                <h3 className="text-base font-semibold text-gray-800">{title}</h3>
                <p className="mt-1 text-sm text-gray-400">{subtitle}</p>
            </div>

            <div className="flex h-56 items-end gap-3">
                {data.map((item) => (
                    <div key={`${title}-${item.label}`} className="flex flex-1 flex-col items-center justify-end gap-3">
                        <div className="flex h-44 w-full items-end rounded-2xl bg-gray-50 px-2 py-2">
                            <div
                                className="w-full rounded-xl"
                                style={{
                                    height: `${Math.max((item.value / max) * 100, item.value > 0 ? 10 : 0)}%`,
                                    background: color,
                                }}
                            />
                        </div>
                        <div className="text-center">
                            <p className="text-sm font-semibold text-gray-700">{item.value}</p>
                            <p className="text-[11px] uppercase tracking-wide text-gray-400">{item.label}</p>
                        </div>
                    </div>
                ))}
            </div>
        </div>
    );
}

function DistributionCard({ title, subtitle, items }) {
    const total = items.reduce((sum, item) => sum + item.value, 0) || 1;

    return (
        <div className="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
            <div className="mb-5">
                <h3 className="text-base font-semibold text-gray-800">{title}</h3>
                <p className="mt-1 text-sm text-gray-400">{subtitle}</p>
            </div>

            <div className="space-y-4">
                {items.map((item) => (
                    <div key={item.label}>
                        <div className="mb-1 flex items-center justify-between gap-4">
                            <div className="flex items-center gap-2">
                                <span className="h-2.5 w-2.5 rounded-full" style={{ backgroundColor: item.color }} />
                                <span className="text-sm font-medium text-gray-700">{item.label}</span>
                            </div>
                            <span className="text-sm font-semibold text-gray-800">{item.value}</span>
                        </div>
                        <div className="h-2 w-full overflow-hidden rounded-full bg-gray-100">
                            <div
                                className="h-full rounded-full"
                                style={{ width: `${(item.value / total) * 100}%`, backgroundColor: item.color }}
                            />
                        </div>
                    </div>
                ))}
            </div>
        </div>
    );
}

function ActivityPanel({ title, subtitle, items, empty, actionLabel, actionUrl, icon, renderMeta }) {
    return (
        <div className="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
            <div className="flex items-center justify-between border-b border-gray-100 px-6 py-4">
                <div>
                    <h3 className="text-base font-semibold text-gray-800">{title}</h3>
                    <p className="mt-0.5 text-sm text-gray-400">{subtitle}</p>
                </div>
                {actionUrl && (
                    <button
                        onClick={() => router.visit(actionUrl)}
                        className="text-xs font-medium text-[#25A7CA] transition hover:underline"
                    >
                        {actionLabel}
                    </button>
                )}
            </div>

            <div className="divide-y divide-gray-50">
                {items.length === 0 && <p className="px-6 py-8 text-center text-sm text-gray-400">{empty}</p>}
                {items.map((item) => (
                    <div key={`${title}-${item.id}`} className="flex items-center gap-3 px-6 py-4">
                        <div className="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-2xl bg-[#25A7CA]/10 text-[#25A7CA]">
                            <Icon icon={icon} width={18} />
                        </div>
                        <div className="min-w-0 flex-1">
                            <p className="truncate text-sm font-semibold text-gray-800">{item.name || item.email || "Sin nombre"}</p>
                            <p className="truncate text-xs text-gray-400">{renderMeta(item)}</p>
                        </div>
                        <div className="text-right">
                            <p className="text-xs text-gray-400">{item.created_at || item.logged_in_at}</p>
                        </div>
                    </div>
                ))}
            </div>
        </div>
    );
}

function buildLinePath(data, key, width, height, padding) {
    const maxValue = Math.max(...data.map((item) => item[key] ?? 0), 1);
    const innerWidth = width - padding.left - padding.right;
    const innerHeight = height - padding.top - padding.bottom;

    return data
        .map((item, index) => {
            const x = padding.left + (index / Math.max(data.length - 1, 1)) * innerWidth;
            const y = padding.top + innerHeight - ((item[key] ?? 0) / maxValue) * innerHeight;
            return `${index === 0 ? "M" : "L"} ${x} ${y}`;
        })
        .join(" ");
}

function buildAreaPath(data, key, width, height, padding) {
    if (!data.length) return "";

    const maxValue = Math.max(...data.map((item) => item[key] ?? 0), 1);
    const innerWidth = width - padding.left - padding.right;
    const innerHeight = height - padding.top - padding.bottom;

    const points = data.map((item, index) => {
        const x = padding.left + (index / Math.max(data.length - 1, 1)) * innerWidth;
        const y = padding.top + innerHeight - ((item[key] ?? 0) / maxValue) * innerHeight;
        return { x, y };
    });

    const bottomY = padding.top + innerHeight;
    const firstPoint = points[0];
    const lastPoint = points[points.length - 1];

    return [
        `M ${firstPoint.x} ${bottomY}`,
        ...points.map((point, index) => `${index === 0 ? "L" : "L"} ${point.x} ${point.y}`),
        `L ${lastPoint.x} ${bottomY}`,
        "Z",
    ].join(" ");
}

function TrafficPerformanceChart({ data = [] }) {
    const width = 860;
    const height = 320;
    const padding = { top: 28, right: 18, bottom: 42, left: 18 };
    const chartData = data.length ? data : [{ label: "Sin datos", sessions: 0, visitors: 0 }];
    const maxValue = Math.max(
        ...chartData.flatMap((item) => [item.sessions ?? 0, item.visitors ?? 0]),
        1,
    );
    const innerHeight = height - padding.top - padding.bottom;
    const gridSteps = 4;

    const sessionsLine = buildLinePath(chartData, "sessions", width, height, padding);
    const visitorsLine = buildLinePath(chartData, "visitors", width, height, padding);
    const sessionsArea = buildAreaPath(chartData, "sessions", width, height, padding);

    return (
        <div className="overflow-hidden rounded-[28px] border border-[#DDE7F3] bg-white shadow-sm">
            <div className="border-b border-[#EEF3F8] px-6 py-5">
                <div className="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h3 className="text-lg font-semibold tracking-tight text-gray-900">Rendimiento de tráfico</h3>
                        <p className="mt-1 text-sm text-gray-400">
                            Evolución diaria de visitas y visitantes únicos durante los últimos 30 días.
                        </p>
                    </div>

                    <div className="flex flex-wrap items-center gap-4 text-sm">
                        <div className="flex items-center gap-2">
                            <span className="h-2.5 w-2.5 rounded-full bg-[#25A7CA]" />
                            <span className="font-medium text-gray-600">Visitas</span>
                        </div>
                        <div className="flex items-center gap-2">
                            <span className="h-2.5 w-2.5 rounded-full bg-[#1F589C]" />
                            <span className="font-medium text-gray-600">Visitantes únicos</span>
                        </div>
                    </div>
                </div>
            </div>

            <div className="p-6">
                <div className="rounded-[24px] bg-gradient-to-br from-[#F7FBFF] via-white to-[#F3F8FD] p-4">
                    <svg viewBox={`0 0 ${width} ${height}`} className="h-[320px] w-full">
                        <defs>
                            <linearGradient id="trafficArea" x1="0" x2="0" y1="0" y2="1">
                                <stop offset="0%" stopColor="#25A7CA" stopOpacity="0.24" />
                                <stop offset="100%" stopColor="#25A7CA" stopOpacity="0.02" />
                            </linearGradient>
                        </defs>

                        {Array.from({ length: gridSteps + 1 }).map((_, index) => {
                            const y = padding.top + (innerHeight / gridSteps) * index;
                            const value = Math.round(maxValue - (maxValue / gridSteps) * index);

                            return (
                                <g key={`grid-${index}`}>
                                    <line
                                        x1={padding.left}
                                        y1={y}
                                        x2={width - padding.right}
                                        y2={y}
                                        stroke="#E7EEF6"
                                        strokeDasharray="4 6"
                                    />
                                    <text x={width - padding.right - 4} y={y - 8} textAnchor="end" fontSize="11" fill="#94A3B8">
                                        {value}
                                    </text>
                                </g>
                            );
                        })}

                        <path d={sessionsArea} fill="url(#trafficArea)" />
                        <path d={sessionsLine} fill="none" stroke="#25A7CA" strokeWidth="4" strokeLinecap="round" />
                        <path d={visitorsLine} fill="none" stroke="#1F589C" strokeWidth="3" strokeLinecap="round" />

                        {chartData.map((item, index) => {
                            const x = padding.left + (index / Math.max(chartData.length - 1, 1)) * (width - padding.left - padding.right);
                            const sessionsY = padding.top + innerHeight - ((item.sessions ?? 0) / maxValue) * innerHeight;
                            const visitorsY = padding.top + innerHeight - ((item.visitors ?? 0) / maxValue) * innerHeight;
                            const isEdgeLabel = index % Math.ceil(chartData.length / 6) === 0 || index === chartData.length - 1;

                            return (
                                <g key={`point-${item.label}-${index}`}>
                                    <circle cx={x} cy={sessionsY} r="4.5" fill="#25A7CA" stroke="#fff" strokeWidth="2" />
                                    <circle cx={x} cy={visitorsY} r="4" fill="#1F589C" stroke="#fff" strokeWidth="2" />
                                    {isEdgeLabel && (
                                        <text x={x} y={height - 12} textAnchor="middle" fontSize="11" fill="#94A3B8">
                                            {item.label}
                                        </text>
                                    )}
                                </g>
                            );
                        })}
                    </svg>
                </div>
            </div>
        </div>
    );
}

function TrafficSummaryCard({ stats }) {
    const trafficItems = [
        {
            label: "Visitas web 7 días",
            value: stats.visits_7d,
            note: "Páginas públicas y privadas registradas",
        },
        {
            label: "Visitantes 7 días",
            value: stats.unique_site_visitors_7d,
            note: "Sesiones únicas detectadas en la web",
        },
        {
            label: "Visitas hoy",
            value: stats.visits_today,
            note: "Actividad total acumulada del día",
        },
        {
            label: "Promedio diario",
            value: stats.avg_daily_sessions_30d,
            note: "Media de visitas durante 30 días",
        },
    ];

    return (
        <div className="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
            <div className="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <h3 className="text-base font-semibold text-gray-800">Tráfico de clientes</h3>
                    <p className="mt-1 text-sm text-gray-400">
                        Vista ejecutiva de visitas reales, usuarios únicos e intensidad de uso del sitio y del acceso privado.
                    </p>
                </div>

                <div className="flex flex-wrap gap-2">
                    <TrendBadge value={stats.traffic_trend_7d} />
                    <TrendBadge value={stats.visitor_trend_7d} positiveLabel="visitantes vs. período anterior" />
                </div>
            </div>

            <div className="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
                {trafficItems.map((item) => (
                    <div key={item.label} className="rounded-2xl bg-[#F7FAFC] px-4 py-4">
                        <p className="text-xs font-semibold uppercase tracking-[0.12em] text-gray-400">{item.label}</p>
                        <p className="mt-2 text-3xl font-bold tracking-tight text-gray-900">{item.value ?? 0}</p>
                        <p className="mt-2 text-xs leading-5 text-gray-500">{item.note}</p>
                    </div>
                ))}
            </div>

            <div className="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-3">
                <div className="rounded-2xl border border-gray-100 px-4 py-4">
                    <p className="text-xs font-semibold uppercase tracking-[0.12em] text-gray-400">Activos 30 días</p>
                    <p className="mt-2 text-xl font-bold text-gray-900">{stats.active_clients_30d}</p>
                    <p className="mt-1 text-xs text-gray-500">Clientes distintos con al menos un ingreso reciente</p>
                </div>

                <div className="rounded-2xl border border-gray-100 px-4 py-4">
                    <p className="text-xs font-semibold uppercase tracking-[0.12em] text-gray-400">Visitas 30 días</p>
                    <p className="mt-2 text-xl font-bold text-gray-900">{stats.site_visits_30d}</p>
                    <p className="mt-1 text-xs text-gray-500">Volumen total de navegación registrado en la web</p>
                </div>

                <div className="rounded-2xl border border-gray-100 px-4 py-4">
                    <p className="text-xs font-semibold uppercase tracking-[0.12em] text-gray-400">Frecuencia privada</p>
                    <p className="mt-2 text-xl font-bold text-gray-900">{stats.engagement_rate_30d}x</p>
                    <p className="mt-1 text-xs text-gray-500">Sesiones promedio por cliente activo en 30 días</p>
                </div>
            </div>
        </div>
    );
}

export default function Dashboard({ stats, charts, recent_messages, recent_logins }) {
    const executiveCards = [
        {
            title: "Ingresos privados 7 días",
            value: stats.logins_7d,
            caption: stats.latest_login_at ? `Último ingreso: ${stats.latest_login_at}` : "Sin ingresos registrados todavía",
            icon: "solar:login-3-outline",
            tone: "blue",
            onClick: () => router.visit("/admin/private-users"),
        },
        {
            title: "Visitantes web 7 días",
            value: stats.unique_site_visitors_7d,
            caption: stats.visitor_trend_7d === null
                ? "Sesiones únicas durante los últimos 7 días"
                : `${stats.visitor_trend_7d >= 0 ? "+" : ""}${stats.visitor_trend_7d}% vs. 7 días anteriores`,
            icon: "solar:chart-2-outline",
            tone: "emerald",
            onClick: () => router.visit("/admin/private-users"),
        },
        {
            title: "Clientes que ingresaron",
            value: stats.unique_visitors_7d,
            caption: "Usuarios únicos con login en zona privada",
            icon: "solar:users-group-rounded-outline",
            tone: "amber",
            onClick: () => router.visit("/admin/private-users"),
        },
        {
            title: "Clientes activos en 30 días",
            value: stats.active_clients_30d,
            caption: `${stats.approved_clients} clientes aprobados en total`,
            icon: "solar:user-id-outline",
            tone: "violet",
            onClick: () => router.visit("/admin/private-users"),
        },
        {
            title: "Mensajes pendientes",
            value: stats.unread_messages,
            caption: `${stats.messages} mensajes recibidos`,
            icon: "solar:letter-unread-outline",
            tone: "rose",
            onClick: () => router.visit("/admin/contact-messages"),
        },
    ];

    const catalogCards = [
        { title: "Productos", value: stats.products, icon: "solar:box-outline", tone: "blue", url: "/admin/products" },
        { title: "Marcas", value: stats.brands, icon: "solar:tag-outline", tone: "violet", url: "/admin/brands" },
        { title: "Cajas", value: stats.gearboxes, icon: "solar:settings-minimalistic-outline", tone: "amber", url: "/admin/gearboxes" },
        { title: "Clientes pendientes", value: stats.pending_clients, icon: "solar:user-check-outline", tone: "emerald", url: "/admin/private-users?status=pending" },
    ];

    return (
        <AdminLayout>
            <div className="space-y-8">
                <section className="rounded-[28px] bg-gradient-to-br from-[#0D2038] via-[#1F589C] to-[#25A7CA] p-8 text-white shadow-xl">
                    <div className="flex flex-col gap-8 lg:flex-row lg:items-end lg:justify-between">
                        <div className="max-w-2xl">
                            <p className="text-sm font-semibold uppercase tracking-[0.2em] text-white/70">Resumen general</p>
                            <h1 className="mt-3 text-4xl font-bold tracking-tight">Panel ejecutivo de administración</h1>
                            <p className="mt-3 max-w-xl text-sm leading-6 text-white/75">
                                Seguimiento comercial, catálogo y actividad de clientes desde una sola vista. Los accesos
                                de la zona privada ahora empiezan a registrarse para alimentar estas métricas.
                            </p>
                        </div>

                        <div className="grid grid-cols-2 gap-4 sm:grid-cols-4">
                            {catalogCards.map((card) => (
                                <button
                                    key={card.title}
                                    onClick={() => router.visit(card.url)}
                                    className="rounded-2xl border border-white/10 bg-white/10 p-4 text-left backdrop-blur transition hover:bg-white/15"
                                >
                                    <div className="mb-3 flex h-10 w-10 items-center justify-center rounded-2xl bg-white/10">
                                        <Icon icon={card.icon} width={18} />
                                    </div>
                                    <p className="text-2xl font-bold">{card.value}</p>
                                    <p className="mt-1 text-xs text-white/70">{card.title}</p>
                                </button>
                            ))}
                        </div>
                    </div>
                </section>

                <section className="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                    {executiveCards.map((card) => (
                        <MetricCard key={card.title} {...card} />
                    ))}
                </section>

                <TrafficSummaryCard stats={stats} />

                <TrafficPerformanceChart data={charts.daily_traffic ?? []} />

                <section className="grid grid-cols-1 gap-6 xl:grid-cols-[1.5fr_1fr]">
                    <MiniBarChart
                        title="Ingresos de clientes"
                        subtitle="Últimos 7 días de accesos registrados en la zona privada"
                        data={charts.daily_logins ?? []}
                        color="#25A7CA"
                    />
                    <DistributionCard
                        title="Estado de clientes"
                        subtitle="Distribución actual de solicitudes y accesos"
                        items={charts.client_status ?? []}
                    />
                </section>

                <section className="grid grid-cols-1 gap-6 xl:grid-cols-[1.5fr_1fr]">
                    <MiniBarChart
                        title="Altas de clientes"
                        subtitle="Nuevos registros durante los últimos 6 meses"
                        data={charts.monthly_registrations ?? []}
                        color="#1F589C"
                    />
                    <div className="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                        <h3 className="text-base font-semibold text-gray-800">Accesos rápidos</h3>
                        <p className="mt-1 text-sm text-gray-400">Acciones frecuentes para trabajar más rápido en el panel.</p>

                        <div className="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2">
                            {[
                                { label: "Nuevo producto", icon: "solar:add-circle-outline", url: "/admin/products/create", color: "text-[#25A7CA]" },
                                { label: "Importar Excel", icon: "solar:import-outline", url: "/admin/import", color: "text-emerald-600" },
                                { label: "Clientes privados", icon: "solar:users-group-rounded-outline", url: "/admin/private-users", color: "text-violet-600" },
                                { label: "Mensajes de contacto", icon: "solar:letter-outline", url: "/admin/contact-messages", color: "text-rose-600" },
                                { label: "Newsletter", icon: "solar:mailbox-outline", url: "/admin/newsletter", color: "text-sky-600" },
                            ].map((shortcut) => (
                                <button
                                    key={shortcut.label}
                                    onClick={() => router.visit(shortcut.url)}
                                    className="flex items-center gap-3 rounded-2xl border border-gray-100 px-4 py-4 text-left transition hover:bg-gray-50"
                                >
                                    <Icon icon={shortcut.icon} width={20} className={shortcut.color} />
                                    <span className="text-sm font-medium text-gray-700">{shortcut.label}</span>
                                </button>
                            ))}
                        </div>
                    </div>
                </section>

                <section className="grid grid-cols-1 gap-6 xl:grid-cols-2">
                    <ActivityPanel
                        title="Últimos mensajes"
                        subtitle="Consultas recibidas desde la web pública"
                        items={recent_messages ?? []}
                        empty="Todavía no hay mensajes recientes."
                        actionLabel="Ver todos"
                        actionUrl="/admin/contact-messages"
                        icon="solar:letter-outline"
                        renderMeta={(item) => `${item.company ? `${item.company} · ` : ""}${item.email}`}
                    />

                    <ActivityPanel
                        title="Últimos ingresos"
                        subtitle="Actividad reciente de clientes en la zona privada"
                        items={recent_logins ?? []}
                        empty="Todavía no hay ingresos registrados."
                        actionLabel="Ver clientes"
                        actionUrl="/admin/private-users"
                        icon="solar:login-3-outline"
                        renderMeta={(item) => `${item.email ?? "Sin email"}${item.ip_address ? ` · ${item.ip_address}` : ""}`}
                    />
                </section>
            </div>
        </AdminLayout>
    );
}
