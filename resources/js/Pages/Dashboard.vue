<script setup>
import { ref } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import BarangMasuk from "./BarangMasuk.vue";
import DataTableBarangMasuk from "@/Components/Dashboard/DataTableBarangMasuk.vue";

// const showDialog = ref(false);
// const suppliers = ref([{ name: "PT. Sidomakmur" }, { name: "PT. Sejahtera" }]);

// const barangMasuk = ref([
//     {
//         no: 1,
//         tanggal: "10/10/23 10:00:00",
//         asalBarang: "PT. Sidomakmur",
//         penerima: "Liana",
//         unit: "Gudang Utama",
//         kode: "ATK0001",
//         nama: "Kertas HVS A4 60Gr",
//         harga: 61000,
//         jumlah: "4 Rim",
//         total: 244000,
//     },
//     {
//         no: 2,
//         tanggal: "10/10/23 11:00:00",
//         asalBarang: "PT. Sejahtera",
//         penerima: "Liana",
//         unit: "Gudang Utama",
//         kode: "ATK0002",
//         nama: "Bolpoint Pilot Std",
//         harga: 20000,
//         jumlah: "5 Dus",
//         total: 100000,
//     },
//     {
//         no: 3,
//         tanggal: "10/10/23 11:00:00",
//         asalBarang: "PT. Sejahtera",
//         penerima: "Liana",
//         unit: "Gudang Utama",
//         kode: "ATK0001",
//         nama: "Kertas HVS A4 60Gr",
//         harga: 60000,
//         jumlah: "1 Rim",
//         total: 60000,
//     },
//     {
//         no: 4,
//         tanggal: "10/10/23 11:00:00",
//         asalBarang: "PT. Sejahtera",
//         penerima: "Liana",
//         unit: "Gudang Utama",
//         kode: "ATK0002",
//         nama: "Bolpoint Pilot Std",
//         harga: 20000,
//         jumlah: "1 Dus",
//         total: 20000,
//     },
// ]);

// const newBarang = ref({
//     tanggal: null,
//     asalBarang: null,
//     penerima: "",
//     unit: "",
//     kode: "",
//     nama: "",
//     harga: 0,
//     jumlah: "",
// });

// const formatCurrency = (value) => {
//     return new Intl.NumberFormat("id-ID", {
//         style: "currency",
//         currency: "IDR",
//         minimumFractionDigits: 0,
//     }).format(value);
// };

// const saveBarang = () => {
//     const total = newBarang.value.harga * parseInt(newBarang.value.jumlah);
//     barangMasuk.value.push({
//         no: barangMasuk.value.length + 1,
//         tanggal: newBarang.value.tanggal.toLocaleString(),
//         asalBarang: newBarang.value.asalBarang?.name || "",
//         penerima: newBarang.value.penerima,
//         unit: newBarang.value.unit,
//         kode: newBarang.value.kode,
//         nama: newBarang.value.nama,
//         harga: newBarang.value.harga,
//         jumlah: newBarang.value.jumlah,
//         total: total,
//     });
//     showDialog.value = false;
//     resetForm();
// };

// const resetForm = () => {
//     newBarang.value = {
//         tanggal: null,
//         asalBarang: null,
//         penerima: "",
//         unit: "",
//         kode: "",
//         nama: "",
//         harga: 0,
//         jumlah: "",
//     };
// };
</script>

<template>
    <AuthenticatedLayout>
        <!-- <BarangMasuk /> -->
        <div class="flex w-5/6">
            <DataTableBarangMasuk />
        </div>
    </AuthenticatedLayout>
</template>

<style scoped></style>
