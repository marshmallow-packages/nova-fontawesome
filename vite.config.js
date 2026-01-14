import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import path from 'path';

export default defineConfig({
    plugins: [
        vue(),
    ],

    define: {
        'process.env.NODE_ENV': JSON.stringify(process.env.NODE_ENV || 'production'),
    },

    build: {
        outDir: path.resolve(__dirname, 'dist'),
        emptyOutDir: true,
        manifest: false,
        lib: {
            entry: path.resolve(__dirname, 'resources/js/field.js'),
            name: 'NovaFontawesome',
            formats: ['umd'],
            fileName: () => 'js/nova-fontawesome.js',
        },
        rollupOptions: {
            external: ['vue', 'nova', 'laravel-nova-ui'],
            output: {
                globals: {
                    vue: 'Vue',
                    nova: 'Nova',
                    'laravel-nova-ui': 'LaravelNovaUi',
                },
                assetFileNames: 'css/[name][extname]',
            },
        },
        sourcemap: false,
        minify: process.env.NODE_ENV === 'production',
    },

    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'resources/js'),
        },
    },
});
