import path from "path";
import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import { bunny } from "laravel-vite-plugin/fonts";
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    plugins: [
        laravel({
            input: ["resources/js/app.tsx", "resources/css/app.css"],
            refresh: true,
            fonts: [
                bunny("Instrument Sans", {
                    weights: [400, 500, 600],
                }),
            ],
        }),
        tailwindcss(),
    ],
    // server: {
    //     watch: {
    //         ignored: ['**/storage/framework/views/**'],
    //     },
    // },

    resolve: {
        alias: {
            "@": path.resolve(__dirname, "resources/js"),
            "@components": path.resolve(__dirname, "resources/js/components"),
            "@pages": path.resolve(__dirname, "resources/js/pages"),
            "@stores": path.resolve(__dirname, "resources/js/stores"),
            "@api": path.resolve(__dirname, "resources/js/api"),
            "@types": path.resolve(__dirname, "resources/js/types"),
            "@utils": path.resolve(__dirname, "resources/js/utils"),
        },
    },
    server: {
        host: "0.0.0.0", // Важно: слушаем все интерфейсы
        port: 5173,
        hmr: {
            host: "stud-conf.loc", // Домен, который вы используете
            port: 5173,
            protocol: "ws", // WebSocket protocol
        },
        cors: true, // Разрешаем CORS для разработки
        strictPort: true, // Используем только указанный порт
    },
});
