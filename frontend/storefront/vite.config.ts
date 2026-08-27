import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'frontend/storefront/css/app.css',
                'frontend/storefront/islands/main.tsx',
            ],
            refresh: ['frontend/storefront/views/**'],
        }),
        react(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
