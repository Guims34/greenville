import { defineConfig, loadEnv } from 'vite';
import react from '@vitejs/plugin-react';
import { VitePWA } from 'vite-plugin-pwa';
import viteCompression from 'vite-plugin-compression';

export default defineConfig(({ mode }) => {
  const env = loadEnv(mode, process.cwd(), '');
  
  return {
    plugins: [
      react(),
      VitePWA({
        registerType: 'autoUpdate',
        includeAssets: ['favicon.ico', 'robots.txt', 'apple-touch-icon.png'],
        manifest: {
          name: 'GreenVille',
          short_name: 'GreenVille',
          description: 'Simulation de Culture de Cannabis',
          theme_color: '#10b981',
          background_color: '#ffffff',
          icons: [
            {
              src: 'pwa-192x192.png',
              sizes: '192x192',
              type: 'image/png'
            },
            {
              src: 'pwa-512x512.png',
              sizes: '512x512',
              type: 'image/png'
            }
          ]
        }
      }),
      viteCompression({
        algorithm: 'gzip',
        ext: '.gz'
      }),
      viteCompression({
        algorithm: 'brotliCompress',
        ext: '.br'
      })
    ],
    server: {
      port: 4000,
      open: true,
      host: true // Permet l'accès depuis le réseau local
    },
    preview: {
      port: 4173,
      open: true,
      host: true
    },
    build: {
      sourcemap: true,
      rollupOptions: {
        output: {
          manualChunks: {
            vendor: ['react', 'react-dom', 'react-router-dom'],
            ui: ['framer-motion', 'lucide-react', 'react-hot-toast'],
            form: ['react-hook-form', '@hookform/resolvers', 'zod'],
            auth: ['@react-oauth/google', '@greatsumini/react-facebook-login']
          }
        }
      },
      chunkSizeWarningLimit: 1000
    }
  };
});