import AdminLayout from "@/Layouts/AdminLayout";
import { useForm } from "@inertiajs/react";
import { Icon } from "@iconify/react";
import ImageUploadField from "@/Components/Admin/ImageUploadField";
import RichTextEditor from "@/Components/Admin/RichTextEditor";

const LANGS = ["es", "en", "pt"];

function LangBadge({ lang }) {
    const colors = { es: "bg-red-100 text-red-600", en: "bg-blue-100 text-blue-600", pt: "bg-green-100 text-green-600" };
    return (
        <span className={`inline-block text-[10px] font-bold px-1.5 py-0.5 rounded uppercase leading-none ${colors[lang]}`}>
            {lang}
        </span>
    );
}

function MultiInput({ label, field, data, setData, placeholder = "", type = "input" }) {
    return (
        <div>
            <label className="block text-sm font-medium text-gray-600 mb-2">{label}</label>
            <div className={type === "textarea" ? "space-y-2" : "grid grid-cols-3 gap-3"}>
                {LANGS.map((lang) => (
                    <div key={lang} className="flex flex-col gap-1">
                        <LangBadge lang={lang} />
                        {type === "textarea" ? (
                            <textarea
                                value={data[`${field}_${lang}`]}
                                onChange={(e) => setData(`${field}_${lang}`, e.target.value)}
                                rows={4}
                                placeholder={placeholder}
                                className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30 resize-y"
                            />
                        ) : (
                            <input
                                type="text"
                                value={data[`${field}_${lang}`]}
                                onChange={(e) => setData(`${field}_${lang}`, e.target.value)}
                                placeholder={placeholder}
                                className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30"
                            />
                        )}
                    </div>
                ))}
            </div>
        </div>
    );
}

export default function HomeAbout({ page, section }) {
    const isNew = !section;

    const { data, setData, post, processing, errors } = useForm({
        _method:         isNew ? undefined : "PUT",
        type:            "about",
        title_es:        section?.title_es        ?? "",
        title_en:        section?.title_en        ?? "",
        title_pt:        section?.title_pt        ?? "",
        subtitle_es:     section?.subtitle_es     ?? "",
        subtitle_en:     section?.subtitle_en     ?? "",
        subtitle_pt:     section?.subtitle_pt     ?? "",
        content_es:      section?.content_es      ?? "",
        content_en:      section?.content_en      ?? "",
        content_pt:      section?.content_pt      ?? "",
        button_text_es:  section?.button_text_es  ?? "",
        button_text_en:  section?.button_text_en  ?? "",
        button_text_pt:  section?.button_text_pt  ?? "",
        button_link:     section?.button_link     ?? "",
        is_active:       section?.is_active       ?? true,
        order:           section?.order           ?? "A",
        image:           null,
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
            <div className="max-w-3xl">
                <div className="mb-6 flex items-start justify-between gap-4">
                    <div>
                        <h1 className="text-2xl font-bold text-gray-800">Nosotros en inicio</h1>
                        <p className="text-sm text-gray-500 mt-1">
                            Editá la sección Nosotros que aparece en la página de inicio.
                        </p>
                    </div>
                    <span className={`inline-flex items-center gap-1.5 text-xs px-2.5 py-1 rounded-full font-medium ${
                        data.is_active ? "bg-green-100 text-green-700" : "bg-gray-100 text-gray-500"
                    }`}>
                        <span className={`w-1.5 h-1.5 rounded-full ${data.is_active ? "bg-green-500" : "bg-gray-400"}`} />
                        {data.is_active ? "Visible en el sitio" : "Oculta"}
                    </span>
                </div>

                <form onSubmit={submit} className="space-y-6">
                    {/* Imagen */}
                    <div className="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
                        <h2 className="font-semibold text-gray-700 mb-4">Imagen</h2>
                        <ImageUploadField
                            currentUrl={section?.image_url}
                            onChange={(f) => setData("image", f)}
                            specs={{ width: 600, height: 600, maxMB: 3, formats: ["PNG", "JPG"] }}
                            error={errors.image}
                        />
                    </div>

                    {/* Contenido multilenguaje */}
                    <div className="bg-white rounded-xl border border-gray-100 shadow-sm p-6 space-y-5">
                        <h2 className="font-semibold text-gray-700">Contenido multilenguaje</h2>

                        <MultiInput label="Título" field="title" data={data} setData={setData} placeholder="Ej: Sobre Gran Sasso" />
                        <MultiInput label="Subtítulo" field="subtitle" data={data} setData={setData} placeholder="Ej: Líderes en repuestos" />
                        <div>
                            <label className="block text-sm font-medium text-gray-600 mb-2">Contenido</label>
                            <div className="space-y-2">
                                {LANGS.map((lang) => (
                                    <div key={lang} className="flex flex-col gap-1">
                                        <LangBadge lang={lang} />
                                        <RichTextEditor
                                            value={data[`content_${lang}`]}
                                            onChange={(val) => setData(`content_${lang}`, val)}
                                            placeholder="Escribí el texto..."
                                        />
                                    </div>
                                ))}
                            </div>
                        </div>
                        <MultiInput label="Texto del botón" field="button_text" data={data} setData={setData} placeholder="Ej: Más información" />
                    </div>

                    {/* Campos fijos */}
                    <div className="bg-white rounded-xl border border-gray-100 shadow-sm p-6 space-y-4">
                        <h2 className="font-semibold text-gray-700">Configuración</h2>
                        <div>
                            <label className="block text-sm font-medium text-gray-600 mb-1">URL del botón</label>
                            <input
                                type="text"
                                value={data.button_link}
                                onChange={(e) => setData("button_link", e.target.value)}
                                placeholder="/empresa"
                                className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30"
                            />
                        </div>
                        <div className="flex items-center gap-6">
                            <div>
                                <label className="block text-sm font-medium text-gray-600 mb-1">Orden</label>
                                <input
                                    type="text"
                                    maxLength={20}
                                    value={data.order}
                                    onChange={(e) => setData("order", e.target.value.toUpperCase())}
                                    placeholder="A"
                                    className="w-24 border border-gray-200 rounded-lg px-3 py-2 text-sm font-mono uppercase focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30"
                                />
                            </div>
                            <label className="flex items-center gap-2 cursor-pointer mt-5">
                                <input
                                    type="checkbox"
                                    checked={!!data.is_active}
                                    onChange={(e) => setData("is_active", e.target.checked)}
                                    className="w-4 h-4 rounded accent-[#25A7CA]"
                                />
                                <span className="text-sm text-gray-700">Sección activa</span>
                            </label>
                        </div>
                    </div>

                    <div className="flex justify-end">
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
