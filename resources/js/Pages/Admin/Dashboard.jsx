import AdminLayout from "@/Layouts/AdminLayout";
import { Icon } from "@iconify/react";
import { router } from "@inertiajs/react";

export default function Dashboard({ stats, recent_quotes, recent_messages }) {
    const cards = [
        { label: "Productos",      value: stats.products,     icon: "solar:box-outline",           color: "bg-blue-50 text-blue-600" },
        { label: "Categorías",     value: stats.categories,   icon: "solar:folder-outline",        color: "bg-purple-50 text-purple-600" },
        { label: "Sectores",       value: stats.industries,   icon: "solar:buildings-outline",     color: "bg-cyan-50 text-cyan-600" },
        { label: "Novedades",      value: stats.posts,        icon: "solar:document-text-outline", color: "bg-green-50 text-green-600" },
        { label: "Cotizaciones",   value: stats.quotes,       icon: "solar:document-add-outline",  color: "bg-orange-50 text-orange-600",   badge: stats.unread_quotes },
        { label: "Mensajes",       value: stats.messages,     icon: "solar:letter-outline",        color: "bg-red-50 text-red-600",         badge: stats.unread_messages },
        { label: "Suscriptores",   value: stats.subscribers,  icon: "solar:mailbox-outline",       color: "bg-teal-50 text-teal-600" },
    ];

    return (
        <AdminLayout>
            <div>
                <h1 className="text-2xl font-bold text-gray-800 mb-1">Dashboard</h1>
                <p className="text-sm text-gray-500 mb-8">Resumen general del panel administrativo</p>

                {/* Stats Grid */}
                <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 mb-10">
                    {cards.map((card) => (
                        <div key={card.label} className="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm relative">
                            {card.badge > 0 && (
                                <span className="absolute top-3 right-3 bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full">
                                    {card.badge} nuevos
                                </span>
                            )}
                            <div className={`w-10 h-10 rounded-xl flex items-center justify-center mb-3 ${card.color}`}>
                                <Icon icon={card.icon} width={20} />
                            </div>
                            <div className="text-2xl font-bold text-gray-800">{card.value}</div>
                            <div className="text-xs text-gray-500 mt-0.5">{card.label}</div>
                        </div>
                    ))}
                </div>

                {/* Recent */}
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {/* Recent Quotes */}
                    <div className="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                        <div className="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                            <h2 className="font-semibold text-gray-800">Últimas Cotizaciones</h2>
                            <button
                                onClick={() => router.visit('/admin/comercial/quotations')}
                                className="text-xs text-[#25A7CA] hover:underline"
                            >
                                Ver todas
                            </button>
                        </div>
                        <div className="divide-y divide-gray-50">
                            {recent_quotes.length === 0 && (
                                <p className="text-sm text-gray-400 px-6 py-4">Sin cotizaciones recientes.</p>
                            )}
                            {recent_quotes.map((q) => (
                                <div key={q.id} className="flex items-center gap-3 px-6 py-3">
                                    <div className="w-8 h-8 rounded-full bg-orange-100 flex items-center justify-center text-orange-600">
                                        <Icon icon="solar:user-outline" width={14} />
                                    </div>
                                    <div className="flex-1 min-w-0">
                                        <p className="text-sm font-medium text-gray-800 truncate">{q.name}</p>
                                        <p className="text-xs text-gray-400">{q.company || q.email}</p>
                                    </div>
                                    {!q.is_read && (
                                        <span className="w-2 h-2 rounded-full bg-orange-400 flex-shrink-0" />
                                    )}
                                </div>
                            ))}
                        </div>
                    </div>

                    {/* Recent Messages */}
                    <div className="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                        <div className="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                            <h2 className="font-semibold text-gray-800">Últimos Mensajes</h2>
                            <button
                                onClick={() => router.visit('/admin/contact-messages')}
                                className="text-xs text-[#25A7CA] hover:underline"
                            >
                                Ver todos
                            </button>
                        </div>
                        <div className="divide-y divide-gray-50">
                            {recent_messages.length === 0 && (
                                <p className="text-sm text-gray-400 px-6 py-4">Sin mensajes recientes.</p>
                            )}
                            {recent_messages.map((m) => (
                                <div key={m.id} className="flex items-center gap-3 px-6 py-3">
                                    <div className="w-8 h-8 rounded-full bg-[#25A7CA]/10 flex items-center justify-center text-[#25A7CA]">
                                        <Icon icon="solar:letter-outline" width={14} />
                                    </div>
                                    <div className="flex-1 min-w-0">
                                        <p className="text-sm font-medium text-gray-800 truncate">{m.name}</p>
                                        <p className="text-xs text-gray-400">{m.email}</p>
                                    </div>
                                    {!m.is_read && (
                                        <span className="w-2 h-2 rounded-full bg-[#25A7CA] flex-shrink-0" />
                                    )}
                                </div>
                            ))}
                        </div>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
