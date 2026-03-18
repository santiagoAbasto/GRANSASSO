import AdminLayout from "@/Layouts/AdminLayout";
import { useForm, router } from "@inertiajs/react";
import { Icon } from "@iconify/react";
import { useState } from "react";
import RichTextEditor from "@/Components/Admin/RichTextEditor";

/* ── Tarjeta de datos de contacto (dirección, email, teléfono) ── */
function ContactInfoCard({ contactInfo }) {
    const { data, setData, post, processing, recentlySuccessful } = useForm({
        address: contactInfo.address || "",
        email: contactInfo.email || "",
        phone: contactInfo.phone || "",
    });

    const save = (e) => {
        e.preventDefault();
        post("/admin/contacto/info");
    };

    return (
        <div className="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 mb-6">
            <div className="flex items-center gap-2 mb-5">
                <Icon icon="solar:map-point-outline" width={20} className="text-[#25A7CA]" />
                <h2 className="font-semibold text-gray-800 text-lg">Datos de Contacto</h2>
            </div>
            <form onSubmit={save} className="space-y-4">
                <div>
                    <label className="block text-sm text-gray-600 mb-1">Dirección</label>
                    <textarea
                        value={data.address}
                        onChange={(e) => setData("address", e.target.value)}
                        rows={2}
                        className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30 resize-none"
                        placeholder="C. 103 1301 (B1672), Villa Lynch, Argentina"
                    />
                </div>
                <div className="grid grid-cols-2 gap-4">
                    <div>
                        <label className="block text-sm text-gray-600 mb-1">Email</label>
                        <input
                            type="email"
                            value={data.email}
                            onChange={(e) => setData("email", e.target.value)}
                            className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30"
                            placeholder="ventas@gransasso.com"
                        />
                    </div>
                    <div>
                        <label className="block text-sm text-gray-600 mb-1">Teléfono</label>
                        <input
                            type="text"
                            value={data.phone}
                            onChange={(e) => setData("phone", e.target.value)}
                            className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30"
                            placeholder="+54 9 11 1234-5678"
                        />
                    </div>
                </div>
                <div className="flex items-center justify-end gap-3">
                    {recentlySuccessful && (
                        <span className="text-sm text-green-600 flex items-center gap-1">
                            <Icon icon="solar:check-circle-bold" width={16} /> Guardado
                        </span>
                    )}
                    <button
                        type="submit"
                        disabled={processing}
                        className="px-5 py-2.5 bg-[#25A7CA] text-white rounded-xl text-sm font-medium hover:bg-[#1d96b8] disabled:opacity-60 transition"
                    >
                        {processing ? "Guardando..." : "Guardar Datos"}
                    </button>
                </div>
            </form>
        </div>
    );
}

/* ── Tarjeta de sección CMS ── */
function SectionCard({ section, onDelete }) {
    const [editing, setEditing] = useState(false);
    const { data, setData, post, processing } = useForm({
        _method: "PUT",
        title: section.title || "",
        subtitle: section.subtitle || "",
        content: section.content || "",
        button_text: section.button_text || "",
        button_link: section.button_link || "",
        order: section.order || 0,
        is_active: section.is_active ?? true,
        image: null,
    });
    const [preview, setPreview] = useState(null);
    const save = (e) => { e.preventDefault(); post(`/admin/sections/${section.id}`, { forceFormData: true, onSuccess: () => setEditing(false) }); };

    if (!editing) return (
        <div className="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <div className="flex items-start justify-between">
                <div className="flex-1 min-w-0">
                    <div className="flex items-center gap-2 mb-1">
                        <span className="text-xs bg-[#25A7CA]/10 text-[#25A7CA] px-2 py-0.5 rounded-full font-medium">{section.type}</span>
                        <span className={`text-xs px-2 py-0.5 rounded-full font-medium ${section.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'}`}>{section.is_active ? 'Activo' : 'Inactivo'}</span>
                    </div>
                    <p className="font-semibold text-gray-800">{section.title || '(Sin título)'}</p>
                    <p className="text-sm text-gray-500">{section.subtitle || '—'}</p>
                </div>
                <div className="flex gap-2 ml-4">
                    <button onClick={() => setEditing(true)} className="p-1.5 rounded-lg hover:bg-gray-100 text-gray-600"><Icon icon="solar:pen-outline" width={16} /></button>
                    <button onClick={() => onDelete(section.id)} className="p-1.5 rounded-lg hover:bg-red-50 text-red-500"><Icon icon="solar:trash-bin-trash-outline" width={16} /></button>
                </div>
            </div>
        </div>
    );

    return (
        <div className="bg-white rounded-xl border border-[#25A7CA]/30 shadow-sm p-5">
            <form onSubmit={save} className="space-y-4">
                <div className="grid grid-cols-2 gap-4">
                    <div><label className="block text-xs text-gray-500 mb-1">Título</label><input type="text" value={data.title} onChange={(e) => setData("title", e.target.value)} className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none" /></div>
                    <div><label className="block text-xs text-gray-500 mb-1">Subtítulo</label><input type="text" value={data.subtitle} onChange={(e) => setData("subtitle", e.target.value)} className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none" /></div>
                </div>
                <div><label className="block text-xs text-gray-500 mb-1">Contenido</label><RichTextEditor value={data.content} onChange={(v) => setData("content", v)} /></div>
                <div className="grid grid-cols-2 gap-4">
                    <div><label className="block text-xs text-gray-500 mb-1">Texto botón</label><input type="text" value={data.button_text} onChange={(e) => setData("button_text", e.target.value)} className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none" /></div>
                    <div><label className="block text-xs text-gray-500 mb-1">URL botón</label><input type="text" value={data.button_link} onChange={(e) => setData("button_link", e.target.value)} className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none" /></div>
                </div>
                <div>
                    <label className="block text-xs text-gray-500 mb-1">Imagen</label>
                    {section.image_url && !preview && <img src={section.image_url} className="h-16 rounded-lg object-cover mb-2" alt="" />}
                    <input type="file" accept="image/*" onChange={(e) => { const f = e.target.files[0]; if(f) { setData("image", f); setPreview(URL.createObjectURL(f)); } }} className="block w-full text-sm text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-[#25A7CA]/10 file:text-[#25A7CA]" />
                    {preview && <img src={preview} className="mt-2 h-20 rounded-lg object-cover" alt="" />}
                </div>
                <div className="flex items-center justify-between">
                    <label className="flex items-center gap-2 cursor-pointer"><input type="checkbox" checked={data.is_active} onChange={(e) => setData("is_active", e.target.checked)} className="w-4 h-4 rounded" /><span className="text-sm text-gray-700">Activo</span></label>
                    <div className="flex gap-2">
                        <button type="button" onClick={() => setEditing(false)} className="px-4 py-2 border border-gray-200 rounded-lg text-sm text-gray-700 hover:bg-gray-50">Cancelar</button>
                        <button type="submit" disabled={processing} className="px-4 py-2 bg-[#25A7CA] text-white rounded-lg text-sm font-medium hover:bg-[#1d96b8] disabled:opacity-60">{processing ? "Guardando..." : "Guardar"}</button>
                    </div>
                </div>
            </form>
        </div>
    );
}

export default function Contacto({ page, sections, contactInfo }) {
    return (
        <AdminLayout>
            <div className="max-w-3xl">
                <div className="mb-6">
                    <h1 className="text-2xl font-bold text-gray-800">CMS – Contacto</h1>
                    <p className="text-sm text-gray-500">Gestión del contenido de la página "Contacto"</p>
                </div>

                {/* ── Datos de contacto (dirección, email, teléfono) ── */}
                <ContactInfoCard contactInfo={contactInfo} />
            </div>
        </AdminLayout>
    );
}

