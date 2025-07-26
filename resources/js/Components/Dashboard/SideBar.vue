<script setup>
import { ref } from "vue";
import { Link } from "@inertiajs/vue3";

const menuItems = ref([
    {
        label: "Master Data",
        items: [
            {
                label: "Kategori",
                href: "/master-data/kategori",
            },
            {
                label: "Sub Kategori",
                href: "/master-data/sub-kategori",
            },
        ],
    },
]);
</script>
<template>
    <div class="w-1/6 bg-slate-900 text-white min-h-screen flex flex-col">
        <div class="p-6 text-lg font-bold border-b border-slate-700">
            Aplikasi Pengelolaan Barang
        </div>
        <Link
            href="/dashboard"
            class="flex items-center gap-2 py-2 px-4 hover:bg-slate-700 rounded transition-colors mb-2"
        >
            <i class="pi pi-box"></i>
            <span>Barang Masuk</span>
        </Link>
        <Link
            href="/master-data/kategori"
            class="flex items-center gap-2 py-2 px-4 hover:bg-slate-700 rounded transition-colors mb-2"
        >
            <i class="pi pi-box"></i>
            <span>kategori</span>
        </Link>
        <Link
            href="/master-data/sub-kategori"
            class="flex items-center gap-2 py-2 px-4 hover:bg-slate-700 rounded transition-colors mb-2"
        >
            <i class="pi pi-box"></i>
            <span>sub katori</span>
        </Link>
        <Link
            href="/manajemen-user"
            class="flex items-center gap-2 cursor-pointer py-2 px-4 hover:bg-slate-700 rounded transition-colors mb-2"
        >
            <i class="pi pi-box"></i>
            <span>Manajemen User</span>
        </Link>
    </div>
</template>
