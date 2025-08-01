<script setup>
import { ref, computed, watch } from "vue";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/components/ui/table";
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/components/ui/select";
import {
    ChevronLeft,
    ChevronRight,
    Pencil,
    Trash2,
    Search,
    Plus,
    CheckCircle2,
    XCircle,
    Download,
    Printer,
} from "lucide-vue-next";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import AddBarang from "./AddBarang.vue";

defineOptions({
    layout: AuthenticatedLayout,
});

const props = defineProps({
    items: Array,
    kategori: Array,
    subkategoris: Array,
});

const ITEMS_PER_PAGE = 10;
const dialogVisible = ref(false);
const currentPage = ref(1);
const isLoading = ref(false);
const isError = ref(false);

const SORT_OPTIONS = [
    { value: "created_at:asc", label: "Tanggal (Terlama)" },
    { value: "created_at:desc", label: "Tanggal (Terbaru)" },
    { value: "asal_barang:asc", label: "Asal Barang (A-Z)" },
    { value: "asal_barang:desc", label: "Asal Barang (Z-A)" },
    { value: "total_harga:asc", label: "Total Harga (Terendah)" },
    { value: "total_harga:desc", label: "Total Harga (Tertinggi)" },
];

const filters = ref({
    kategori: null,
    subKategori: null,
    tahun: null,
    search: "",
    sort: "created_at:desc",
});

//computed
const kategoriOptions = computed(() => {
    return (
        props.kategori?.map((k) => ({
            value: k.id,
            label: k.nama_kategori,
        })) || []
    );
});

const subKategoriOptions = computed(() => {
    if (!filters.value.kategori) return [];
    return props.subkategoris
        .filter((sk) => sk.kategori_id == filters.value.kategori)
        .map((sk) => ({
            value: sk.id,
            label: sk.nama_subkategori,
        }));
});

const tahunOptions = computed(() => {
    const years = new Set();
    props.items.forEach((item) => {
        if (item.created_at) {
            years.add(new Date(item.created_at).getFullYear());
        }
    });
    return Array.from(years).sort((a, b) => b - a);
});

const filteredItems = computed(() => {
    let results = [...props.items];

    if (filters.value.kategori) {
        results = results.filter(
            (item) => item.kategori_id == filters.value.kategori
        );
    }

    if (filters.value.subKategori) {
        results = results.filter(
            (item) => item.sub_kategori_id == filters.value.subKategori
        );
    }

    if (filters.value.tahun) {
        results = results.filter(
            (item) =>
                new Date(item.created_at).getFullYear() == filters.value.tahun
        );
    }

    if (filters.value.search) {
        const searchTerm = filters.value.search.toLowerCase();
        results = results.filter((item) => {
            const subKategoriName =
                props.subkategoris.find((sk) => sk.id == item.sub_kategori_id)
                    ?.nama_subkategori || "";

            return (
                item.asal_barang.toLowerCase().includes(searchTerm) ||
                subKategoriName.toLowerCase().includes(searchTerm)
            );
        });
    }

    // Apply sorting
    const [field, direction] = filters.value.sort.split(":");
    results.sort((a, b) => {
        // Special handling for total_harga
        if (field === "total_harga") {
            const totalA = a.daftar_barang.reduce(
                (sum, barang) => sum + barang.harga * barang.jumlah,
                0
            );
            const totalB = b.daftar_barang.reduce(
                (sum, barang) => sum + barang.harga * barang.jumlah,
                0
            );
            return direction === "asc" ? totalA - totalB : totalB - totalA;
        }
        // Date comparison
        else if (field === "created_at") {
            return direction === "asc"
                ? new Date(a[field]) - new Date(b[field])
                : new Date(b[field]) - new Date(a[field]);
        } else {
            return direction === "asc"
                ? String(a[field] || "").localeCompare(String(b[field] || ""))
                : String(b[field] || "").localeCompare(String(a[field] || ""));
        }
    });

    return results;
});

const paginatedItems = computed(() => {
    const start = (currentPage.value - 1) * ITEMS_PER_PAGE;
    return filteredItems.value.slice(start, start + ITEMS_PER_PAGE);
});

// Total pages calculation
const totalPages = computed(() =>
    Math.ceil(filteredItems.value.length / ITEMS_PER_PAGE)
);

const currentPageRange = computed(() => {
    const start = (currentPage.value - 1) * ITEMS_PER_PAGE + 1;
    const end = Math.min(
        currentPage.value * ITEMS_PER_PAGE,
        filteredItems.value.length
    );
    return { start, end };
});

const handlePageChange = (newPage) => {
    currentPage.value = newPage;
};

// Methods
const formatDate = (dateString) =>
    new Date(dateString).toLocaleDateString("id-ID", {
        year: "numeric",
        month: "short",
        day: "numeric",
    });

const formatCurrency = (value) => value?.toLocaleString("id-ID") || "0";


const printSuratMasuk = (item) => {
    const printWindow = window.open("", "_blank");
    printWindow.document.write(`
        <html>
        <head>
            <title>Surat Masuk Barang - ${item.asal_barang}</title>
            <style>
                body { font-family: Arial; padding: 20px; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #f2f2f2; }
                .header { text-align: center; margin-bottom: 30px; }
                .footer { margin-top: 50px; text-align: right; }
            </style>
        </head>
        <body>
            <div class="header">
                <h2>SURAT MASUK BARANG</h2>
                <p>No: ${item.id}</p>
            </div>
            
            <table>
                <tr><th>Tanggal</th><td>${formatDate(item.created_at)}</td></tr>
                <tr><th>Asal Barang</th><td>${item.asal_barang}</td></tr>
                <tr><th>Penerima</th><td>${item.user?.name || "-"}</td></tr>
            </table>
            
            <h3>Daftar Barang:</h3>
            <table>
                <thead>
                    <tr>
                        <th>No</th><th>Nama</th><th>Harga</th><th>Jumlah</th><th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    ${item.daftar_barang
                        .map(
                            (barang, i) => `
                        <tr>
                            <td>${i + 1}</td>
                            <td>${barang.nama}</td>
                            <td>${formatCurrency(barang.harga)}</td>
                            <td>${barang.jumlah}</td>
                            <td>${formatCurrency(
                                barang.harga * barang.jumlah
                            )}</td>
                        </tr>
                    `
                        )
                        .join("")}
                </tbody>
            </table>
            
            <div class="footer">
                <p>Mengetahui,</p>
                <br><br><br>
                <p>_________________________</p>
            </div>
            
            <script>
                window.onload = function() {
                    setTimeout(function() {
                        window.print();
                        window.close();
                    }, 200);
                }
            <\/script>
        </body>
        </html>
    `);
    printWindow.document.close();
};

//no use
const toggleVerification = async (item) => {
    try {
        const response = await axios.patch(`/items/${item.id}/verify`);
        item.status = !item.status;
    } catch (error) {
        console.error("Error toggling verification:", error);
    }
};

const editingItem = ref(null);
const isEditing = ref(false);

const openDialog = () => {
    editingItem.value = null;
    isEditing.value = false;
    dialogVisible.value = true;
};

const editItem = (item) => {
    console.log
    editingItem.value = item;
    isEditing.value = true;
    dialogVisible.value = true;
};

const closeDialog = () => {
    dialogVisible.value = false;
    editingItem.value = null;
    isEditing.value = false;
};

// Watchers
watch(
    filters,
    () => {
        currentPage.value = 1;
    },
    { deep: true }
);

watch(
    () => filters.value.kategori,
    (newVal) => {
        if (!newVal) filters.value.subKategori = null;
    }
);
</script>

<template>
    <div class="w-full p-6">
        <div
            class="flex justify-between items-center mb-4"
            :visible="!dialogVisible"
            v-if="!dialogVisible"
        >
            <Button @click="openDialog" class="gap-2">
                <Plus class="h-4 w-4" />
                Tambah Data
            </Button>
            <Button @click="exportExcel" variant="outline" class="gap-2">
                <Download class="h-4 w-4" />
                Export Excel
            </Button>
        </div>

        <AddBarang
            v-if="dialogVisible"
            :visible="dialogVisible"
            :editing="isEditing"
            :initialData="editingItem"
            @close="closeDialog"
            @success="closeDialog"
        />

        <template v-if="!dialogVisible">
            <div v-if="isLoading" class="text-center py-8">
                <p>Memuat data...</p>
            </div>
            <div v-else-if="isError" class="text-center py-8 text-red-500">
                <p>Gagal memuat data</p>
            </div>

            <template v-else>
                <!-- Filters -->
                <div class="flex flex-wrap justify-end items-center gap-3 mb-4">
                    <!-- Kategori Select -->
                    <div class="w-[180px]">
                        <Select v-model="filters.kategori">
                            <SelectTrigger>
                                <SelectValue placeholder="Semua Kategori" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem :value="null"
                                    >Semua Kategori</SelectItem
                                >
                                <SelectItem
                                    v-for="option in kategoriOptions"
                                    :key="option.value"
                                    :value="option.value"
                                >
                                    {{ option.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <!-- Subkategori Select -->
                    <div class="w-[180px]">
                        <Select
                            v-model="filters.subKategori"
                            :disabled="!filters.kategori"
                        >
                            <SelectTrigger>
                                <SelectValue placeholder="Pilih Subkategori" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem :value="null"
                                    >Semua Subkategori</SelectItem
                                >
                                <SelectItem
                                    v-for="sk in subKategoriOptions"
                                    :key="sk.value"
                                    :value="sk.value"
                                >
                                    {{ sk.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <!-- Tahun Select -->
                    <div class="w-[150px]">
                        <Select v-model="filters.tahun">
                            <SelectTrigger>
                                <SelectValue placeholder="Semua Tahun" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem :value="null"
                                    >Semua Tahun</SelectItem
                                >
                                <SelectItem
                                    v-for="year in tahunOptions"
                                    :key="year"
                                    :value="year"
                                >
                                    {{ year }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <!-- Sort Select -->
                    <div class="w-[180px]">
                        <Select v-model="filters.sort">
                            <SelectTrigger>
                                <SelectValue placeholder="Urutkan" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="option in SORT_OPTIONS"
                                    :key="option.value"
                                    :value="option.value"
                                >
                                    {{ option.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <!-- Search Input -->
                    <div class="flex-1 max-w-[350px]">
                        <div class="relative">
                            <Search
                                class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                            />
                            <Input
                                v-model="filters.search"
                                placeholder="Cari asal barang atau sub kategori..."
                                class="pl-10 w-full"
                            />
                        </div>
                    </div>
                </div>

                <!-- Table -->
                <div class="rounded-md border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>No</TableHead>
                                <TableHead>Action</TableHead>
                                <TableHead>Tanggal</TableHead>
                                <TableHead>Asal Barang</TableHead>
                                <TableHead>Penerima</TableHead>
                                <TableHead>Unit</TableHead>
                                <TableHead>Kode</TableHead>
                                <TableHead>Nama</TableHead>
                                <TableHead>Harga (Rp.)</TableHead>
                                <TableHead>Jumlah</TableHead>
                                <TableHead>Total (Rp.)</TableHead>
                                <TableHead>Status</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <template
                                v-for="(item, index) in paginatedItems"
                                :key="item.id"
                            >
                                <template
                                    v-for="(
                                        barang, barangIndex
                                    ) in item.daftar_barang"
                                    :key="barangIndex"
                                >
                                    <TableRow>
                                        <TableCell
                                            v-if="barangIndex === 0"
                                            :rowspan="item.daftar_barang.length"
                                        >
                                            {{
                                                (currentPage - 1) *
                                                    ITEMS_PER_PAGE +
                                                index +
                                                1
                                            }}
                                        </TableCell>
                                        <TableCell
                                            v-if="barangIndex === 0"
                                            :rowspan="item.daftar_barang.length"
                                        >
                                            <div class="flex gap-2">
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    @click="
                                                        printSuratMasuk(item)
                                                    "
                                                    title="Cetak Surat Masuk"
                                                >
                                                    <Printer
                                                        class="h-4 w-4 text-blue-600"
                                                    />
                                                </Button>
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    title="Edit"
                                                    @click="editItem(item)"
                                                >
                                                    <Pencil
                                                        class="h-4 w-4 text-blue-600"
                                                    />
                                                </Button>
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    title="Hapus"
                                                >
                                                    <Trash2
                                                        class="h-4 w-4 text-red-600"
                                                    />
                                                </Button>
                                            </div>
                                        </TableCell>
                                        <TableCell
                                            v-if="barangIndex === 0"
                                            :rowspan="item.daftar_barang.length"
                                        >
                                            {{ formatDate(item.created_at) }}
                                        </TableCell>
                                        <TableCell
                                            v-if="barangIndex === 0"
                                            :rowspan="item.daftar_barang.length"
                                        >
                                            {{ item.asal_barang }}
                                        </TableCell>
                                        <TableCell
                                            v-if="barangIndex === 0"
                                            :rowspan="item.daftar_barang.length"
                                        >
                                            {{ item.user?.name || "-" }}
                                        </TableCell>
                                        <TableCell
                                            v-if="barangIndex === 0"
                                            :rowspan="item.daftar_barang.length"
                                        >
                                            {{
                                                item.kategori?.nama_kategori ||
                                                "-"
                                            }}
                                        </TableCell>
                                        <TableCell>{{
                                            item.kategori?.kode_kategori || "-"
                                        }}</TableCell>
                                        <TableCell>{{ barang.nama }}</TableCell>
                                        <TableCell>{{
                                            formatCurrency(barang.harga)
                                        }}</TableCell>
                                        <TableCell>{{
                                            barang.jumlah
                                        }}</TableCell>
                                        <TableCell>{{
                                            formatCurrency(
                                                barang.harga * barang.jumlah
                                            )
                                        }}</TableCell>
                                        <TableCell
                                            v-if="barangIndex === 0"
                                            :rowspan="item.daftar_barang.length"
                                        >
                                            <Button
                                                variant="ghost"
                                                size="icon"
                                                @click="
                                                    toggleVerification(item)
                                                "
                                                :title="
                                                    item.status
                                                        ? 'Terverifikasi'
                                                        : 'Belum Verifikasi'
                                                "
                                            >
                                                <CheckCircle2
                                                    v-if="item.status"
                                                    class="h-4 w-4 text-green-500"
                                                />
                                                <XCircle
                                                    v-else
                                                    class="h-4 w-4 text-red-500"
                                                />
                                            </Button>
                                        </TableCell>
                                    </TableRow>
                                </template>
                            </template>
                        </TableBody>
                    </Table>
                </div>

                <!-- Pagination -->
                <div class="flex items-center justify-between py-4">
                    <div class="text-sm text-muted-foreground">
                        Menampilkan {{ currentPageRange.start }} sampai
                        {{ currentPageRange.end }} dari
                        {{ filteredItems.length }} data
                    </div>
                    <div class="flex items-center space-x-2">
                        <Button
                            variant="outline"
                            size="sm"
                            :disabled="currentPage === 1"
                            @click="handlePageChange(currentPage - 1)"
                        >
                            <ChevronLeft class="h-4 w-4" />
                        </Button>
                        <span class="text-sm text-muted-foreground">
                            Halaman {{ currentPage }} dari {{ totalPages }}
                        </span>
                        <Button
                            variant="outline"
                            size="sm"
                            :disabled="currentPage >= totalPages"
                            @click="handlePageChange(currentPage + 1)"
                        >
                            <ChevronRight class="h-4 w-4" />
                        </Button>
                    </div>
                </div>
            </template>
        </template>
    </div>
</template>
