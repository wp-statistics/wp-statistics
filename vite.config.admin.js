import { defineConfig } from 'vite'
import { resolve } from 'path'

export default defineConfig({
  publicDir: false,
  build: {
    outDir: resolve(__dirname, 'public/admin'),
    emptyOutDir: true,
    minify: 'terser',
    terserOptions: {
      compress: {
        drop_console: false,
      },
      format: {
        comments: false,
      },
    },
    rollupOptions: {
      input: {
        'js/command-palette': resolve(__dirname, 'resources/admin/js/command-palette.js'),
      },
      output: {
        entryFileNames: '[name].js',
      },
    },
    target: 'es2020',
  },
})
