<script setup>
import { ref } from "vue";
import { useForm, router } from "@inertiajs/vue3";
import {
    Select,
    SelectTrigger,
    SelectContent,
    SelectItem,
} from "@/components/ui/select";
import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";
import { useToast } from "@/components/ui/toast/use-toast";

const { toast } = useToast();

const roles = ref(["Admin", "Operator"]);
const emit = defineEmits(["success", "cancel"]);

const form = useForm({
    name: "",
    username: "",
    email: "",
    password: "",
    role: roles.value[0],
});

const validateForm = () => {
    let isValid = true;
    form.clearErrors();

    if (!form.name) {
        form.setError("name", "Name is required");
        isValid = false;
    }

    if (!form.username) {
        form.setError("username", "Username is required");
        isValid = false;
    } else if (form.username.length < 8) {
        form.setError("username", "Username must be at least 8 characters");
        isValid = false;
    }

    if (!form.email) {
        form.setError("email", "Email is required");
        isValid = false;
    } else if (!/^\S+@\S+\.\S+$/.test(form.email)) {
        form.setError("email", "Email is invalid");
        isValid = false;
    }

    if (!form.password) {
        form.setError("password", "Password is required");
        isValid = false;
    } else if (form.password.length < 8) {
        form.setError("password", "Password must be at least 8 characters");
        isValid = false;
    }

    if (!form.role) {
        form.setError("role", "Role is required");
        isValid = false;
    }

    return isValid;
};

const handleSubmit = () => {
    if (!validateForm()) return;

    form.post(route("users.store"), {
        preserveScroll: true,
        onSuccess: () => {
            toast({
                title: "Success",
                description: "User created successfully",
            });
            form.reset();
            emit("success");
            router.reload({ only: ["users"] });
        },
        onError: () => {
            toast({
                title: "Error",
                description: "Failed to create user",
                variant: "destructive",
            });
        },
    });
};
</script>

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
                    :class="{ 'border-red-500': form.errors.role }"
                >
                    <SelectTrigger class="w-full border">
                        {{ form.role || "Select Role" }}
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="unassign">Unassign</SelectItem>

                        <!-- Errror bikim rusak di selectimen value -->
                        <SelectItem
                            v-for="role in roles"
                            :key="role"
                            :value="role"
                        >
                            {{ role }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <small v-if="form.errors.role" class="text-red-500">
                    {{ form.errors.role }}
                </small>
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
                    :class="{ 'border-red-500': form.errors.username }"
                />
                <small v-if="form.errors.username" class="text-red-500">
                    {{ form.errors.username }}
                </small>
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
                    :class="{ 'border-red-500': form.errors.password }"
                />
                <small v-if="form.errors.password" class="text-red-500">
                    {{ form.errors.password }}
                </small>
            </div>

            <!-- Name -->
            <div class="flex flex-col gap-1">
                <label for="name"
                    >Name <span class="text-red-500">*</span></label
                >
                <Input
                    id="name"
                    v-model="form.name"
                    class="w-full"
                    :class="{ 'border-red-500': form.errors.name }"
                />
                <small v-if="form.errors.name" class="text-red-500">
                    {{ form.errors.name }}
                </small>
            </div>
            <!-- Email -->
            <div class="flex flex-col gap-1">
                <label for="email"
                    >Email <span class="text-red-500">*</span></label
                >
                <Input
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="w-full"
                    :class="{ 'border-red-500': form.errors.email }"
                />
                <small v-if="form.errors.email" class="text-red-500">
                    {{ form.errors.email }}
                </small>
            </div>

            <!-- Buttons -->
            <div class="flex justify-end gap-2 pt-2">
                <Button
                    variant="outline"
                    type="button"
                    @click="$emit('cancel')"
                    :disabled="form.processing"
                >
                    Close
                </Button>
                <Button
                    variant="default"
                    type="submit"
                    :disabled="form.processing"
                >
                    <span v-if="form.processing">Creating...</span>
                    <span v-else>Submit</span>
                </Button>
            </div>
        </form>
    </div>
</template>
