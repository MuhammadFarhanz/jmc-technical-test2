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
import AddBarangMasuk from "../Dialog/AddBarangMasuk.vue";
import { useItems } from "@/composables/useItems";

// Constants
const ITEMS_PER_PAGE = 10;
const SORT_OPTIONS = [
    { value: "created_at:asc", label: "Tanggal (Terlama)" },
    { value: "created_at:desc", label: "Tanggal (Terbaru)" },
    { value: "asal_barang:asc", label: "Asal Barang (A-Z)" },
    { value: "asal_barang:desc", label: "Asal Barang (Z-A)" },
    { value: "total_harga:asc", label: "Total Harga (Terendah)" },
    { value: "total_harga:desc", label: "Total Harga (Tertinggi)" },
];

// Refs
const filters = ref({
    kategori: "",
    subKategori: "",
    tahun: "",
    search: "",
    sort: "created_at:desc",
});
const dialogVisible = ref(false);
const currentPage = ref(1);

// Data fetching
const { fetchItemsQuery, refetchItems, updateVerificationStatus } = useItems();

// Computed properties
const items = computed(() => fetchItemsQuery.data.value?.data || []);
const meta = computed(() => fetchItemsQuery.data.value?.data?.meta || {});
const isLoading = computed(() => fetchItemsQuery.isLoading.value);
const isError = computed(() => fetchItemsQuery.isError.value);

const kategoriOptions = computed(() => {
    const uniqueKategories = [
        ...new Set(items.value.map((item) => item.kategori?.nama_kategori)),
    ];
    return uniqueKategories.filter(Boolean);
});

const subKategoriOptions = computed(() => {
    if (!filters.value.kategori) return [];
    return items.value
        .filter(
            (item) => item.kategori?.nama_kategori === filters.value.kategori
        )
        .map((item) => item.sub_kategori?.nama_sub_kategori)
        .filter((value, index, self) => value && self.indexOf(value) === index);
});

const tahunOptions = computed(() => {
    const years = items.value.map((item) =>
        new Date(item.created_at).getFullYear()
    );
    return [...new Set(years)].sort((a, b) => b - a);
});

// Methods
const formatDate = (dateString) =>
    new Date(dateString).toLocaleDateString("id-ID", {
        year: "numeric",
        month: "short",
        day: "numeric",
    });

const formatCurrency = (value) => value?.toLocaleString("id-ID") || "0";

const openDialog = () => (dialogVisible.value = true);
const closeDialog = () => {
    dialogVisible.value = false;
    refetchItems();
};

const handlePageChange = (newPage) => {
    currentPage.value = newPage;
    refetchItems({
        page: newPage,
        per_page: ITEMS_PER_PAGE,
        ...filters.value,
    });
};

const handleFilterChange = () => {
    currentPage.value = 1;
    refetchItems({
        page: 1,
        per_page: ITEMS_PER_PAGE,
        ...filters.value,
    });
};

const toggleVerification = async (item) => {
    await updateVerificationStatus(item.id, !item.status);
    refetchItems();
};

const printSuratMasuk = (item) => {
    // Implement your print functionality here
    console.log("Printing surat masuk:", item.nomor_surat);
    window.print(); // Simple print for demo
};

// Watchers
watch(
    () => filters.value.kategori,
    () => {
        filters.value.subKategori = "";
        handleFilterChange();
    }
);

watch(
    () => [
        filters.value.subKategori,
        filters.value.tahun,
        filters.value.search,
        filters.value.sort,
    ],
    handleFilterChange,
    { deep: true }
);
</script>

<template>
    <div class="w-full p-6">
        <div class="flex justify-between items-center mb-4">
            <Button @click="openDialog" class="gap-2">
                <Plus class="h-4 w-4" />
                Tambah Data
            </Button>
        </div>

        <AddBarangMasuk
            v-if="dialogVisible"
            :visible="dialogVisible"
            @update:visible="closeDialog"
        />

        <template v-if="!dialogVisible">
            <!-- Loading and error states -->
            <div v-if="isLoading" class="text-center py-8">
                <p>Memuat data...</p>
            </div>
            <div v-else-if="isError" class="text-center py-8 text-red-500">
                <p>Gagal memuat data</p>
            </div>

            <!-- Data display -->
            <template v-else>
                <!-- Filters -->
                <div class="flex flex-row gap-4 mb-4">
                    <Select v-model="filters.kategori">
                        <SelectTrigger class="w-40">
                            <SelectValue placeholder="Semua Kategori" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="">Semua Kategori</SelectItem>
                            <SelectItem
                                v-for="option in kategoriOptions"
                                :key="option"
                                :value="option"
                            >
                                {{ option }}
                            </SelectItem>
                        </SelectContent>
                    </Select>

                    <Select
                        v-model="filters.subKategori"
                        :disabled="!filters.kategori"
                    >
                        <SelectTrigger class="w-40">
                            <SelectValue placeholder="Semua Sub Kategori" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="">Semua Sub Kategori</SelectItem>
                            <SelectItem
                                v-for="option in subKategoriOptions"
                                :key="option"
                                :value="option"
                            >
                                {{ option }}
                            </SelectItem>
                        </SelectContent>
                    </Select>

                    <Select v-model="filters.tahun">
                        <SelectTrigger class="w-32">
                            <SelectValue placeholder="Semua Tahun" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="">Semua Tahun</SelectItem>
                            <SelectItem
                                v-for="option in tahunOptions"
                                :key="option"
                                :value="option"
                            >
                                {{ option }}
                            </SelectItem>
                        </SelectContent>
                    </Select>

                    <Select v-model="filters.sort">
                        <SelectTrigger class="w-48">
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

                    <div class="relative flex-1 max-w-md">
                        <Search
                            class="absolute left-3 top-3 h-4 w-4 text-muted-foreground"
                        />
                        <Input
                            v-model="filters.search"
                            placeholder="Cari asal barang atau sub kategori..."
                            class="pl-10"
                        />
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
                                v-for="(item, index) in items"
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
                                            {{ index + 1 }}
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
                                                item.kategori.nama_kategori ||
                                                "-"
                                            }}
                                        </TableCell>
                                        <TableCell>{{
                                            item.kategori.kode_kategori || "-"
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
                        Menampilkan {{ meta.from }} sampai {{ meta.to }} dari
                        {{ meta.total }} data
                    </div>
                    <div class="flex items-center space-x-2">
                        <Button
                            variant="outline"
                            size="sm"
                            :disabled="meta.current_page === 1"
                            @click="handlePageChange(meta.current_page - 1)"
                        >
                            <ChevronLeft class="h-4 w-4" />
                        </Button>
                        <span class="text-sm text-muted-foreground">
                            Halaman {{ meta.current_page }} dari
                            {{ meta.last_page }}
                        </span>
                        <Button
                            variant="outline"
                            size="sm"
                            :disabled="meta.current_page >= meta.last_page"
                            @click="handlePageChange(meta.current_page + 1)"
                        >
                            <ChevronRight class="h-4 w-4" />
                        </Button>
                    </div>
                </div>
            </template>
        </template>
    </div>
</template>
