import {defineConfig} from 'vite';
import react from '@vitejs/plugin-react';

// https://vitejs.dev/config/
/** @type {import('vite').UserConfig} */
export default defineConfig({
    plugins: [react()],
    build: {
        watch: {
            include: 'src/**'
        },
        outDir: 'build',
        rollupOptions: {
            external: ['react', 'react-dom'],
            output: {
                // assetFileNames: 'assets/[name][extname]',
                entryFileNames: 'learningcompanions-chat.min.js',
                format: 'iife',
                globals: {
                    react: 'React',
                    'react-dom': 'ReactDOM'
                }
            }
        }
    },
    esbuild: {
        loader: 'jsx',
        include: /src\/.*\.jsx?$/,
        exclude: [],
    },
    optimizeDeps: {
        esbuildOptions: {
            loader: {
                '.js': 'jsx',
            },
        },
    },
});
