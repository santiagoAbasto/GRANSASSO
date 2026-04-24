import { useEffect, useState } from "react";
import { usePage, router } from "@inertiajs/react";
import { Icon } from "@iconify/react";

export default function Sidebar() {
    const page = usePage();
    const auth = page.props?.auth;
    const currentUrl = page.url ?? page.props?.url ?? "";

    const sections = [
        {
            id: "inicio",
            title: "Inicio",
            icon: "solar:home-smile-outline",
            children: [
                { title: "Resumen general", icon: "solar:home-outline", url: "/admin/dashboard" },
                { title: "Banners", icon: "solar:gallery-wide-outline", url: "/admin/hero" },
                { title: "Nosotros en inicio", icon: "solar:info-circle-outline", url: "/admin/home-about" },
                { title: "Importamos/calidad", icon: "solar:medal-ribbons-star-outline", url: "/admin/home-calidad" },
                { title: "Imágenes en inicio", icon: "solar:gallery-outline", url: "/admin/home-gallery" },
            ],
        },
        {
            id: "empresa",
            title: "Empresa",
            icon: "solar:users-group-rounded-outline",
            children: [
                { title: "Contenido", icon: "solar:document-text-outline", url: "/admin/empresa" },
            ],
        },
        {
            id: "productos",
            title: "Productos",
            icon: "solar:box-outline",
            children: [
                { title: "Banner de productos", icon: "solar:gallery-wide-outline", url: "/admin/products-banner" },
                { title: "Marcas", icon: "solar:tag-outline", url: "/admin/brands" },
                { title: "Cajas de cambio", icon: "solar:settings-minimalistic-outline", url: "/admin/gearboxes" },
                { title: "Productos", icon: "solar:box-outline", url: "/admin/products" },
                { title: "Importar Excel", icon: "solar:import-outline", url: "/admin/import" },
            ],
        },
        {
            id: "desarrollos",
            title: "Desarrollos especiales",
            icon: "solar:settings-outline",
            children: [
                { title: "Contenido", icon: "solar:document-text-outline", url: "/admin/desarrollos" },
                { title: "Mensajes", icon: "solar:letter-outline", url: "/admin/desarrollos-messages" },
            ],
        },
        {
            id: "calidad",
            title: "Calidad",
            icon: "solar:medal-ribbons-star-outline",
            children: [
                { title: "Contenido", icon: "solar:document-text-outline", url: "/admin/calidad" },
            ],
        },
        {
            id: "novedades-publicas",
            title: "Novedades",
            icon: "solar:star-outline",
            children: [
                { title: "Contenido", icon: "solar:document-text-outline", url: "/admin/novedades" },
            ],
        },
        {
            id: "contacto",
            title: "Contacto",
            icon: "solar:chat-round-outline",
            children: [
                { title: "Contenido", icon: "solar:map-point-outline", url: "/admin/contacto" },
                { title: "Mensajes", icon: "solar:letter-outline", url: "/admin/contact-messages" },
            ],
        },
        {
            id: "zona-privada",
            title: "Zona Privada",
            icon: "solar:lock-keyhole-outline",
            children: [
                { title: "Usuarios", icon: "solar:users-group-two-rounded-outline", url: "/admin/private-users" },
                { title: "Listas de Precios", icon: "solar:file-text-outline", url: "/admin/price-lists" },
                { title: "Info de Pagos", icon: "solar:card-outline", url: "/admin/payment-info" },
                { title: "Novedades", icon: "solar:star-outline", url: "/admin/private-novedades" },
                { title: "Notificaciones", icon: "solar:bell-outline", url: "/admin/private-notifications" },
            ],
        },
        {
            id: "newsletter",
            title: "Newsletter",
            icon: "solar:mailbox-outline",
            children: [
                { title: "Suscriptores", icon: "solar:letter-outline", url: "/admin/newsletter" },
            ],
        },
        {
            id: "whatsapp",
            title: "Redes Sociales y WhatsApp",
            icon: "solar:chat-round-like-outline",
            children: [
                { title: "Configuración", icon: "solar:chat-round-like-outline", url: "/admin/whatsapp-float" },
            ],
        },
        {
            id: "extras",
            title: "Extras",
            icon: "solar:widget-add-outline",
            children: [
                { title: "Metadatos SEO", icon: "solar:tag-outline", url: "/admin/seo" },
                { title: "Usuarios administradores", icon: "solar:users-group-two-rounded-outline", url: "/admin/users" },
            ],
        },
    ];

    const getActiveSectionId = () => (
        sections.find((section) => section.children.some((item) => currentUrl.startsWith(item.url)))?.id ?? sections[0].id
    );

    const [openSection, setOpenSection] = useState(getActiveSectionId);

    useEffect(() => {
        const activeSectionId = getActiveSectionId();
        setOpenSection((current) => (current === activeSectionId ? current : activeSectionId));
    }, [currentUrl]);

    return (
        <aside className="w-[272px] h-screen sticky top-0 flex flex-col bg-white border-r border-gray-200">
            {/* HEADER */}
            <div className="flex items-center gap-3 px-5 h-16 border-b border-gray-200">
                <div className="flex items-center justify-center w-9 h-9 rounded-xl bg-[#25A7CA]">
                    <Icon icon="solar:settings-bold" width={18} color="white" />
                </div>
                <div className="leading-tight">
                    <p className="text-sm font-semibold text-gray-800">Panel administrativo</p>
                    <p className="text-xs text-gray-400">Gran Sasso</p>
                </div>
            </div>

            {/* NAV */}
            <nav className="flex-1 overflow-y-auto px-3 py-4 space-y-2">
                {sections.map(section => (
                    <div key={section.id} className="rounded-2xl border border-gray-100 bg-gray-50/60 overflow-hidden">
                        <button
                            type="button"
                            onClick={() => setOpenSection((current) => current === section.id ? "" : section.id)}
                            className={`w-full flex items-center gap-3 px-3 py-3 text-left transition ${openSection === section.id ? "bg-[#25A7CA]/8" : "hover:bg-white"
                                }`}
                        >
                            <div className={`flex items-center justify-center w-8 h-8 rounded-xl ${openSection === section.id ? "bg-[#25A7CA] text-white" : "bg-white text-gray-400"
                                }`}>
                                <Icon icon={section.icon} width={16} />
                            </div>
                            <div className="min-w-0 flex-1">
                                <p className="text-sm font-semibold text-gray-800 truncate">{section.title}</p>
                                <p className="text-[11px] text-gray-400">
                                    {section.children.length} {section.children.length === 1 ? "opción" : "opciones"}
                                </p>
                            </div>
                            <Icon
                                icon="solar:alt-arrow-down-outline"
                                width={16}
                                className={`text-gray-400 transition-transform ${openSection === section.id ? "rotate-180" : ""}`}
                            />
                        </button>

                        <div className={openSection === section.id ? "block px-2 pb-2" : "hidden"}>
                            {section.children.map(item => {
                                const active = currentUrl.startsWith(item.url);
                                return (
                                    <button
                                        key={item.title}
                                        onClick={() => router.visit(item.url)}
                                        className={`w-full flex items-center gap-2.5 px-3 py-2.5 rounded-xl transition relative
                                            ${active
                                                ? "bg-white text-[#25A7CA] font-medium shadow-sm"
                                                : "text-gray-700 hover:bg-white"
                                            }`}
                                    >
                                        {active && (
                                            <span className="absolute left-0 w-[3px] h-5 bg-[#25A7CA] rounded-r-full" />
                                        )}
                                        <Icon
                                            icon={item.icon}
                                            width={15}
                                            className={active ? "text-[#25A7CA]" : "text-gray-400"}
                                        />
                                        <span className="text-sm truncate">{item.title}</span>
                                    </button>
                                );
                            })}
                        </div>
                    </div>
                ))}
            </nav>

            {/* FOOTER */}
            <div className="border-t border-gray-200 p-3">
                {auth?.user && (
                    <div className="flex items-center gap-2.5 p-2 rounded-xl bg-gray-50 mb-2">
                        <div className="flex items-center justify-center w-8 h-8 rounded-full bg-[#25A7CA]/20 flex-shrink-0">
                            <Icon icon="solar:user-bold" width={16} className="text-[#25A7CA]" />
                        </div>
                        <div className="min-w-0">
                            <p className="text-xs font-semibold text-gray-800 truncate">{auth.user.name}</p>
                            <p className="text-xs text-gray-400 truncate">{auth.user.email}</p>
                        </div>
                    </div>
                )}
                <button
                    onClick={() => router.post("/admin/logout")}
                    className="w-full flex items-center justify-center gap-2 py-2.5 rounded-xl bg-red-50 border border-red-100 text-red-500 hover:bg-red-100 transition"
                >
                    <Icon icon="solar:logout-2-outline" width={15} />
                    <span className="text-sm font-medium">Cerrar sesión</span>
                </button>
            </div>
        </aside>
    );
}
