import { useQuery, useMutation, useQueryClient } from "@tanstack/vue-query";
import axios from "axios";

export function useItems() {
    const queryClient = useQueryClient();

    // Fetch items with pagination
    const fetchItems = async ({ queryKey, pageParam = 1 }) => {
        const [_, page] = queryKey;
        const { data } = await axios.get("/api/items", {
            params: {
                page: pageParam || page,
            },
        });
        return data;
    };

    // Get items query with pagination support
    const itemsQuery = (page = 1) =>
        useQuery({
            queryKey: ["items", page],
            queryFn: fetchItems,
            keepPreviousData: true,
        });

    // Create item mutation with proper error handling
    const createItemMutation = useMutation({
        mutationFn: async (newItem) => {
            const { data } = await axios.post("/api/items", newItem);
            return data;
        },
        onSuccess: (data) => {
            queryClient.invalidateQueries(["items"]);
            return data;
        },
        onError: (error) => {
            throw error.response?.data;
        },
    });

    // Update item mutation
    const updateItemMutation = useMutation({
        mutationFn: async ({ id, ...payload }) => {
            const { data } = await axios.put(`/api/items/${id}`, payload);
            return data;
        },
        onSuccess: () => {
            queryClient.invalidateQueries(["items"]);
        },
    });

    // Delete item mutation
    const deleteItemMutation = useMutation({
        mutationFn: async (id) => {
            await axios.delete(`/api/items/${id}`);
            return id;
        },
        onSuccess: () => {
            queryClient.invalidateQueries(["items"]);
        },
    });

    return {
        itemsQuery,
        createItemMutation,
        updateItemMutation,
        deleteItemMutation,
    };
}
