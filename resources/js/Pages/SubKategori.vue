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
    Pencil,
    Trash2,
    Search,
    Plus,
    ChevronLeft,
    ChevronRight,
} from "lucide-vue-next";
import AddSubKategori from "@/Components/Dialog/AddSubKategori.vue";
import { Dialog, DialogContent, DialogTrigger } from "@/components/ui/dialog";
const search = ref("");
const currentPage = ref(1);
const rowsPerPage = 2;

const data = ref([
    {
        no: 1,
        kategori: "Perlengkapan Kantor",
        subKategori: "Alat Tulis",
        batasHarga: "500.000",
    },
    {
        no: 2,
        kategori: "Perlengkapan Kantor",
        subKategori: "Perabotan",
        batasHarga: "7.500.000",
    },
    {
        no: 3,
        kategori: "Makanan",
        subKategori: "Cemilan",
        batasHarga: "200.000",
    },
    {
        no: 4,
        kategori: "Elektronik",
        subKategori: "Printer",
        batasHarga: "3.000.000",
    },
]);

const filteredData = computed(() => {
    return data.value.filter(
        (item) =>
            item.kategori.toLowerCase().includes(search.value.toLowerCase()) ||
            item.subKategori.toLowerCase().includes(search.value.toLowerCase())
    );
});

const pagedData = computed(() => {
    const start = (currentPage.value - 1) * rowsPerPage;
    return filteredData.value.slice(start, start + rowsPerPage);
});

const totalPages = computed(() => {
    return Math.ceil(filteredData.value.length / rowsPerPage);
});
</script>
<template>
    <AuthenticatedLayout>
        <div class="p-6 w-5/6">
            <!-- Breadcrumb -->
            <p class="text-sm text-gray-500 mb-2">
                HOME / MASTER DATA / SUB KATEGORI
            </p>

            <!-- Title -->
            <h2 class="text-xl font-semibold mb-4">Sub Kategori</h2>

            <!-- Header actions -->
            <div class="flex justify-between items-center mb-4">
                <Dialog>
                    <DialogTrigger>
                        <Button variant="default" class="gap-2">
                            <Plus class="h-4 w-4" />
                            Tambah Data
                        </Button>
                    </DialogTrigger>
                    <DialogContent>
                        <AddSubKategori />
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

            <!-- Table -->
            <div class="rounded-md border shadow-sm">
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
                        <TableRow v-for="item in pagedData" :key="item.no">
                            <TableCell>{{ item.no }}</TableCell>
                            <TableCell>
                                <div class="flex gap-2 justify-center">
                                    <Pencil
                                        class="h-4 w-4 text-blue-600 cursor-pointer"
                                    />
                                    <Trash2
                                        class="h-4 w-4 text-red-600 cursor-pointer"
                                    />
                                </div>
                            </TableCell>
                            <TableCell>{{ item.kategori }}</TableCell>
                            <TableCell>{{ item.subKategori }}</TableCell>
                            <TableCell>{{ item.batasHarga }}</TableCell>
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
    <
</template>
