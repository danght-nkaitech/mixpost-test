import {defineConfig, loadEnv} from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import tailwindcss from "@tailwindcss/vite";
import DefineOptions from 'unplugin-vue-define-options/vite'
import fs from 'fs';
import path from 'path'
import {homedir} from 'os';

export default defineConfig(({command, mode}) => {
    const env = loadEnv(mode, process.cwd(), '')
    let serverConfig = {}

    if (mode === 'development') {
        if (!env.APP_URL) {
            console.error('[vite] APP_URL is required in your .env file.');
            return;
        }

        const isSSLEnabled = env.ENABLE_SSL === 'true' || (env.ENABLE_SSL !== 'false' && env.APP_URL?.startsWith('https://'));

        if (isSSLEnabled && !env.CERTIFICATES_KEY_PATH) {
            console.error('[vite] SSL is enabled but CERTIFICATES_KEY_PATH is not set in your .env file.');
            return;
        }

        if (isSSLEnabled && !env.CERTIFICATES_CRT_PATH) {
            console.error('[vite] SSL is enabled but CERTIFICATES_CRT_PATH is not set in your .env file.');
            return;
        }

        const homeDir = homedir();
        const host = new URL(env.APP_URL).host;

        if (isSSLEnabled && host) {
            const keyPath = path.resolve(homeDir, env.CERTIFICATES_KEY_PATH);
            const crtPath = path.resolve(homeDir, env.CERTIFICATES_CRT_PATH);

            if (fs.existsSync(keyPath) && fs.existsSync(crtPath)) {
                serverConfig = {
                    https: {
                        key: fs.readFileSync(keyPath), cert: fs.readFileSync(crtPath),
                    }, hmr: {host}, host,
                };
            } else {
                console.error('[vite] SSL is enabled but one or both certificate files were not found.');
                return;
            }
        }
    }

    return {
        publicDir: 'vendor/mixpost-enterprise',
        plugins: [
            laravel({
                input: 'resources/js/app.js',
                publicDirectory: 'resources/dist',
                buildDirectory: 'vendor/mixpost-enterprise',
                refresh: true
            }),
            vue({
                template: {
                    transformAssetUrls: {
                        base: null,
                        includeAbsolute: false,
                    },
                },
            }),
            tailwindcss(),
            DefineOptions()
        ],
        resolve: {
            alias: {
                '@mRs': path.resolve(__dirname, '../mixpost-pro-team/resources'),
                '@mJs': path.resolve(__dirname, '../mixpost-pro-team/resources/js'),
                '@mCss': path.resolve(__dirname, '../mixpost-pro-team/resources/css'),
                '@': path.resolve(__dirname, 'resources/js'),
                '@css': path.resolve(__dirname, 'resources/css'),
                '@img': path.resolve(__dirname, 'resources/img'),
            },
        },
        server: serverConfig
    }
});
