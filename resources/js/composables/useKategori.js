import { useQuery, useMutation, useQueryClient } from "@tanstack/vue-query";
import axios from "axios";

export function useKategori() {
    const queryClient = useQueryClient();

    // Fetch all kategori with proper error handling
    const {
        data: kategoris,
        isLoading,
        isError,
        error,
    } = useQuery({
        queryKey: ["kategori"],
        queryFn: async () => {
            const { data } = await axios.get("/kategori");
            return data;
        },
    });

    // Create kategori with proper error handling
    const createKategori = useMutation({
        mutationFn: async (payload) => {
            const { data } = await axios.post("/kategori", payload);
            return data;
        },
        onSuccess: () => {
            queryClient.invalidateQueries(["kategori"]);
        },
        onError: (error) => {
            console.error("Error creating kategori:", error);
        },
    });

    // Update kategori
    const updateKategori = useMutation({
        mutationFn: async ({ id, ...payload }) => {
            const { data } = await axios.put(`/kategori/${id}`, payload);
            return data;
        },
        onSuccess: () => {
            queryClient.invalidateQueries(["kategori"]);
        },
    });

    // Delete kategori
    const deleteKategori = useMutation({
        mutationFn: async (id) => {
            await axios.delete(`/kategori/${id}`);
            return id;
        },
        onSuccess: () => {
            queryClient.invalidateQueries(["kategori"]);
        },
    });

    return {
        kategoris,
        isLoading,
        isError,
        error,
        createKategori,
        updateKategori,
        deleteKategori,
    };
}
