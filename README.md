<div align="center">
<img src="docs/readme/banner.svg" alt="Gran Sasso — Catálogo de cajas de cambio por marca, con códigos OEM y zona privada de clientes" width="100%">
<br/>

**Sitio corporativo con catálogo técnico de cajas de cambio, zona privada de clientes y panel de gestión.**

<img alt="Proyecto cliente" src="https://img.shields.io/badge/estado-proyecto_cliente-136cba?style=for-the-badge&labelColor=101010">
<img alt="Laravel 12" src="https://img.shields.io/badge/Laravel-12-ff2d20?style=flat-square&logo=laravel&logoColor=white">
<img alt="PHP 8.2+" src="https://img.shields.io/badge/PHP-8.2%2B-777bb4?style=flat-square&logo=php&logoColor=white">
<img alt="React 18" src="https://img.shields.io/badge/React-18-149eca?style=flat-square&logo=react&logoColor=white">
<img alt="Inertia.js 2" src="https://img.shields.io/badge/Inertia.js-2-9553e9?style=flat-square">
<img alt="Tailwind CSS 4" src="https://img.shields.io/badge/Tailwind_CSS-4-06b6d4?style=flat-square&logo=tailwindcss&logoColor=white">
<img alt="Vite 7" src="https://img.shields.io/badge/Vite-7-646cff?style=flat-square&logo=vite&logoColor=white">
</div>

<img src="docs/readme/divider.svg" alt="" width="100%">

## 📑 Contenido

[El problema](#-el-problema) · [La solución](#-la-solución) · [Funcionalidades](#-funcionalidades) · [Arquitectura](#-arquitectura) · [Flujos de trabajo](#-flujos-de-trabajo) · [Modelo de datos](#-modelo-de-datos) · [Criterios y decisiones](#-criterios-y-decisiones) · [Stack](#-stack) · [Estructura](#-estructura-del-repo) · [Puesta en marcha](#-puesta-en-marcha) · [Pruebas y calidad](#-pruebas-y-calidad) · [Despliegue](#-despliegue) · [Estado y pendientes](#-estado-y-pendientes) · [Licencia y autoría](#-licencia-y-autoría)

<img src="docs/readme/divider.svg" alt="" width="100%">

## 🧩 El problema

Encontrar una pieza para una caja de cambios exige cruzar marca, caja, producto y códigos de fabricante original (OEM). Ese cruce se hacía en planillas, y los precios y documentos de los clientes mayoristas no tenían un lugar propio: se enviaban por otros medios y se actualizaban a mano.

## 💡 La solución

**Gran Sasso** es una aplicación Laravel con tres perfiles:

- **Visitantes**: sitio institucional y catálogo navegable por marca, caja de cambios y producto, con fichas PDF y documentación.
- **Clientes registrados**: zona privada con listas de precios, novedades, notificaciones e información de pagos. Las cuentas nuevas quedan pendientes hasta que el equipo las aprueba.
- **Administración**: panel para catálogo, importaciones, CMS, usuarios, mensajes y estadísticas.

<img src="docs/readme/divider.svg" alt="" width="100%">

## ✨ Funcionalidades

**Sitio público** (rol: visitante)
- Inicio, empresa, calidad, novedades, contacto y desarrollos especiales.
- Catálogo por marca, caja y producto, con búsqueda, imágenes, documentación y fichas PDF.
- Suscripción a newsletter y acceso flotante a WhatsApp con enlace configurable.

**Zona privada** (rol: cliente aprobado)
- Listas de precios, novedades y notificaciones.
- Presentación de comprobantes de pago con archivo adjunto.

**Panel administrativo** (rol: administrador)
- Marcas, cajas, productos y sus ítems, con códigos OEM, aplicaciones y variantes.
- Importación de XLSX, XLS y CSV en formato plano y de catálogo, con autodetección, agrupación y reporte de altas, actualizaciones y errores.
- CMS de páginas, secciones, banners, sliders, galería, calidad, novedades, SEO y datos de contacto.
- Aprobación de clientes, usuarios, mensajes y registro de visitas para el dashboard.

<img src="docs/readme/divider.svg" alt="" width="100%">

## 🏗️ Arquitectura

Monolito Laravel con tres guards de acceso: `web` para el panel, `client` para la zona privada y el público sin autenticación. Las páginas usan Inertia con React y las plantillas de PDF se generan en Blade. Los contenidos se guardan en JSON con fallback a español.

<img src="docs/readme/flujo.svg" alt="Cómo viaja una petición: el visitante explora marca, caja y producto, Blade o Inertia responden, Laravel importa, valida y envía correo, la base guarda productos, ítems y contenidos" width="100%">

```mermaid
flowchart LR
    V["Visitante"] --> PUB["Sitio público (Inertia + React)"]
    C["Cliente aprobado"] --> PRV["Zona privada (guard client)"]
    A["Administrador"] --> ADM["Panel (guard web)"]
    PUB --> CTL["Controladores"]
    PRV --> CTL
    ADM --> CTL
    CTL --> VAL["Validación y sanitización del CMS"]
    CTL --> IMP["Importadores XLSX, XLS y CSV"]
    CTL --> PDF["Fichas PDF desde Blade"]
    CTL --> EL["Eloquent"]
    IMP --> Q["Cola"]
    Q --> EL
    EL --> DB[("Base de datos")]
    CTL --> ST["Storage privado y público"]
    CTL --> ML["Correo (campañas en cola sin envío conectado)"]
```

<img src="docs/readme/divider.svg" alt="" width="100%">

## 🔄 Flujos de trabajo

**Alta y aprobación de un cliente**

```mermaid
stateDiagram-v2
    [*] --> Pendiente: se registra
    Pendiente --> Aprobado: el administrador aprueba
    Pendiente --> Rechazado: el administrador rechaza
    Aprobado --> [*]
    Rechazado --> [*]
```

**Comprobante de pago**

```mermaid
sequenceDiagram
    actor Cliente
    participant Zona as Zona privada
    participant Laravel as Laravel
    participant ST as Storage privado
    participant DB as Base de datos
    participant Admin as Panel
    Cliente->>Zona: Sube el comprobante con su archivo
    Zona->>Laravel: Envía el formulario
    Laravel->>Laravel: Valida tipo, formato y tamaño
    Laravel->>ST: Guarda el archivo en disco privado
    Laravel->>DB: Registra la presentación del pago
    Admin->>Laravel: Consulta el comprobante desde ruta protegida
    Laravel-->>Admin: Descarga autorizada
```

<img src="docs/readme/divider.svg" alt="" width="100%">

## 🗃️ Modelo de datos

Eloquent con 65 migraciones y 31 modelos en el repositorio.

```mermaid
erDiagram
    BRAND ||--o{ GEARBOX : tiene
    GEARBOX ||--o{ PRODUCT : principal
    PRODUCT ||--o{ PRODUCT_ITEM : contiene
    PAGE ||--o{ PAGE_SECTION : contiene
    PAGE_SECTION ||--o{ PAGE_SECTION_IMAGE : incluye
    PRIVATE_USER ||--o{ PAYMENT_SUBMISSION : presenta
    PRIVATE_USER ||--o{ PRIVATE_NOTIFICATION : recibe
    BRAND {
        bigint id PK
        string slug
    }
    PRODUCT {
        bigint id PK
    }
    PRIVATE_USER {
        bigint id PK
    }
```

La caja es única por marca y slug; el producto, por caja y código Gran Sasso. Un producto tiene una caja principal y puede vincularse a cajas secundarias. Cada ítem conserva códigos OEM y aplicaciones sin duplicar el producto base.

<img src="docs/readme/divider.svg" alt="" width="100%">

## 🧭 Criterios y decisiones

- **Producto base con ítems.** Un mismo producto agrupa códigos OEM y variantes, para no repetir la pieza por cada código.
- **Clientes aprobados antes de ver precios.** Las cuentas nuevas pasan por aprobación administrativa.
- **Guards separados.** Panel y zona privada tienen usuarios, proveedores y sesiones independientes.
- **Documentos privados.** Comprobantes y adjuntos de desarrollos viven en almacenamiento privado y se descargan solo por rutas protegidas.

| Decisión | Por qué |
| --- | --- |
| Monolito con Inertia y React | Un solo despliegue y una sola base para sitio, zona privada y panel. |
| Importación con detección de formato | El catálogo llega en planillas planas o jerárquicas. |
| Contenido multilingüe en JSON | Español, inglés y portugués en un mismo registro, con fallback a español. |

<img src="docs/readme/divider.svg" alt="" width="100%">

## 🧱 Stack

| Capa | Tecnología | Versión |
| --- | --- | --- |
| Lenguaje | PHP | ^8.2 |
| Backend | Laravel framework | ^12 (lock 12.54.1) |
| Integración panel | inertiajs/inertia-laravel | 2.0.22 |
| API y sesiones | Laravel Sanctum | 4.3.1 |
| Importación | maatwebsite/excel | 3.1.67 |
| Frontend | React | 18.3.1 |
| Frontend | @inertiajs/react | 2.3.18 |
| Estilos | Tailwind CSS | 4.2.1 |
| Build | Vite | 7.3.1 |
| Base de datos | SQLite en `.env.example`; otras conexiones por configuración | — |

<img src="docs/readme/divider.svg" alt="" width="100%">

## 🗂️ Estructura del repo

```text
app/Http/Controllers/     público, zona privada y administración
app/Models/               entidades Eloquent (31)
database/migrations/      esquema relacional (65)
resources/js/             páginas y componentes React (Inertia)
resources/views/          Blade para el público y las fichas PDF
routes/                   rutas públicas, privadas y del panel
storage/                  archivos privados y públicos
tests/                    pruebas unitarias y feature
```

<img src="docs/readme/divider.svg" alt="" width="100%">

## 🚀 Puesta en marcha

Requisitos: PHP 8.2+, Composer, Node.js y npm.

```bash
composer install
npm ci
cp .env.example .env              # definir base, correo y almacenamiento localmente
php artisan key:generate
touch database/database.sqlite    # si se usa SQLite
php artisan migrate --seed
php artisan storage:link
composer run dev                  # servidor, cola, logs y Vite en paralelo
```

Variables relevantes (por nombre): `DB_*`, `MAIL_*`, `QUEUE_CONNECTION`, `FILESYSTEM_*` y la lista de correos administradores, configurada fuera del código. Los valores reales no se versionan.

<img src="docs/readme/divider.svg" alt="" width="100%">

## 🧪 Pruebas y calidad

Pruebas unitarias y feature sobre la portada y el flujo de autenticación administrativa: render del login, credenciales válidas e inválidas y cierre de sesión.

```bash
composer test
vendor/bin/pint --test
npm run build
```

La última ejecución registrada en la versión anterior de este README fue de 6 pruebas aprobadas; no se volvió a ejecutar para este cambio. No hay pruebas E2E, de carga ni pipeline CI versionados.

<img src="docs/readme/divider.svg" alt="" width="100%">

## 🚢 Despliegue

No definido en el repositorio. No hay manifiestos Docker, Kubernetes ni configuración de CI/CD. Servidor web, workers, TLS, copias de seguridad y gestión de secretos se definen fuera del código.

<img src="docs/readme/divider.svg" alt="" width="100%">

## 📌 Estado y pendientes

- **Estado:** aplicación funcional con catálogo, CMS, zona privada, importaciones y panel. Producción no verificada.
- **Último commit:** 2026-04-30, `VERSION 1.3.5`, en el historial local.
- **Pendientes:**
  - La acción de envío de campañas de newsletter es un stub: la infraestructura de correo en cola existe, pero el controlador no despacha el job.
  - El acceso a WhatsApp es solo un enlace; no hay integración con su API.
  - Ampliar la cobertura de pruebas más allá de la portada y la autenticación.

<img src="docs/readme/divider.svg" alt="" width="100%">

## 📄 Licencia y autoría

Desarrollado por el **Ing. Santiago Abasto**.


Repositorio sin archivo de licencia. El código y el contenido son propiedad del cliente, Gran Sasso, salvo que se acuerde otra cosa.
