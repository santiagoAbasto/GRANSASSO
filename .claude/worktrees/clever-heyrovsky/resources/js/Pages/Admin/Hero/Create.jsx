import AdminLayout from "@/Layouts/AdminLayout";
import { useForm, router } from "@inertiajs/react";
import { Icon } from "@iconify/react";
import ImageUploadField from "@/Components/Admin/ImageUploadField";

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
                                rows={3}
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

export default function HeroCreate() {
    const { data, setData, post, processing, errors } = useForm({
        title_es: "", title_en: "", title_pt: "",
        subtitle_es: "", subtitle_en: "", subtitle_pt: "",
        button_text_es: "", button_text_en: "", button_text_pt: "",
        button_link: "",
        image: null,
        order: "A",
        is_active: true,
    });

    const submit = (e) => {
        e.preventDefault();
        post("/admin/hero", { forceFormData: true });
    };

    return (
        <AdminLayout>
            <div className="max-w-3xl">
                <div className="flex items-center gap-3 mb-6">
                    <button onClick={() => router.visit("/admin/hero")} className="p-2 rounded-lg hover:bg-gray-100 text-gray-500">
                        <Icon icon="solar:arrow-left-outline" width={18} />
                    </button>
                    <div>
                        <h1 className="text-2xl font-bold text-gray-800">Nuevo Slide</h1>
                        <p className="text-sm text-gray-500">Agregar slide al slider principal</p>
                    </div>
                </div>

                <form onSubmit={submit} className="space-y-6">
                    {/* Imagen */}
                    <div className="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                        <h2 className="font-semibold text-gray-700 mb-4">Imagen</h2>
                        <ImageUploadField
                            onChange={(f) => setData("image", f)}
                            specs={{ width: 1440, height: 680, maxMB: 5, formats: ["JPG", "PNG"] }}
                            error={errors.image}
                        />
                    </div>

                    {/* Contenido multilenguaje */}
                    <div className="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-5">
                        <h2 className="font-semibold text-gray-700">Contenido multilenguaje</h2>

                        <MultiInput label="Título" field="title" data={data} setData={setData} placeholder="Título del slide" />
                        <MultiInput label="Subtítulo" field="subtitle" data={data} setData={setData} placeholder="Subtítulo" />
                        <MultiInput label="Texto del botón" field="button_text" data={data} setData={setData} placeholder="Ver productos" />

                        <div>
                            <label className="block text-sm font-medium text-gray-600 mb-1">URL del botón</label>
                            <input
                                type="text"
                                value={data.button_link}
                                onChange={(e) => setData("button_link", e.target.value)}
                                placeholder="/productos"
                                className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30"
                            />
                        </div>
                    </div>

                    {/* Config */}
                    <div className="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                        <h2 className="font-semibold text-gray-700 mb-4">Configuración</h2>
                        <div className="flex items-center gap-6">
                            <div>
                                <label className="block text-sm text-gray-600 mb-1">Orden</label>
                                <input type="text" value={data.order} onChange={(e) => setData("order", e.target.value.toUpperCase())}
                                    className="w-24 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none" />
                            </div>
                            <label className="flex items-center gap-2 cursor-pointer mt-5">
                                <input type="checkbox" checked={data.is_active} onChange={(e) => setData("is_active", e.target.checked)} className="w-4 h-4 rounded" />
                                <span className="text-sm text-gray-700">Activo</span>
                            </label>
                        </div>
                    </div>

                    <div className="flex justify-end gap-3">
                        <button type="button" onClick={() => router.visit("/admin/hero")} className="px-5 py-2.5 rounded-xl border border-gray-200 text-sm text-gray-700 hover:bg-gray-50">Cancelar</button>
                        <button type="submit" disabled={processing} className="px-5 py-2.5 rounded-xl bg-[#25A7CA] text-white text-sm font-medium hover:bg-[#1d96b8] disabled:opacity-60 transition">
                            {processing ? "Guardando..." : "Crear Slide"}
                        </button>
                    </div>
                </form>
            </div>
        </AdminLayout>
    );
}
