import { fileURLToPath, URL } from 'node:url'

import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

// https://vitejs.dev/config/
export default defineConfig({

  resolve: {
    alias: {
	  vue: '@vue/compat',
      '@': fileURLToPath(new URL('./src', import.meta.url))
    }
  },
  plugins: [
    vue({
      template: {
        compilerOptions: {
          compatConfig: {
            MODE: 3
          }
        }
      }
    })
  ],
   server: {
        port: '9100',
        https: false,
        hmr: {
            host: 'localhost',
            protocol: 'ws'
        }
    },
	test: {
		globals: true,
		environment: 'jsdom'
	}
})
