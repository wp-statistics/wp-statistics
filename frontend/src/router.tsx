import type { QueryClient } from '@tanstack/react-query'
import { createHashHistory, createRouter } from '@tanstack/react-router'

import { routeTree } from './routeTree.gen'

export const createAppRouter = (queryClient: QueryClient) => {
  const hashHistory = createHashHistory()

  return createRouter({
    routeTree,
    context: { queryClient },
    history: hashHistory,
    defaultPreload: 'intent' as const,
    defaultPreloadStaleTime: 0,
    scrollRestoration: true,
  })
}

// Register the router instance for type safety
declare module '@tanstack/react-router' {
  interface Register {
    router: ReturnType<typeof createAppRouter>
  }
}
