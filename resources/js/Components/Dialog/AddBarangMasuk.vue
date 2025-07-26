<script setup>
import { ref, computed, onMounted } from "vue";
import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";
import {
    Select,
    SelectTrigger,
    SelectContent,
    SelectItem,
} from "@/components/ui/select";
import { Calendar } from "@/components/ui/calendar";

const isAdmin = ref(false); // Set to true if admin
const props = defineProps({
    visible: {
        type: Boolean,
        required: true,
    },
});
const emit = defineEmits(["update:visible"]);

// Form data
const form = ref({
    operator: null,
    kategori: null,
    subKategori: null,
    batasHarga: null,
    asalBarang: "",
    nomorSurat: "",
    lampiran: null,
    barang: [
        {
            namaBarang: "",
            harga: null,
            jumlah: 1,
            satuan: "",
            total: 0,
            tglExpired: null,
        },
    ],
});

// Options for dropdowns (mock data - replace with your API calls)
const operatorOptions = ref([
    { id: 1, name: "Budi" },
    { id: 2, name: "Andi" },
    { id: 3, name: "Siti" },
]);

const kategoriOptions = ref([
    { id: 1, name: "Perlengkapan Kantor" },
    { id: 2, name: "Elektronik" },
    { id: 3, name: "Furniture" },
]);

const subKategoriOptions = ref([]);
const allSubKategori = ref([
    { id: 1, kategoriId: 1, name: "Alat Tulis", batasHarga: 5000000 },
    { id: 2, kategoriId: 1, name: "Kertas", batasHarga: 3000000 },
    { id: 3, kategoriId: 2, name: "Komputer", batasHarga: 15000000 },
    { id: 4, kategoriId: 2, name: "Printer", batasHarga: 8000000 },
    { id: 5, kategoriId: 3, name: "Meja", batasHarga: 5000000 },
    { id: 6, kategoriId: 3, name: "Kursi", batasHarga: 4000000 },
]);

// Errors
const errors = ref({
    operator: null,
    kategori: null,
    subKategori: null,
    asalBarang: null,
    nomorSurat: null,
    barang: [],
});

// Computed properties
const totalKeseluruhan = computed(() => {
    return form.value.barang.reduce((sum, item) => sum + (item.total || 0), 0);
});

const isOverBudget = computed(() => {
    if (!form.value.batasHarga) return false;
    return totalKeseluruhan.value > form.value.batasHarga;
});

// Methods
const loadSubKategori = () => {
    form.value.subKategori = null;
    form.value.batasHarga = null;
    subKategoriOptions.value = allSubKategori.value.filter(
        (sub) => sub.kategoriId === form.value.kategori
    );
};

const loadBatasHarga = () => {
    const selectedSubKategori = allSubKategori.value.find(
        (sub) => sub.id === form.value.subKategori
    );
    form.value.batasHarga = selectedSubKategori
        ? selectedSubKategori.batasHarga
        : null;
};

const addBarangRow = () => {
    form.value.barang.push({
        namaBarang: "",
        harga: null,
        jumlah: 1,
        satuan: "",
        total: 0,
        tglExpired: null,
    });
    errors.value.barang.push({});
};

const removeBarangRow = (index) => {
    form.value.barang.splice(index, 1);
    errors.value.barang.splice(index, 1);
};

const calculateTotal = (index) => {
    const item = form.value.barang[index];
    item.total = (item.harga || 0) * (item.jumlah || 0);
};

const handleFileUpload = (event) => {
    form.value.lampiran = event.target.files[0];
};

const formatCurrency = (value) => {
    if (!value) return "Rp 0";
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
    }).format(value);
};

const validateForm = () => {
    let isValid = true;
    errors.value = {
        operator: null,
        kategori: null,
        subKategori: null,
        asalBarang: null,
        nomorSurat: null,
        barang: [],
    };

    // Validate operator (only if admin)
    if (isAdmin.value && !form.value.operator) {
        errors.value.operator = "Operator wajib dipilih";
        isValid = false;
    }

    // Validate kategori
    if (!form.value.kategori) {
        errors.value.kategori = "Kategori wajib dipilih";
        isValid = false;
    }

    // Validate sub kategori
    if (!form.value.subKategori) {
        errors.value.subKategori = "Sub kategori wajib dipilih";
        isValid = false;
    }

    // Validate asal barang
    if (!form.value.asalBarang.trim()) {
        errors.value.asalBarang = "Asal barang wajib diisi";
        isValid = false;
    } else if (form.value.asalBarang.length > 200) {
        errors.value.asalBarang = "Maksimal 200 karakter";
        isValid = false;
    }

    // Validate nomor surat
    if (form.value.nomorSurat && form.value.nomorSurat.length > 100) {
        errors.value.nomorSurat = "Maksimal 100 karakter";
        isValid = false;
    }

    // Validate barang items
    form.value.barang.forEach((item, index) => {
        errors.value.barang[index] = {};

        // Validate nama barang
        if (!item.namaBarang.trim()) {
            errors.value.barang[index].namaBarang = "Nama barang wajib diisi";
            isValid = false;
        } else if (item.namaBarang.length > 200) {
            errors.value.barang[index].namaBarang = "Maksimal 200 karakter";
            isValid = false;
        }

        // Validate harga
        if (!item.harga) {
            errors.value.barang[index].harga = "Harga wajib diisi";
            isValid = false;
        }

        // Validate jumlah
        if (!item.jumlah) {
            errors.value.barang[index].jumlah = "Jumlah wajib diisi";
            isValid = false;
        }

        // Validate satuan
        if (!item.satuan.trim()) {
            errors.value.barang[index].satuan = "Satuan wajib diisi";
            isValid = false;
        } else if (item.satuan.length > 40) {
            errors.value.barang[index].satuan = "Maksimal 40 karakter";
            isValid = false;
        }
    });

    return isValid;
};

const submitForm = () => {
    if (validateForm()) {
        if (isOverBudget.value) {
            alert("Total harga melebihi batas harga sub kategori!");
            return;
        }

        // Here you would typically call your API to save the data
        console.log("Form submitted:", form.value);
        alert("Data berhasil disimpan!");
        // router.push("/barang-masuk");
        emit("update:visible", false);
    }
};

// Initialize form based on user role
onMounted(() => {
    // Set operator automatically if not admin
    if (!isAdmin.value) {
        form.value.operator = 1; // Assuming current user is Budi (id: 1)
    }
});
</script>
<template>
    <div class="h-full p-4">
        <h1 class="text-2xl font-bold mb-6">Barang Masuk</h1>

        <div class="bg-white rounded-lg shadow p-6">
            <!-- INFORMASI UMUM -->
            <div class="mb-8">
                <h2 class="text-lg font-semibold border-b pb-2 mb-4">
                    INFORMASI UMUM
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Operator -->
                    <div class="flex flex-col gap-2">
                        <label for="operator" class="font-medium"
                            >Operator</label
                        >
                        <Select v-model="form.operator" :disabled="!isAdmin">
                            <SelectTrigger class="w-full border">
                                {{
                                    operatorOptions.find(
                                        (opt) => opt.id === form.operator
                                    )?.name || "Pilih Operator"
                                }}
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="opt in operatorOptions"
                                    :key="opt.id"
                                    :value="opt.id"
                                >
                                    {{ opt.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <small v-if="errors.operator" class="text-red-500">{{
                            errors.operator
                        }}</small>
                    </div>

                    <!-- Kategori -->
                    <div class="flex flex-col gap-2">
                        <label for="kategori" class="font-medium"
                            >Kategori <span class="text-red-500">*</span></label
                        >
                        <Select
                            v-model="form.kategori"
                            @update:modelValue="loadSubKategori"
                        >
                            <SelectTrigger class="w-full border">
                                {{
                                    kategoriOptions.find(
                                        (opt) => opt.id === form.kategori
                                    )?.name || "Pilih Kategori"
                                }}
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="opt in kategoriOptions"
                                    :key="opt.id"
                                    :value="opt.id"
                                >
                                    {{ opt.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <small v-if="errors.kategori" class="text-red-500">{{
                            errors.kategori
                        }}</small>
                    </div>

                    <!-- Sub Kategori -->
                    <div class="flex flex-col gap-2">
                        <label for="subKategori" class="font-medium"
                            >Sub Kategori
                            <span class="text-red-500">*</span></label
                        >
                        <Select
                            v-model="form.subKategori"
                            :disabled="!form.kategori"
                            @update:modelValue="loadBatasHarga"
                        >
                            <SelectTrigger class="w-full border">
                                {{
                                    subKategoriOptions.find(
                                        (opt) => opt.id === form.subKategori
                                    )?.name || "Pilih Sub Kategori"
                                }}
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="opt in subKategoriOptions"
                                    :key="opt.id"
                                    :value="opt.id"
                                >
                                    {{ opt.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <small v-if="errors.subKategori" class="text-red-500">{{
                            errors.subKategori
                        }}</small>
                    </div>

                    <!-- Batas Harga -->
                    <div class="flex flex-col gap-2">
                        <label for="batasHarga" class="font-medium"
                            >Batas Harga</label
                        >
                        <Input
                            v-model="form.batasHarga"
                            readonly
                            class="w-full"
                        />
                    </div>

                    <!-- Asal Barang -->
                    <div class="flex flex-col gap-2">
                        <label for="asalBarang" class="font-medium"
                            >Asal Barang
                            <span class="text-red-500">*</span></label
                        >
                        <Input
                            v-model="form.asalBarang"
                            placeholder="Masukkan asal barang"
                            class="w-full"
                            :class="{ 'border-red-500': errors.asalBarang }"
                        />
                        <small v-if="errors.asalBarang" class="text-red-500">{{
                            errors.asalBarang
                        }}</small>
                    </div>

                    <!-- Nomor Surat -->
                    <div class="flex flex-col gap-2">
                        <label for="nomorSurat" class="font-medium"
                            >Nomor Surat</label
                        >
                        <Input
                            v-model="form.nomorSurat"
                            placeholder="Masukkan nomor surat"
                            class="w-full"
                            :class="{ 'border-red-500': errors.nomorSurat }"
                        />
                        <small v-if="errors.nomorSurat" class="text-red-500">{{
                            errors.nomorSurat
                        }}</small>
                    </div>

                    <!-- Lampiran -->
                    <div class="flex flex-col gap-2">
                        <label for="lampiran" class="font-medium"
                            >Lampiran</label
                        >
                        <input
                            type="file"
                            id="lampiran"
                            @change="handleFileUpload"
                            accept=".doc,.docx,.zip"
                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                        />
                        <small class="text-gray-500"
                            >Format: doc, docx, zip (max 5MB)</small
                        >
                    </div>
                </div>
            </div>

            <!-- INFORMASI BARANG -->
            <div class="mb-8">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-semibold">INFORMASI BARANG</h2>
                    <Button variant="outline" @click="addBarangRow"
                        >Tambah Barang</Button
                    >
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full border">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="py-2 px-4 border">
                                    Nama Barang
                                    <span class="text-red-500">*</span>
                                </th>
                                <th class="py-2 px-4 border">
                                    Harga (Rp.)
                                    <span class="text-red-500">*</span>
                                </th>
                                <th class="py-2 px-4 border">
                                    Jumlah <span class="text-red-500">*</span>
                                </th>
                                <th class="py-2 px-4 border">
                                    Satuan <span class="text-red-500">*</span>
                                </th>
                                <th class="py-2 px-4 border">Total (Rp.)</th>
                                <th class="py-2 px-4 border">Tgl. Expired</th>
                                <th class="py-2 px-4 border">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="(item, index) in form.barang"
                                :key="index"
                                class="border"
                            >
                                <!-- Nama Barang -->
                                <td class="py-2 px-4 border">
                                    <Input
                                        v-model="item.namaBarang"
                                        placeholder="Nama barang"
                                        class="w-full"
                                        :class="{
                                            'border-red-500':
                                                errors.barang &&
                                                errors.barang[index]
                                                    ?.namaBarang,
                                        }"
                                    />
                                    <small
                                        v-if="
                                            errors.barang &&
                                            errors.barang[index]?.namaBarang
                                        "
                                        class="text-red-500"
                                    >
                                        {{ errors.barang[index].namaBarang }}
                                    </small>
                                </td>

                                <!-- Harga -->
                                <td class="py-2 px-4 border">
                                    <Input
                                        type="number"
                                        v-model="item.harga"
                                        class="w-full"
                                        @input="calculateTotal(index)"
                                        :class="{
                                            'border-red-500':
                                                errors.barang &&
                                                errors.barang[index]?.harga,
                                        }"
                                    />
                                    <small
                                        v-if="
                                            errors.barang &&
                                            errors.barang[index]?.harga
                                        "
                                        class="text-red-500"
                                    >
                                        {{ errors.barang[index].harga }}
                                    </small>
                                </td>

                                <!-- Jumlah -->
                                <td class="py-2 px-4 border">
                                    <Input
                                        type="number"
                                        min="1"
                                        v-model="item.jumlah"
                                        class="w-full"
                                        @input="calculateTotal(index)"
                                        :class="{
                                            'border-red-500':
                                                errors.barang &&
                                                errors.barang[index]?.jumlah,
                                        }"
                                    />
                                    <small
                                        v-if="
                                            errors.barang &&
                                            errors.barang[index]?.jumlah
                                        "
                                        class="text-red-500"
                                    >
                                        {{ errors.barang[index].jumlah }}
                                    </small>
                                </td>

                                <!-- Satuan -->
                                <td class="py-2 px-4 border">
                                    <Input
                                        v-model="item.satuan"
                                        placeholder="Satuan"
                                        class="w-full"
                                        :class="{
                                            'border-red-500':
                                                errors.barang &&
                                                errors.barang[index]?.satuan,
                                        }"
                                    />
                                    <small
                                        v-if="
                                            errors.barang &&
                                            errors.barang[index]?.satuan
                                        "
                                        class="text-red-500"
                                    >
                                        {{ errors.barang[index].satuan }}
                                    </small>
                                </td>

                                <!-- Total -->
                                <td class="py-2 px-4 border">
                                    <Input
                                        v-model="item.total"
                                        readonly
                                        class="w-full"
                                    />
                                </td>

                                <!-- Tgl Expired -->
                                <td class="py-2 px-4 border">
                                    <!-- <Calendar
                                        v-model="item.tglExpired"
                                        class="w-full"
                                    /> -->
                                </td>

                                <!-- Aksi -->
                                <td class="py-2 px-4 border text-center">
                                    <Button
                                        variant="destructive"
                                        @click="removeBarangRow(index)"
                                        v-if="form.barang.length > 1"
                                    >
                                        Hapus
                                    </Button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 flex justify-between items-center">
                    <div class="font-semibold">
                        Total Keseluruhan:
                        {{ formatCurrency(totalKeseluruhan) }}
                    </div>
                    <div v-if="form.batasHarga" class="font-semibold">
                        Batas Harga: {{ formatCurrency(form.batasHarga) }}
                        <span v-if="isOverBudget" class="text-red-500 ml-2"
                            >(Melebihi batas!)</span
                        >
                    </div>
                </div>
            </div>

            <!-- ACTION BUTTONS -->
            <div class="flex justify-end gap-2">
                <Button
                    variant="outline"
                    @click="$emit('update:visible', false)"
                >
                    Kembali
                </Button>
                <Button
                    variant="default"
                    @click="submitForm"
                    :disabled="isOverBudget"
                >
                    Simpan
                </Button>
            </div>
        </div>
    </div>
</template>

<style scoped>
.border-red-500 {
    border-color: #f44336 !important;
}
.text-red-500 {
    color: #f44336;
}
</style>
