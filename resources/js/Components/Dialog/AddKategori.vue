<script setup>
import { ref } from "vue";
import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";
import {
    Select,
    SelectTrigger,
    SelectContent,
    SelectItem,
} from "@/components/ui/select";

// Form data
const form = ref({
    kategori: null,
    namaSubKategori: "",
    batasHarga: null,
});

// Validation errors
const errors = ref({
    kategori: null,
    namaSubKategori: null,
    batasHarga: null,
});

// Sample kategori options (replace with your actual data)
const kategoriOptions = ref([
    { id: 1, name: "Perlengkapan Kantor" },
    { id: 2, name: "Elektronik" },
    { id: 3, name: "Furniture" },
]);

// Form validation
const validateForm = () => {
    let isValid = true;
    errors.value = { kategori: null, namaSubKategori: null, batasHarga: null };

    if (!form.value.kategori) {
        errors.value.kategori = "Kategori wajib dipilih";
        isValid = false;
    }

    if (!form.value.namaSubKategori.trim()) {
        errors.value.namaSubKategori = "Nama sub kategori wajib diisi";
        isValid = false;
    } else if (form.value.namaSubKategori.length > 100) {
        errors.value.namaSubKategori = "Maksimal 100 karakter";
        isValid = false;
    }

    if (
        form.value.batasHarga === null ||
        form.value.batasHarga === undefined ||
        form.value.batasHarga === ""
    ) {
        errors.value.batasHarga = "Batas harga wajib diisi";
        isValid = false;
    } else if (isNaN(Number(form.value.batasHarga))) {
        errors.value.batasHarga = "Batas harga harus berupa angka";
        isValid = false;
    }

    return isValid;
};

// Form submission
const submitForm = () => {
    if (validateForm()) {
        // Here you would typically call your API to save the data
        console.log("Form submitted:", form.value);
        alert("Data berhasil disimpan!");
    }
};

// Reset form
const resetForm = () => {
    form.value = {
        kategori: null,
        namaSubKategori: "",
        batasHarga: null,
    };
    errors.value = {
        kategori: null,
        namaSubKategori: null,
        batasHarga: null,
    };
};
</script>
<template>
    <div class="bg-white rounded-lg">
        <h2 class="text-xl font-semibold mb-6">Form Sub Kategori</h2>

        <div class="space-y-4">
            <!-- Kategori Select -->
            <div>
                <label
                    for="kategori"
                    class="block text-sm font-medium text-gray-700 mb-1"
                >
                    Kategori <span class="text-red-500">*</span>
                </label>
                <Select v-model="form.kategori">
                    <SelectTrigger
                        class="w-full border"
                        :class="{ 'border-red-500': errors.kategori }"
                    >
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

            <!-- Nama Sub Kategori -->
            <div>
                <label
                    for="namaSubKategori"
                    class="block text-sm font-medium text-gray-700 mb-1"
                >
                    Nama Sub Kategori <span class="text-red-500">*</span>
                </label>
                <Input
                    id="namaSubKategori"
                    v-model="form.namaSubKategori"
                    placeholder="Masukkan nama sub kategori"
                    class="w-full"
                    :class="{ 'border-red-500': errors.namaSubKategori }"
                    maxlength="100"
                />
                <small v-if="errors.namaSubKategori" class="text-red-500">{{
                    errors.namaSubKategori
                }}</small>
                <small class="text-gray-500 text-xs"
                    >Maksimal 100 karakter</small
                >
            </div>

            <!-- Batas Harga -->
            <div>
                <label
                    for="batasHarga"
                    class="block text-sm font-medium text-gray-700 mb-1"
                >
                    Batas Harga <span class="text-red-500">*</span>
                </label>
                <Input
                    id="batasHarga"
                    v-model="form.batasHarga"
                    type="number"
                    min="0"
                    placeholder="Masukkan batas harga"
                    class="w-full"
                    :class="{ 'border-red-500': errors.batasHarga }"
                />
                <small v-if="errors.batasHarga" class="text-red-500">{{
                    errors.batasHarga
                }}</small>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end space-x-3 pt-4">
                <Button variant="outline" @click="resetForm">Batal</Button>
                <Button variant="default" @click="submitForm">Simpan</Button>
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
