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
import { useUser } from "@/composables/useUser";
import { useToast } from "@/components/ui/toast/use-toast";

// Props
const visible = defineModel("visible");
const { toast } = useToast();
const { createUser } = useUser();
const emit = defineEmits(["success"]);
// Options & form
const roles = ["Admin", "Operator"];
const form = ref({
    role: "",
    username: "",
    password: "",
    nama: "",
    email: "",
});

// Errors
const errors = ref({
    role: null,
    username: null,
    password: null,
    nama: null,
    email: null,
});

// Validation
const validateForm = () => {
    let isValid = true;
    errors.value = {
        role: null,
        username: null,
        password: null,
        nama: null,
        email: null,
    };

    if (!form.value.role) {
        errors.value.role = "Role is required";
        isValid = false;
    }

    if (!form.value.username) {
        errors.value.username = "Username is required";
        isValid = false;
    } else if (form.value.username.length < 3) {
        errors.value.username = "Username must be at least 3 characters";
        isValid = false;
    }

    if (!form.value.password) {
        errors.value.password = "Password is required";
        isValid = false;
    } else if (form.value.password.length < 8) {
        errors.value.password = "Password must be at least 8 characters";
        isValid = false;
    }

    if (!form.value.nama) {
        errors.value.nama = "Name is required";
        isValid = false;
    }

    if (!form.value.email) {
        errors.value.email = "Email is required";
        isValid = false;
    } else if (!/^\S+@\S+\.\S+$/.test(form.value.email)) {
        errors.value.email = "Email is invalid";
        isValid = false;
    }

    return isValid;
};

// Submission
const handleSubmit = async () => {
    if (!validateForm()) return;

    try {
        await createUser.mutateAsync({
            name: form.value.nama,
            username: form.value.username,
            email: form.value.email,
            password: form.value.password,
            role: form.value.role,
        });

        toast({
            title: "Success",
            description: "User created successfully",
            variant: "success",
        });

        // Reset form
        form.value = {
            role: "",
            username: "",
            password: "",
            nama: "",
            email: "",
        };

        emit("success");
    } catch (error) {
        toast({
            title: "Error",
            description: error.message || "Failed to create user",
            variant: "destructive",
        });
    }
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
                <label for="role"
                    >Role <span class="text-red-500">*</span></label
                >
                <Select
                    v-model="form.role"
                    :class="{ 'border-red-500': errors.role }"
                >
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
                <small v-if="errors.role" class="text-red-500">{{
                    errors.role
                }}</small>
            </div>

            <!-- Username -->
            <div class="flex flex-col gap-1">
                <label for="username"
                    >Username <span class="text-red-500">*</span></label
                >
                <Input
                    id="username"
                    v-model="form.username"
                    class="w-full"
                    :class="{ 'border-red-500': errors.username }"
                />
                <small v-if="errors.username" class="text-red-500">{{
                    errors.username
                }}</small>
            </div>

            <!-- Password -->
            <div class="flex flex-col gap-1">
                <label for="password"
                    >Password <span class="text-red-500">*</span></label
                >
                <Input
                    id="password"
                    v-model="form.password"
                    type="password"
                    class="w-full"
                    :class="{ 'border-red-500': errors.password }"
                />
                <small v-if="errors.password" class="text-red-500">{{
                    errors.password
                }}</small>
            </div>

            <!-- Nama -->
            <div class="flex flex-col gap-1">
                <label for="nama"
                    >Nama <span class="text-red-500">*</span></label
                >
                <Input
                    id="nama"
                    v-model="form.nama"
                    class="w-full"
                    :class="{ 'border-red-500': errors.nama }"
                />
                <small v-if="errors.nama" class="text-red-500">{{
                    errors.nama
                }}</small>
            </div>

            <!-- Email -->
            <div class="flex flex-col gap-1">
                <label for="email"
                    >Email <span class="text-red-500">*</span></label
                >
                <Input
                    id="email"
                    v-model="form.email"
                    class="w-full"
                    :class="{ 'border-red-500': errors.email }"
                />
                <small v-if="errors.email" class="text-red-500">{{
                    errors.email
                }}</small>
            </div>

            <!-- Buttons -->
            <div class="flex justify-end gap-2 pt-2">
                <Button
                    variant="outline"
                    type="button"
                    @click="visible = false"
                    :disabled="createUser.isLoading"
                >
                    Close
                </Button>
                <Button
                    variant="default"
                    type="submit"
                    :disabled="createUser.isLoading"
                >
                    <span v-if="createUser.isLoading">Creating...</span>
                    <span v-else>Submit</span>
                </Button>
            </div>
        </form>
    </div>
</template>
