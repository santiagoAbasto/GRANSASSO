import AdminLayout from "@/Layouts/AdminLayout";
import NovedadesManager from "@/Components/Admin/NovedadesManager";

export default function Novedades({ novedades }) {
    return (
        <AdminLayout title="Novedades — Zona Privada">
            <NovedadesManager
                novedades={novedades}
                baseUrl="/admin/private-novedades"
                title="Novedades"
                description="Contenido en 3 idiomas con texto enriquecido."
                createLabel="Nueva novedad"
                emptyLabel="No hay novedades. Creá la primera."
            />
        </AdminLayout>
    );
}
