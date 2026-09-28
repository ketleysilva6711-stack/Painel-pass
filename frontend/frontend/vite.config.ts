import vue from '@vitejs/plugin-vue'
import { defineConfig } from 'vite'
import path from 'path'

// https://vite.dev/config/
export default defineConfig({
  plugins: [vue()],
  resolve: {
    alias: {
      '@': path.resolve(__dirname, 'src'),
      'qrcode': path.resolve(__dirname, 'node_modules/qrcode/lib/browser.js'),
    },
  },
  optimizeDeps: {
    include: ['qrcode'],
  },
  build: {
    rollupOptions: {
      external: [],
    },
    commonjsOptions: {
      include: [/qrcode/, /node_modules/],
    },
  },
})