import AdminLayout from "@/Layouts/AdminLayout";
import { useForm } from "@inertiajs/react";
import { Icon } from "@iconify/react";

export default function HrSectorsCreate() {
    const { data, setData, post, processing, errors } = useForm({
        name: "",
        description: "",
        order: 0,
        is_active: true,
    });

    const submit = (e) => {
        e.preventDefault();
        post("/admin/hr/sectors");
    };

    return (
        <AdminLayout>
            <div className="max-w-2xl">
                <div className="flex items-center gap-3 mb-6">
                    <button onClick={() => history.back()} className="p-2 rounded-lg hover:bg-gray-100 text-gray-500"><Icon icon="solar:arrow-left-outline" width={18} /></button>
                    <h1 className="text-2xl font-bold text-gray-800">Nuevo Sector RRHH</h1>
                </div>

                <form onSubmit={submit} className="space-y-6">
                    <div className="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-4">
                        <div>
                            <label className="block text-sm text-gray-600 mb-1">Nombre *</label>
                            <input type="text" value={data.name} onChange={(e) => setData("name", e.target.value)} className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30" />
                            {errors.name && <p className="text-red-500 text-xs mt-1">{errors.name}</p>}
                        </div>
                        <div>
                            <label className="block text-sm text-gray-600 mb-1">Descripción</label>
                            <textarea value={data.description} onChange={(e) => setData("description", e.target.value)} rows={3} className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none resize-none" />
                        </div>
                        <div className="flex items-center gap-6">
                            <div>
                                <label className="block text-sm text-gray-600 mb-1">Orden</label>
                                <input type="number" value={data.order} onChange={(e) => setData("order", parseInt(e.target.value) || 0)} min="0" className="w-24 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none" />
                            </div>
                            <label className="flex items-center gap-2 cursor-pointer mt-5">
                                <input type="checkbox" checked={data.is_active} onChange={(e) => setData("is_active", e.target.checked)} className="w-4 h-4 rounded" />
                                <span className="text-sm text-gray-700">Activo</span>
                            </label>
                        </div>
                    </div>

                    <div className="flex justify-end gap-3">
                        <button type="button" onClick={() => history.back()} className="px-5 py-2.5 rounded-xl border border-gray-200 text-sm text-gray-700 hover:bg-gray-50">Cancelar</button>
                        <button type="submit" disabled={processing} className="px-5 py-2.5 rounded-xl bg-[#25A7CA] text-white text-sm font-medium hover:bg-[#1d96b8] disabled:opacity-60 transition">
                            {processing ? "Guardando..." : "Crear Sector"}
                        </button>
                    </div>
                </form>
            </div>
        </AdminLayout>
    );
}
