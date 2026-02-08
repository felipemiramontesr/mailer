/// <reference types="vitest" />
import vue from '@vitejs/plugin-vue';
import { defineConfig } from 'vite';

// https://vite.dev/config/
export default defineConfig({
  plugins: [vue()],
  build: {
    assetsDir: '', // No assets subfolder
  },
  test: {
    environment: 'jsdom',
    globals: true,
    server: {
      deps: {
        inline: ['lucide-vue-next'],
      },
    },
  },
});
