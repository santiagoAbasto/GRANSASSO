import AdminLayout from "@/Layouts/AdminLayout";
import { router } from "@inertiajs/react";
import { Icon } from "@iconify/react";

function DetailRow({ label, value }) {
    if (!value) return null;
    return (
        <div className="py-3 border-b border-gray-50 last:border-0">
            <p className="text-xs text-gray-400 mb-0.5">{label}</p>
            <p className="text-sm text-gray-800">{value}</p>
        </div>
    );
}

export default function HrApplicationShow({ application }) {
    const destroy = () => {
        if (!confirm("¿Eliminar esta postulación?")) return;
        router.delete(`/admin/hr/applications/${application.id}`, { onSuccess: () => router.visit('/admin/hr/applications') });
    };

    return (
        <AdminLayout>
            <div className="max-w-3xl">
                <div className="flex items-center justify-between mb-6">
                    <div className="flex items-center gap-3">
                        <button onClick={() => history.back()} className="p-2 rounded-lg hover:bg-gray-100 text-gray-500"><Icon icon="solar:arrow-left-outline" width={18} /></button>
                        <div>
                            <h1 className="text-2xl font-bold text-gray-800">{application.name}</h1>
                            <p className="text-sm text-gray-500">{application.created_at}</p>
                        </div>
                    </div>
                    <button onClick={destroy} className="flex items-center gap-2 px-4 py-2 bg-red-50 text-red-600 border border-red-100 rounded-xl text-sm hover:bg-red-100 transition">
                        <Icon icon="solar:trash-bin-trash-outline" width={16} />
                        Eliminar
                    </button>
                </div>

                {application.position && (
                    <div className="bg-[#25A7CA]/5 border border-[#25A7CA]/20 rounded-xl px-5 py-3 mb-6 flex items-center gap-2">
                        <Icon icon="solar:case-outline" width={16} className="text-[#25A7CA]" />
                        <span className="text-sm font-medium text-[#25A7CA]">Puesto: {application.position.title}</span>
                    </div>
                )}

                <div className="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 mb-6">
                    <h2 className="font-semibold text-gray-700 mb-4 flex items-center gap-2"><Icon icon="solar:user-outline" width={16} className="text-[#25A7CA]" />Datos del candidato</h2>
                    <DetailRow label="Nombre" value={application.name} />
                    <DetailRow label="Email" value={application.email} />
                    <DetailRow label="Teléfono" value={application.phone} />
                </div>

                {application.message && (
                    <div className="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 mb-6">
                        <h2 className="font-semibold text-gray-700 mb-3 flex items-center gap-2"><Icon icon="solar:chat-square-outline" width={16} className="text-[#25A7CA]" />Mensaje</h2>
                        <p className="text-sm text-gray-700 whitespace-pre-wrap">{application.message}</p>
                    </div>
                )}

                {application.cv_url && (
                    <div className="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                        <h2 className="font-semibold text-gray-700 mb-3 flex items-center gap-2"><Icon icon="solar:file-outline" width={16} className="text-[#25A7CA]" />CV / Currículum</h2>
                        <a href={application.cv_url} target="_blank" rel="noopener noreferrer" className="flex items-center gap-2 text-sm text-[#25A7CA] hover:underline">
                            <Icon icon="solar:download-outline" width={16} />
                            Descargar CV
                        </a>
                    </div>
                )}
            </div>
        </AdminLayout>
    );
}
