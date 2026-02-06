import type { QueryClient } from '@tanstack/react-query'

declare global {
  interface Window {
    wps_react?: wpsReact
    wp: unknown
  }

  interface RouterContext {
    queryClient: QueryClient
  }
}

export {}
