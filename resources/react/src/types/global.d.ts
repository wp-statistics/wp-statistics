import type { QueryClient } from '@tanstack/react-query'

declare global {
  interface Window {
    wp_statistics_react?: wpsReact
    wp: unknown
    wpsHeaderExtensions?: Array<import('react').ComponentType>
  }

  interface RouterContext {
    queryClient: QueryClient
  }
}

export {}
