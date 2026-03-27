import AdminLayout from "@/Layouts/AdminLayout";
import { router } from "@inertiajs/react";
import { Icon } from "@iconify/react";

export default function DesarrollosMessageShow({ message }) {
    const destroy = () => {
        if (!confirm("¿Eliminar este mensaje?")) return;
        router.delete(`/admin/desarrollos-messages/${message.id}`, {
            onSuccess: () => router.visit("/admin/desarrollos-messages"),
        });
    };

    return (
        <AdminLayout>
            <div className="max-w-2xl">
                <div className="flex items-center justify-between mb-6">
                    <div className="flex items-center gap-3">
                        <button onClick={() => history.back()} className="p-2 rounded-lg hover:bg-gray-100 text-gray-500">
                            <Icon icon="solar:arrow-left-outline" width={18} />
                        </button>
                        <div>
                            <h1 className="text-2xl font-bold text-gray-800">Mensaje de {message.name}</h1>
                            <p className="text-sm text-gray-500">{message.created_at}</p>
                        </div>
                    </div>
                    <button
                        onClick={destroy}
                        className="flex items-center gap-2 px-4 py-2 bg-red-50 text-red-600 border border-red-100 rounded-xl text-sm hover:bg-red-100 transition"
                    >
                        <Icon icon="solar:trash-bin-trash-outline" width={16} />
                        Eliminar
                    </button>
                </div>

                {/* Datos del remitente */}
                <div className="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 mb-6">
                    <h2 className="font-semibold text-gray-700 mb-4 flex items-center gap-2">
                        <Icon icon="solar:user-outline" width={16} className="text-[#25A7CA]" />
                        Datos del remitente
                    </h2>
                    <div className="grid grid-cols-2 gap-4">
                        <div>
                            <p className="text-xs text-gray-400">Nombre</p>
                            <p className="text-sm font-medium text-gray-800">{message.name}</p>
                        </div>
                        <div>
                            <p className="text-xs text-gray-400">Email</p>
                            <p className="text-sm text-gray-800">{message.email}</p>
                        </div>
                        <div>
                            <p className="text-xs text-gray-400">Teléfono</p>
                            <p className="text-sm text-gray-800">{message.phone}</p>
                        </div>
                    </div>
                </div>

                {/* Mensaje */}
                <div className="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 mb-6">
                    <h2 className="font-semibold text-gray-700 mb-3 flex items-center gap-2">
                        <Icon icon="solar:chat-square-outline" width={16} className="text-[#25A7CA]" />
                        Mensaje
                    </h2>
                    <p className="text-sm text-gray-700 whitespace-pre-wrap leading-relaxed">{message.message}</p>
                </div>

                {/* Archivo adjunto */}
                {message.download_url && (
                    <div className="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 mb-6">
                        <h2 className="font-semibold text-gray-700 mb-3 flex items-center gap-2">
                            <Icon icon="solar:paperclip-outline" width={16} className="text-[#25A7CA]" />
                            Archivo adjunto
                        </h2>
                        <a
                            href={message.download_url}
                            className="inline-flex items-center gap-2 px-4 py-2.5 border border-gray-200 text-gray-700 rounded-xl text-sm hover:bg-gray-50 transition"
                        >
                            <Icon icon="solar:download-outline" width={16} />
                            Descargar archivo
                        </a>
                    </div>
                )}

                {/* Acciones */}
                <div className="flex gap-3">
                    <a
                        href={`mailto:${message.email}`}
                        className="flex items-center gap-2 px-4 py-2.5 bg-[#25A7CA] text-white rounded-xl text-sm hover:bg-[#1d96b8] transition"
                    >
                        <Icon icon="solar:letter-outline" width={16} />
                        Responder por email
                    </a>
                </div>
            </div>
        </AdminLayout>
    );
}
