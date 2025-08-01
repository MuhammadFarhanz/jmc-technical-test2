<script setup>
import { ref, computed, watch, nextTick } from "vue";
import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";
import {
    Select,
    SelectTrigger,
    SelectContent,
    SelectItem,
    SelectValue,
} from "@/components/ui/select";
import { Calendar } from "@/components/ui/calendar";
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from "@/components/ui/popover";
import { useToast } from "@/components/ui/toast/use-toast";
import { Calendar as CalendarIcon } from "lucide-vue-next";
import { router, useForm, usePage } from "@inertiajs/vue3";
import {
    CalendarDate,
    DateFormatter,
    getLocalTimeZone,
    parseDate,
    today,
} from "@internationalized/date";
import { parseISO } from "date-fns";
// Constants
const ALLOWED_FILE_TYPES = [
    "application/msword",
    "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
    "application/zip",
];
const MAX_FILE_SIZE = 5 * 1024 * 1024; // 5MB

const { auth, kategori, subkategoris, operators } = usePage().props;
const { toast } = useToast();

const props = defineProps({
    visible: Boolean,
    editing: {
        type: Boolean,
        default: false,
    },
    initialData: {
        type: Object,
        default: () => ({}),
    },
});

const emit = defineEmits(["close", "success"]);

const form = useForm({
    user_id: auth.user?.id,
    kategori_id: null,
    sub_kategori_id: null,
    batas_harga: null,
    nomor_surat: "",
    asal_barang: "",
    lampiran: null,
    barang: [createBarangItem()],
    _method: "POST",
});

const isInitializing = ref(false);

watch(
    () => props.editing && props.initialData,
    (shouldLoad) => {
        if (shouldLoad && props.initialData.id) {
            isInitializing.value = true;

            const normalizedData = {
                ...props.initialData,
                batas_harga: props.initialData.batas_harga
                    ? Number(props.initialData.batas_harga)
                    : null,
                barang:
                    props.initialData.daftar_barang?.map((item) => ({
                        ...item,
                        harga: item.harga ? Number(item.harga) : null,
                        jumlah: item.jumlah ? Number(item.jumlah) : 1,
                    })) || [],
            };

            form.defaults({
                ...normalizedData,
                _method: "PUT",
            }).reset();

            nextTick(() => {
                isInitializing.value = false;
            });
        } else if (!props.editing) {
            // Explicitly set to false for create mode
            isInitializing.value = false;
        }
    },
    { immediate: true }
);
console.log(
    form.barang.map((item) => item.tgl_expired),
    "isi form"
);

const isAdmin = computed(() => auth.user?.role === "Admin");

const kategoriOptions = computed(
    () =>
        kategori?.map((k) => ({
            id: k.id,
            name: k.nama_kategori,
        })) || []
);

const subKategoriOptions = computed(
    () =>
        subkategoris?.map((sk) => ({
            id: sk.id,
            name: sk.nama_subkategori,
            batas_harga: sk.batas_harga ? Number(sk.batas_harga) : 0,
            kategori_id: sk.kategori_id,
        })) || []
);

const filteredSubKategoriOptions = computed(() => {
    if (!form.kategori_id) return [];
    return subKategoriOptions.value.filter(
        (sk) => sk.kategori_id === form.kategori_id
    );
});

const operatorOptions = computed(() => {
    if (isAdmin.value) {
        return operators.map((op) => ({
            id: op.id,
            name: op.name,
        }));
    }
    return [
        {
            id: auth.user.id,
            name: auth.user.name,
        },
    ];
});

watch(
    () => form.sub_kategori_id,
    (newVal, oldVal) => {
        // Only update batas_harga if not in edit mode or if the value actually changed
        if (newVal && (!props.editing || newVal !== oldVal)) {
            const selected = subKategoriOptions.value.find(
                (sk) => sk.id === newVal
            );
            form.batas_harga = selected?.batas_harga || null;
        }
    }
);

const totalKeseluruhan = computed(() =>
    form.barang.reduce(
        (total, item) => total + (item.harga || 0) * (item.jumlah || 0),
        0
    )
);

const isOverBudget = computed(
    () => form.batas_harga && totalKeseluruhan.value > form.batas_harga
);

// File validation
const validateFile = (file) => {
    if (!file) return true;

    if (!ALLOWED_FILE_TYPES.includes(file.type)) {
        toast({
            title: "Error",
            description: "Format file harus DOC, DOCX, atau ZIP",
            variant: "destructive",
        });
        return false;
    }

    if (file.size > MAX_FILE_SIZE) {
        toast({
            title: "Error",
            description: "Ukuran file maksimal 5MB",
            variant: "destructive",
        });
        return false;
    }

    return true;
};

const handleFileChange = (e) => {
    const file = e.target.files[0];
    if (validateFile(file)) {
        form.lampiran = file;
    } else {
        e.target.value = null;
    }
};

const submit = () => {
    const formData = {
        ...form.data(),
        barang: form.barang.map((item) => ({
            ...item,
            tgl_expired: item.tgl_expired
                ? `${item.tgl_expired.year}-${String(
                      item.tgl_expired.month
                  ).padStart(2, "0")}-${String(item.tgl_expired.day).padStart(
                      2,
                      "0"
                  )}`
                : null,
        })),
    };

    // Rest of your submit logic remains the same
    if (props.editing) {
        form.put(route("items.update", props.initialData.id), {
            data: formData,
            preserveScroll: true,
            onSuccess: () => {
                toast({
                    title: "Sukses",
                    description: "Data berhasil diperbarui",
                });
                emit("success");
            },
        });
    } else {
        form.post(route("items.store"), {
            data: formData,
            preserveScroll: true,
            onSuccess: () => {
                toast({
                    title: "Sukses",
                    description: "Data berhasil disimpan",
                });
                emit("success");
            },
        });
    }
};
// Barang items management
function createBarangItem() {
    return {
        nama: "",
        harga: null,
        jumlah: 1,
        satuan: "",
        tgl_expired: null,
    };
}

const addBarangItem = () => {
    form.barang.push(createBarangItem());
};

const removeBarangItem = (index) => {
    if (form.barang.length > 1) {
        form.barang.splice(index, 1);
    }
};

// Helpers
const formatCurrency = (value) => {
    if (!value) return "Rp 0";
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
    }).format(value);
};
</script>

<template>
    <div class="h-full p-4">
        <h1 class="text-2xl font-bold mb-6">
            {{ editing ? "Edit Barang Masuk" : "Barang Masuk" }}
        </h1>

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
                                <SelectValue placeholder="Select Operator">
                                    {{
                                        operatorOptions.find(
                                            (o) => o.id === form.user_id
                                        )?.name || "Select Operator"
                                    }}
                                </SelectValue>
                            </SelectTrigger>

                            <SelectContent v-if="isAdmin">
                                <SelectItem
                                    v-for="operator in operatorOptions"
                                    :key="operator.id"
                                    :value="operator.id"
                                >
                                    {{ operator.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <small
                            v-if="form.errors.user_id"
                            class="text-red-500"
                            >{{ form.errors.user_id }}</small
                        >
                    </div>

                    <!-- Kategori -->
                    <div class="flex flex-col gap-2">
                        <label for="kategori" class="font-medium">
                            Kategori <span class="text-red-500">*</span>
                        </label>
                        <Select v-model="form.kategori_id">
                            <SelectTrigger class="w-full border">
                                <SelectValue placeholder="Pilih Kategori">
                                    {{
                                        kategoriOptions.find(
                                            (opt) => opt.id === form.kategori_id
                                        )?.name || "Pilih Kategori"
                                    }}
                                </SelectValue>
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
                        <small
                            v-if="form.errors.kategori_id"
                            class="text-red-500"
                            >{{ form.errors.kategori_id }}</small
                        >
                    </div>

                    <!-- Sub Kategori -->
                    <div class="flex flex-col gap-2">
                        <label for="subKategori" class="font-medium">
                            Sub Kategori <span class="text-red-500">*</span>
                        </label>
                        <Select
                            v-model="form.sub_kategori_id"
                            :disabled="!form.kategori_id || isInitializing"
                        >
                            <SelectTrigger class="w-full border">
                                <SelectValue placeholder="Pilih Sub Kategori">
                                    {{
                                        isInitializing
                                            ? "Memuat..."
                                            : filteredSubKategoriOptions.find(
                                                  (opt) =>
                                                      opt.id ===
                                                      form.sub_kategori_id
                                              )?.name || "Pilih Sub Kategori"
                                    }}
                                </SelectValue>
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="opt in filteredSubKategoriOptions"
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
                            v-if="form.errors.sub_kategori_id"
                            class="text-red-500"
                        >
                            {{ form.errors.sub_kategori_id }}
                        </small>
                    </div>

                    <!-- Batas Harga -->
                    <div class="flex flex-col gap-2">
                        <label for="batasHarga" class="font-medium"
                            >Batas Harga</label
                        >
                        <Input
                            :value="
                                isInitializing
                                    ? 'Memuat...'
                                    : formatCurrency(form.batas_harga || 0)
                            "
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
                            :class="{
                                'border-red-500': form.errors.asal_barang,
                            }"
                        />
                        <small
                            v-if="form.errors.asal_barang"
                            class="text-red-500"
                            >{{ form.errors.asal_barang }}</small
                        >
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
                            :class="{
                                'border-red-500': form.errors.nomor_surat,
                            }"
                        />
                        <small
                            v-if="form.errors.nomor_surat"
                            class="text-red-500"
                            >{{ form.errors.nomor_surat }}</small
                        >
                    </div>

                    <!-- Lampiran -->
                    <div class="flex flex-col gap-2">
                        <label for="lampiran" class="font-medium"
                            >Lampiran</label
                        >
                        <input
                            type="file"
                            id="lampiran"
                            @change="handleFileChange"
                            accept=".doc,.docx,.zip"
                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                        />
                        <small class="text-gray-500"
                            >Format: doc, docx, zip (max 5MB)</small
                        >
                        <small
                            v-if="form.errors.lampiran"
                            class="text-red-500"
                            >{{ form.errors.lampiran }}</small
                        >
                        <small
                            v-if="editing && initialData.lampiran"
                            class="text-blue-500"
                        >
                            File saat ini: {{ initialData.lampiran }}
                        </small>
                    </div>
                </div>
            </div>

            <!-- INFORMASI BARANG -->
            <div class="mb-8">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-semibold">INFORMASI BARANG</h2>
                    <Button variant="outline" @click="addBarangItem"
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
                                                form.errors[
                                                    `barang.${index}.nama`
                                                ],
                                        }"
                                    />
                                    <small
                                        v-if="
                                            form.errors[`barang.${index}.nama`]
                                        "
                                        class="text-red-500"
                                    >
                                        {{
                                            form.errors[`barang.${index}.nama`]
                                        }}
                                    </small>
                                </td>

                                <!-- Harga -->
                                <td class="py-2 px-4 border">
                                    <Input
                                        type="number"
                                        v-model="item.harga"
                                        class="w-full"
                                        min="1"
                                        :class="{
                                            'border-red-500':
                                                form.errors[
                                                    `barang.${index}.harga`
                                                ],
                                        }"
                                    />
                                    <small
                                        v-if="
                                            form.errors[`barang.${index}.harga`]
                                        "
                                        class="text-red-500"
                                    >
                                        {{
                                            form.errors[`barang.${index}.harga`]
                                        }}
                                    </small>
                                </td>

                                <!-- Jumlah -->
                                <td class="py-2 px-4 border">
                                    <Input
                                        type="number"
                                        min="1"
                                        v-model="item.jumlah"
                                        class="w-full"
                                        :class="{
                                            'border-red-500':
                                                form.errors[
                                                    `barang.${index}.jumlah`
                                                ],
                                        }"
                                    />
                                    <small
                                        v-if="
                                            form.errors[
                                                `barang.${index}.jumlah`
                                            ]
                                        "
                                        class="text-red-500"
                                    >
                                        {{
                                            form.errors[
                                                `barang.${index}.jumlah`
                                            ]
                                        }}
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
                                                form.errors[
                                                    `barang.${index}.satuan`
                                                ],
                                        }"
                                    />
                                    <small
                                        v-if="
                                            form.errors[
                                                `barang.${index}.satuan`
                                            ]
                                        "
                                        class="text-red-500"
                                    >
                                        {{
                                            form.errors[
                                                `barang.${index}.satuan`
                                            ]
                                        }}
                                    </small>
                                </td>

                                <!-- Total -->
                                <td class="py-2 px-4 border">
                                    {{
                                        formatCurrency(
                                            (item.harga || 0) *
                                                (item.jumlah || 1)
                                        )
                                    }}
                                </td>

                                <!-- Tgl Expired -->
                                <td class="py-2 px-4 border">
                                    <Popover>
                                        <PopoverTrigger as-child>
                                            <Button
                                                variant="outline"
                                                class="w-full justify-start text-left font-normal"
                                            >
                                                <CalendarIcon
                                                    class="mr-2 h-4 w-4"
                                                />
                                                {{
                                                    item.tgl_expired
                                                        ? `${item.tgl_expired.day}/${item.tgl_expired.month}/${item.tgl_expired.year}`
                                                        : "Pilih tanggal"
                                                }}
                                            </Button>
                                        </PopoverTrigger>
                                        <PopoverContent class="w-auto p-0">
                                            <Calendar
                                                v-model="item.tgl_expired"
                                                :min-value="
                                                    today(getLocalTimeZone())
                                                "
                                            />
                                        </PopoverContent>
                                    </Popover>
                                    <div
                                        v-if="
                                            form.errors[
                                                `barang.${index}.tgl_expired`
                                            ]
                                        "
                                        class="text-xs text-red-500 mt-1"
                                    >
                                        {{
                                            form.errors[
                                                `barang.${index}.tgl_expired`
                                            ]
                                        }}
                                    </div>
                                </td>
                                <!-- Aksi -->
                                <td class="py-2 px-4 border text-center">
                                    <Button
                                        variant="destructive"
                                        @click="removeBarangItem(index)"
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
            <div class="flex justify-start gap-2">
                <Button variant="outline" @click="$emit('close')">
                    Kembali
                </Button>
                <Button
                    variant="default"
                    @click="submit"
                    :disabled="isOverBudget || form.processing"
                >
                    <span v-if="form.processing">Menyimpan...</span>
                    <span v-else
                        >{{ editing ? "Update" : "Simpan" }} (Ctrl+Enter)</span
                    >
                </Button>
            </div>
        </div>
    </div>
</template>
