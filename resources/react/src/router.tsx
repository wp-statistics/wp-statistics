import type { QueryClient } from '@tanstack/react-query'
import { createHashHistory, createRouter } from '@tanstack/react-router'

import { routeTree } from './routeTree.gen'

/**
 * Custom search param stringifier that keeps brackets and colons unencoded
 * for more readable URLs like: filter[country]=in:JP,CN
 * instead of: filter%5Bcountry%5D=in%3AJP%2CCN
 *
 * Note: With hash history, we need to include the '?' prefix because the router
 * doesn't add it when constructing hash URLs. This is a workaround for a
 * TanStack Router hash history quirk.
 */
const stringifySearch = (search: Record<string, unknown>): string => {
  const params = new URLSearchParams()

  for (const [key, value] of Object.entries(search)) {
    if (value !== undefined && value !== null) {
      params.set(key, String(value))
    }
  }

  const searchString = params.toString()

  // Return empty string if no params
  if (!searchString) return ''

  // Convert to string and decode brackets, colons, and commas for readability
  // Include '?' prefix for hash history compatibility - TanStack Router's hash history
  // builds URLs by concatenating pathname + searchStr, so we need to include the '?'
  return '?' + searchString
    .replace(/%5B/g, '[')
    .replace(/%5D/g, ']')
    .replace(/%3A/g, ':')
    .replace(/%2C/g, ',')
}

/**
 * Custom search param parser that handles hash-based routing correctly.
 *
 * When using hash history with a main URL like:
 * http://example.com/admin.php?page=wp-statistics#/views?date_from=2024-01-01
 *
 * The browser's location.search contains "?page=wp-statistics" (main URL params)
 * The hash contains "#/views?date_from=2024-01-01" (hash route params)
 *
 * Without this parser, the main URL's query params can incorrectly get appended
 * to the hash route on page reload. This parser filters out WordPress admin params
 * that don't belong in the hash route's search params.
 */
const WORDPRESS_ADMIN_PARAMS = new Set(['page', 'post_type', 'taxonomy'])

const parseSearch = (searchString: string): Record<string, string> => {
  // Remove leading '?' if present
  let cleanSearch = searchString.startsWith('?') ? searchString.slice(1) : searchString

  // Early return for empty search
  if (!cleanSearch) return {}

  // Fix for TanStack Router hash history quirk:
  // When navigating to a URL like: admin.php?page=wp-statistics#/visitors?order=asc
  // The hash search string can incorrectly get the main URL's query string appended:
  // "order_by=lastVisit&order=asc?page=wp-statistics" instead of "order_by=lastVisit&order=asc"
  // This happens because the browser's location includes both query strings.
  // We detect this by finding a '?' in the middle of the search string (after removing the leading '?')
  // and strip everything from that point.
  const extraQueryIndex = cleanSearch.indexOf('?')
  if (extraQueryIndex > 0) {
    cleanSearch = cleanSearch.substring(0, extraQueryIndex)
  }

  const params = new URLSearchParams(cleanSearch)
  const result: Record<string, string> = {}

  params.forEach((value, key) => {
    // Exclude WordPress admin params that belong to the main URL, not the hash route.
    // These can get incorrectly appended due to TanStack Router hash history quirks.
    // See: top-categories.tsx and top-authors.tsx for similar workarounds.
    if (WORDPRESS_ADMIN_PARAMS.has(key)) {
      return // Skip - this param is from the main WordPress admin URL
    }
    result[key] = value
  })

  return result
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
    parseSearch,
  })
}

// Register the router instance for type safety
declare module '@tanstack/react-router' {
  interface Register {
    router: ReturnType<typeof createAppRouter>
  }
}
