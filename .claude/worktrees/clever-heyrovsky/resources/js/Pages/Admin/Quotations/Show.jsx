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

export default function QuotationShow({ quotation }) {
    const destroy = () => {
        if (!confirm("¿Eliminar esta cotización?")) return;
        router.delete(`/admin/comercial/quotations/${quotation.id}`, { onSuccess: () => router.visit('/admin/comercial/quotations') });
    };

    return (
        <AdminLayout>
            <div className="max-w-3xl">
                <div className="flex items-center justify-between mb-6">
                    <div className="flex items-center gap-3">
                        <button onClick={() => history.back()} className="p-2 rounded-lg hover:bg-gray-100 text-gray-500"><Icon icon="solar:arrow-left-outline" width={18} /></button>
                        <div>
                            <h1 className="text-2xl font-bold text-gray-800">Cotización #{quotation.id}</h1>
                            <p className="text-sm text-gray-500">{quotation.created_at}</p>
                        </div>
                    </div>
                    <button onClick={destroy} className="flex items-center gap-2 px-4 py-2 bg-red-50 text-red-600 border border-red-100 rounded-xl text-sm hover:bg-red-100 transition">
                        <Icon icon="solar:trash-bin-trash-outline" width={16} />
                        Eliminar
                    </button>
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div className="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                        <h2 className="font-semibold text-gray-700 mb-4 flex items-center gap-2"><Icon icon="solar:user-outline" width={16} className="text-[#25A7CA]" />Datos del solicitante</h2>
                        <DetailRow label="Nombre" value={quotation.name} />
                        <DetailRow label="Email" value={quotation.email} />
                        <DetailRow label="Teléfono" value={quotation.phone} />
                        <DetailRow label="Empresa" value={quotation.company} />
                    </div>

                    <div className="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                        <h2 className="font-semibold text-gray-700 mb-4 flex items-center gap-2"><Icon icon="solar:document-add-outline" width={16} className="text-[#25A7CA]" />Detalles del pedido</h2>
                        <DetailRow label="Instrumental/Producto" value={quotation.instrumental} />
                        <DetailRow label="Solución" value={quotation.solution} />
                        <DetailRow label="Cantidad" value={quotation.quantity} />
                    </div>
                </div>

                {quotation.message && (
                    <div className="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 mt-6">
                        <h2 className="font-semibold text-gray-700 mb-3 flex items-center gap-2"><Icon icon="solar:chat-square-outline" width={16} className="text-[#25A7CA]" />Mensaje</h2>
                        <p className="text-sm text-gray-700 whitespace-pre-wrap">{quotation.message}</p>
                    </div>
                )}

                {quotation.process_data && (
                    <div className="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 mt-6">
                        <h2 className="font-semibold text-gray-700 mb-3">Datos del proceso</h2>
                        <pre className="text-xs bg-gray-50 rounded-lg p-3 overflow-auto">{JSON.stringify(quotation.process_data, null, 2)}</pre>
                    </div>
                )}
            </div>
        </AdminLayout>
    );
}
