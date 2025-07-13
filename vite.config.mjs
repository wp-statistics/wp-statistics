import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import path from 'path';
import { fileURLToPath } from 'url';
import cssInjectedByJsPlugin from 'vite-plugin-css-injected-by-js';

const __filename = fileURLToPath(
    import.meta.url);
const __dirname = path.dirname(__filename);

export default defineConfig({
    plugins: [
        react({
            jsxRuntime: 'automatic',
            babel: {
                plugins: [
                    ['@emotion/babel-plugin']
                ]
            }
        }),
        cssInjectedByJsPlugin()
    ],
    css: {
        preprocessorOptions: {
            scss: {
                includePaths: [path.resolve(__dirname, 'assets/scss')],
                additionalData: `@use "sass:math";`
            }
        },
        modules: {
            localsConvention: 'camelCase'
        },
        devSourcemap: true
    },
    build: {
        outDir: 'assets/dist/react',
        watch: process.env.WATCH === 'true' ? {} : null,
        rollupOptions: {
            input: {
                migration: path.resolve(__dirname, 'assets/js/react/pages/DataMigration/index.jsx')
            },
            external: [
                'wp',
                '@WpStatistics/dashboard',
                'wps_react'
            ],
            output: {
                format: 'iife',
                name: 'WpStatisticsReact',
                inlineDynamicImports: true,
                globals: {
                    wp: 'wp',
                    '@WpStatistics/dashboard': 'wps_react',
                    wps_react: 'wps_react',
                },
                entryFileNames: '[name].js',
                chunkFileNames: '[name].[hash].js'
            }
        }
    },
    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'assets/js/react'),
            '@scss': path.resolve(__dirname, 'assets/scss')
        }
    }
});