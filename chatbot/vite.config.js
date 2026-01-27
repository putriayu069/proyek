import { defineConfig } from 'vite';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    root: './resources',
    build: {
        outDir: '../dist',
        emptyOutDir: true,
    },
    plugins: [
        tailwindcss(),
    ],
});