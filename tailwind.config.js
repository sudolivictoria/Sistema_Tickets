import defaultTheme from "tailwindcss/defaultTheme";
import forms from "@tailwindcss/forms";
import containerQueries from "@tailwindcss/container-queries";

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
        "./resources/js/**/*.js",
    ],

    darkMode: "class",
    theme: {
        extend: {
            colors: {
                primary: "#84cc16",
                secondary: "#04003B",
                "background-light": "#f8fafc",
                "background-dark": "#0f172a",
            },
            fontFamily: {
                display: ["Inter", "sans-serif"],
            },
        },
    },

    plugins: [forms, containerQueries],
};
