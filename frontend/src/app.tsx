import { QueryClientProvider } from '@tanstack/react-query'
import { RouterProvider } from '@tanstack/react-router'

import { createQueryClient } from './lib/query-client'
import { createAppRouter } from './router'

const queryClient = createQueryClient()
const router = createAppRouter(queryClient)

export const App = () => {
  return (
    <QueryClientProvider client={queryClient}>
      <RouterProvider router={router} context={{ queryClient }} />
    </QueryClientProvider>
  )
}
