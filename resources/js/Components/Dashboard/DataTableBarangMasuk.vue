<script setup>
import { ref } from "vue";
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
} from "lucide-vue-next";
import AddBarangMasuk from "../Dialog/AddBarangMasuk.vue";

// Data and state
const kategori = ref("");
const subKategori = ref("");
const tahun = ref("");
const search = ref("");
const dialogVisible = ref(false);

const openDialog = () => {
    dialogVisible.value = true;
};
const closeDialog = () => {
    dialogVisible.value = false;
};

const kategoriOptions = ["ATK", "Elektronik"];
const subKategoriOptions = ["Pulpen", "Kertas"];
const tahunOptions = ["2023", "2024"];

const data = [
    {
        no: 1,
        tanggal: "04/10/23 10:00:00",
        asalBarang: "PT. Sidomakmur",
        penerima: "Liana",
        unit: "Gudang Utama",
        kode: "ATK0001",
        nama: "Kertas HVS A4 60Gr",
        harga: "61.000",
        jumlah: "4 Rim",
        total: "244.000",
        status: true,
    },
    {
        no: 2,
        tanggal: "01/10/23 11:00:00",
        asalBarang: "PT. Sejahtera",
        penerima: "Liana",
        unit: "Gudang Utama",
        kode: "ATK0002",
        nama: "Bolpoint Pilot Std",
        harga: "20.000",
        jumlah: "1 Dus",
        total: "20.000",
        status: true,
    },
];

// Pagination
const currentPage = ref(1);
const itemsPerPage = 2;
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
            v-show="dialogVisible"
            :visible="dialogVisible"
            @update:visible="closeDialog"
        />
        <template v-if="!dialogVisible">
            <div class="flex gap-4 mb-4">
                <Select v-model="kategori">
                    <SelectTrigger class="w-40">
                        <SelectValue placeholder="Semua Kategori" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="option in kategoriOptions"
                            :key="option"
                            :value="option"
                        >
                            {{ option }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <Select v-model="subKategori">
                    <SelectTrigger class="w-40">
                        <SelectValue placeholder="Semua Sub Kategori" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="option in subKategoriOptions"
                            :key="option"
                            :value="option"
                        >
                            {{ option }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <Select v-model="tahun">
                    <SelectTrigger class="w-32">
                        <SelectValue placeholder="Semua Tahun" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="option in tahunOptions"
                            :key="option"
                            :value="option"
                        >
                            {{ option }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <div class="relative">
                    <Search
                        class="absolute left-3 top-3 h-4 w-4 text-muted-foreground"
                    />
                    <Input
                        v-model="search"
                        placeholder="Search"
                        class="pl-10 w-48"
                    />
                </div>
            </div>
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
                        <TableRow v-for="item in data" :key="item.no">
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
                            <TableCell>{{ item.tanggal }}</TableCell>
                            <TableCell>{{ item.asalBarang }}</TableCell>
                            <TableCell>{{ item.penerima }}</TableCell>
                            <TableCell>{{ item.unit }}</TableCell>
                            <TableCell>{{ item.kode }}</TableCell>
                            <TableCell>{{ item.nama }}</TableCell>
                            <TableCell>{{ item.harga }}</TableCell>
                            <TableCell>{{ item.jumlah }}</TableCell>
                            <TableCell>{{ item.total }}</TableCell>
                            <TableCell>
                                <CheckCircle2
                                    v-if="item.status"
                                    class="h-4 w-4 text-green-500"
                                />
                                <XCircle v-else class="h-4 w-4 text-red-500" />
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
            <!-- Pagination -->
            <div class="flex items-center justify-end space-x-2 py-4">
                <Button
                    variant="outline"
                    size="sm"
                    :disabled="currentPage === 1"
                    @click="currentPage--"
                >
                    <ChevronLeft class="h-4 w-4" />
                </Button>
                <Button
                    variant="outline"
                    size="sm"
                    :disabled="currentPage * itemsPerPage >= data.length"
                    @click="currentPage++"
                >
                    <ChevronRight class="h-4 w-4" />
                </Button>
            </div>
        </template>
    </div>
</template>
