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
import { useDokumen } from "@/composables/useDokumen";

const { uploadDokumen } = useDokumen();

const { toast } = useToast();
const isAdmin = ref(false); // Set to true if admin
const props = defineProps({
    visible: {
        type: Boolean,
        required: true,
    },
});
const emit = defineEmits(["update:visible", "submitted"]);
const { kategoris, isLoading: isLoadingKategori } = useKategori();
// const { subKategorisQuery } = useSubKategori();

const kategoriOptions = computed(() => {
    return (
        kategoris.value?.map((kategori) => ({
            id: kategori.id,
            name: kategori.nama_kategori, // or whatever field name your backend returns
        })) || []
    );
});
const { subKategoriOptions, subKategorisQuery } = useSubKategori();

console.log("Kategori Options:", kategoriOptions);


console.log("Sub Kategori Options:", subKategoriOptions.value);
// Form data with localStorage persistence
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

// Load draft from localStorage
const loadDraft = () => {
    const draft = localStorage.getItem("barangMasukDraft");
    if (draft) {
        form.value = JSON.parse(draft);
    }
};

// Save draft to localStorage
watch(
    form,
    (newValue) => {
        localStorage.setItem("barangMasukDraft", JSON.stringify(newValue));
    },
    { deep: true }
);

// Errors
const errors = ref({
    operator: null,
    kategori: null,
    subKategori: null,
    asalBarang: null,
    nomorSurat: null,
    barang: [],
});

// Fetch operators
const { data: operatorOptions } = useQuery({
    queryKey: ["operators"],
    queryFn: async () => {
        const { data } = await axios.get("/api/operators");
        return data;
    },
    initialData: [],
});

// Update the loadSubKategori method to use your hook
const loadSubKategori = async () => {
    form.value.subKategori = null;
    form.value.batasHarga = null;
};

// Update the loadBatasHarga method
const loadBatasHarga = () => {
    const selectedSubKategori = subKategoriOptions.value?.find(
        (sub) => sub.id === form.value.subKategori
    );
    form.value.batasHarga = selectedSubKategori
        ? selectedSubKategori.batasHarga
        : null;
};

// Watch for kategori changes to load subkategori
watch(
    () => form.value.kategori,
    (newVal) => {
        if (newVal) {
            loadSubKategori();
        }
    }
);

// Computed properties
const totalKeseluruhan = computed(() => {
    return form.value.barang.reduce((sum, item) => sum + (item.total || 0), 0);
});

const isOverBudget = computed(() => {
    if (!form.value.batasHarga) return false;
    return totalKeseluruhan.value > form.value.batasHarga;
});


const {} = useItems()
// Mutation for submitting the form

const handleSubmit = async () => {
    if (!validateForm()) return;

    try {
        await createItemMutation.mutateAsync({
            ...form.value,
            items: form.value.barang, // Rename to match API expectation
        });

        toast.success("Barang masuk berhasil dicatat");
        resetForm();
        emit("update:visible", false);
        emit("submitted");
    } catch (error) {
        console.error("Submission error:", error);
    }
};
const { mutateAsync: submitFormMutation, isLoading: isSubmitting } =
    useMutation({
        mutationFn: async (formData) => {
            let lampiranUrl = null;

            // Upload file if exists using useDokumen
            if (formData.lampiran) {
                lampiranUrl = await uploadDokumen(formData.lampiran);
            }

            // Prepare data to send
            const payload = {
                ...formData,
                lampiran: lampiranUrl,
                barang: formData.barang,
            };
            await createItemMutation.mutateAsync({
                ...form.value,
                items: form.value.barang, // Rename to match API expectation
            });

            const response = await axios.post("/api/barang-masuk", payload);

            return response.data;
        },
        onSuccess: (data) => {
            toast({
                title: "Sukses",
                description: data.message || "Data berhasil disimpan",
                variant: "success",
            });
            resetForm();
            emit("update:visible", false);
            emit("submitted");
        },
        onError: (error) => {
            let errorMessage = "Gagal menyimpan data";

            if (axios.isAxiosError(error)) {
                errorMessage = error.response?.data?.message || error.message;

                if (error.response?.status === 422) {
                    const validationErrors = error.response.data.errors;
                    Object.entries(validationErrors).forEach(
                        ([field, messages]) => {
                            if (field.startsWith("barang.")) {
                                const [_, index, prop] = field.split(".");
                                if (!errors.value.barang[index])
                                    errors.value.barang[index] = {};
                                errors.value.barang[index][prop] = messages[0];
                            } else {
                                errors.value[field] = messages[0];
                            }
                        }
                    );
                }
            }

            toast({
                title: "Error",
                description: errorMessage,
                variant: "destructive",
            });
        },
    });

// Methods
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
    if (form.value.barang[index].harga > 0) {
        if (!confirm("Anda yakin ingin menghapus barang ini?")) return;
    }
    form.value.barang.splice(index, 1);
    errors.value.barang.splice(index, 1);
};

const calculateTotal = debounce((index) => {
    const item = form.value.barang[index];
    item.total = (item.harga || 0) * (item.jumlah || 0);
}, 300);

const handleFileUpload = (event) => {
    const file = event.target.files[0];
    if (!file) return;

    // Validate file type
    const allowedTypes = [
        "application/msword",
        "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
        "application/zip",
    ];
    if (
        !allowedTypes.includes(file.type) &&
        !file.name.match(/\.(doc|docx|zip)$/)
    ) {
        toast({
            title: "Error",
            description:
                "Format file tidak didukung. Harap unggah file doc, docx, atau zip.",
            variant: "destructive",
        });
        event.target.value = "";
        return;
    }

    // Validate file size (5MB max)
    if (file.size > 5 * 1024 * 1024) {
        toast({
            title: "Error",
            description: "Ukuran file terlalu besar. Maksimal 5MB.",
            variant: "destructive",
        });
        event.target.value = "";
        return;
    }

    form.value.lampiran = file;
};

const formatCurrency = (value) => {
    if (!value) return "Rp 0";
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
    }).format(value);
};

const formatDate = (date) => {
    if (!date) return "";
    return new Date(date).toLocaleDateString("id-ID", {
        day: "2-digit",
        month: "2-digit",
        year: "numeric",
    });
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
        } else if (item.harga < 0) {
            errors.value.barang[index].harga = "Harga tidak boleh negatif";
            isValid = false;
        }

        // Validate jumlah
        if (!item.jumlah) {
            errors.value.barang[index].jumlah = "Jumlah wajib diisi";
            isValid = false;
        } else if (item.jumlah <= 0) {
            errors.value.barang[index].jumlah = "Jumlah harus lebih dari 0";
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

const submitForm = async () => {
    if (!validateForm()) return;

    if (isOverBudget.value) {
        toast({
            title: "Peringatan",
            description: `Total harga melebihi batas harga sub kategori sebesar ${formatCurrency(
                totalKeseluruhan.value - form.value.batasHarga
            )}`,
            variant: "destructive",
        });
        return;
    }

    try {
        await submitFormMutation(form.value);
    } catch (error) {
        console.error("Submission error:", error);
    }
};

const resetForm = () => {
    form.value = {
        operator: isAdmin.value ? null : operatorOptions.value[0]?.id || null,
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
    };
    errors.value = {
        operator: null,
        kategori: null,
        subKategori: null,
        asalBarang: null,
        nomorSurat: null,
        barang: [],
    };
    localStorage.removeItem("barangMasukDraft");
};

// Keyboard shortcuts
const handleKeyDown = (e) => {
    if (e.ctrlKey && e.key === "Enter") {
        submitForm();
    }
};

// Watch for dialog open to reset form if needed
watch(
    () => props.visible,
    (val) => {
        if (val) {
            loadDraft();
        }
    }
);

// Initialize form
onMounted(() => {
    loadDraft();
    window.addEventListener("keydown", handleKeyDown);

    // Set operator automatically if not admin
    if (!isAdmin.value && operatorOptions.value.length > 0) {
        form.value.operator = operatorOptions.value[0].id;
    }
});

onUnmounted(() => {
    window.removeEventListener("keydown", handleKeyDown);
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
                            :disabled="!form.kategori || isLoadingSubKategori"
                            @update:modelValue="loadBatasHarga"
                        >
                            <SelectTrigger class="w-full border">
                                {{
                                    isLoadingSubKategori
                                        ? "Memuat..."
                                        : subKategoriOptions.find(
                                              (opt) =>
                                                  opt.id === form.subKategori
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
                                        formatCurrency(opt.batasHarga)
                                    }})
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
                            :value="formatCurrency(form.batasHarga)"
                            readonly
                            class="w-full"
                            :class="{ 'border-red-500': isOverBudget }"
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
                                        :value="formatCurrency(item.total)"
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
                                                        !item.tglExpired,
                                                }"
                                            >
                                                <CalendarIcon
                                                    class="mr-2 h-4 w-4"
                                                />
                                                {{
                                                    item.tglExpired
                                                        ? formatDate(
                                                              item.tglExpired
                                                          )
                                                        : "Pilih tanggal"
                                                }}
                                            </Button>
                                        </PopoverTrigger>
                                        <PopoverContent class="w-auto p-0">
                                            <Calendar
                                                v-model="item.tglExpired"
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
                    <div v-if="form.batasHarga" class="font-semibold">
                        Batas Harga: {{ formatCurrency(form.batasHarga) }}
                        <span v-if="isOverBudget" class="text-red-500 ml-2">
                            (Melebihi batas
                            {{
                                formatCurrency(
                                    totalKeseluruhan - form.batasHarga
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
