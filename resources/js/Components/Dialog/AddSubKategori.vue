<script setup>
import { ref, watch } from "vue";
import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";
import {
    Dialog,
    DialogTrigger,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogFooter,
} from "@/components/ui/dialog";
import {
    Select,
    SelectTrigger,
    SelectContent,
    SelectItem,
    SelectValue,
} from "@/components/ui/select";

const props = defineProps({
    visible: Boolean,
    kategoriOptions: {
        type: Array,
        default: () => [],
    },
    initialData: {
        type: Object,
        default: () => ({}),
    },
});

const emit = defineEmits(["update:visible", "submit"]);

const form = ref({
    kategori: props.initialData.kategori || "",
    nama: props.initialData.nama || "",
    batasHarga: props.initialData.batasHarga || "",
});

const errors = ref({
    kategori: null,
    nama: null,
    batasHarga: null,
});

watch(
    () => props.visible,
    (val) => {
        if (val) {
            form.value = {
                kategori: props.initialData.kategori || "",
                nama: props.initialData.nama || "",
                batasHarga: props.initialData.batasHarga || "",
            };
        }
    }
);

const close = () => {
    emit("update:visible", false);
};

const validate = () => {
    let valid = true;
    errors.value = {
        kategori: null,
        nama: null,
        batasHarga: null,
    };

    if (!form.value.kategori) {
        errors.value.kategori = "Kategori wajib dipilih";
        valid = false;
    }

    if (!form.value.nama.trim()) {
        errors.value.nama = "Nama sub kategori wajib diisi";
        valid = false;
    } else if (form.value.nama.length > 100) {
        errors.value.nama = "Maksimal 100 karakter";
        valid = false;
    }

    if (!form.value.batasHarga || isNaN(Number(form.value.batasHarga))) {
        errors.value.batasHarga = "Batas harga harus berupa angka";
        valid = false;
    }

    return valid;
};

const submit = () => {
    if (validate()) {
        emit("submit", form.value);
        close();
    }
};
</script>

<template>
    <div>
        <div class="max-w-xl w-full bg-white rounded-lg p-6">
            <DialogHeader>
                <DialogTitle>Form Sub Kategori</DialogTitle>
            </DialogHeader>

            <div class="flex flex-col gap-4 mt-2">
                <!-- Select Kategori -->
                <div class="flex flex-col gap-1">
                    <label class="font-medium"
                        >Kategori <span class="text-red-500">*</span></label
                    >
                    <Select v-model="form.kategori">
                        <SelectTrigger class="w-full">
                            <SelectValue placeholder="Pilih Kategori" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="kategori in kategoriOptions"
                                :key="kategori.id"
                                :value="kategori.id"
                            >
                                {{ kategori.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <small v-if="errors.kategori" class="text-red-500 text-sm">
                        {{ errors.kategori }}
                    </small>
                </div>

                <!-- Nama Sub Kategori -->
                <div class="flex flex-col gap-1">
                    <label class="font-medium"
                        >Nama Sub Kategori
                        <span class="text-red-500">*</span></label
                    >
                    <Input
                        v-model="form.nama"
                        placeholder="Masukkan nama sub kategori"
                        class="w-full"
                    />
                    <small v-if="errors.nama" class="text-red-500 text-sm">
                        {{ errors.nama }}
                    </small>
                </div>

                <!-- Batas Harga -->
                <div class="flex flex-col gap-1">
                    <label class="font-medium"
                        >Batas Harga <span class="text-red-500">*</span></label
                    >
                    <Input
                        v-model="form.batasHarga"
                        type="number"
                        placeholder="Masukkan batas harga (angka)"
                        class="w-full"
                    />
                    <small
                        v-if="errors.batasHarga"
                        class="text-red-500 text-sm"
                    >
                        {{ errors.batasHarga }}
                    </small>
                </div>
            </div>

            <DialogFooter class="mt-6 flex justify-end gap-2">
                <Button variant="ghost" @click="close">Batal</Button>
                <Button @click="submit">Submit</Button>
            </DialogFooter>
        </div>
    </div>
</template>
