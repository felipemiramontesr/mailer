/// <reference types="vitest" />
import vue from '@vitejs/plugin-vue';
import { defineConfig } from 'vitest/config';

// https://vite.dev/config/
export default defineConfig({
  plugins: [vue()],
  build: {
    assetsDir: '', // No assets subfolder
  },
  test: {
    environment: 'jsdom',
    globals: true,
    css: true,
    deps: {
      optimizer: {
        web: {
          include: ['lucide-vue-next'],
        },
      },
    },
  },
});
