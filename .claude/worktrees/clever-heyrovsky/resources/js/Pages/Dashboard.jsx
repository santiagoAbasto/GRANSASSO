import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';

export default function Dashboard({ auth }) {

    return (
        <AuthenticatedLayout user={auth.user}>

            <Head title="Dashboard" />

            <div style={{ padding: "40px" }}>

                <h1 style={{ fontSize: "28px", fontWeight: "600" }}>
                    Panel administrador
                </h1>

                <p style={{ marginTop: "10px", color: "#666" }}>
                    Bienvenido {auth.user.name}
                </p>

            </div>

        </AuthenticatedLayout>
    );
}