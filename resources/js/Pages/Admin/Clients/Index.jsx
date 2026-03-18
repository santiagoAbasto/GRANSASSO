import AdminLayout from "@/Layouts/AdminLayout";
import { router } from "@inertiajs/react";
import { Icon } from "@iconify/react";

export default function ClientsIndex({ clients }) {
    const destroy = (id) => {
        if (!confirm("¿Eliminar este cliente?")) return;
        router.delete(`/admin/clients/${id}`);
    };

    return (
        <AdminLayout>
            <div className="flex items-center justify-between mb-6">
                <div>
                    <h1 className="text-2xl font-bold text-gray-800">Clientes</h1>
                    <p className="text-sm text-gray-500">{clients.length} clientes</p>
                </div>
                <button onClick={() => router.visit('/admin/clients/create')} className="flex items-center gap-2 bg-[#25A7CA] text-white px-4 py-2 rounded-xl text-sm font-medium hover:bg-[#1d96b8] transition">
                    <Icon icon="solar:add-circle-outline" width={18} />
                    Nuevo Cliente
                </button>
            </div>

            <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-4">
                {clients.length === 0 && (
                    <div className="col-span-6 bg-white rounded-2xl border border-dashed border-gray-300 p-12 text-center">
                        <Icon icon="solar:users-group-outline" width={40} className="text-gray-300 mx-auto mb-3" />
                        <p className="text-gray-500 text-sm">Sin clientes. Agrega el primero.</p>
                    </div>
                )}
                {clients.map((c) => (
                    <div key={c.id} className="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden p-4 flex flex-col items-center gap-3">
                        <div className="w-16 h-16 rounded-xl bg-gray-50 flex items-center justify-center overflow-hidden">
                            {c.logo_url ? <img src={c.logo_url} className="w-full h-full object-contain" alt={c.name} /> : <Icon icon="solar:buildings-outline" width={24} className="text-gray-300" />}
                        </div>
                        <div className="text-center min-w-0 w-full">
                            <p className="text-sm font-medium text-gray-800 truncate">{c.name}</p>
                            {c.website && <p className="text-xs text-gray-400 truncate">{c.website}</p>}
                        </div>
                        <span className={`text-xs px-2 py-0.5 rounded-full font-medium ${c.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'}`}>{c.is_active ? 'Activo' : 'Inactivo'}</span>
                        <div className="flex gap-2 w-full">
                            <button onClick={() => router.visit(`/admin/clients/${c.id}/edit`)} className="flex-1 flex items-center justify-center gap-1 py-1.5 rounded-lg bg-gray-50 hover:bg-gray-100 text-xs text-gray-700 transition"><Icon icon="solar:pen-outline" width={12} />Editar</button>
                            <button onClick={() => destroy(c.id)} className="flex items-center justify-center py-1.5 px-2 rounded-lg bg-red-50 hover:bg-red-100 text-red-500 transition"><Icon icon="solar:trash-bin-trash-outline" width={12} /></button>
                        </div>
                    </div>
                ))}
            </div>
        </AdminLayout>
    );
}
