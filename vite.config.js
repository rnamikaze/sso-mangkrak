import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import react from "@vitejs/plugin-react";

export default defineConfig({
    publicDir: "",
    plugins: [
        laravel({
            input: "resources/js/app.jsx",
            refresh: true,
        }),
        react(),
    ],
    optimizeDeps: {
        include: ["react", "react-dom"], // Include any modules you want Vite to handle
    },
	base: './',
});
