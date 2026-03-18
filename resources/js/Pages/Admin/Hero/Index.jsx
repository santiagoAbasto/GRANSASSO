import AdminLayout from "@/Layouts/AdminLayout";
import { router } from "@inertiajs/react";
import { Icon } from "@iconify/react";

export default function HeroIndex({ slides }) {
    const destroy = (id) => {
        if (!confirm("¿Eliminar este slide?")) return;
        router.delete(`/admin/hero/${id}`);
    };

    return (
        <AdminLayout>
            <div className="flex items-center justify-between mb-6">
                <div>
                    <h1 className="text-2xl font-bold text-gray-800">Hero / Slider</h1>
                    <p className="text-sm text-gray-500">Gestión del slider principal</p>
                </div>
                <button
                    onClick={() => router.visit('/admin/hero/create')}
                    className="flex items-center gap-2 bg-[#25A7CA] text-white px-4 py-2 rounded-xl text-sm font-medium hover:bg-[#1d96b8] transition"
                >
                    <Icon icon="solar:add-circle-outline" width={18} />
                    Nuevo Slide
                </button>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
                {slides.length === 0 && (
                    <div className="col-span-3 bg-white rounded-2xl border border-dashed border-gray-300 p-12 text-center">
                        <Icon icon="solar:gallery-outline" width={40} className="text-gray-300 mx-auto mb-3" />
                        <p className="text-gray-500 text-sm">No hay slides. Crea el primero.</p>
                    </div>
                )}
                {slides.map((slide) => (
                    <div key={slide.id} className="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                        {/* Thumbnail */}
                        <div className="relative h-40 bg-gray-100">
                            {slide.media_type === 'image' && slide.media_url ? (
                                <img src={slide.media_url} alt={slide.title} className="w-full h-full object-cover" />
                            ) : slide.media_type === 'video' ? (
                                <div className="w-full h-full flex items-center justify-center bg-gray-800 text-white">
                                    <Icon icon="solar:play-circle-outline" width={40} />
                                </div>
                            ) : (
                                <div className="w-full h-full flex items-center justify-center text-gray-300">
                                    <Icon icon="solar:gallery-outline" width={40} />
                                </div>
                            )}
                            <div className="absolute top-2 right-2">
                                <span className={`text-xs px-2 py-0.5 rounded-full font-medium ${slide.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'}`}>
                                    {slide.is_active ? 'Activo' : 'Inactivo'}
                                </span>
                            </div>
                            <div className="absolute top-2 left-2 bg-black/40 text-white text-xs px-2 py-0.5 rounded-full font-medium">
                                {slide.order || '—'}
                            </div>
                        </div>
                        <div className="p-4">
                            <p className="font-semibold text-gray-800 truncate">{slide.title || '(Sin título)'}</p>
                            <p className="text-xs text-gray-500 mt-0.5 truncate">{slide.subtitle || '—'}</p>
                            <div className="flex items-center gap-1 mt-4">
                                <span className={`text-xs px-2 py-0.5 rounded-full ${slide.media_type === 'video' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700'}`}>
                                    {slide.media_type === 'video' ? 'Video' : 'Imagen'}
                                </span>
                            </div>
                            <div className="flex gap-2 mt-4">
                                <button
                                    onClick={() => router.visit(`/admin/hero/${slide.id}/edit`)}
                                    className="flex-1 flex items-center justify-center gap-1.5 py-2 rounded-lg bg-gray-50 hover:bg-gray-100 text-sm text-gray-700 transition"
                                >
                                    <Icon icon="solar:pen-outline" width={14} />
                                    Editar
                                </button>
                                <button
                                    onClick={() => destroy(slide.id)}
                                    className="flex items-center justify-center gap-1.5 py-2 px-3 rounded-lg bg-red-50 hover:bg-red-100 text-sm text-red-600 transition"
                                >
                                    <Icon icon="solar:trash-bin-trash-outline" width={14} />
                                </button>
                            </div>
                        </div>
                    </div>
                ))}
            </div>
        </AdminLayout>
    );
}
