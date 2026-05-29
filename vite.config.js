import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";

export default defineConfig({
    plugins: [
        laravel({
            input: [
                "resources/css/app.css",
                "resources/css/tickets.css",
                "resources/css/login.css",
                "resources/css/graficos.css",
                "resources/js/app.js",
                "resources/js/mis-tickets.js",
                "resources/js/gestion-usuarios.js",
                "resources/js/gestion-recursos.js",
                "resources/js/recursos.js",
                "resources/js/usuario.js",
                "resources/js/mis-asignados.js",
                "resources/js/ticket-form.js",
                "resources/js/asignar-tickets.js",
                "resources/js/usuario.js",
                "resources/js/gestor.js",
                "resources/js/admin.js",
                "resources/js/usuario-menu.js",
                "resources/js/historial.js",
                "resources/js/reportes.js",
                "resources/js/api.js",
                "resources/js/recursos-api.js"
            ],
            refresh: true,
        }),
    ],
});
