import { defineConfig } from 'vite';
import tailwindcss from '@tailwindcss/vite';
import { resolve } from 'path';

export default defineConfig({
    root: './resources',
    build: {
        outDir: '../dist',
        emptyOutDir: true,
        rollupOptions: {
            input: {
                main: resolve(__dirname, 'resources/index.html'),
                about: resolve(__dirname, 'resources/about.html'),
                products: resolve(__dirname, 'resources/products.html'),
                contact: resolve(__dirname, 'resources/contact.html'),
            },
        },
    },
    plugins: [
        tailwindcss(),
    ],
});