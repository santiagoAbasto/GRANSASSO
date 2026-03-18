import AdminLayout from "@/Layouts/AdminLayout";
import { useForm, router } from "@inertiajs/react";
import { Icon } from "@iconify/react";

import ImageUploadField from "@/Components/Admin/ImageUploadField";

export default function HomeAbout({ page, section }) {

    const isNew = !section;

    const { data, setData, post, processing, errors } = useForm({
        _method:      isNew ? undefined : "PUT",
        type:         "about",
        title:        section?.title       || "",
        content:      section?.content     || "",
        button_text:  section?.button_text || "",
        button_link:  section?.button_link || "",
        is_active:    section?.is_active   ?? true,
        order:        section?.order       ?? 0,
        image:        null,
    });

    const submit = (e) => {
        e.preventDefault();
        const url = isNew
            ? `/admin/pages/${page.id}/sections`
            : `/admin/sections/${section.id}`;
        post(url, { forceFormData: true });
    };

    return (
        <AdminLayout>
            <div className="max-w-2xl">

                {/* HEADER */}
                <div className="mb-6">
                    <h1 className="text-2xl font-bold text-gray-800">Home – About</h1>
                    <p className="text-sm text-gray-500 mt-1">
                        Edita el bloque "Sobre nosotros" que aparece en la página de inicio.
                    </p>
                </div>

                {/* FORM */}
                <form onSubmit={submit} className="bg-white rounded-xl border border-gray-100 shadow-sm p-6 space-y-5">

                    {/* Imagen */}
                    <div>
                        <label className="block text-xs font-medium text-gray-500 mb-2">Imagen</label>
                        <ImageUploadField
                            currentUrl={section?.image_url}
                            onChange={(f) => setData("image", f)}
                            specs={{ width: 600, height: 600, maxMB: 3, formats: ["PNG", "JPG"] }}
                            error={errors.image}
                        />
                    </div>

                    <hr className="border-gray-100" />

                    {/* Título */}
                    <div>
                        <label className="block text-xs font-medium text-gray-500 mb-1">Título</label>
                        <input
                            type="text"
                            value={data.title}
                            onChange={(e) => setData("title", e.target.value)}
                            placeholder="Ej: Sobre Gran Sasso"
                            className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30"
                        />
                        {errors.title && <p className="text-xs text-red-500 mt-1">{errors.title}</p>}
                    </div>

                    {/* Contenido */}
                    <div>
                        <label className="block text-xs font-medium text-gray-500 mb-1">
                            Contenido
                            <span className="text-gray-400 font-normal ml-1">(cada línea es un párrafo)</span>
                        </label>
                        <textarea
                            value={data.content}
                            onChange={(e) => setData("content", e.target.value)}
                            rows={5}
                            placeholder="Escribe el texto que aparece junto a la imagen..."
                            className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30 resize-y"
                        />
                        {errors.content && <p className="text-xs text-red-500 mt-1">{errors.content}</p>}
                    </div>

                    <hr className="border-gray-100" />

                    {/* Botón */}
                    <div className="grid grid-cols-2 gap-4">
                        <div>
                            <label className="block text-xs font-medium text-gray-500 mb-1">Texto del botón</label>
                            <input
                                type="text"
                                value={data.button_text}
                                onChange={(e) => setData("button_text", e.target.value)}
                                placeholder="Ej: Más información"
                                className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30"
                            />
                        </div>
                        <div>
                            <label className="block text-xs font-medium text-gray-500 mb-1">URL del botón</label>
                            <input
                                type="text"
                                value={data.button_link}
                                onChange={(e) => setData("button_link", e.target.value)}
                                placeholder="/nosotros"
                                className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30"
                            />
                        </div>
                    </div>

                    <hr className="border-gray-100" />

                    {/* Footer del form */}
                    <div className="flex items-center justify-between">
                        <label className="flex items-center gap-2 cursor-pointer">
                            <input
                                type="checkbox"
                                checked={data.is_active}
                                onChange={(e) => setData("is_active", e.target.checked)}
                                className="w-4 h-4 rounded accent-[#25A7CA]"
                            />
                            <span className="text-sm text-gray-700">Sección activa</span>
                        </label>

                        <button
                            type="submit"
                            disabled={processing}
                            className="inline-flex items-center gap-2 px-5 py-2 bg-[#25A7CA] text-white rounded-lg text-sm font-medium hover:bg-[#1d96b8] disabled:opacity-60 transition"
                        >
                            <Icon icon={processing ? "solar:refresh-outline" : "solar:diskette-outline"} width={15} />
                            {processing ? "Guardando..." : "Guardar cambios"}
                        </button>
                    </div>

                </form>
            </div>
        </AdminLayout>
    );
}
