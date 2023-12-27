import { fileURLToPath, URL } from 'url';

import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import tsconfigPaths from 'vite-tsconfig-paths';
import { sentryVitePlugin } from "@sentry/vite-plugin";

// https://vitejs.dev/config/
export default defineConfig(({ command }) => {
    return {
        build: {
            sourcemap: true, 
          },
        plugins: [
            tsconfigPaths(), 
            vue(),
            sentryVitePlugin({
                org: "fullbl",
                project: "belluno-hora-fe",
                authToken: import.meta.env.VITE_SENTRY_AUTH_TOKEN,
              }),
        ],
        base: '/',
        resolve: {
            alias: {
                '@': fileURLToPath(new URL('./src', import.meta.url)),
            }
        },
        server: {
            host: true
        }
        
    };
});
