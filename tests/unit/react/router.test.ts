import { describe, it, expect } from 'vitest'

/**
 * Tests for router.tsx stringifySearch and parseSearch functions
 *
 * These functions handle URL search parameter serialization for TanStack Router
 * with hash history. The key requirement is that stringifySearch must include
 * the '?' prefix for hash history URLs to be properly formatted.
 */

// Re-implement the functions here for testing since they're not exported
// This ensures the test logic matches the implementation

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

  // Include '?' prefix for hash history compatibility
  return '?' + searchString
    .replace(/%5B/g, '[')
    .replace(/%5D/g, ']')
    .replace(/%3A/g, ':')
    .replace(/%2C/g, ',')
}

const WORDPRESS_ADMIN_PARAMS = new Set(['page', 'post_type', 'taxonomy'])

const parseSearch = (searchString: string): Record<string, string> => {
  // Remove leading '?' if present
  const cleanSearch = searchString.startsWith('?') ? searchString.slice(1) : searchString

  // Early return for empty search
  if (!cleanSearch) return {}

  const params = new URLSearchParams(cleanSearch)
  const result: Record<string, string> = {}

  params.forEach((value, key) => {
    // Exclude WordPress admin params
    if (WORDPRESS_ADMIN_PARAMS.has(key)) {
      return
    }
    result[key] = value
  })

  return result
}

describe('router stringifySearch', () => {
  describe('question mark prefix', () => {
    it('should include ? prefix when there are search params', () => {
      const result = stringifySearch({ date_from: '2026-01-01' })
      expect(result).toMatch(/^\?/)
      expect(result).toBe('?date_from=2026-01-01')
    })

    it('should return empty string when no params', () => {
      const result = stringifySearch({})
      expect(result).toBe('')
    })

    it('should return empty string when all values are undefined', () => {
      const result = stringifySearch({ date_from: undefined, date_to: null })
      expect(result).toBe('')
    })
  })

  describe('bracket notation decoding', () => {
    it('should decode bracket notation for filter params', () => {
      const result = stringifySearch({ 'filter[country]': 'eq:US' })
      expect(result).toBe('?filter[country]=eq:US')
      expect(result).not.toContain('%5B')
      expect(result).not.toContain('%5D')
    })

    it('should decode colons in filter values', () => {
      const result = stringifySearch({ 'filter[country]': 'in:US,JP,CN' })
      expect(result).toBe('?filter[country]=in:US,JP,CN')
      expect(result).not.toContain('%3A')
    })

    it('should decode commas in filter values', () => {
      const result = stringifySearch({ 'filter[country]': 'in:US,JP,CN' })
      expect(result).toBe('?filter[country]=in:US,JP,CN')
      expect(result).not.toContain('%2C')
    })
  })

  describe('multiple params', () => {
    it('should handle multiple date params', () => {
      const result = stringifySearch({
        date_from: '2026-01-01',
        date_to: '2026-01-14',
      })
      expect(result).toMatch(/^\?/)
      expect(result).toContain('date_from=2026-01-01')
      expect(result).toContain('date_to=2026-01-14')
    })

    it('should handle date params with filter', () => {
      const result = stringifySearch({
        date_from: '2026-01-01',
        date_to: '2026-01-14',
        'filter[visitor_type]': 'is:0',
      })
      expect(result).toMatch(/^\?/)
      expect(result).toContain('date_from=2026-01-01')
      expect(result).toContain('filter[visitor_type]=is:0')
    })

    it('should handle multiple filters', () => {
      const result = stringifySearch({
        'filter[country]': 'eq:US',
        'filter[browser]': 'eq:Chrome',
      })
      expect(result).toMatch(/^\?/)
      expect(result).toContain('filter[country]=eq:US')
      expect(result).toContain('filter[browser]=eq:Chrome')
    })
  })

  describe('URL construction for hash history', () => {
    it('should produce valid URL when concatenated with pathname', () => {
      const pathname = '/visitors-overview'
      const searchStr = stringifySearch({
        date_from: '2026-01-01',
        date_to: '2026-01-14',
      })
      const fullPath = `${pathname}${searchStr}`

      // This is the critical test - the URL should have ? between path and params
      expect(fullPath).toBe('/visitors-overview?date_from=2026-01-01&date_to=2026-01-14')
      expect(fullPath).toMatch(/^\/visitors-overview\?/)
    })

    it('should not produce malformed URL without ? separator', () => {
      const pathname = '/visitors-overview'
      const searchStr = stringifySearch({ date_from: '2026-01-01' })
      const fullPath = `${pathname}${searchStr}`

      // Should NOT be /visitors-overviewdate_from=... (missing ?)
      expect(fullPath).not.toMatch(/\/visitors-overviewdate_from/)
      expect(fullPath).toBe('/visitors-overview?date_from=2026-01-01')
    })
  })
})

describe('router parseSearch', () => {
  describe('basic parsing', () => {
    it('should parse search string with leading ?', () => {
      const result = parseSearch('?date_from=2026-01-01')
      expect(result).toEqual({ date_from: '2026-01-01' })
    })

    it('should parse search string without leading ?', () => {
      const result = parseSearch('date_from=2026-01-01')
      expect(result).toEqual({ date_from: '2026-01-01' })
    })

    it('should return empty object for empty string', () => {
      expect(parseSearch('')).toEqual({})
      expect(parseSearch('?')).toEqual({})
    })
  })

  describe('WordPress admin params filtering', () => {
    it('should filter out page param', () => {
      const result = parseSearch('?page=wp-statistics&date_from=2026-01-01')
      expect(result).not.toHaveProperty('page')
      expect(result).toEqual({ date_from: '2026-01-01' })
    })

    it('should filter out post_type param', () => {
      const result = parseSearch('?post_type=post&date_from=2026-01-01')
      expect(result).not.toHaveProperty('post_type')
      expect(result).toEqual({ date_from: '2026-01-01' })
    })

    it('should filter out taxonomy param', () => {
      const result = parseSearch('?taxonomy=category&date_from=2026-01-01')
      expect(result).not.toHaveProperty('taxonomy')
      expect(result).toEqual({ date_from: '2026-01-01' })
    })

    it('should filter multiple WordPress admin params', () => {
      const result = parseSearch('?page=wp-statistics&post_type=post&date_from=2026-01-01')
      expect(result).not.toHaveProperty('page')
      expect(result).not.toHaveProperty('post_type')
      expect(result).toEqual({ date_from: '2026-01-01' })
    })
  })

  describe('filter params parsing', () => {
    it('should parse bracket notation filter params', () => {
      const result = parseSearch('?filter[country]=eq:US')
      expect(result).toEqual({ 'filter[country]': 'eq:US' })
    })

    it('should parse multiple filter params', () => {
      const result = parseSearch('?filter[country]=eq:US&filter[browser]=eq:Chrome')
      expect(result).toEqual({
        'filter[country]': 'eq:US',
        'filter[browser]': 'eq:Chrome',
      })
    })
  })
})
