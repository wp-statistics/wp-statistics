import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest'

import {
  clearCachedColumns,
  computeApiColumns,
  type ColumnConfig,
  getCachedApiColumns,
  getCachedVisibility,
  getCachedVisibleColumns,
  getCacheKey,
  getDefaultApiColumns,
  getVisibleColumnsForSave,
  setCachedColumns,
} from '@/lib/column-utils'

// Mock localStorage
const localStorageMock = (() => {
  let store: Record<string, string> = {}
  return {
    getItem: vi.fn((key: string) => store[key] || null),
    setItem: vi.fn((key: string, value: string) => {
      store[key] = value
    }),
    removeItem: vi.fn((key: string) => {
      delete store[key]
    }),
    clear: () => {
      store = {}
    },
  }
})()

Object.defineProperty(global, 'localStorage', { value: localStorageMock })

describe('column-utils', () => {
  // Sample column config for tests
  const sampleConfig: ColumnConfig = {
    baseColumns: ['id', 'date'],
    columnDependencies: {
      visitorInfo: ['country_code', 'browser', 'os'],
      location: ['country_code', 'region', 'city'],
      views: ['total_views'],
      duration: ['avg_duration'],
    },
    context: 'test-table',
  }

  const allColumnIds = ['visitorInfo', 'location', 'views', 'duration', 'status']

  beforeEach(() => {
    localStorageMock.clear()
    vi.clearAllMocks()
  })

  afterEach(() => {
    localStorageMock.clear()
  })

  describe('getCacheKey', () => {
    it('generates correct cache key with context', () => {
      expect(getCacheKey('visitors')).toBe('wp_statistics_columns_visitors')
      expect(getCacheKey('top-pages')).toBe('wp_statistics_columns_top-pages')
    })
  })

  describe('computeApiColumns', () => {
    it('returns base columns when all columns hidden', () => {
      const visibility = {
        visitorInfo: false,
        location: false,
        views: false,
        duration: false,
      }
      const result = computeApiColumns(visibility, allColumnIds, sampleConfig)
      expect(result).toEqual(['id', 'date'])
    })

    it('includes dependencies for visible columns', () => {
      const visibility = {
        visitorInfo: true,
        location: false,
        views: false,
        duration: false,
      }
      const result = computeApiColumns(visibility, allColumnIds, sampleConfig)
      expect(result).toContain('country_code')
      expect(result).toContain('browser')
      expect(result).toContain('os')
      expect(result).not.toContain('region')
      expect(result).not.toContain('city')
    })

    it('treats undefined visibility as visible (TanStack Table default)', () => {
      const visibility = {} // All columns visible by default
      const result = computeApiColumns(visibility, allColumnIds, sampleConfig)
      // Should include dependencies for all columns with dependencies
      expect(result).toContain('country_code')
      expect(result).toContain('browser')
      expect(result).toContain('total_views')
      expect(result).toContain('avg_duration')
    })

    it('includes sort column dependencies even when column is hidden', () => {
      const visibility = {
        duration: false, // Hidden but sorting by it
      }
      const result = computeApiColumns(visibility, allColumnIds, sampleConfig, 'duration')
      expect(result).toContain('avg_duration')
    })

    it('handles columns without dependencies', () => {
      const visibility = { status: true }
      const result = computeApiColumns(visibility, allColumnIds, sampleConfig)
      // Should still return base columns
      expect(result).toContain('id')
      expect(result).toContain('date')
    })

    it('deduplicates shared dependencies', () => {
      const visibility = {
        visitorInfo: true,
        location: true,
      }
      const result = computeApiColumns(visibility, allColumnIds, sampleConfig)
      // country_code is in both visitorInfo and location dependencies
      const countryCodeCount = result.filter((col) => col === 'country_code').length
      expect(countryCodeCount).toBe(1)
    })
  })

  describe('getVisibleColumnsForSave', () => {
    it('returns visible columns in default order when no custom order', () => {
      const visibility = {
        visitorInfo: true,
        location: false,
        views: true,
        duration: true,
      }
      const result = getVisibleColumnsForSave(visibility, [], allColumnIds)
      expect(result).toEqual(['visitorInfo', 'views', 'duration', 'status'])
    })

    it('respects custom column order for visible columns', () => {
      const visibility = {
        visitorInfo: true,
        location: true,
        views: true,
      }
      const columnOrder = ['views', 'location', 'visitorInfo']
      const result = getVisibleColumnsForSave(visibility, columnOrder, allColumnIds)
      // Should start with ordered columns
      expect(result[0]).toBe('views')
      expect(result[1]).toBe('location')
      expect(result[2]).toBe('visitorInfo')
    })

    it('appends visible columns not in order at the end', () => {
      const visibility = {
        visitorInfo: true,
        location: true,
        views: true,
        duration: true,
        status: true,
      }
      const columnOrder = ['views', 'location'] // Only partial order
      const result = getVisibleColumnsForSave(visibility, columnOrder, allColumnIds)
      expect(result[0]).toBe('views')
      expect(result[1]).toBe('location')
      // Remaining columns should follow in their default order
      expect(result).toContain('visitorInfo')
      expect(result).toContain('duration')
      expect(result).toContain('status')
    })

    it('treats undefined visibility as visible', () => {
      const visibility = {} // All visible by default
      const result = getVisibleColumnsForSave(visibility, [], allColumnIds)
      expect(result).toEqual(allColumnIds)
    })

    it('excludes hidden columns from order', () => {
      const visibility = {
        visitorInfo: false,
        views: true,
      }
      const columnOrder = ['visitorInfo', 'views'] // visitorInfo in order but hidden
      const result = getVisibleColumnsForSave(visibility, columnOrder, allColumnIds)
      expect(result).not.toContain('visitorInfo')
      expect(result[0]).toBe('views')
    })
  })

  describe('getDefaultApiColumns', () => {
    it('returns all base columns and dependencies combined', () => {
      const result = getDefaultApiColumns(sampleConfig)
      expect(result).toContain('id')
      expect(result).toContain('date')
      expect(result).toContain('country_code')
      expect(result).toContain('browser')
      expect(result).toContain('total_views')
      expect(result).toContain('avg_duration')
    })

    it('deduplicates columns', () => {
      const result = getDefaultApiColumns(sampleConfig)
      // country_code appears in multiple dependencies
      const countryCodeCount = result.filter((col) => col === 'country_code').length
      expect(countryCodeCount).toBe(1)
    })

    it('returns only base columns when no dependencies', () => {
      const configNoDeps: ColumnConfig = {
        baseColumns: ['id', 'name'],
        columnDependencies: {},
        context: 'simple',
      }
      const result = getDefaultApiColumns(configNoDeps)
      expect(result).toEqual(['id', 'name'])
    })
  })

  describe('setCachedColumns', () => {
    it('saves columns to localStorage with correct key', () => {
      const columns = ['visitorInfo', 'views', 'duration']
      setCachedColumns('test-context', columns)
      expect(localStorageMock.setItem).toHaveBeenCalledWith(
        'wp_statistics_columns_test-context',
        JSON.stringify(columns)
      )
    })

    it('handles empty array', () => {
      setCachedColumns('test-context', [])
      expect(localStorageMock.setItem).toHaveBeenCalledWith('wp_statistics_columns_test-context', '[]')
    })
  })

  describe('getCachedVisibleColumns', () => {
    it('returns parsed columns from localStorage', () => {
      const columns = ['visitorInfo', 'views']
      localStorageMock.getItem.mockReturnValueOnce(JSON.stringify(columns))
      const result = getCachedVisibleColumns('test-context')
      expect(result).toEqual(columns)
    })

    it('returns null when no cache exists', () => {
      localStorageMock.getItem.mockReturnValueOnce(null)
      const result = getCachedVisibleColumns('test-context')
      expect(result).toBeNull()
    })

    it('returns null for empty array in cache', () => {
      localStorageMock.getItem.mockReturnValueOnce('[]')
      const result = getCachedVisibleColumns('test-context')
      expect(result).toBeNull()
    })

    it('returns null for invalid JSON', () => {
      localStorageMock.getItem.mockReturnValueOnce('not-valid-json')
      const result = getCachedVisibleColumns('test-context')
      expect(result).toBeNull()
    })

    it('returns null for non-array cached data', () => {
      localStorageMock.getItem.mockReturnValueOnce('"string"')
      const result = getCachedVisibleColumns('test-context')
      expect(result).toBeNull()
    })
  })

  describe('getCachedApiColumns', () => {
    it('converts cached visible columns to API columns', () => {
      localStorageMock.getItem.mockReturnValueOnce(JSON.stringify(['visitorInfo', 'views']))
      const result = getCachedApiColumns(allColumnIds, sampleConfig)
      expect(result).toContain('id') // base
      expect(result).toContain('date') // base
      expect(result).toContain('country_code') // visitorInfo
      expect(result).toContain('browser') // visitorInfo
      expect(result).toContain('total_views') // views
      expect(result).not.toContain('avg_duration') // duration not cached
    })

    it('returns null when no cache exists', () => {
      localStorageMock.getItem.mockReturnValueOnce(null)
      const result = getCachedApiColumns(allColumnIds, sampleConfig)
      expect(result).toBeNull()
    })

    it('ignores unknown columns in cache', () => {
      localStorageMock.getItem.mockReturnValueOnce(JSON.stringify(['unknown', 'views']))
      const result = getCachedApiColumns(allColumnIds, sampleConfig)
      // Should still work, just ignoring unknown
      expect(result).toContain('total_views')
      expect(result).not.toContain('unknown')
    })
  })

  describe('getCachedVisibility', () => {
    it('converts cached columns to visibility object', () => {
      localStorageMock.getItem.mockReturnValueOnce(JSON.stringify(['visitorInfo', 'views']))
      const result = getCachedVisibility('test-context', allColumnIds)
      expect(result).toEqual({
        visitorInfo: true,
        location: false,
        views: true,
        duration: false,
        status: false,
      })
    })

    it('returns null when no cache exists', () => {
      localStorageMock.getItem.mockReturnValueOnce(null)
      const result = getCachedVisibility('test-context', allColumnIds)
      expect(result).toBeNull()
    })

    it('handles cached columns not in allColumnIds', () => {
      localStorageMock.getItem.mockReturnValueOnce(JSON.stringify(['visitorInfo', 'unknownColumn']))
      const result = getCachedVisibility('test-context', allColumnIds)
      expect(result?.visitorInfo).toBe(true)
      // unknownColumn not in result since not in allColumnIds
      expect(result).not.toHaveProperty('unknownColumn')
    })
  })

  describe('clearCachedColumns', () => {
    it('removes cache from localStorage', () => {
      clearCachedColumns('test-context')
      expect(localStorageMock.removeItem).toHaveBeenCalledWith('wp_statistics_columns_test-context')
    })
  })
})
