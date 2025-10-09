declare global {
  interface Window {
    wp: any
  }
}

interface RouterContext {
  queryClient: QueryClient
}
