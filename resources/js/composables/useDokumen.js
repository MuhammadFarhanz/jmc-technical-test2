import { useQuery, useMutation, useQueryClient } from "@tanstack/vue-query";
import axios from "axios";

export function useDokumen() {
    const queryClient = useQueryClient();

    // Fetch all dokumen
    const dokumenQuery = useQuery({
        queryKey: ["dokumen"],
        queryFn: async () => {
            const { data } = await axios.get("/dokumen");
            return data;
        },
    });

    // Create dokumen
    const createDokumen = useMutation({
        mutationFn: async (payload) => {
            const formData = new FormData();
            Object.entries(payload).forEach(([key, value]) => {
                formData.append(key, value);
            });
            const { data } = await axios.post("/dokumen", formData, {
                headers: { "Content-Type": "multipart/form-data" },
            });
            return data;
        },
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: ["dokumen"] });
        },
    });

    // Update dokumen
    const updateDokumen = useMutation({
        mutationFn: async ({ id, ...payload }) => {
            const formData = new FormData();
            Object.entries(payload).forEach(([key, value]) => {
                formData.append(key, value);
            });
            const { data } = await axios.post(
                `/dokumen/${id}?_method=PUT`,
                formData,
                {
                    headers: { "Content-Type": "multipart/form-data" },
                }
            );
            return data;
        },
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: ["dokumen"] });
        },
    });

    // Delete dokumen
    const deleteDokumen = useMutation({
        mutationFn: async (id) => {
            await axios.delete(`/dokumen/${id}`);
            return id;
        },
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: ["dokumen"] });
        },
    });

    return {
        dokumenQuery,
        createDokumen,
        updateDokumen,
        deleteDokumen,
    };
}
