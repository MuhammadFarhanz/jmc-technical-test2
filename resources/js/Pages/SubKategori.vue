<script setup>
import { ref, computed, onMounted } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
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
    Pagination,
    PaginationContent,
    PaginationItem,
} from "@/components/ui/pagination";
import {
    Pencil,
    Trash2,
    Search,
    Plus,
    ChevronLeft,
    ChevronRight,
} from "lucide-vue-next";
import AddSubKategori from "@/Components/Dialog/AddSubKategori.vue";
import { Dialog, DialogContent, DialogTrigger } from "@/components/ui/dialog";
import { useSubKategori } from "@/composables/useSubKategori";
import { useKategori } from "@/composables/useKategori";
import { useToast } from "@/components/ui/toast/use-toast";

const { toast } = useToast();
const search = ref("");
const currentPage = ref(1);
const rowsPerPage = 10;
const showDialog = ref(false);
const currentEditItem = ref(null);

// Fetch data
const { subKategorisQuery, deleteSubKategori } = useSubKategori();
const { kategoris, isLoading: isLoadingKategori } = useKategori();

// Handle delete
const handleDelete = async (id) => {
    if (confirm("Apakah Anda yakin ingin menghapus sub kategori ini?")) {
        try {
            await deleteSubKategori.mutateAsync(id);
            toast({
                title: "Berhasil",
                description: "Sub kategori berhasil dihapus",
                variant: "default",
            });
        } catch (error) {
            toast({
                title: "Gagal",
                description:
                    error.response?.data?.message ||
                    "Gagal menghapus sub kategori",
                variant: "destructive",
            });
        }
    }
};

// Handle edit
const handleEdit = (item) => {
    currentEditItem.value = {
        id: item.id,
        kategori_id: item.kategori_id,
        nama_subkategori: item.nama_subkategori,
        batas_harga: item.batas_harga,
    };
    showDialog.value = true;
};

const kategoriOptions = computed(() => {
    return (
        kategoris.value?.map((kategori) => ({
            id: kategori.id,
            name: kategori.nama_kategori, 
        })) || []
    );
});

// Computed properties
const filteredData = computed(() => {
    if (!subKategorisQuery.data.value) return [];

    return subKategorisQuery.data.value.filter(
        (item) =>
            item.kategori.nama_kategori
                .toLowerCase()
                .includes(search.value.toLowerCase()) ||
            item.nama_subkategori
                .toLowerCase()
                .includes(search.value.toLowerCase())
    );
});

const pagedData = computed(() => {
    const start = (currentPage.value - 1) * rowsPerPage;
    return filteredData.value.slice(start, start + rowsPerPage);
});

const totalPages = computed(() => {
    return Math.ceil(filteredData.value.length / rowsPerPage);
});

const handleSuccess = () => {
    showDialog.value = false;
    currentEditItem.value = null;
    subKategorisQuery.refetch();
};
</script>

<template>
    <AuthenticatedLayout>
        <div class="p-6 w-5/6">
            <!-- Breadcrumb -->
            <p class="text-sm text-muted-foreground mb-2">
                HOME / MASTER DATA / SUB KATEGORI
            </p>

            <!-- Title -->
            <h2 class="text-xl font-semibold mb-4">Sub Kategori</h2>

            <!-- Header actions -->
            <div class="flex justify-between items-center mb-4">
                <Dialog v-model:open="showDialog">
                    <DialogTrigger as-child>
                        <Button class="gap-2">
                            <Plus class="h-4 w-4" />
                            Tambah Data
                        </Button>
                    </DialogTrigger>
                    <DialogContent class="sm:max-w-[425px]">
                        <AddSubKategori
                            :kategori-options="kategoriOptions"
                            :initial-data="currentEditItem"
                            @success="handleSuccess"
                            @cancel="showDialog = false"
                        />
                    </DialogContent>
                </Dialog>
                <div class="relative">
                    <Search
                        class="absolute left-3 top-3 h-4 w-4 text-muted-foreground"
                    />
                    <Input
                        v-model="search"
                        placeholder="Cari data..."
                        class="pl-10 w-48"
                    />
                </div>
            </div>

            <!-- Loading State -->
            <div
                v-if="subKategorisQuery.isLoading.value"
                class="text-center py-4"
            >
                Memuat data...
            </div>

            <!-- Error State -->
            <div
                v-else-if="subKategorisQuery.isError.value"
                class="text-center py-4 text-red-500"
            >
                Gagal memuat data sub kategori
            </div>

            <!-- Table -->
            <div v-else class="rounded-md border shadow-sm">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead class="w-[50px]">No</TableHead>
                            <TableHead class="w-[90px]">Action</TableHead>
                            <TableHead>Kategori Barang</TableHead>
                            <TableHead>Sub Kategori Barang</TableHead>
                            <TableHead>Batas Harga (Rp)</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow
                            v-for="(item, index) in pagedData"
                            :key="item.id"
                        >
                            <TableCell>
                                {{ index + 1 }}
                            </TableCell>
                            <TableCell>
                                <div class="flex gap-2 justify-center">
                                    <Pencil
                                        class="h-4 w-4 text-blue-600 cursor-pointer hover:text-blue-800"
                                        @click="handleEdit(item)"
                                    />
                                    <Trash2
                                        class="h-4 w-4 text-red-600 cursor-pointer hover:text-red-800"
                                        @click="handleDelete(item.id)"
                                    />
                                </div>
                            </TableCell>
                            <TableCell>{{
                                item.kategori?.nama_kategori
                            }}</TableCell>
                            <TableCell>{{ item.nama_subkategori }}</TableCell>
                            <TableCell>
                                {{
                                    item.batas_harga
                                        ? new Intl.NumberFormat("id-ID").format(
                                              item.batas_harga
                                          )
                                        : "-"
                                }}
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="pagedData.length === 0">
                            <TableCell colspan="5" class="text-center py-4">
                                Tidak ada data ditemukan
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <!-- Pagination -->
            <Pagination
                v-if="
                    !subKategorisQuery.isLoading.value &&
                    !subKategorisQuery.isError.value
                "
                class="mt-4 justify-end"
            >
                <PaginationContent>
                    <PaginationItem>
                        <Button
                            variant="ghost"
                            :disabled="currentPage === 1"
                            @click="currentPage--"
                        >
                            <ChevronLeft class="h-4 w-4" />
                        </Button>
                    </PaginationItem>
                    <PaginationItem v-for="page in totalPages" :key="page">
                        <Button
                            variant="ghost"
                            :class="{ 'bg-accent': currentPage === page }"
                            @click="currentPage = page"
                        >
                            {{ page }}
                        </Button>
                    </PaginationItem>
                    <PaginationItem>
                        <Button
                            variant="ghost"
                            :disabled="currentPage === totalPages"
                            @click="currentPage++"
                        >
                            <ChevronRight class="h-4 w-4" />
                        </Button>
                    </PaginationItem>
                </PaginationContent>
            </Pagination>
        </div>
    </AuthenticatedLayout>
</template>
