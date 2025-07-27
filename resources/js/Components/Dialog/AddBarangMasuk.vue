<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from "vue";
import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";
import {
    Select,
    SelectTrigger,
    SelectContent,
    SelectItem,
} from "@/components/ui/select";
import { Calendar } from "@/components/ui/calendar";
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from "@/components/ui/popover";
import { useToast } from "@/components/ui/toast/use-toast";
import { Calendar as CalendarIcon } from "lucide-vue-next";
import { useQuery, useMutation } from "@tanstack/vue-query";
import axios from "axios";
import { debounce } from "lodash-es";
import { useKategori } from "@/composables/useKategori";
import { useSubKategori } from "@/composables/useSubKategori";
import { useItems } from "@/composables/useItems";
import { useUser } from "@/composables/useUser";

// Constants
const ALLOWED_FILE_TYPES = [
    "application/msword",
    "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
    "application/zip",
];
const MAX_FILE_SIZE = 5 * 1024 * 1024; // 5MB

// Composables
const { toast } = useToast();
const { kategoris, isLoading: isLoadingKategori } = useKategori();
const { subKategorisQuery, isLoading: isLoadingSubKategori } = useSubKategori();
const { createMutation } = useItems();

const props = defineProps({
    visible: { type: Boolean, required: true },
});

// const emitButton = defineEmits(["update:visible", "submitted"]);
const emit = defineEmits(["update:visible", false]);

const currentUser = ref({
    id: null,
    name: "",
    isAdmin: false,
});
const { operatorOptions } = useUser();

onMounted(async () => {
    try {
        const { data } = await axios.get("/user/current");
        currentUser.value = data;
        isAdmin.value = data.isAdmin;

        if (!isAdmin.value) {
            form.value.user_id = data.id;
        }
    } catch (error) {
        console.error("Failed to fetch current user:", error);
    }
});

// State
const isAdmin = ref(false);
const form = ref(initializeForm());
const errors = ref(initializeErrors());
const isSubmitting = ref(false);

// Computed
const kategoriOptions = computed(
    () =>
        kategoris.value?.map((k) => ({ id: k.id, name: k.nama_kategori })) || []
);

const subKategoriOptions = computed(() =>
    (subKategorisQuery.data?.value ?? []).map((sk) => ({
        id: sk.id,
        name: sk.nama_subkategori,
        batas_harga: sk.batas_harga,
    }))
);

const totalKeseluruhan = computed(() =>
    form.value.barang.reduce(
        (sum, item) => sum + (item.harga || 0) * (item.jumlah || 1),
        0
    )
);

const isOverBudget = computed(
    () =>
        form.value.batas_harga &&
        totalKeseluruhan.value > form.value.batas_harga
);

// Watchers
watch(form, saveDraft, { deep: true });

watch(
    () => props.visible,
    (val) => val && loadDraft()
);

watch(
    () => form.value.kategori_id,
    () => loadSubKategori()
);

watch(
    () => form.value.sub_kategori_id,
    () => loadBatasHarga()
);

// Lifecycle hooks
onMounted(() => {
    loadDraft();
    window.addEventListener("keydown", handleKeyDown);
    if (!isAdmin.value && operatorOptions.value.length) {
        form.value.user_id = operatorOptions.value[0].id;
    }
});

onUnmounted(() => {
    window.removeEventListener("keydown", handleKeyDown);
});

// Methods
function initializeForm() {
    return {
        user_id: null,
        kategori_id: null,
        sub_kategori_id: null,
        batas_harga: null,
        nomor_surat: "",
        asal_barang: "",
        lampiran: null,
        barang: [createBarangItem()],
    };
}

function initializeErrors() {
    return {
        user_id: null,
        kategori_id: null,
        sub_kategori_id: null,
        asal_barang: null,
        nomor_surat: null,
        barang: [],
    };
}

function createBarangItem() {
    return {
        nama: "",
        harga: null,
        jumlah: 1,
        satuan: "",
        tgl_expired: null,
    };
}

function loadDraft() {
    const draft = localStorage.getItem("barangMasukDraft");
    if (draft) form.value = JSON.parse(draft);
}

function saveDraft() {
    localStorage.setItem("barangMasukDraft", JSON.stringify(form.value));
}

async function loadSubKategori() {
    await subKategorisQuery.refetch();
    form.value.sub_kategori_id = null;
    form.value.batas_harga = null;
}

function loadBatasHarga() {
    const selectedSubKategori = subKategoriOptions.value?.find(
        (sub) => sub.id === form.value.sub_kategori_id
    );
    form.value.batas_harga = selectedSubKategori?.batas_harga || null;
}

function addBarangRow() {
    form.value.barang.push(createBarangItem());
    errors.value.barang.push({});
}

function removeBarangRow(index) {
    if (
        form.value.barang[index].harga > 0 &&
        !confirm("Anda yakin ingin menghapus barang ini?")
    ) {
        return;
    }
    form.value.barang.splice(index, 1);
    errors.value.barang.splice(index, 1);
}

const calculateTotal = debounce((index) => {
    const item = form.value.barang[index];
    item.total = (item.harga || 0) * (item.jumlah || 0);
}, 300);

function handleFileUpload(event) {
    const file = event.target.files[0];
    if (!file) return;

    if (!isValidFileType(file) || !isValidFileSize(file)) {
        event.target.value = "";
        return;
    }

    form.value.lampiran = file;
}

function isValidFileType(file) {
    const isValid =
        ALLOWED_FILE_TYPES.includes(file.type) ||
        file.name.match(/\.(doc|docx|zip)$/);

    if (!isValid) {
        showFileError(
            "Format file tidak didukung. Harap unggah file doc, docx, atau zip."
        );
    }

    return isValid;
}

function isValidFileSize(file) {
    const isValid = file.size <= MAX_FILE_SIZE;

    if (!isValid) {
        showFileError("Ukuran file terlalu besar. Maksimal 5MB.");
    }

    return isValid;
}

function showFileError(message) {
    toast({
        title: "Error",
        description: message,
        variant: "destructive",
    });
}

function formatCurrency(value) {
    if (!value) return "Rp 0";
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
    }).format(value);
}

function formatDate(date) {
    if (!date) return "";
    return new Date(date).toLocaleDateString("id-ID", {
        day: "2-digit",
        month: "2-digit",
        year: "numeric",
    });
}

function validateForm() {
    errors.value = initializeErrors();
    let isValid = true;

    // Required fields validation
    const requiredFields = [
        { field: "user_id", message: "User ID is required" },
        { field: "kategori_id", message: "Kategori is required" },
        { field: "sub_kategori_id", message: "Sub Kategori is required" },
        { field: "asal_barang", message: "Asal Barang is required" },
    ];

    requiredFields.forEach(({ field, message }) => {
        if (!form.value[field]) {
            errors.value[field] = message;
            isValid = false;
        }
    });

    // Validate barang items
    form.value.barang.forEach((item, index) => {
        const itemErrors = {};

        if (!item.nama.trim()) {
            itemErrors.nama = "Nama Barang is required";
            isValid = false;
        }

        if (!item.harga || item.harga <= 0) {
            itemErrors.harga = "Harga must be greater than 0";
            isValid = false;
        }

        if (!item.jumlah || item.jumlah <= 0) {
            itemErrors.jumlah = "Jumlah must be greater than 0";
            isValid = false;
        }

        if (!item.satuan.trim()) {
            itemErrors.satuan = "Satuan is required";
            isValid = false;
        }

        if (Object.keys(itemErrors).length) {
            errors.value.barang[index] = itemErrors;
        }
    });

    return isValid;
}

async function submitForm() {
    if (!validateForm()) return;

    if (isOverBudget.value) {
        toast({
            title: "Peringatan",
            description: `Total harga melebihi batas harga sub kategori sebesar ${formatCurrency(
                totalKeseluruhan.value - form.value.batas_harga
            )}`,
            variant: "destructive",
        });
        return;
    }

    isSubmitting.value = true;

    try {
        const formData = new FormData();

        Object.entries(form.value).forEach(([key, value]) => {
            if (key === "lampiran") {
                if (value) formData.append(key, value);
            } else if (key === "barang") {
                // Append each barang item separately
                value.forEach((item, index) => {
                    Object.entries(item).forEach(([itemKey, itemValue]) => {
                        formData.append(
                            `barang[${index}][${itemKey}]`,
                            itemValue
                        );
                    });
                });
            } else if (value !== null) {
                formData.append(key, value);
            }
        });

        await createMutation.mutateAsync(formData);

        resetForm();
        emit("update:visible", false);
    } catch (error) {
        console.error(error);
    } finally {
        isSubmitting.value = false;
    }
}

function resetForm() {
    form.value = initializeForm();
    errors.value = initializeErrors();
    localStorage.removeItem("barangMasukDraft");
}

function handleKeyDown(e) {
    if (e.ctrlKey && e.key === "Enter") {
        submitForm();
    }
}
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
                        <Select v-model="form.user_id" :disabled="!isAdmin">
                            <SelectTrigger class="w-full border">
                                {{
                                    currentUser
                                        ? !isAdmin
                                            ? currentUser.name
                                            : operatorOptions.find(
                                                  (opt) =>
                                                      opt.id === form.user_id
                                              )?.name || "Pilih Operator"
                                        : "Loading..."
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
                        <small v-if="errors.user_id" class="text-red-500">{{
                            errors.user_id
                        }}</small>
                    </div>

                    <!-- Kategori -->
                    <div class="flex flex-col gap-2">
                        <label for="kategori" class="font-medium">
                            Kategori <span class="text-red-500">*</span>
                        </label>
                        <Select v-model="form.kategori_id">
                            <SelectTrigger class="w-full border">
                                {{
                                    kategoriOptions.find(
                                        (opt) => opt.id === form.kategori_id
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
                        <small v-if="errors.kategori_id" class="text-red-500">{{
                            errors.kategori_id
                        }}</small>
                    </div>

                    <!-- Sub Kategori -->
                    <div class="flex flex-col gap-2">
                        <label for="subKategori" class="font-medium">
                            Sub Kategori <span class="text-red-500">*</span>
                        </label>
                        <Select
                            v-model="form.sub_kategori_id"
                            :disabled="
                                !form.kategori_id || isLoadingSubKategori
                            "
                        >
                            <SelectTrigger class="w-full border">
                                {{
                                    isLoadingSubKategori
                                        ? "Memuat..."
                                        : subKategoriOptions.find(
                                              (opt) =>
                                                  opt.id ===
                                                  form.sub_kategori_id
                                          )?.name || "Pilih Sub Kategori"
                                }}
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="opt in subKategoriOptions"
                                    :key="opt.id"
                                    :value="opt.id"
                                >
                                    {{ opt.name }} ({{
                                        formatCurrency(opt.batas_harga)
                                    }})
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <small
                            v-if="errors.sub_kategori_id"
                            class="text-red-500"
                        >
                            {{ errors.sub_kategori_id }}
                        </small>
                    </div>

                    <!-- Batas Harga -->
                    <div class="flex flex-col gap-2">
                        <label for="batasHarga" class="font-medium"
                            >Batas Harga</label
                        >
                        <Input
                            :value="formatCurrency(form.batas_harga)"
                            readonly
                            class="w-full"
                            :class="{ 'border-red-500': isOverBudget }"
                        />
                    </div>

                    <!-- Asal Barang -->
                    <div class="flex flex-col gap-2">
                        <label for="asal_barang" class="font-medium">
                            Asal Barang <span class="text-red-500">*</span>
                        </label>
                        <Input
                            v-model="form.asal_barang"
                            placeholder="Masukkan asal barang"
                            class="w-full"
                            :class="{ 'border-red-500': errors.asal_barang }"
                        />
                        <small v-if="errors.asal_barang" class="text-red-500">{{
                            errors.asal_barang
                        }}</small>
                    </div>

                    <!-- Nomor Surat -->
                    <div class="flex flex-col gap-2">
                        <label for="nomor_surat" class="font-medium"
                            >Nomor Surat</label
                        >
                        <Input
                            v-model="form.nomor_surat"
                            placeholder="Masukkan nomor surat"
                            class="w-full"
                            :class="{ 'border-red-500': errors.nomor_surat }"
                        />
                        <small v-if="errors.nomor_surat" class="text-red-500">{{
                            errors.nomor_surat
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
                                        v-model="item.nama"
                                        placeholder="Nama barang"
                                        class="w-full"
                                        :class="{
                                            'border-red-500':
                                                errors.barang?.[index]?.nama,
                                        }"
                                    />
                                    <small
                                        v-if="errors.barang?.[index]?.nama"
                                        class="text-red-500"
                                    >
                                        {{ errors.barang[index].nama }}
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
                                                errors.barang?.[index]?.harga,
                                        }"
                                    />
                                    <small
                                        v-if="errors.barang?.[index]?.harga"
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
                                                errors.barang?.[index]?.jumlah,
                                        }"
                                    />
                                    <small
                                        v-if="errors.barang?.[index]?.jumlah"
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
                                                errors.barang?.[index]?.satuan,
                                        }"
                                    />
                                    <small
                                        v-if="errors.barang?.[index]?.satuan"
                                        class="text-red-500"
                                    >
                                        {{ errors.barang[index].satuan }}
                                    </small>
                                </td>

                                <!-- Total -->
                                <td class="py-2 px-4 border">
                                    <Input
                                        :value="
                                            formatCurrency(
                                                item.harga * item.jumlah
                                            )
                                        "
                                        readonly
                                        class="w-full"
                                    />
                                </td>

                                <!-- Tgl Expired -->
                                <td class="py-2 px-4 border">
                                    <Popover>
                                        <PopoverTrigger as-child>
                                            <Button
                                                variant="outline"
                                                class="w-full justify-start text-left font-normal"
                                                :class="{
                                                    'text-muted-foreground':
                                                        !item.tgl_expired,
                                                }"
                                            >
                                                <CalendarIcon
                                                    class="mr-2 h-4 w-4"
                                                />
                                                {{
                                                    item.tgl_expired
                                                        ? formatDate(
                                                              item.tgl_expired
                                                          )
                                                        : "Pilih tanggal"
                                                }}
                                            </Button>
                                        </PopoverTrigger>
                                        <PopoverContent class="w-auto p-0">
                                            <Calendar
                                                v-model="item.tgl_expired"
                                                initial-focus
                                            />
                                        </PopoverContent>
                                    </Popover>
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
                        <span :class="{ 'text-red-500': isOverBudget }">
                            {{ formatCurrency(totalKeseluruhan) }}
                        </span>
                    </div>
                    <div v-if="form.batas_harga" class="font-semibold">
                        Batas Harga: {{ formatCurrency(form.batas_harga) }}
                        <span v-if="isOverBudget" class="text-red-500 ml-2">
                            (Melebihi batas
                            {{
                                formatCurrency(
                                    totalKeseluruhan - form.batas_harga
                                )
                            }})
                        </span>
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
                    :disabled="isOverBudget || isSubmitting"
                >
                    <span v-if="isSubmitting">Menyimpan...</span>
                    <span v-else>Simpan (Ctrl+Enter)</span>
                </Button>
            </div>
        </div>
    </div>
</template>
