import Sidebar from "@/Components/Admin/Sidebar";
import Header from "@/Components/Admin/Header";
import { usePage } from "@inertiajs/react";
import Toast from "@/Components/Admin/Toast";

export default function AdminLayout({ children }) {
    const { flash } = usePage().props;

    return (
        <div className="flex h-screen bg-[#F3F4F6] font-sans text-[#111827]">
            {/* TOAST GLOBAL */}
            <Toast message={flash?.success} type="success" />
            <Toast message={flash?.error} type="error" />

            {/* SIDEBAR */}
            <div className="hidden md:block bg-white border-r border-[#E5E7EB] flex-shrink-0">
                <Sidebar />
            </div>

            {/* MAIN */}
            <div className="flex flex-col flex-1 min-w-0 overflow-hidden">
                <Header />
                <main className="flex-1 overflow-y-auto p-8">
                    <div className="max-w-[1400px] mx-auto space-y-6">
                        {children}
                    </div>
                </main>
            </div>
        </div>
    );
}
