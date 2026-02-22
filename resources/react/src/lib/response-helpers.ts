/**
 * Response Helper Utilities
 *
 * Safe extraction of data from nested API responses.
 *
 * @package WP_Statistics
 */

/**
 * Safely extract rows from API response with type validation
 *
 * @example
 * ```typescript
 * const rows = extractRows<EntryPageRecord>(response)
 * // or with custom path
 * const rows = extractRows<EntryPageRecord>(response, ['data', 'items'])
 * ```
 */
export function extractRows<T>(
  response: unknown,
  path: string[] = ['data', 'data', 'rows']
): T[] {
  let current: unknown = response
  for (const key of path) {
    if (current && typeof current === 'object' && key in current) {
      current = (current as Record<string, unknown>)[key]
    } else {
      return []
    }
  }
  return Array.isArray(current) ? current : []
}

/**
 * Extract metadata from API response
 *
 * @example
 * ```typescript
 * const meta = extractMeta(response)
 * if (meta) {
 *   console.log(`Page ${meta.page} of ${meta.totalPages}`)
 * }
 * ```
 */
export function extractMeta(
  response: unknown,
  path: string[] = ['data', 'meta']
): { totalRows: number; totalPages: number; page: number } | null {
  let current: unknown = response
  for (const key of path) {
    if (current && typeof current === 'object' && key in current) {
      current = (current as Record<string, unknown>)[key]
    } else {
      return null
    }
  }
  if (current && typeof current === 'object') {
    const meta = current as Record<string, unknown>
    return {
      totalRows: Number(meta.total_rows) || 0,
      totalPages: Number(meta.total_pages) || 1,
      page: Number(meta.page) || 1,
    }
  }
  return null
}
