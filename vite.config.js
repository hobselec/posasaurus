const path = require('path')

import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue'

import { dependencies } from './package.json';
function renderChunks(deps) {
  let chunks = {};
  Object.keys(deps).forEach((key) => {
    if (['vue','sweetalert2','axios'].includes(key)) return;
    chunks[key] = [key];
  });
  return chunks;
}


export default defineConfig({
    plugins: [
        vue(),
        laravel({
            input: [
                'resources/css/app.css',
                'resources/scss/app-pos.scss',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
    ],
    resolve: {
        alias: {
          '~bootstrap': path.resolve(__dirname, 'node_modules/bootstrap'),
          vue: 'vue/dist/vue.esm-bundler.js',
        }
      },
	server : { 
        port: '3000',
        https: false,
	    hmr: {
            host: 'localhost',
            protocol: 'ws',

        },
 
	 },
     base: "/pos",
     build : {

        rollupOptions: {
            output: {
              manualChunks: {
                vendor: ['vue','sweetalert2','axios'],
                ...renderChunks(dependencies),
              },
            },
          },
     }
});
