import { useMemo } from "react";
import { useForm } from "@inertiajs/react";
import AdminLayout from "@/Layouts/AdminLayout";
import { Icon } from "@iconify/react";

function onlyDigits(value) {
    return (value || "").replace(/\D/g, "");
}

export default function WhatsappFloat({ whatsapp }) {
    const { data, setData, post, processing, errors } = useForm({
        number: whatsapp?.number ?? "",
        facebook: whatsapp?.facebook ?? "",
        instagram: whatsapp?.instagram ?? "",
    });

    const previewUrl = useMemo(() => {
        const digits = onlyDigits(data.number);
        return digits ? `https://wa.me/${digits}` : "";
    }, [data.number]);

    const facebookPreviewUrl = useMemo(() => {
        const value = (data.facebook || "").trim();
        if (!value) return "";
        return /^https?:\/\//i.test(value) ? value : `https://${value}`;
    }, [data.facebook]);

    const instagramPreviewUrl = useMemo(() => {
        const value = (data.instagram || "").trim();
        if (!value) return "";
        return /^https?:\/\//i.test(value) ? value : `https://${value}`;
    }, [data.instagram]);

    function submit(e) {
        e.preventDefault();
        post("/admin/whatsapp-float", {
            preserveScroll: true,
        });
    }

    return (
        <AdminLayout title="Redes sociales">
            <div className="max-w-5xl">
                <div className="mb-6">
                    <h1 className="text-2xl font-bold text-gray-800">Redes sociales</h1>
                    <p className="text-sm text-gray-500 mt-1">
                        Editá el acceso de WhatsApp flotante y los enlaces sociales del footer.
                    </p>
                </div>

                <div className="grid grid-cols-1 xl:grid-cols-[minmax(0,1.15fr)_380px] gap-6">
                    <div className="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                        <form onSubmit={submit} className="space-y-5">
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">
                                    Número del botón flotante de WhatsApp
                                </label>
                                <input
                                    type="text"
                                    value={data.number}
                                    onChange={(e) => setData("number", e.target.value)}
                                    placeholder="+54 11 4755-9078"
                                    className="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30"
                                />
                                <p className="text-xs text-gray-400 mt-1">
                                    Se precarga con el número que hoy ya está usando la web pública.
                                </p>
                                {errors.number && <p className="text-xs text-red-500 mt-1">{errors.number}</p>}
                            </div>

                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">
                                    Enlace de Facebook del footer
                                </label>
                                <input
                                    type="text"
                                    value={data.facebook}
                                    onChange={(e) => setData("facebook", e.target.value)}
                                    placeholder="https://facebook.com/tu-pagina"
                                    className="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30"
                                />
                                <p className="text-xs text-gray-400 mt-1">
                                    Este enlace define a dónde lleva el ícono de Facebook del footer.
                                </p>
                                {errors.facebook && <p className="text-xs text-red-500 mt-1">{errors.facebook}</p>}
                            </div>

                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">
                                    Enlace de Instagram del footer
                                </label>
                                <input
                                    type="text"
                                    value={data.instagram}
                                    onChange={(e) => setData("instagram", e.target.value)}
                                    placeholder="https://instagram.com/tu-cuenta"
                                    className="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30"
                                />
                                <p className="text-xs text-gray-400 mt-1">
                                    Este enlace define a dónde lleva el ícono de Instagram del footer.
                                </p>
                                {errors.instagram && <p className="text-xs text-red-500 mt-1">{errors.instagram}</p>}
                            </div>

                            {whatsapp?.fallback_number && (
                                <div className="rounded-xl border border-sky-100 bg-sky-50 px-4 py-3 text-sm text-sky-800">
                                    Número detectado actualmente en la web: <strong>{whatsapp.fallback_number}</strong>
                                </div>
                            )}

                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">
                                    Enlace de WhatsApp generado
                                </label>
                                <div className="rounded-xl border border-gray-200 bg-gray-50 px-3 py-3 text-sm text-gray-700 break-all">
                                    {previewUrl || "Completá el código de país y el número para generar el enlace."}
                                </div>
                            </div>

                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">
                                    Enlace de Facebook
                                </label>
                                <div className="rounded-xl border border-gray-200 bg-gray-50 px-3 py-3 text-sm text-gray-700 break-all">
                                    {facebookPreviewUrl || "Completá el enlace para activar el ícono de Facebook del footer."}
                                </div>
                            </div>

                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">
                                    Enlace de Instagram
                                </label>
                                <div className="rounded-xl border border-gray-200 bg-gray-50 px-3 py-3 text-sm text-gray-700 break-all">
                                    {instagramPreviewUrl || "Completá el enlace para activar el ícono de Instagram del footer."}
                                </div>
                            </div>

                            <div className="flex justify-end">
                                <button
                                    type="submit"
                                    disabled={processing}
                                    className="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#25A7CA] text-white text-sm font-medium hover:bg-[#1d96b8] disabled:opacity-60 transition"
                                >
                                    <Icon icon="solar:check-circle-outline" width={16} />
                                    {processing ? "Guardando..." : "Guardar configuración"}
                                </button>
                            </div>
                        </form>
                    </div>

                    <div className="bg-[#0D2038] rounded-3xl p-6 text-white shadow-sm">
                        <div className="flex items-center gap-2 mb-5">
                            <Icon icon="solar:eye-outline" width={18} className="text-[#7FD0E7]" />
                            <h2 className="font-semibold">Vista previa</h2>
                        </div>

                        <div className="rounded-[28px] bg-white/8 border border-white/10 p-5 min-h-[380px] relative overflow-hidden">
                            <div className="absolute inset-x-0 top-0 h-16 bg-black/10" />
                            <div className="absolute right-5 bottom-5">
                                <div className="w-16 h-16 rounded-full bg-[#25D366] text-white flex items-center justify-center shadow-[0_18px_40px_rgba(37,211,102,0.35)]">
                                    <Icon icon="ri:whatsapp-fill" width={32} />
                                </div>
                            </div>

                            <div className="relative pt-16 space-y-5">
                                <div>
                                    <p className="text-xs uppercase tracking-[0.24em] text-white/60">WhatsApp</p>
                                    <p className="text-base mt-1">
                                        {data.number || "—"}
                                    </p>
                                </div>

                                <div>
                                    <p className="text-xs uppercase tracking-[0.24em] text-white/60">Enlace de WhatsApp</p>
                                    {previewUrl ? (
                                        <a
                                            href={previewUrl}
                                            target="_blank"
                                            rel="noreferrer"
                                            className="mt-2 inline-flex items-center gap-2 text-[#7FD0E7] hover:text-white transition break-all"
                                        >
                                            <Icon icon="solar:link-round-angle-outline" width={16} />
                                            <span>{previewUrl}</span>
                                        </a>
                                    ) : (
                                        <p className="mt-2 text-sm text-white/60">Aún no hay enlace generado.</p>
                                    )}
                                </div>

                                <div className="pt-2 border-t border-white/10">
                                    <p className="text-xs uppercase tracking-[0.24em] text-white/60">Facebook en footer</p>
                                    {facebookPreviewUrl ? (
                                        <a
                                            href={facebookPreviewUrl}
                                            target="_blank"
                                            rel="noreferrer"
                                            className="mt-2 inline-flex items-center gap-2 text-[#7FD0E7] hover:text-white transition break-all"
                                        >
                                            <Icon icon="ri:facebook-fill" width={16} />
                                            <span>{facebookPreviewUrl}</span>
                                        </a>
                                    ) : (
                                        <p className="mt-2 text-sm text-white/60">Aún no hay enlace configurado.</p>
                                    )}
                                </div>

                                <div className="pt-2 border-t border-white/10">
                                    <p className="text-xs uppercase tracking-[0.24em] text-white/60">Instagram en footer</p>
                                    {instagramPreviewUrl ? (
                                        <a
                                            href={instagramPreviewUrl}
                                            target="_blank"
                                            rel="noreferrer"
                                            className="mt-2 inline-flex items-center gap-2 text-[#7FD0E7] hover:text-white transition break-all"
                                        >
                                            <Icon icon="ri:instagram-line" width={16} />
                                            <span>{instagramPreviewUrl}</span>
                                        </a>
                                    ) : (
                                        <p className="mt-2 text-sm text-white/60">Aún no hay enlace configurado.</p>
                                    )}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
