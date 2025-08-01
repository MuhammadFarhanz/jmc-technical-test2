<script setup>
import { ref } from "vue";
import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";
import { useForm } from "@inertiajs/vue3";

const emit = defineEmits(["success", "close"]);

const props = defineProps({
    kategori: {
        type: Object,
        default: null,
    },
});

const form = useForm({
    kode_kategori: props.kategori?.kode_kategori || "",
    nama_kategori: props.kategori?.nama_kategori || "",
});

const validate = () => {
    let valid = true;
    form.clearErrors();

    if (!form.kode_kategori.trim()) {
        form.setError("kode_kategori", "Kode kategori wajib diisi");
        valid = false;
    } else if (form.kode_kategori.length > 10) {
        form.setError("kode_kategori", "Maksimal 10 karakter");
        valid = false;
    }

    if (!form.nama_kategori.trim()) {
        form.setError("nama_kategori", "Nama kategori wajib diisi");
        valid = false;
    } else if (form.nama_kategori.length > 100) {
        form.setError("nama_kategori", "Maksimal 100 karakter");
        valid = false;
    }

    return valid;
};

const handleSubmit = () => {
    if (!validate()) return;

    const isEdit = !!props.kategori;
    const routeName = isEdit
        ? route("kategori.update", props.kategori.id)
        : route("kategori.store");
    const method = isEdit ? "put" : "post";

    form.submit(method, routeName, {
        preserveScroll: true,
        onSuccess: () => {
            emit("success");
            emit("close");
        },
    });
};
</script>

<template>
    <form @submit.prevent="handleSubmit" class="space-y-6">
        <h2 class="text-xl font-semibold">
            {{ kategori ? "Edit Kategori" : "Tambah Kategori" }}
        </h2>

        <!-- Kode Kategori -->
        <div class="flex flex-col gap-1">
            <label for="kode_kategori" class="font-medium">
                Kode Kategori <span class="text-red-500">*</span>
            </label>
            <Input
                id="kode_kategori"
                v-model="form.kode_kategori"
                placeholder="Contoh: KT001"
                :disabled="form.processing"
                :class="{ 'border-red-500': form.errors.kode_kategori }"
            />
            <p v-if="form.errors.kode_kategori" class="text-sm text-red-500">
                {{ form.errors.kode_kategori }}
            </p>
        </div>

        <!-- Nama Kategori -->
        <div class="flex flex-col gap-1">
            <label for="nama_kategori" class="font-medium">
                Nama Kategori <span class="text-red-500">*</span>
            </label>
            <Input
                id="nama_kategori"
                v-model="form.nama_kategori"
                placeholder="Contoh: Elektronik"
                :disabled="form.processing"
                :class="{ 'border-red-500': form.errors.nama_kategori }"
            />
            <p v-if="form.errors.nama_kategori" class="text-sm text-red-500">
                {{ form.errors.nama_kategori }}
            </p>
        </div>

        <!-- Submit -->
        <div class="flex justify-end space-x-2">
            <Button
                type="button"
                variant="outline"
                @click="$emit('close')"
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
