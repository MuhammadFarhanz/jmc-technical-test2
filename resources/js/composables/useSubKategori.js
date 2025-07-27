import { useQuery, useMutation, useQueryClient } from "@tanstack/vue-query";
import axios from "axios";
import { computed } from "vue";

export function useSubKategori() {
    const queryClient = useQueryClient();

    // Fetch all subkategori with error handling
    const subKategorisQuery = useQuery({
        queryKey: ["sub-kategori"],
        queryFn: async () => {
            try {
                const { data } = await axios.get("/sub-kategori");

                return data;
            } catch (error) {
                throw new Error("Failed to fetch sub categories");
            }
        },
    });

    const subKategoriOptions = computed(() => {
        const items = subKategorisQuery.data?.value ?? [];

        console.log(
            "Sub Kategori Options:",
            items.map((item) => typeof item)
        );
        return items.map((subKategori) => ({
            id: subKategori.id,
            name: subKategori.nama_subkategori,
            batasHarga: subKategori.batas_harga,
        }));
    });

    // Create subkategori with enhanced error handling
    const createSubKategori = useMutation({
        mutationFn: async (payload) => {
            try {
                const { data } = await axios.post("/sub-kategori", payload);
                return data;
            } catch (error) {
                // Throw the error response to access validation errors
                throw error.response?.data;
            }
        },
        onSuccess: () => {
            queryClient.invalidateQueries(["sub-kategori"]);
        },
        onError: (error) => {
            console.error("Create error:", error);
        },
    });

    // Update subkategori
    const updateSubKategori = useMutation({
        mutationFn: async ({ id, ...payload }) => {
            try {
                const { data } = await axios.put(
                    `/sub-kategori/${id}`,
                    payload
                );
                return data;
            } catch (error) {
                throw error.response?.data;
            }
        },
        onSuccess: () => {
            queryClient.invalidateQueries(["sub-kategori"]);
        },
    });

    // Delete subkategori
    const deleteSubKategori = useMutation({
        mutationFn: async (id) => {
            try {
                await axios.delete(`/sub-kategori/${id}`);
                return id;
            } catch (error) {
                throw new Error("Failed to delete sub category");
            }
        },
        onSuccess: () => {
            queryClient.invalidateQueries(["sub-kategori"]);
        },
    });

    return {
        subKategoriOptions,
        subKategorisQuery,
        createSubKategori,
        updateSubKategori,
        deleteSubKategori,
    };
}
