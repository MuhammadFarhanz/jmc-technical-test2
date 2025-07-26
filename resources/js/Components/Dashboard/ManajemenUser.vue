<script setup>
import { ref, computed } from "vue";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import {
    Select,
    SelectTrigger,
    SelectContent,
    SelectItem,
} from "@/components/ui/select";
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
    Lock,
    Search,
    Plus,
} from "lucide-vue-next";
import AddUserDialog from "../Dialog/AddUserDialog.vue";
import { Dialog, DialogContent, DialogTrigger } from "@/components/ui/dialog";

const search = ref("");
const selectedRole = ref("");
const currentPage = ref(1);
const rowsPerPage = 10;

const users = ref([
    {
        no: 1,
        username: "admin",
        nama: "Administrator",
        email: "admin@gmail.com",
        role: "Admin",
    },
    {
        no: 2,
        username: "joko",
        nama: "Joko Suyanto",
        email: "jokos@gmail.com",
        role: "Operator",
    },
    {
        no: 3,
        username: "sari",
        nama: "Sari Dewi",
        email: "sari@gmail.com",
        role: "Operator",
    },
    {
        no: 4,
        username: "budi",
        nama: "Budi Santoso",
        email: "budi@gmail.com",
        role: "Admin",
    },
    {
        no: 5,
        username: "lisa",
        nama: "Lisa Mulyani",
        email: "lisa@gmail.com",
        role: "Operator",
    },
    {
        no: 6,
        username: "rehan",
        nama: "Rehan Ali",
        email: "rehan@gmail.com",
        role: "Admin",
    },
    {
        no: 7,
        username: "dian",
        nama: "Dian Setiawan",
        email: "dian@gmail.com",
        role: "Operator",
    },
    {
        no: 8,
        username: "yusuf",
        nama: "Yusuf Hadi",
        email: "yusuf@gmail.com",
        role: "Admin",
    },
    {
        no: 9,
        username: "tina",
        nama: "Tina Nurhaliza",
        email: "tina@gmail.com",
        role: "Operator",
    },
    {
        no: 10,
        username: "andi",
        nama: "Andi Prasetyo",
        email: "andi@gmail.com",
        role: "Admin",
    },
    {
        no: 11,
        username: "rahma",
        nama: "Rahma Ayu",
        email: "rahma@gmail.com",
        role: "Operator",
    },
    // Tambahkan lebih banyak user jika perlu
]);

const roleOptions = ["Admin", "Operator"];

const filteredData = computed(() => {
    return users.value.filter((user) => {
        const matchSearch =
            user.username.toLowerCase().includes(search.value.toLowerCase()) ||
            user.nama.toLowerCase().includes(search.value.toLowerCase());

        const matchRole = selectedRole.value
            ? user.role === selectedRole.value
            : true;

        return matchSearch && matchRole;
    });
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
    <div class="p-6 w-5/6">
        <!-- Breadcrumb -->
        <p class="text-sm text-muted-foreground mb-2">HOME / MANAJEMEN USER</p>

        <!-- Title -->
        <h2 class="text-xl font-semibold mb-4">Manajemen User</h2>

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
                    <AddUserDialog />
                </DialogContent>
            </Dialog>
            <div class="flex gap-2 items-center">
                <Select v-model="selectedRole">
                    <SelectTrigger class="w-40">
                        {{ selectedRole || "Semua Role" }}
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem :value="''">Semua Role</SelectItem>
                        <SelectItem
                            v-for="role in roleOptions"
                            :key="role"
                            :value="role"
                        >
                            {{ role }}
                        </SelectItem>
                    </SelectContent>
                </Select>
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
        </div>

        <!-- Data Table -->
        <div class="rounded-md border shadow-sm">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead class="w-[50px]">No</TableHead>
                        <TableHead class="w-[100px]">Action</TableHead>
                        <TableHead>Username</TableHead>
                        <TableHead>Nama</TableHead>
                        <TableHead>Email</TableHead>
                        <TableHead>Role</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="user in pagedData" :key="user.no">
                        <TableCell>{{ user.no }}</TableCell>
                        <TableCell>
                            <div class="flex gap-2 justify-center">
                                <Pencil
                                    class="h-4 w-4 text-blue-600 cursor-pointer"
                                />
                                <Trash2
                                    class="h-4 w-4 text-red-600 cursor-pointer"
                                />
                                <Lock
                                    class="h-4 w-4 text-gray-600 cursor-pointer"
                                />
                            </div>
                        </TableCell>
                        <TableCell>{{ user.username }}</TableCell>
                        <TableCell>{{ user.nama }}</TableCell>
                        <TableCell>{{ user.email }}</TableCell>
                        <TableCell>{{ user.role }}</TableCell>
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
</template>
