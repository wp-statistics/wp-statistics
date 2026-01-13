import type { QueryClient } from '@tanstack/react-query'
import { createHashHistory, createRouter } from '@tanstack/react-router'

import { routeTree } from './routeTree.gen'

/**
 * Custom search param stringifier that keeps brackets and colons unencoded
 * for more readable URLs like: filter[country]=in:JP,CN
 * instead of: filter%5Bcountry%5D=in%3AJP%2CCN
 */
const stringifySearch = (search: Record<string, unknown>): string => {
  const params = new URLSearchParams()

  for (const [key, value] of Object.entries(search)) {
    if (value !== undefined && value !== null) {
      params.set(key, String(value))
    }
  }

  // Convert to string and decode brackets, colons, and commas for readability
  return params
    .toString()
    .replace(/%5B/g, '[')
    .replace(/%5D/g, ']')
    .replace(/%3A/g, ':')
    .replace(/%2C/g, ',')
}

export const createAppRouter = (queryClient: QueryClient) => {
  const hashHistory = createHashHistory()

  return createRouter({
    routeTree,
    context: { queryClient },
    history: hashHistory,
    defaultPreload: 'intent' as const,
    defaultPreloadStaleTime: 0,
    scrollRestoration: true,
    stringifySearch,
  })
}

// Register the router instance for type safety
declare module '@tanstack/react-router' {
  interface Register {
    router: ReturnType<typeof createAppRouter>
  }
}
