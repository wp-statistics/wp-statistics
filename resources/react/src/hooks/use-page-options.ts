import { useContext } from 'react'

import { PageOptionsContext, type PageOptionsContextValue } from '@/contexts/page-options-context'

/**
 * Hook to access page options context
 * Must be used within a PageOptionsProvider
 */
export function usePageOptions(): PageOptionsContextValue {
  const context = useContext(PageOptionsContext)
  if (!context) {
    throw new Error('usePageOptions must be used within a PageOptionsProvider')
  }
  return context
}
