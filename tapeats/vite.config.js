import { defineConfig } from "vite";
// import tailwindcss from '@tailwindcss/vite'
import laravel from "laravel-vite-plugin";

export default defineConfig({
    server: {
        host: "0.0.0.0",
        hmr: {
            host: "localhost",
        },
    },
    plugins: [
        // tailwindcss(),
        laravel({
            input: ["resources/css/app.css", "resources/js/app.js"],
            refresh: true,
        }),
    ],
});
