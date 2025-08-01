<script setup>
import { ref } from "vue";
import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";
import {
    Select,
    SelectTrigger,
    SelectContent,
    SelectItem,
    SelectValue,
} from "@/components/ui/select";
import { useForm, usePage } from "@inertiajs/vue3";
import { useToast } from "@/components/ui/toast/use-toast";

const { toast } = useToast();
const { kategori } = usePage().props;

const emit = defineEmits(["success", "cancel"]);

// Initialize form with Inertia's useForm
const form = useForm({
    kategori_id: "",
    nama_subkategori: "",
    batas_harga: "",
});

const validate = () => {
    let valid = true;
    form.clearErrors();

    if (!form.kategori_id) {
        form.setError("kategori_id", "Kategori wajib dipilih");
        valid = false;
    }

    if (!form.nama_subkategori.trim()) {
        form.setError("nama_subkategori", "Nama sub kategori wajib diisi");
        valid = false;
    } else if (form.nama_subkategori.length > 100) {
        form.setError("nama_subkategori", "Maksimal 100 karakter");
        valid = false;
    }

    if (!form.batas_harga) {
        form.setError("batas_harga", "Batas harga wajib diisi");
        valid = false;
    } else if (Number(form.batas_harga) < 1) {
        form.setError("batas_harga", "Batas harga minimal 1");
        valid = false;
    }

    return valid;
};

const submit = () => {
    if (!validate()) return;

    const payload = {
        kategori_id: form.kategori_id,
        nama_subkategori: form.nama_subkategori,
        batas_harga: form.batas_harga || null,
    };

    form.post(route("subkategori.store"), {
        data: payload,
        preserveScroll: true,
        onSuccess: () => {
            toast({
                title: "Berhasil",
                description: "Sub kategori berhasil ditambahkan",
            });
            form.reset();
            emit("success");
        },
        onError: () => {
            toast({
                title: "Gagal",
                description: "Terjadi kesalahan saat menyimpan data",
                variant: "destructive",
            });
        },
    });
};
</script>

<template>
    <form @submit.prevent="submit" class="space-y-4">
        <h2 class="text-lg font-semibold">Tambah Sub Kategori</h2>

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
                        v-for="item in kategori"
                        :key="item.id"
                        :value="item.id"
                    >
                        {{ item.nama_kategori }}
                    </SelectItem>
                </SelectContent>
            </Select>
            <p v-if="form.errors.kategori_id" class="text-sm text-red-500">
                {{ form.errors.kategori_id }}
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
                :class="{ 'border-red-500': form.errors.nama_subkategori }"
            />
            <p v-if="form.errors.nama_subkategori" class="text-sm text-red-500">
                {{ form.errors.nama_subkategori }}
            </p>
        </div>

        <!-- Batas Harga -->
        <div class="space-y-2">
            <label class="block text-sm font-medium"> Batas Harga </label>
            <Input
                v-model="form.batas_harga"
                type="number"
                min="0"
                placeholder="Masukkan batas harga"
                :class="{ 'border-red-500': form.errors.batas_harga }"
            />
            <p v-if="form.errors.batas_harga" class="text-sm text-red-500">
                {{ form.errors.batas_harga }}
            </p>
        </div>

        <!-- Form Actions -->
        <div class="flex justify-end gap-2 pt-4">
            <Button
                type="button"
                variant="outline"
                @click="$emit('cancel')"
                :disabled="form.processing"
            >
                Batal
            </Button>
            <Button type="submit" :disabled="form.processing">
                <span v-if="form.processing">Menyimpan...</span>
                <span v-else>Simpan</span>
            </Button>
        </div>
    </form>
</template>
