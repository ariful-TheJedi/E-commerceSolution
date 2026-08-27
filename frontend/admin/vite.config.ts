import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import { resolve } from 'node:path';
import { unlinkSync, writeFileSync } from 'node:fs';

const hotFile = resolve(__dirname, '../../public/hot-admin');

export default defineConfig({
    plugins: [
        react(),
        {
            name: 'hot-file',
            configureServer(server) {
                writeFileSync(hotFile, 'http://localhost:5174');
                const cleanup = (): void => {
                    try {
                        unlinkSync(hotFile);
                    } catch {
                        //
                    }
                };
                server.httpServer?.once('close', cleanup);
            },
        },
    ],
    root: __dirname,
    base: '/build/admin/',
    server: {
        port: 5174,
        strictPort: true,
        origin: 'http://localhost:5174',
    },
    build: {
        outDir: resolve(__dirname, '../../public/build/admin'),
        emptyOutDir: true,
        manifest: true,
        rollupOptions: {
            input: resolve(__dirname, 'src/main.tsx'),
        },
    },
});
