import { QueryClientProvider } from '@tanstack/react-query'
import { RouterProvider } from '@tanstack/react-router'

import { ContentRegistryProvider } from './contexts/content-registry-context'
import { queryClient } from './lib/query-client'
import { createAppRouter } from './router'

const router = createAppRouter(queryClient)

export const App = () => {
  return (
    <QueryClientProvider client={queryClient}>
      <ContentRegistryProvider>
        <RouterProvider router={router} context={{ queryClient }} />
      </ContentRegistryProvider>
    </QueryClientProvider>
  )
}
