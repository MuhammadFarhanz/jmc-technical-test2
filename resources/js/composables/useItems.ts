import axios from "axios";
import { useMutation, useQuery } from "@tanstack/vue-query";

export function useItems() {
    const createMutation = useMutation({
        mutationFn: async (formData: FormData) => {
            const response = await axios.post("/items", formData, {
                headers: {
                    "Content-Type": "multipart/form-data",
                },
            });
            return response.data;
        },
    });

    const fetchItemsQuery = useQuery({
        queryKey: ["items"],
        queryFn: async () => {
            const response = await axios.get("/items");
            return response.data.data;
        },

        // Optional: refetch interval or other options
        refetchOnWindowFocus: false,
    });

    return { createMutation, fetchItemsQuery };
}
