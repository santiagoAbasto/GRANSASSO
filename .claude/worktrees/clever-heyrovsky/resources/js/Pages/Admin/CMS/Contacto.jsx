import { useState, useRef } from "react";
import AdminLayout from "@/Layouts/AdminLayout";
import { router, useForm } from "@inertiajs/react";
import { Icon } from "@iconify/react";

/* ═══════════════════════════════════════════
   BANNER SECTION
═══════════════════════════════════════════ */
function BannerSection({ section }) {
    const { data, setData, post, processing } = useForm({ image: null });
    const fileRef = useRef(null);
    const [preview, setPreview] = useState(null);

    const handleFile = (e) => {
        const file = e.target.files[0];
        if (!file) return;
        setData("image", file);
        setPreview(URL.createObjectURL(file));
    };

    const submit = (e) => {
        e.preventDefault();
        if (!data.image) return;
        post(`/admin/sections/${section.id}/images`, {
            forceFormData: true,
            onSuccess: () => {
                setData("image", null);
                setPreview(null);
                if (fileRef.current) fileRef.current.value = "";
            },
        });
    };

    const deleteImage = (id) => {
        if (!confirm("¿Eliminar esta imagen del banner?")) return;
        router.delete(`/admin/section-images/${id}`);
    };

    const images = section.images ?? [];

    return (
        <div className="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div className="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <div className="flex items-center gap-2">
                    <div className="flex items-center justify-center w-8 h-8 rounded-lg bg-[#25A7CA]/10">
                        <Icon icon="solar:gallery-wide-outline" width={16} className="text-[#25A7CA]" />
                    </div>
                    <div>
                        <p className="text-sm font-semibold text-gray-800">Banner / Slider</p>
                        <p className="text-xs text-gray-400">
                            {images.length === 0
                                ? "Sin imágenes — el banner no se mostrará"
                                : images.length === 1
                                ? "1 imagen — banner estático"
                                : `${images.length} imágenes — slider con dots`}
                        </p>
                    </div>
                </div>
                <span className={`text-xs px-2 py-0.5 rounded-full font-medium ${section.is_active ? "bg-green-100 text-green-700" : "bg-gray-100 text-gray-500"}`}>
                    {section.is_active ? "Activo" : "Inactivo"}
                </span>
            </div>

            <div className="p-5">
                {images.length === 0 ? (
                    <div className="flex items-center justify-center h-28 rounded-xl border-2 border-dashed border-gray-200 text-gray-300 mb-4">
                        <div className="text-center">
                            <Icon icon="solar:gallery-outline" width={28} className="mx-auto mb-1" />
                            <p className="text-xs">Sin imágenes todavía</p>
                        </div>
                    </div>
                ) : (
                    <div className="grid grid-cols-2 sm:grid-cols-3 gap-3 mb-4">
                        {images.map((img) => (
                            <div key={img.id} className="relative group rounded-xl overflow-hidden border border-gray-100" style={{ aspectRatio: "16/7" }}>
                                <img src={img.image_url} alt="" className="w-full h-full object-cover" />
                                <button
                                    onClick={() => deleteImage(img.id)}
                                    className="absolute top-1.5 right-1.5 p-1 rounded-lg bg-red-500 text-white opacity-0 group-hover:opacity-100 transition"
                                >
                                    <Icon icon="solar:trash-bin-trash-outline" width={13} />
                                </button>
                            </div>
                        ))}
                    </div>
                )}

                <form onSubmit={submit} className="flex flex-col sm:flex-row items-start sm:items-end gap-3 p-4 bg-gray-50 rounded-xl border border-dashed border-gray-200">
                    <div className="flex-1">
                        <label className="block text-xs font-medium text-gray-500 mb-1">Agregar imagen al banner</label>
                        <p className="text-xs text-gray-400 mb-2">Recomendado: 1920×521 px · JPG/PNG · máx 5 MB</p>
                        <input
                            ref={fileRef}
                            type="file"
                            accept="image/*"
                            onChange={handleFile}
                            className="block w-full text-sm text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-[#25A7CA]/10 file:text-[#25A7CA] hover:file:bg-[#25A7CA]/20"
                        />
                        {preview && <img src={preview} className="mt-2 h-16 rounded-lg object-cover border border-gray-100" alt="preview" />}
                    </div>
                    <button
                        type="submit"
                        disabled={!data.image || processing}
                        className="inline-flex items-center gap-1.5 px-4 py-2 bg-[#25A7CA] text-white rounded-lg text-sm font-medium hover:bg-[#1d96b8] disabled:opacity-40 transition whitespace-nowrap"
                    >
                        <Icon icon={processing ? "solar:refresh-outline" : "solar:add-circle-outline"} width={15} className={processing ? "animate-spin" : ""} />
                        {processing ? "Subiendo..." : "Agregar imagen"}
                    </button>
                </form>
            </div>
        </div>
    );
}

/* ═══════════════════════════════════════════
   CONTACT INFO CARD
═══════════════════════════════════════════ */
function ContactInfoCard({ contactInfo }) {
    const [phones, setPhones] = useState(contactInfo.phones?.length ? contactInfo.phones : [""]);
    const [emails, setEmails] = useState(contactInfo.emails?.length ? contactInfo.emails : [""]);
    const [address, setAddress]   = useState(contactInfo.address || "");
    const [mapEmbed, setMapEmbed] = useState(contactInfo.map_embed || "");
    const [saving, setSaving]     = useState(false);
    const [saved, setSaved]       = useState(false);

    const addPhone  = () => setPhones([...phones, ""]);
    const addEmail  = () => setEmails([...emails, ""]);
    const rmPhone   = (i) => setPhones(phones.filter((_, idx) => idx !== i));
    const rmEmail   = (i) => setEmails(emails.filter((_, idx) => idx !== i));
    const editPhone = (i, v) => setPhones(phones.map((p, idx) => idx === i ? v : p));
    const editEmail = (i, v) => setEmails(emails.map((e, idx) => idx === i ? v : e));

    const save = (e) => {
        e.preventDefault();
        setSaving(true);
        router.post("/admin/contacto/info", {
            address,
            phones: phones.filter(Boolean),
            emails: emails.filter(Boolean),
            map_embed: mapEmbed,
        }, {
            preserveScroll: true,
            onSuccess: () => { setSaved(true); setTimeout(() => setSaved(false), 3000); },
            onFinish:  () => setSaving(false),
        });
    };

    return (
        <div className="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div className="flex items-center gap-2 px-5 py-4 border-b border-gray-100">
                <div className="flex items-center justify-center w-8 h-8 rounded-lg bg-[#25A7CA]/10">
                    <Icon icon="solar:map-point-outline" width={16} className="text-[#25A7CA]" />
                </div>
                <div>
                    <p className="text-sm font-semibold text-gray-800">Datos de Contacto</p>
                    <p className="text-xs text-gray-400">Dirección, teléfonos, emails y mapa</p>
                </div>
            </div>

            <form onSubmit={save} className="p-5 space-y-6">

                {/* Dirección */}
                <div>
                    <label className="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Dirección</label>
                    <textarea
                        value={address}
                        onChange={(e) => setAddress(e.target.value)}
                        rows={2}
                        className="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30 resize-none"
                        placeholder="Calle 33 Nro. 2235 (1650) Villa Maipú..."
                    />
                </div>

                {/* Teléfonos */}
                <div>
                    <label className="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Teléfonos</label>
                    <div className="space-y-2">
                        {phones.map((ph, i) => (
                            <div key={i} className="flex gap-2">
                                <input
                                    type="text"
                                    value={ph}
                                    onChange={(e) => editPhone(i, e.target.value)}
                                    className="flex-1 border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30"
                                    placeholder="+54 11 4755-9078"
                                />
                                {phones.length > 1 && (
                                    <button type="button" onClick={() => rmPhone(i)}
                                        className="p-2 rounded-lg hover:bg-red-50 text-red-400 transition">
                                        <Icon icon="solar:trash-bin-trash-outline" width={16} />
                                    </button>
                                )}
                            </div>
                        ))}
                    </div>
                    <button type="button" onClick={addPhone}
                        className="mt-2 inline-flex items-center gap-1 text-xs text-[#25A7CA] hover:text-[#1d96b8] transition">
                        <Icon icon="solar:add-circle-outline" width={14} /> Agregar teléfono
                    </button>
                </div>

                {/* Emails */}
                <div>
                    <label className="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Emails</label>
                    <div className="space-y-2">
                        {emails.map((em, i) => (
                            <div key={i} className="flex gap-2">
                                <input
                                    type="email"
                                    value={em}
                                    onChange={(e) => editEmail(i, e.target.value)}
                                    className="flex-1 border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30"
                                    placeholder="ventas@gransasso.com.ar"
                                />
                                {emails.length > 1 && (
                                    <button type="button" onClick={() => rmEmail(i)}
                                        className="p-2 rounded-lg hover:bg-red-50 text-red-400 transition">
                                        <Icon icon="solar:trash-bin-trash-outline" width={16} />
                                    </button>
                                )}
                            </div>
                        ))}
                    </div>
                    <button type="button" onClick={addEmail}
                        className="mt-2 inline-flex items-center gap-1 text-xs text-[#25A7CA] hover:text-[#1d96b8] transition">
                        <Icon icon="solar:add-circle-outline" width={14} /> Agregar email
                    </button>
                </div>

                {/* Mapa embed */}
                <div>
                    <label className="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">
                        Mapa (iframe embed de Google Maps)
                    </label>
                    <textarea
                        value={mapEmbed}
                        onChange={(e) => setMapEmbed(e.target.value)}
                        rows={3}
                        className="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30 resize-none font-mono"
                        placeholder={'<iframe src="https://maps.google.com/maps?..." width="100%" height="480" ...></iframe>'}
                    />
                    <p className="mt-1 text-xs text-gray-400">Pegá el código iframe completo de Google Maps / Google My Maps</p>

                    {mapEmbed && (
                        <div className="mt-3">
                            <p className="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">Vista previa</p>
                            <div
                                style={{ width: "100%", height: "534px", borderRadius: "10px", overflow: "hidden" }}
                                dangerouslySetInnerHTML={{
                                    __html: mapEmbed.replace(/<iframe/gi, '<iframe style="width:100%;height:100%;border:0;display:block;"')
                                }}
                            />
                        </div>
                    )}
                </div>

                <div className="flex items-center justify-end gap-3 pt-2 border-t border-gray-100">
                    {saved && (
                        <span className="text-sm text-green-600 flex items-center gap-1">
                            <Icon icon="solar:check-circle-bold" width={16} /> Guardado
                        </span>
                    )}
                    <button
                        type="submit"
                        disabled={saving}
                        className="px-5 py-2.5 bg-[#25A7CA] text-white rounded-xl text-sm font-medium hover:bg-[#1d96b8] disabled:opacity-60 transition"
                    >
                        {saving ? "Guardando..." : "Guardar datos"}
                    </button>
                </div>
            </form>
        </div>
    );
}

/* ═══════════════════════════════════════════
   MESSAGES CARD
═══════════════════════════════════════════ */
function MessagesCard({ messages, totalMessages, unreadMessages }) {
    return (
        <div className="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div className="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <div className="flex items-center gap-2">
                    <div className="flex items-center justify-center w-8 h-8 rounded-lg bg-[#25A7CA]/10">
                        <Icon icon="solar:letter-outline" width={16} className="text-[#25A7CA]" />
                    </div>
                    <div>
                        <p className="text-sm font-semibold text-gray-800">Mensajes recibidos</p>
                        <p className="text-xs text-gray-400">
                            {totalMessages} total
                            {unreadMessages > 0 && (
                                <span className="ml-1 text-orange-500 font-medium">· {unreadMessages} sin leer</span>
                            )}
                        </p>
                    </div>
                </div>
                <a
                    href="/admin/contact-messages"
                    className="inline-flex items-center gap-1 text-xs font-medium text-[#25A7CA] hover:text-[#1d96b8] transition"
                >
                    Ver todos <Icon icon="solar:arrow-right-outline" width={14} />
                </a>
            </div>

            {messages.length === 0 ? (
                <div className="flex flex-col items-center justify-center py-10 text-gray-300">
                    <Icon icon="solar:letter-outline" width={36} className="mb-2" />
                    <p className="text-sm">Sin mensajes todavía</p>
                </div>
            ) : (
                <div className="divide-y divide-gray-50">
                    {messages.map((msg) => (
                        <a
                            key={msg.id}
                            href={`/admin/contact-messages/${msg.id}`}
                            className="flex items-start gap-3 px-5 py-3.5 hover:bg-gray-50 transition"
                        >
                            <div className={`mt-1.5 w-2 h-2 rounded-full flex-shrink-0 ${msg.is_read ? "bg-gray-200" : "bg-orange-400"}`} />
                            <div className="flex-1 min-w-0">
                                <div className="flex items-center justify-between gap-2">
                                    <p className={`text-sm truncate ${msg.is_read ? "text-gray-600 font-normal" : "text-gray-800 font-semibold"}`}>
                                        {msg.name}
                                    </p>
                                    <span className="text-xs text-gray-400 whitespace-nowrap">{msg.created_at}</span>
                                </div>
                                <p className="text-xs text-gray-400 truncate">
                                    {msg.email}{msg.company ? ` · ${msg.company}` : ""}
                                </p>
                            </div>
                        </a>
                    ))}
                </div>
            )}
        </div>
    );
}

/* ═══════════════════════════════════════════
   PAGE
═══════════════════════════════════════════ */
export default function Contacto({ sections, contactInfo, messages, totalMessages, unreadMessages }) {
    const banner = sections?.find((s) => s.type === "banner");

    return (
        <AdminLayout>
            <div className="max-w-3xl space-y-6">
                <div>
                    <h1 className="text-2xl font-bold text-gray-800">CMS – Contacto</h1>
                    <p className="text-sm text-gray-500">Banner, datos de contacto y mensajes</p>
                </div>

                {banner && <BannerSection section={banner} />}

                <ContactInfoCard contactInfo={contactInfo} />

                <MessagesCard
                    messages={messages ?? []}
                    totalMessages={totalMessages ?? 0}
                    unreadMessages={unreadMessages ?? 0}
                />
            </div>
        </AdminLayout>
    );
}
