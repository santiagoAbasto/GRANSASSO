import AdminLayout from "@/Layouts/AdminLayout";
import { useForm } from "@inertiajs/react";
import { Icon } from "@iconify/react";
import { useState } from "react";

function MetaForm({ page }) {
    const [open, setOpen] = useState(false);
    const { data, setData, post, processing, errors, reset } = useForm({
        page:        page.key,
        title:       page.title || "",
        description: page.description || "",
        keywords:    page.keywords || "",
        og_image:    null,
    });

    const [previewOg, setPreviewOg] = useState(page.og_image_url || null);

    const submit = (e) => {
        e.preventDefault();
        post("/admin/seo", {
            forceFormData: true,
            onSuccess: () => setOpen(false),
        });
    };

    const filled = page.title || page.description || page.keywords;

    return (
        <div className="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            {/* Header */}
            <button
                type="button"
                onClick={() => setOpen(!open)}
                className="w-full flex items-center justify-between px-5 py-4 hover:bg-gray-50/50 transition"
            >
                <div className="flex items-center gap-3">
                    <div className={`w-2.5 h-2.5 rounded-full flex-shrink-0 ${filled ? "bg-green-400" : "bg-gray-200"}`} />
                    <span className="font-medium text-gray-800 text-sm">{page.label}</span>
                    {filled ? (
                        <span className="text-xs text-gray-400 truncate max-w-xs hidden sm:block">{page.title}</span>
                    ) : (
                        <span className="text-xs bg-amber-50 text-amber-500 border border-amber-100 rounded-full px-2 py-0.5">Sin configurar</span>
                    )}
                </div>
                <Icon
                    icon={open ? "solar:alt-arrow-up-outline" : "solar:alt-arrow-down-outline"}
                    width={16}
                    className="text-gray-400 flex-shrink-0"
                />
            </button>

            {open && (
                <form onSubmit={submit} className="border-t border-gray-100 px-5 py-5 space-y-5">

                    {/* Título */}
                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">
                            Título de la página
                        </label>
                        <input
                            type="text"
                            value={data.title}
                            onChange={(e) => setData("title", e.target.value)}
                            maxLength={160}
                            placeholder={`Ej: Gran Sasso – ${page.label}`}
                            className="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30"
                        />
                        <div className="flex items-start justify-between mt-1">
                            <p className="text-xs text-gray-400">
                                Aparece en la pestaña del navegador y en Google. Idealmente menos de 60 letras.
                            </p>
                            <span className={`text-xs flex-shrink-0 ml-2 ${data.title.length > 60 ? "text-amber-500" : "text-gray-300"}`}>
                                {data.title.length}/60
                            </span>
                        </div>
                    </div>

                    {/* Descripción */}
                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">
                            Descripción
                        </label>
                        <textarea
                            value={data.description}
                            onChange={(e) => setData("description", e.target.value)}
                            maxLength={320}
                            rows={3}
                            placeholder="Breve descripción de la página. Ej: Gran Sasso ofrece repuestos y componentes para cajas de cambio con respaldo técnico especializado..."
                            className="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30 resize-none"
                        />
                        <div className="flex items-start justify-between mt-1">
                            <p className="text-xs text-gray-400">
                                Texto que Google muestra debajo del título en los resultados de búsqueda. Máximo 160 letras.
                            </p>
                            <span className={`text-xs flex-shrink-0 ml-2 ${data.description.length > 160 ? "text-amber-500" : "text-gray-300"}`}>
                                {data.description.length}/160
                            </span>
                        </div>
                    </div>

                    {/* Palabras clave */}
                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">
                            Palabras clave
                        </label>
                        <input
                            type="text"
                            value={data.keywords}
                            onChange={(e) => setData("keywords", e.target.value)}
                            placeholder="instrumentación, medición, control industrial, caudalímetros..."
                            className="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30"
                        />
                        <p className="text-xs text-gray-400 mt-1">
                            Separadas por comas. Palabras relacionadas con la página que ayudan a buscadores a entender el contenido.
                        </p>
                    </div>

                    {/* Imagen para redes sociales */}
                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">
                            Imagen para redes sociales
                        </label>
                        {previewOg && (
                            <div className="mb-2 relative inline-block">
                                <img src={previewOg} className="h-24 rounded-xl object-cover border border-gray-100" alt="Vista previa" />
                                <span className="absolute bottom-1 left-1 text-[10px] bg-black/50 text-white px-1.5 py-0.5 rounded">Vista previa</span>
                            </div>
                        )}
                        <input
                            type="file"
                            accept="image/*"
                            onChange={(e) => {
                                const f = e.target.files[0];
                                if (f) {
                                    setData("og_image", f);
                                    setPreviewOg(URL.createObjectURL(f));
                                }
                            }}
                            className="block w-full text-sm text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-[#25A7CA]/10 file:text-[#25A7CA]"
                        />
                        <p className="text-xs text-gray-400 mt-1">
                            Se muestra cuando alguien comparte el link en WhatsApp, Facebook o LinkedIn. Tamaño ideal: 1200 × 630 píxeles.
                        </p>
                    </div>

                    {/* Botones */}
                    <div className="flex justify-end gap-3 pt-1 border-t border-gray-50">
                        <button
                            type="button"
                            onClick={() => setOpen(false)}
                            className="px-4 py-2 border border-gray-200 rounded-xl text-sm text-gray-600 hover:bg-gray-50"
                        >
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            disabled={processing}
                            className="px-5 py-2 bg-[#25A7CA] text-white rounded-xl text-sm font-medium hover:bg-[#1d96b8] disabled:opacity-60 flex items-center gap-2"
                        >
                            {processing ? (
                                <>
                                    <Icon icon="svg-spinners:ring-resize" width={14} />
                                    Guardando...
                                </>
                            ) : (
                                <>
                                    <Icon icon="solar:check-circle-outline" width={14} />
                                    Guardar
                                </>
                            )}
                        </button>
                    </div>
                </form>
            )}
        </div>
    );
}

export default function SeoIndex({ pages }) {
    const configured = pages.filter(p => p.title || p.description || p.keywords).length;

    return (
        <AdminLayout>
            <div className="max-w-3xl">
                <div className="mb-6">
                    <h1 className="text-2xl font-bold text-gray-800">Posicionamiento web (SEO)</h1>
                    <p className="text-sm text-gray-500 mt-1">
                        Configurá cómo aparece el sitio en Google y cómo se ve cuando se comparte en redes sociales.
                    </p>
                </div>

                {/* Estado general */}
                <div className="flex items-center gap-3 mb-6 bg-white rounded-2xl border border-gray-100 shadow-sm px-5 py-4">
                    <div className="w-10 h-10 rounded-xl bg-[#25A7CA]/10 flex items-center justify-center">
                        <Icon icon="solar:chart-2-outline" width={20} className="text-[#25A7CA]" />
                    </div>
                    <div>
                        <p className="text-sm font-semibold text-gray-800">
                            {configured} de {pages.length} secciones configuradas
                        </p>
                        <p className="text-xs text-gray-400">
                            {configured === pages.length
                                ? "¡Excelente! Todas las páginas tienen metadatos."
                                : "Completá las secciones que aparecen en naranja para mejorar el posicionamiento."}
                        </p>
                    </div>
                </div>

                {/* Info */}
                <div className="bg-blue-50 border border-blue-100 rounded-2xl p-5 mb-6 space-y-3">
                    <p className="font-semibold text-blue-800 text-sm flex items-center gap-2">
                        <Icon icon="solar:info-circle-bold" width={16} className="text-blue-500" />
                        ¿Para qué sirve esto?
                    </p>
                    <div className="grid sm:grid-cols-2 gap-3">
                        {[
                            {
                                icon: "solar:magnifer-outline",
                                title: "Aparecer mejor en Google",
                                desc: "El título y la descripción son lo primero que ve alguien cuando te busca. Un buen texto aumenta los clics.",
                            },
                            {
                                icon: "solar:smartphone-outline",
                                title: "Compartir en redes sociales",
                                desc: "La imagen OG se muestra cuando alguien comparte tu link en WhatsApp, LinkedIn o Facebook.",
                            },
                            {
                                icon: "solar:tag-outline",
                                title: "Palabras clave",
                                desc: "Ayudan a los buscadores a entender de qué trata cada página. Usá términos que tus clientes buscarían.",
                            },
                            {
                                icon: "solar:check-circle-outline",
                                title: "Se aplica automáticamente",
                                desc: "Lo que guardás acá se refleja en el sitio de inmediato. No hace falta hacer nada más.",
                            },
                        ].map((item) => (
                            <div key={item.title} className="flex gap-2.5">
                                <Icon icon={item.icon} width={16} className="text-blue-400 flex-shrink-0 mt-0.5" />
                                <div>
                                    <p className="text-xs font-semibold text-blue-700">{item.title}</p>
                                    <p className="text-xs text-blue-600/70 leading-relaxed">{item.desc}</p>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>

                {/* Lista de páginas */}
                <div className="space-y-3">
                    {pages.map((page) => (
                        <MetaForm key={page.key} page={page} />
                    ))}
                </div>
            </div>
        </AdminLayout>
    );
}
