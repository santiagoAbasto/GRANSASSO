import AdminLayout from "@/Layouts/AdminLayout";
import { useForm } from "@inertiajs/react";
import { Icon } from "@iconify/react";
import RichTextEditor from "@/Components/Admin/RichTextEditor";

export default function HrPositionsEdit({ position, sectors }) {
    const { data, setData, post, processing, errors } = useForm({
        _method: "PUT",
        hr_sector_id: position.hr_sector_id || "",
        title: position.title || "",
        description: position.description || "",
        requirements: position.requirements || "",
        order: position.order ?? 0,
        is_active: position.is_active ?? true,
    });

    const submit = (e) => {
        e.preventDefault();
        post(`/admin/hr/positions/${position.id}`);
    };

    return (
        <AdminLayout>
            <div className="max-w-3xl">
                <div className="flex items-center gap-3 mb-6">
                    <button onClick={() => history.back()} className="p-2 rounded-lg hover:bg-gray-100 text-gray-500"><Icon icon="solar:arrow-left-outline" width={18} /></button>
                    <div>
                        <h1 className="text-2xl font-bold text-gray-800">Editar Puesto</h1>
                        <p className="text-sm text-gray-500">{position.title}</p>
                    </div>
                </div>

                <form onSubmit={submit} className="space-y-6">
                    <div className="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-4">
                        <div>
                            <label className="block text-sm text-gray-600 mb-1">Título *</label>
                            <input type="text" value={data.title} onChange={(e) => setData("title", e.target.value)} className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30" />
                        </div>
                        <div>
                            <label className="block text-sm text-gray-600 mb-1">Sector</label>
                            <select value={data.hr_sector_id} onChange={(e) => setData("hr_sector_id", e.target.value)} className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none">
                                <option value="">Sin sector</option>
                                {sectors.map(s => <option key={s.id} value={s.id}>{s.name}</option>)}
                            </select>
                        </div>
                        <div>
                            <label className="block text-sm text-gray-600 mb-2">Descripción</label>
                            <RichTextEditor value={data.description} onChange={(v) => setData("description", v)} />
                        </div>
                        <div>
                            <label className="block text-sm text-gray-600 mb-2">Requisitos</label>
                            <RichTextEditor value={data.requirements} onChange={(v) => setData("requirements", v)} />
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
                            {processing ? "Guardando..." : "Actualizar Puesto"}
                        </button>
                    </div>
                </form>
            </div>
        </AdminLayout>
    );
}
