import { usePage, router } from "@inertiajs/react";
import { Icon } from "@iconify/react";

export default function Header() {
    const { auth } = usePage().props;

    return (
        <header className="h-16 bg-white border-b border-[#E5E7EB] px-10 flex items-center justify-between">
            <h2 className="text-lg font-semibold text-[#111827]">
                Panel Administrativo
            </h2>
            <div className="flex items-center gap-6">
                {/* Search */}
                <div className="relative">
                    <Icon
                        icon="solar:magnifer-outline"
                        className="absolute left-3 top-1/2 -translate-y-1/2 text-[#9CA3AF]"
                    />
                    <input
                        type="text"
                        placeholder="Buscar..."
                        className="pl-9 pr-4 py-2 rounded-lg border border-[#E5E7EB] text-sm focus:outline-none focus:ring-2 focus:ring-[#2563EB]/30"
                    />
                </div>
                <Icon
                    icon="solar:bell-outline"
                    width={20}
                    className="text-[#6B7280] hover:text-[#2563EB] cursor-pointer transition"
                />
                <div className="flex items-center gap-3">
                    <div className="w-9 h-9 rounded-full bg-[#2563EB] text-white flex items-center justify-center text-sm font-semibold">
                        {auth?.user?.name?.charAt(0)}
                    </div>
                    <span className="text-sm text-[#374151]">
                        {auth?.user?.name}
                    </span>
                    <button
                        onClick={() => router.post(route("admin.logout"))}
                        className="text-sm text-red-500 hover:text-red-600"
                    >
                        Cerrar sesión
                    </button>
                </div>
            </div>
        </header>
    );
}
