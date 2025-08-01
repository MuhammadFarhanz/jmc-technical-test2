<script setup>
import { ref, computed } from "vue";
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
    ChevronLeft,
    ChevronRight,
    Pencil,
    Trash2,
    Search,
    Plus,
} from "lucide-vue-next";

import { Dialog, DialogContent, DialogTrigger } from "@/components/ui/dialog";
import AddKategori from "@/Components/Dialog/AddKategori.vue";

const search = ref("");
const currentPage = ref(1);
const rowsPerPage = 10;



defineOptions({
    layout: AuthenticatedLayout,
});

const showDialog = ref(false);
const selectedKategori = ref(null); 

const openCreateDialog = () => {
    selectedKategori.value = null;
    showDialog.value = true;
};

const openEditDialog = (kategori) => {
    selectedKategori.value = { ...kategori };
    showDialog.value = true;
};

const handlePageChange = (page) => {
    currentPage.value = page;
};

const handleSuccess = () => {

    showDialog.value = false;
};

const props = defineProps({
    kategoris: {
        type: Array,
        required: true,
        default: () => [],
    },
});

const filteredData = computed(() => {
    if (!props.kategoris) return [];

    return props.kategoris.filter(
        (item) =>
            item.nama_kategori
                .toLowerCase()
                .includes(search.value.toLowerCase()) ||
            item.kode_kategori
                .toLowerCase()
                .includes(search.value.toLowerCase())
    );
});

const paginatedData = computed(() => {
    const start = (currentPage.value - 1) * rowsPerPage;
    return filteredData.value.slice(start, start + rowsPerPage);
});

const totalPages = computed(() =>
    Math.ceil(filteredData.value.length / rowsPerPage)
);

console.log(props.kategoris, "props.kategoris");
// console.log(kategoris, "kategoris");
</script>

<template>
    <!-- <AuthenticatedLayout> -->
    <div class="p-6 w-full">
        <!-- Breadcrumb -->
        <p class="text-sm text-muted-foreground mb-2">
            HOME / MASTER DATA / KATEGORI
        </p>

        <!-- Title -->
        <h2 class="text-xl font-semibold mb-4">Kategori</h2>

        <!-- Header actions -->
        <div class="flex justify-between items-center mb-4">
            <Dialog v-model:open="showDialog">
                <DialogTrigger as-child>
                    <Button class="gap-2" @click="openCreateDialog">
                        <Plus class="h-4 w-4" />
                        Tambah Data
                    </Button>
                </DialogTrigger>
                <DialogContent>
                    <AddKategori
                        :kategori="selectedKategori"
                        @success="handleSuccess"
                        @close="showDialog = false"
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
        <div v-if="isLoading" class="text-center py-4">Memuat data...</div>

        <!-- Error State -->
        <div v-else-if="isError" class="text-center py-4 text-red-500">
            Gagal memuat data kategori
        </div>

        <!-- Data Table -->
        <div v-else class="rounded-md border">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead class="w-[50px]">No</TableHead>
                        <TableHead class="w-[90px]">Action</TableHead>
                        <TableHead>Kode</TableHead>
                        <TableHead>Nama Kategori</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow
                        v-for="(item, index) in paginatedData"
                        :key="item.id"
                    >
                        <TableCell>{{ index + 1 }}</TableCell>
                        <TableCell>
                            <div class="flex gap-2">
                                <Pencil
                                    class="h-4 w-4 text-blue-600 cursor-pointer hover:text-blue-800"
                                    @click="openEditDialog(item)"
                                />
                                <Trash2
                                    class="h-4 w-4 text-red-600 cursor-pointer hover:text-red-800"
                                    @click="handleDelete(item.id)"
                                />
                            </div>
                        </TableCell>
                        <TableCell>{{ item.kode_kategori }}</TableCell>
                        <TableCell>{{ item.nama_kategori }}</TableCell>
                    </TableRow>
                    <TableRow v-if="paginatedData.length === 0">
                        <TableCell colspan="4" class="text-center py-4">
                            Tidak ada data ditemukan
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>

        <!-- Pagination -->
        <Pagination v-if="!isLoading && !isError" class="mt-4 justify-end">
            <PaginationContent>
                <PaginationItem>
                    <Button
                        variant="ghost"
                        size="sm"
                        :disabled="currentPage === 1"
                        @click="handlePageChange(currentPage - 1)"
                    >
                        <ChevronLeft class="h-4 w-4" />
                    </Button>
                </PaginationItem>

                <PaginationItem v-for="page in totalPages" :key="page">
                    <Button
                        variant="ghost"
                        size="sm"
                        :class="{ 'bg-accent': currentPage === page }"
                        @click="handlePageChange(page)"
                    >
                        {{ page }}
                    </Button>
                </PaginationItem>

                <PaginationItem>
                    <Button
                        variant="ghost"
                        size="sm"
                        :disabled="currentPage === totalPages"
                        @click="handlePageChange(currentPage + 1)"
                    >
                        <ChevronRight class="h-4 w-4" />
                    </Button>
                </PaginationItem>
            </PaginationContent>
        </Pagination>
    </div>
    <!-- </AuthenticatedLayout> -->
</template>
