import { useMemo, useState } from "react";
import { router } from "@inertiajs/react";
import AdminLayout from "@/Layouts/AdminLayout";
import { Icon } from "@iconify/react";

function StatCard({ icon, label, value, tone = "sky" }) {
    const tones = {
        sky: "bg-sky-50 text-sky-600 border-sky-100",
        emerald: "bg-emerald-50 text-emerald-600 border-emerald-100",
        amber: "bg-amber-50 text-amber-600 border-amber-100",
    };

    return (
        <div className="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
            <div className={`mb-3 flex h-10 w-10 items-center justify-center rounded-xl border ${tones[tone]}`}>
                <Icon icon={icon} width={18} />
            </div>
            <p className="text-xs font-semibold uppercase tracking-wide text-gray-400">{label}</p>
            <p className="mt-1 text-2xl font-semibold text-gray-800">{value}</p>
        </div>
    );
}

function UploadPanel({ form, setForm, uploading, error, onSubmit }) {
    const selectedFileLabel = form.file ? `${form.file.name} · ${form.file.size ? `${(form.file.size / 1024 / 1024).toFixed(2)} MB` : ""}` : null;

    return (
        <div className="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
            <div className="mb-5 flex items-start gap-3">
                <div className="flex h-11 w-11 items-center justify-center rounded-2xl bg-[#25A7CA]/10 text-[#25A7CA]">
                    <Icon icon="solar:upload-minimalistic-outline" width={20} />
                </div>
                <div>
                    <h2 className="text-base font-semibold text-gray-800">Subir nueva lista</h2>
                    <p className="mt-1 text-sm text-gray-500">Cargá el PDF y dejá un nombre claro para que el cliente lo encuentre rápido en zona privada.</p>
                </div>
            </div>

            {error && (
                <div className="mb-4 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    {error}
                </div>
            )}

            <form onSubmit={onSubmit} className="grid gap-4 xl:grid-cols-[1fr_1fr_auto]">
                <div>
                    <label className="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Nombre visible</label>
                    <input
                        type="text"
                        value={form.name}
                        onChange={(e) => setForm((current) => ({ ...current, name: e.target.value }))}
                        className="w-full rounded-2xl border border-gray-200 px-3 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#25A7CA]/30"
                        placeholder="Ej: Lista de precios Abril 2026"
                    />
                </div>

                <div>
                    <label className="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Archivo PDF</label>
                    <label className="flex cursor-pointer items-center gap-3 rounded-2xl border border-dashed border-gray-200 bg-gray-50 px-4 py-3 transition hover:border-[#25A7CA]/40">
                        <Icon icon="solar:file-text-outline" width={18} className="flex-shrink-0 text-gray-400" />
                        <div className="min-w-0 flex-1">
                            {selectedFileLabel ? (
                                <p className="truncate text-sm font-medium text-gray-700">{selectedFileLabel}</p>
                            ) : (
                                <>
                                    <p className="text-sm text-gray-600">Elegir archivo PDF</p>
                                    <p className="text-xs text-gray-400">Recomendado: PDF · máx 20 MB</p>
                                </>
                            )}
                        </div>
                        <input
                            id="plFileInput"
                            type="file"
                            accept=".pdf"
                            className="hidden"
                            onChange={(e) => setForm((current) => ({ ...current, file: e.target.files[0] || null }))}
                        />
                    </label>
                </div>

                <div className="flex items-end">
                    <button
                        type="submit"
                        disabled={uploading}
                        className="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-[#25A7CA] px-5 py-3 text-sm font-medium text-white transition hover:bg-[#1d96b8] disabled:opacity-60 xl:w-auto"
                    >
                        <Icon icon={uploading ? "solar:refresh-outline" : "solar:upload-minimalistic-outline"} width={16} className={uploading ? "animate-spin" : ""} />
                        {uploading ? "Subiendo..." : "Subir lista"}
                    </button>
                </div>
            </form>
        </div>
    );
}

function PriceListCard({ item, onDelete }) {
    return (
        <div className="rounded-3xl border border-gray-100 bg-white p-5 shadow-sm transition hover:shadow-md">
            <div className="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div className="min-w-0 flex-1">
                    <div className="flex items-start gap-3">
                        <div className="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-2xl bg-[#25A7CA]/10 text-[#25A7CA]">
                            <Icon icon="solar:file-text-outline" width={20} />
                        </div>
                        <div className="min-w-0">
                            <p className="truncate text-base font-semibold text-gray-800">{item.name}</p>
                            <div className="mt-2 flex flex-wrap gap-2 text-xs text-gray-500">
                                <span className="rounded-full bg-gray-100 px-2.5 py-1 font-medium">{item.file_size_human}</span>
                                <span className="rounded-full bg-gray-100 px-2.5 py-1 font-medium">Subida: {item.created_at_label}</span>
                                <span className={`rounded-full px-2.5 py-1 font-medium ${item.is_active ? "bg-emerald-50 text-emerald-700" : "bg-gray-100 text-gray-500"}`}>
                                    {item.is_active ? "Disponible" : "Inactiva"}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div className="flex flex-col gap-2 sm:flex-row">
                    <a
                        href={item.view_url}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="inline-flex items-center justify-center gap-2 rounded-xl border border-[#25A7CA]/20 bg-[#25A7CA]/10 px-4 py-2.5 text-sm font-medium text-[#25A7CA] transition hover:bg-[#25A7CA]/20"
                    >
                        <Icon icon="solar:eye-outline" width={15} />
                        Abrir PDF
                    </a>
                    <button
                        type="button"
                        onClick={() => onDelete(item.id)}
                        className="inline-flex items-center justify-center gap-2 rounded-xl border border-rose-100 px-4 py-2.5 text-sm font-medium text-rose-600 transition hover:bg-rose-50"
                    >
                        <Icon icon="solar:trash-bin-minimalistic-outline" width={15} />
                        Eliminar
                    </button>
                </div>
            </div>
        </div>
    );
}

export default function PriceLists({ priceLists }) {
    const [form, setForm] = useState({ name: "", file: null });
    const [uploading, setUploading] = useState(false);
    const [error, setError] = useState("");

    const totalSize = useMemo(() => {
        return priceLists.reduce((acc, item) => acc + (item.file_size || 0), 0);
    }, [priceLists]);

    function handleSubmit(e) {
        e.preventDefault();

        if (!form.name.trim() || !form.file) {
            setError("Completá el nombre y seleccioná un PDF.");
            return;
        }

        setError("");
        setUploading(true);

        const fd = new FormData();
        fd.append("name", form.name.trim());
        fd.append("file", form.file);

        router.post("/admin/price-lists", fd, {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                setForm({ name: "", file: null });
                setUploading(false);
                const input = document.getElementById("plFileInput");
                if (input) input.value = "";
            },
            onError: () => setUploading(false),
        });
    }

    function handleDelete(id) {
        if (!confirm("¿Eliminar esta lista de precios?")) return;
        router.delete(`/admin/price-lists/${id}`, { preserveScroll: true });
    }

    return (
        <AdminLayout title="Listas de Precios">
            <div className="max-w-6xl space-y-6">
                <div className="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <h1 className="text-2xl font-bold text-gray-800">Listas de Precios</h1>
                        <p className="mt-1 text-sm text-gray-500">Ordená mejor los PDFs que ve el cliente y resolvé la carga sin pasos confusos.</p>
                    </div>
                </div>

                <div className="grid gap-4 md:grid-cols-3">
                    <StatCard icon="solar:file-text-outline" label="Listas cargadas" value={priceLists.length} />
                    <StatCard icon="solar:folder-with-files-outline" label="Peso total" value={totalSize ? `${(totalSize / 1024 / 1024).toFixed(1)} MB` : "0 MB"} tone="amber" />
                    <StatCard icon="solar:eye-outline" label="Disponibles" value={priceLists.filter((item) => item.is_active).length} tone="emerald" />
                </div>

                <UploadPanel form={form} setForm={setForm} uploading={uploading} error={error} onSubmit={handleSubmit} />

                <div className="rounded-3xl border border-gray-100 bg-white p-5 shadow-sm">
                    <div className="mb-4 flex items-start gap-3">
                        <div className="flex h-11 w-11 items-center justify-center rounded-2xl bg-[#25A7CA]/10 text-[#25A7CA]">
                            <Icon icon="solar:documents-outline" width={20} />
                        </div>
                        <div>
                            <h2 className="text-base font-semibold text-gray-800">Archivos disponibles</h2>
                            <p className="mt-1 text-sm text-gray-500">Abrí un PDF para verificarlo o eliminá los que ya no quieras mostrar en zona privada.</p>
                        </div>
                    </div>

                    <div className="space-y-3">
                        {priceLists.length === 0 ? (
                            <div className="rounded-2xl border border-dashed border-gray-200 bg-gray-50 px-6 py-14 text-center">
                                <div className="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-gray-400 shadow-sm">
                                    <Icon icon="solar:file-text-outline" width={24} />
                                </div>
                                <p className="text-base font-semibold text-gray-700">Todavía no hay listas de precios</p>
                                <p className="mt-1 text-sm text-gray-400">Subí el primer PDF para dejarlo disponible en zona privada.</p>
                            </div>
                        ) : (
                            priceLists.map((item) => <PriceListCard key={item.id} item={item} onDelete={handleDelete} />)
                        )}
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
