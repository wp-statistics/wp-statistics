// Register custom React components for PHP-driven settings/tools tabs
import './settings/custom-components'

import { QueryClientProvider } from '@tanstack/react-query'
import { RouterProvider } from '@tanstack/react-router'

import { queryClient } from './lib/query-client'
import { createAppRouter } from './router'

const router = createAppRouter(queryClient)

export const App = () => {
  return (
    <QueryClientProvider client={queryClient}>
      <RouterProvider router={router} context={{ queryClient }} />
    </QueryClientProvider>
  )
}
