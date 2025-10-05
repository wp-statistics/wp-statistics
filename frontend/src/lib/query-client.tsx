import { QueryClient } from '@tanstack/react-query'

export function createQueryClient(): QueryClient {
  return new QueryClient({
    defaultOptions: {
      queries: {
        staleTime: 5 * 1000 * 60, // Data is considered fresh for 5 minutes (reduces unnecessary refetching)
        gcTime: 10 * 1000 * 60,
        refetchOnWindowFocus: false, // Prevents automatic refetching when the window regains focus
        retry: 1, // Retries failed queries only once
        experimental_prefetchInRender: true, // Enables experimental prefetching during render
      },
      mutations: {
        retry: 1,
      },
    },
  })
}
