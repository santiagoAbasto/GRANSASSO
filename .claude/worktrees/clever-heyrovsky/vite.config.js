import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import react from '@vitejs/plugin-react'
import tailwindcss from '@tailwindcss/vite'

export default defineConfig({

    server: {
        host: '127.0.0.1',
        port: 5173,
        strictPort: true,
    },

    plugins: [
        laravel({
            input: [

                // ADMIN
                'resources/js/app.jsx',
                'resources/css/admin/app.css',

                // WEB
                'resources/css/web.css',
                'resources/js/web.js',

            ],
            refresh: true,
        }),

        react(),
        tailwindcss(),

    ],

    resolve: {
        alias: {
            '@': '/resources/js',
        },
    },

})