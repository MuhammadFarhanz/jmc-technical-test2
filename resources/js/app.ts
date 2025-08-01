import "../css/app.css";
import "./bootstrap";

import { createInertiaApp } from "@inertiajs/vue3";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import { createApp, DefineComponent, h } from "vue";
import { ZiggyVue } from "../../vendor/tightenco/ziggy";
import { QueryClient, VueQueryPlugin } from "@tanstack/vue-query";
import { plugin, defaultConfig } from "@formkit/vue";
import Toaster from "@/components/ui/toast/Toaster.vue";
const appName = import.meta.env.VITE_APP_NAME || "Laravel";

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    // resolve: (name) =>
    //     resolvePageComponent(
    //         `./Pages/${name}.vue`,
    //         import.meta.glob<DefineComponent>("./Pages/**/*.vue")
    //     ),

    resolve: (name: string) => {
        const pages = import.meta.glob("./Pages/**/*.vue", { eager: true });
        return pages[`./Pages/${name}.vue`] as DefineComponent;
    },
    setup({ el, App, props, plugin }) {
        const queryClient = new QueryClient({
            defaultOptions: {
                queries: {
                    refetchOnWindowFocus: false,
                    staleTime: 5 * 60 * 1000, // 5 minutes
                },
            },
        });

        const vueApp = createApp({
            render: () => h(App, props),
        });

        vueApp.use(plugin);
        vueApp.use(ZiggyVue);
        vueApp.use(VueQueryPlugin, { queryClient });

        // Register Toaster globally
        vueApp.component("Toaster", Toaster);

        vueApp.mount(el);
    },
    progress: {
        color: "#4B5563",
    },
});
