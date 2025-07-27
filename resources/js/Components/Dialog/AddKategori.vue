<script setup>
import { ref } from "vue";
import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";
import { useKategori } from "@/composables/useKategori";

const form = ref({
    kode_kategori: "",
    nama_kategori: "",
});

const errors = ref({
    kode_kategori: null,
    nama_kategori: null,
});

const { createKategori } = useKategori();
const {
    mutate: submitKategori,
    isLoading: isSubmitting,
    error: submitError,
} = createKategori;

const validate = () => {
    let valid = true;
    errors.value = { kode_kategori: null, nama_kategori: null };

    if (!form.value.kode_kategori.trim()) {
        errors.value.kode_kategori = "Kode kategori wajib diisi";
        valid = false;
    } else if (form.value.kode_kategori.length > 10) {
        errors.value.kode_kategori = "Maksimal 10 karakter";
        valid = false;
    }

    if (!form.value.nama_kategori.trim()) {
        errors.value.nama_kategori = "Nama kategori wajib diisi";
        valid = false;
    } else if (form.value.nama_kategori.length > 100) {
        errors.value.nama_kategori = "Maksimal 100 karakter";
        valid = false;
    }

    return valid;
};

const emit = defineEmits(["success", "close"]);

const handleSubmit = () => {
    if (validate()) {
        submitKategori(form.value, {
            onSuccess: () => {
                form.value = { kode_kategori: "", nama_kategori: "" };
                // You might want to add a success notification here
                emit("success");
            },
            onError: (error) => {
                if (error.response?.data?.errors) {
                    errors.value = {
                        ...errors.value,
                        ...error.response.data.errors,
                    };
                }
            },
        });
    }
};
</script>

<template>
    <form @submit.prevent="handleSubmit" class="space-y-6">
        <h2 class="text-xl font-semibold">Tambah Kategori</h2>

        <!-- Error message from API -->
        <div v-if="submitError" class="p-4 bg-red-100 text-red-700 rounded">
            Gagal menyimpan kategori: {{ submitError.message }}
        </div>

        <!-- Kode Kategori -->
        <div class="flex flex-col gap-1">
            <label for="kode_kategori" class="font-medium"
                >Kode Kategori <span class="text-red-500">*</span></label
            >
            <Input
                id="kode_kategori"
                v-model="form.kode_kategori"
                placeholder="Contoh: KT001"
                :disabled="isSubmitting"
                :class="{ 'border-red-500': errors.kode_kategori }"
            />
            <p v-if="errors.kode_kategori" class="text-sm text-red-500">
                {{ errors.kode_kategori }}
            </p>
        </div>

        <!-- Nama Kategori -->
        <div class="flex flex-col gap-1">
            <label for="nama_kategori" class="font-medium"
                >Nama Kategori <span class="text-red-500">*</span></label
            >
            <Input
                id="nama_kategori"
                v-model="form.nama_kategori"
                placeholder="Contoh: Elektronik"
                :disabled="isSubmitting"
                :class="{ 'border-red-500': errors.nama_kategori }"
            />
            <p v-if="errors.nama_kategori" class="text-sm text-red-500">
                {{ errors.nama_kategori }}
            </p>
        </div>

        <!-- Submit -->
        <div class="flex justify-end">
            <Button type="submit" :disabled="isSubmitting">
                <span v-if="isSubmitting">Menyimpan...</span>
                <span v-else>Simpan</span>
            </Button>
        </div>
    </form>
</template>
