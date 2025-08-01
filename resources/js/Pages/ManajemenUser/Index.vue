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
import { Dialog, DialogContent, DialogTrigger } from "@/components/ui/dialog";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import AddUserDialog from "@/Components/Dialog/AddUserDialog.vue";

const search = ref("");
const selectedRole = ref("");
const currentPage = ref(1);
const rowsPerPage = 10;
const showDialog = ref(false);

defineOptions({
    layout: AuthenticatedLayout,
});

const props = defineProps({
    users: {
        type: Array,
        required: true,
        default: () => [],
    },
});

const filteredData = computed(() => {
    if (!props.users) return [];

    return props.users.filter((user) => {
        // Safe property access with optional chaining and nullish coalescing
        const username = user.username?.toLowerCase() ?? "";
        const name = user.name?.toLowerCase() ?? "";
        const searchTerm = search.value.toLowerCase();

        const matchSearch =
            username.includes(searchTerm) || name.includes(searchTerm);

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

const roleOptions = ["Admin", "Operator"];

const handleSuccess = () => {
    showDialog.value = false;
    props.users = [...props.users, props.newUser];
    props.newUser = {
        id: null,
        username: "",
        name: "",
        email: "",
        role: "",
    };
};

const deleteUser = (id) => {
    if (confirm("Are you sure you want to delete this user?")) {
        const index = props.users.findIndex((user) => user.id === id);
        if (index !== -1) {
            props.users.splice(index, 1);
        }
    }
};
</script>

<template>
    <div class="p-6 w-full">
        <!-- Breadcrumb -->
        <p class="text-sm text-muted-foreground mb-2">HOME / MANAJEMEN USER</p>

        <!-- Title -->
        <h2 class="text-xl font-semibold mb-4">Manajemen User</h2>

        <!-- Header actions -->
        <div class="flex justify-between items-center mb-4">
            <Dialog v-model:open="showDialog">
                <DialogTrigger>
                    <Button variant="default" class="gap-2">
                        <Plus class="h-4 w-4" />
                        Tambah Data
                    </Button>
                </DialogTrigger>
                <DialogContent>
                    <AddUserDialog
                        v-model:new-user="props.newUser"
                        @success="handleSuccess"
                        @cancel="showDialog = false"
                    />
                </DialogContent>
            </Dialog>
            <div class="flex gap-2 items-center">
                <Select v-model="selectedRole">
                    <SelectTrigger class="w-40">
                        {{ "Semua Role" }}
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem :value="null">Semua Role</SelectItem>
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
                    <TableRow v-for="(user, index) in pagedData" :key="user.id">
                        <TableCell> {{ index + 1 }}</TableCell>
                        <TableCell>
                            <div class="flex gap-2 justify-center">
                                <Pencil
                                    class="h-4 w-4 text-blue-600 cursor-pointer"
                                />
                                <Trash2
                                    class="h-4 w-4 text-red-600 cursor-pointer"
                                    @click="deleteUser(user.id)"
                                />
                                <Lock
                                    class="h-4 w-4 text-gray-600 cursor-pointer"
                                />
                            </div>
                        </TableCell>
                        <TableCell>{{ user.username }}</TableCell>
                        <TableCell>{{ user.name }}</TableCell>
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

