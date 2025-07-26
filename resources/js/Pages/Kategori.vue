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
import AddKategori from "@/Components/Dialog/AddKategori.vue";
import { Dialog, DialogContent, DialogTrigger } from "@/components/ui/dialog";

const search = ref("");
const currentPage = ref(1);
const rowsPerPage = 2;

const data = ref([
    { no: 1, kode: "PK", namaKategori: "Perlengkapan Kantor" },
    { no: 2, kode: "FO", namaKategori: "Makanan" },
    { no: 3, kode: "AT", namaKategori: "ATK" },
    { no: 4, kode: "EL", namaKategori: "Elektronik" },
]);

const filteredData = computed(() => {
    return data.value.filter(
        (item) =>
            item.namaKategori
                .toLowerCase()
                .includes(search.value.toLowerCase()) ||
            item.kode.toLowerCase().includes(search.value.toLowerCase())
    );
});

const paginatedData = computed(() => {
    const start = (currentPage.value - 1) * rowsPerPage;
    return filteredData.value.slice(start, start + rowsPerPage);
});

const totalPages = computed(() => {
    return Math.ceil(filteredData.value.length / rowsPerPage);
});

function handlePageChange(newPage) {
    currentPage.value = newPage;
}

const showDialog = ref(false);
</script>

<template>
    <AuthenticatedLayout>
        <div class="p-6 w-5/6">
            <!-- Breadcrumb -->
            <p class="text-sm text-muted-foreground mb-2">
                HOME / MASTER DATA / KATEGORI
            </p>

            <!-- Title -->
            <h2 class="text-xl font-semibold mb-4">Kategori</h2>

            <!-- Header actions -->
            <div class="flex justify-between items-center mb-4">
                <Dialog>
                    <DialogTrigger>
                        <!-- Your button to open dialog -->
                        <Button class="gap-2" @click="showDialog = true">
                            <Plus class="h-4 w-4" />
                            Tambah Data
                        </Button>
                    </DialogTrigger>
                    <DialogContent>
                        <AddKategori />
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

            <!-- Data Table -->
            <div class="rounded-md border">
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
                        <TableRow v-for="item in paginatedData" :key="item.no">
                            <TableCell>{{ item.no }}</TableCell>
                            <TableCell>
                                <div class="flex gap-2">
                                    <Pencil
                                        class="h-4 w-4 text-blue-600 cursor-pointer hover:text-blue-800"
                                    />
                                    <Trash2
                                        class="h-4 w-4 text-red-600 cursor-pointer hover:text-red-800"
                                    />
                                </div>
                            </TableCell>
                            <TableCell>{{ item.kode }}</TableCell>
                            <TableCell>{{ item.namaKategori }}</TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <!-- Pagination -->
            <Pagination class="mt-4 justify-end">
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
    </AuthenticatedLayout>
</template>
