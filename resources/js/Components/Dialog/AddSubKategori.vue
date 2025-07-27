<script setup>
import { ref, watch } from "vue";
import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";
import {
    Select,
    SelectTrigger,
    SelectContent,
    SelectItem,
    SelectValue,
} from "@/components/ui/select";
import { useSubKategori } from "@/composables/useSubKategori";
import { useToast } from "@/components/ui/toast/use-toast";

const { toast } = useToast();
const props = defineProps({
    kategoriOptions: {
        type: Array,
        default: () => [],
    },
    initialData: {
        type: Object,
        default: null, // Allow null initially
    },
});

const emit = defineEmits(["success", "cancel"]);

// Initialize form with empty values
const form = ref({
    kategori_id: "",
    nama_subkategori: "",
    batas_harga: "",
});

const errors = ref({
    kategori_id: null,
    nama_subkategori: null,
    batas_harga: null,
});

const { createSubKategori, updateSubKategori } = useSubKategori();
const isEditing = ref(false);
const isSubmitting = ref(false);

// Initialize or reset form
const initForm = () => {
    const initialData = props.initialData || {};
    form.value = {
        kategori_id: initialData.kategori_id || "",
        nama_subkategori: initialData.nama_subkategori || "",
        batas_harga: initialData.batas_harga || "",
    };
    isEditing.value = !!initialData.id;
    errors.value = {
        kategori_id: null,
        nama_subkategori: null,
        batas_harga: null,
    };
};

// Initialize on mount and when initialData changes
initForm();
watch(() => props.initialData, initForm);

const validate = () => {
    let valid = true;
    errors.value = {
        kategori_id: null,
        nama_subkategori: null,
        batas_harga: null,
    };

    if (!form.value.kategori_id) {
        errors.value.kategori_id = "Kategori wajib dipilih";
        valid = false;
    }

    if (!form.value.nama_subkategori.trim()) {
        errors.value.nama_subkategori = "Nama sub kategori wajib diisi";
        valid = false;
    } else if (form.value.nama_subkategori.length > 100) {
        errors.value.nama_subkategori = "Maksimal 100 karakter";
        valid = false;
    }

    if (form.value.batas_harga && isNaN(Number(form.value.batas_harga))) {
        errors.value.batas_harga = "Batas harga harus berupa angka";
        valid = false;
    }

    return valid;
};

const submit = async () => {
    if (!validate()) return;

    isSubmitting.value = true;

    try {
        const payload = {
            kategori_id: form.value.kategori_id,
            nama_subkategori: form.value.nama_subkategori,
            batas_harga: form.value.batas_harga || null,
        };

        if (isEditing.value) {
            await updateSubKategori.mutateAsync({
                id: props.initialData.id,
                ...payload,
            });
            toast({
                title: "Berhasil",
                description: "Sub kategori berhasil diperbarui",
                variant: "default",
            });
        } else {
            await createSubKategori.mutateAsync(payload);
            toast({
                title: "Berhasil",
                description: "Sub kategori berhasil ditambahkan",
                variant: "default",
            });
        }

        emit("success");
    } catch (error) {
        if (error.response?.data?.errors) {
            errors.value = {
                ...errors.value,
                ...error.response.data.errors,
            };
        }
        toast({
            title: "Gagal",
            description: error.response?.data?.message || "Terjadi kesalahan",
            variant: "destructive",
        });
    } finally {
        isSubmitting.value = false;
    }
};
</script>

<template>
    <form @submit.prevent="submit" class="space-y-4">
        <h2 class="text-lg font-semibold">
            {{ isEditing ? "Edit Sub Kategori" : "Tambah Sub Kategori" }}
        </h2>

        <!-- Kategori Select -->
        <div class="space-y-2">
            <label class="block text-sm font-medium">
                Kategori <span class="text-red-500">*</span>
            </label>
            <Select v-model="form.kategori_id">
                <SelectTrigger>
                    <SelectValue placeholder="Pilih Kategori" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem
                        v-for="kategori in kategoriOptions"
                        :key="kategori.id"
                        :value="kategori.id"
                    >
                        {{ kategori.nama_kategori || kategori.name }}
                    </SelectItem>
                </SelectContent>
            </Select>
            <p v-if="errors.kategori_id" class="text-sm text-red-500">
                {{ errors.kategori_id }}
            </p>
        </div>

        <!-- Nama Sub Kategori -->
        <div class="space-y-2">
            <label class="block text-sm font-medium">
                Nama Sub Kategori <span class="text-red-500">*</span>
            </label>
            <Input
                v-model="form.nama_subkategori"
                placeholder="Masukkan nama sub kategori"
            />
            <p v-if="errors.nama_subkategori" class="text-sm text-red-500">
                {{ errors.nama_subkategori }}
            </p>
        </div>

        <!-- Batas Harga -->
        <div class="space-y-2">
            <label class="block text-sm font-medium"> Batas Harga </label>
            <Input
                v-model="form.batas_harga"
                type="number"
                placeholder="Masukkan batas harga"
            />
            <p v-if="errors.batas_harga" class="text-sm text-red-500">
                {{ errors.batas_harga }}
            </p>
        </div>

        <!-- Form Actions -->
        <div class="flex justify-end gap-2 pt-4">
            <Button
                type="button"
                variant="outline"
                @click="$emit('cancel')"
                :disabled="isSubmitting"
            >
                Batal
            </Button>
            <Button type="submit" :disabled="isSubmitting">
                <span v-if="isSubmitting">Menyimpan...</span>
                <span v-else>Simpan</span>
            </Button>
        </div>
    </form>
</template>
