import AdminLayout from "@/Layouts/AdminLayout";
import { Icon } from "@iconify/react";
import { router } from "@inertiajs/react";

export default function Dashboard({ stats, recent_messages }) {
    recent_messages = recent_messages ?? [];
    const cards = [
        { label: "Productos",    value: stats.products,        icon: "solar:box-outline",                   color: "bg-blue-50 text-blue-600",     url: "/admin/products" },
        { label: "Marcas",       value: stats.brands,          icon: "solar:tag-outline",                   color: "bg-purple-50 text-purple-600", url: "/admin/brands" },
        { label: "Cajas",        value: stats.gearboxes,       icon: "solar:settings-minimalistic-outline", color: "bg-cyan-50 text-cyan-600",     url: "/admin/gearboxes" },
        { label: "Mensajes",     value: stats.messages,        icon: "solar:letter-outline",                color: "bg-red-50 text-red-600",       url: "/admin/contact-messages", badge: stats.unread_messages },
        { label: "Suscriptores", value: stats.subscribers,     icon: "solar:mailbox-outline",               color: "bg-teal-50 text-teal-600",     url: "/admin/newsletter" },
    ];

    return (
        <AdminLayout>
            <div>
                <h1 className="text-2xl font-bold text-gray-800 mb-1">Dashboard</h1>
                <p className="text-sm text-gray-500 mb-8">Panel de administración — Gran Sasso</p>

                {/* Stats */}
                <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 mb-10">
                    {cards.map((card) => (
                        <button
                            key={card.label}
                            onClick={() => router.visit(card.url)}
                            className="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm relative text-left hover:shadow-md transition-shadow w-full"
                        >
                            {card.badge > 0 && (
                                <span className="absolute top-3 right-3 bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full">
                                    {card.badge} nuevos
                                </span>
                            )}
                            <div className={`w-10 h-10 rounded-xl flex items-center justify-center mb-3 ${card.color}`}>
                                <Icon icon={card.icon} width={20} />
                            </div>
                            <div className="text-2xl font-bold text-gray-800">{card.value ?? 0}</div>
                            <div className="text-xs text-gray-500 mt-0.5">{card.label}</div>
                        </button>
                    ))}
                </div>

                {/* Mensajes recientes */}
                <div className="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden mb-6">
                    <div className="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                        <h2 className="font-semibold text-gray-800">Últimos mensajes de contacto</h2>
                        <button onClick={() => router.visit("/admin/contact-messages")} className="text-xs text-[#25A7CA] hover:underline">
                            Ver todos
                        </button>
                    </div>
                    <div className="divide-y divide-gray-50">
                        {recent_messages.length === 0 && (
                            <p className="text-sm text-gray-400 px-6 py-8 text-center">Sin mensajes recientes.</p>
                        )}
                        {recent_messages.map((m) => (
                            <div key={m.id} onClick={() => router.visit(`/admin/contact-messages/${m.id}`)}
                                className="flex items-center gap-3 px-6 py-3 cursor-pointer hover:bg-gray-50/60">
                                <div className="w-8 h-8 rounded-full bg-[#25A7CA]/10 flex items-center justify-center text-[#25A7CA] flex-shrink-0">
                                    <Icon icon="solar:letter-outline" width={14} />
                                </div>
                                <div className="flex-1 min-w-0">
                                    <p className="text-sm font-medium text-gray-800 truncate">{m.name}</p>
                                    <p className="text-xs text-gray-400">{m.company ? `${m.company} · ` : ""}{m.email}</p>
                                </div>
                                <div className="flex items-center gap-2 flex-shrink-0">
                                    <span className="text-xs text-gray-400">{m.created_at}</span>
                                    {!m.is_read && <span className="w-2 h-2 rounded-full bg-[#25A7CA]" />}
                                </div>
                            </div>
                        ))}
                    </div>
                </div>

                {/* Accesos rápidos */}
                <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                    {[
                        { label: "Nuevo Producto",    icon: "solar:add-circle-outline",    url: "/admin/products/create",    color: "text-blue-600" },
                        { label: "Importar Excel",    icon: "solar:import-outline",        url: "/admin/import",             color: "text-green-600" },
                        { label: "Editar Sliders",    icon: "solar:gallery-wide-outline",  url: "/admin/hero",               color: "text-purple-600" },
                        { label: "Ver Mensajes",      icon: "solar:letter-unread-outline", url: "/admin/contact-messages",   color: "text-red-600" },
                    ].map((q) => (
                        <button key={q.label} onClick={() => router.visit(q.url)}
                            className="bg-white border border-gray-100 rounded-2xl p-4 flex items-center gap-3 hover:shadow-sm transition text-left w-full">
                            <Icon icon={q.icon} width={20} className={q.color} />
                            <span className="text-sm text-gray-700 font-medium">{q.label}</span>
                        </button>
                    ))}
                </div>
            </div>
        </AdminLayout>
    );
}
