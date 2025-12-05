import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import { v4wp } from '@kucrut/vite-for-wp';
import { fileURLToPath, URL } from 'node:url';
import tsconfigPaths from 'vite-tsconfig-paths';
import * as path from 'path';

// https://vite.dev/config/
export default defineConfig({
  css: {
    preprocessorOptions: {
      scss: {
        api: 'modern-compiler',
      },
    },
  },
  server: {
    cors: true,
  },
  plugins: [
    v4wp({
      input: {
        admin: '/src/admin/main.ts',
        widget: '/src/widget/main.ts',
        sticky: '/src/sticky/main.ts',
      },
      outDir: './dist',
    }),
    vue(),
    tsconfigPaths(),
  ],
  resolve: {
    alias: {
      '@/styles': '/assets',
      '@/images': '/assets/images',
      '@/admin': fileURLToPath(new URL('./src/admin/', import.meta.url)),
      '@/widget': fileURLToPath(new URL('./src/widget/', import.meta.url)),
      '@/sticky': fileURLToPath(new URL('./src/sticky/', import.meta.url)),

      // Force all imports of vue-demi to use the same copy from your project's node_modules
      'vue-demi': path.resolve(__dirname, 'node_modules/vue-demi'),
    },
  },
  build: {
    minify: true,
  },
});
