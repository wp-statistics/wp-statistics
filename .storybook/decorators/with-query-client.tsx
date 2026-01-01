import { QueryClient, QueryClientProvider } from '@tanstack/react-query'
import type { Decorator } from '@storybook/react'

/**
 * Decorator that provides QueryClient context for Storybook
 * This prevents "No QueryClient set" errors when using React Query hooks in stories
 */
const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      retry: false,
      staleTime: Infinity,
      gcTime: Infinity,
    },
  },
})

export const withQueryClient: Decorator = (Story) => (
  <QueryClientProvider client={queryClient}>
    <Story />
  </QueryClientProvider>
)
