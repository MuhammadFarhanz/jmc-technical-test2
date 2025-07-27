// useUser.js
import { useQuery, useMutation, useQueryClient } from "@tanstack/vue-query";
import axios from "axios";

export function useUser() {
    const queryClient = useQueryClient();

    // Fetch all users
    const usersQuery = useQuery({
        queryKey: ["users"],
        queryFn: async () => {
            const { data } = await axios.get("/users");

            return data;
        },
    });

    // Create user
    const createUser = useMutation({
        mutationFn: async (payload) => {
            const { data } = await axios.post("/users", payload);
            return data;
        },
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: ["users"] });
        },
    });

    // Update user
    const updateUser = useMutation({
        mutationFn: async ({ id, ...payload }) => {
            const { data } = await axios.put(`/users/${id}`, payload);
            return data;
        },
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: ["users"] });
        },
    });

    // Delete user
    const deleteUser = useMutation({
        mutationFn: async (id) => {
            await axios.delete(`/users/${id}`);
            return id;
        },
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: ["users"] });
        },
    });

    return {
        usersQuery,
        createUser,
        updateUser,
        deleteUser,
    };
}
