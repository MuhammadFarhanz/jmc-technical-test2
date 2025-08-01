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
// import AddSubKategori from "@/Components/Dialog/AddSubKategori.vue";
import { Dialog, DialogContent, DialogTrigger } from "@/components/ui/dialog";

import { useToast } from "@/components/ui/toast/use-toast";
import AddSubKategori from "@/Components/Dialog/AddSubKategori.vue";

const { toast } = useToast();
const search = ref("");
const currentPage = ref(1);
const rowsPerPage = 10;
const showDialog = ref(false);
const currentEditItem = ref(null);

const props = defineProps({
    subKategoris: {
        type: Array,
        required: true,
        default: () => [],
    },
});

defineOptions({
    layout: AuthenticatedLayout,
});
console.log(props.subKategoris);
const filteredData = computed(() => {
    if (!props.subKategoris) return [];

    return props.subKategoris.filter(
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

};
</script>

<template>
    <!-- <p>{{ props.subKategoris }}</p> -->
    <div class="p-6 w-full">
        <p class="text-sm text-muted-foreground mb-2">
            HOME / MASTER DATA / SUB KATEGORI
        </p>

        <h2 class="text-xl font-semibold mb-4">Sub Kategori</h2>

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
        <!-- 
        <div v-if="subKategorisQuery.isLoading.value" class="text-center py-4">
            Memuat data...
        </div>

        <div
            v-else-if="subKategorisQuery.isError.value"
            class="text-center py-4 text-red-500"
        >
            Gagal memuat data sub kategori
        </div> -->

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
                    <TableRow v-for="(item, index) in pagedData" :key="item.id">
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

        <!-- <Pagination
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
        </Pagination> -->
    </div>
</template>
