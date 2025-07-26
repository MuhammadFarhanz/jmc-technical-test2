<script setup>
import { ref } from "vue";
import { Dialog } from "@/components/ui/dialog";
import {
    Select,
    SelectTrigger,
    SelectContent,
    SelectItem,
} from "@/components/ui/select";
import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";

// Props
const visible = defineModel("visible");

// Options & form
const roles = ["Admin", "Operator"];
const form = ref({
    role: "",
    username: "",
    password: "",
    nama: "",
    email: "",
});

// Submission
const handleSubmit = () => {
    console.log("Submitted form:", form.value);
    // TODO: Add validation & API call
    visible.value = false;
};
</script>

<style scoped>
.text-red-500 {
    color: #f44336;
}
</style>
<template>
    <div class="max-w-lg w-full rounded-xl">
        <form @submit.prevent="handleSubmit" class="space-y-4">
            <!-- Role -->
            <div class="flex flex-col gap-1">
                <label for="role">Role</label>
                <Select v-model="form.role">
                    <SelectTrigger class="w-full border">
                        {{ form.role || "Pilih Role" }}
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="role in roles"
                            :key="role"
                            :value="role"
                        >
                            {{ role }}
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <!-- Username -->
            <div class="flex flex-col gap-1">
                <label for="username">Username</label>
                <Input id="username" v-model="form.username" class="w-full" />
            </div>

            <!-- Password -->
            <div class="flex flex-col gap-1">
                <label for="password">Password</label>
                <Input
                    id="password"
                    v-model="form.password"
                    type="password"
                    class="w-full"
                />
            </div>

            <!-- Nama -->
            <div class="flex flex-col gap-1">
                <label for="nama">Nama</label>
                <Input id="nama" v-model="form.nama" class="w-full" />
            </div>

            <!-- Email -->
            <div class="flex flex-col gap-1">
                <label for="email">Email</label>
                <Input id="email" v-model="form.email" class="w-full" />
            </div>

            <!-- Buttons -->
            <div class="flex justify-end gap-2 pt-2">
                <Button variant="outline" type="button" @click="visible = false"
                    >Close</Button
                >
                <Button variant="default" type="submit">Submit</Button>
            </div>
        </form>
    </div>
</template>
