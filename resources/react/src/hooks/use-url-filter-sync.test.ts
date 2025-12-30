import { describe, it, expect, vi, beforeEach } from 'vitest'
import { renderHook, act } from '@testing-library/react'
import { useUrlFilterSync } from './use-url-filter-sync'
import type { Filter } from '@/components/custom/filter-bar'
import type { FilterField } from '@/components/custom/filter-row'
import type { UrlFilter } from '@/lib/filter-utils'

// Mock TanStack Router
const mockNavigate = vi.fn()
vi.mock('@tanstack/react-router', () => ({
  useNavigate: () => mockNavigate,
}))

describe('useUrlFilterSync', () => {
  const mockFilterFields: FilterField[] = [
    {
      name: 'country',
      label: 'Country',
      type: 'select',
      options: [
        { value: 'US', label: 'United States' },
        { value: 'UK', label: 'United Kingdom' },
      ],
    },
    {
      name: 'browser',
      label: 'Browser',
      type: 'text',
    },
  ]

  beforeEach(() => {
    vi.clearAllMocks()
  })

  describe('initialization', () => {
    it('should initialize with empty filters when URL has no filters', () => {
      const { result } = renderHook(() =>
        useUrlFilterSync({
          urlFilters: undefined,
          urlPage: undefined,
          filterFields: mockFilterFields,
        })
      )

      expect(result.current.appliedFilters).toEqual([])
      expect(result.current.page).toBe(1)
      expect(result.current.isInitialized).toBe(true)
    })

    it('should parse filters from URL on mount', () => {
      const urlFilters: UrlFilter[] = [
        { field: 'country', operator: 'eq', value: 'US' },
      ]

      const { result } = renderHook(() =>
        useUrlFilterSync({
          urlFilters,
          urlPage: undefined,
          filterFields: mockFilterFields,
        })
      )

      expect(result.current.appliedFilters).toHaveLength(1)
      expect(result.current.appliedFilters?.[0]).toMatchObject({
        label: 'Country',
        operator: 'eq',
        rawValue: 'US',
      })
      expect(result.current.isInitialized).toBe(true)
    })

    it('should restore page number from URL', () => {
      const { result } = renderHook(() =>
        useUrlFilterSync({
          urlFilters: undefined,
          urlPage: 5,
          filterFields: mockFilterFields,
        })
      )

      expect(result.current.page).toBe(5)
    })

    it('should apply default filters when URL has no filters', () => {
      const defaultFilters: Filter[] = [
        {
          id: 'country-filter-1',
          label: 'Country',
          operator: 'eq',
          value: 'US',
        },
      ]

      const { result } = renderHook(() =>
        useUrlFilterSync({
          urlFilters: undefined,
          urlPage: undefined,
          filterFields: mockFilterFields,
          defaultFilters,
        })
      )

      expect(result.current.appliedFilters).toEqual(defaultFilters)
    })

    it('should not apply default filters when URL has filters', () => {
      const urlFilters: UrlFilter[] = [
        { field: 'browser', operator: 'eq', value: 'Chrome' },
      ]
      const defaultFilters: Filter[] = [
        {
          id: 'country-filter-1',
          label: 'Country',
          operator: 'eq',
          value: 'US',
        },
      ]

      const { result } = renderHook(() =>
        useUrlFilterSync({
          urlFilters,
          urlPage: undefined,
          filterFields: mockFilterFields,
          defaultFilters,
        })
      )

      expect(result.current.appliedFilters).toHaveLength(1)
      expect(result.current.appliedFilters?.[0].label).toBe('Browser')
    })
  })

  describe('handleRemoveFilter', () => {
    it('should remove filter by ID', () => {
      const urlFilters: UrlFilter[] = [
        { field: 'country', operator: 'eq', value: 'US' },
        { field: 'browser', operator: 'eq', value: 'Chrome' },
      ]

      const { result } = renderHook(() =>
        useUrlFilterSync({
          urlFilters,
          urlPage: undefined,
          filterFields: mockFilterFields,
        })
      )

      const filterIdToRemove = result.current.appliedFilters?.[0].id || ''

      act(() => {
        result.current.handleRemoveFilter(filterIdToRemove)
      })

      expect(result.current.appliedFilters).toHaveLength(1)
      expect(result.current.appliedFilters?.[0].label).toBe('Browser')
    })

    it('should reset page to 1 when removing filter', () => {
      const { result } = renderHook(() =>
        useUrlFilterSync({
          urlFilters: [{ field: 'country', operator: 'eq', value: 'US' }],
          urlPage: 5,
          filterFields: mockFilterFields,
        })
      )

      expect(result.current.page).toBe(5)

      act(() => {
        result.current.handleRemoveFilter(result.current.appliedFilters?.[0].id || '')
      })

      expect(result.current.page).toBe(1)
    })
  })

  describe('handleApplyFilters', () => {
    it('should apply new filters', () => {
      const { result } = renderHook(() =>
        useUrlFilterSync({
          urlFilters: undefined,
          urlPage: undefined,
          filterFields: mockFilterFields,
        })
      )

      const newFilters: Filter[] = [
        {
          id: 'country-filter-new',
          label: 'Country',
          operator: 'eq',
          value: 'UK',
        },
      ]

      act(() => {
        result.current.handleApplyFilters(newFilters)
      })

      expect(result.current.appliedFilters).toEqual(newFilters)
    })

    it('should reset page to 1 when applying filters', () => {
      const { result } = renderHook(() =>
        useUrlFilterSync({
          urlFilters: undefined,
          urlPage: 5,
          filterFields: mockFilterFields,
        })
      )

      act(() => {
        result.current.handleApplyFilters([
          { id: 'test', label: 'Test', operator: 'eq', value: 'test' },
        ])
      })

      expect(result.current.page).toBe(1)
    })
  })

  describe('URL synchronization', () => {
    it('should sync filters to URL when changed', () => {
      const { result } = renderHook(() =>
        useUrlFilterSync({
          urlFilters: undefined,
          urlPage: undefined,
          filterFields: mockFilterFields,
        })
      )

      act(() => {
        result.current.handleApplyFilters([
          {
            id: 'country-filter-1',
            label: 'Country',
            operator: 'eq',
            value: 'US',
            rawValue: 'US',
            rawOperator: 'eq',
          },
        ])
      })

      expect(mockNavigate).toHaveBeenCalledWith({
        search: expect.any(Function),
        replace: true,
      })
    })

    it('should sync page to URL when changed', () => {
      const { result } = renderHook(() =>
        useUrlFilterSync({
          urlFilters: undefined,
          urlPage: undefined,
          filterFields: mockFilterFields,
        })
      )

      act(() => {
        result.current.setPage(3)
      })

      expect(mockNavigate).toHaveBeenCalled()
    })

    it('should not sync to URL when values have not changed', () => {
      const { result } = renderHook(() =>
        useUrlFilterSync({
          urlFilters: [],
          urlPage: 1,
          filterFields: mockFilterFields,
        })
      )

      // Clear initial calls
      mockNavigate.mockClear()

      // Set same values
      act(() => {
        result.current.setAppliedFilters([])
        result.current.setPage(1)
      })

      // Should not navigate since values are the same
      expect(mockNavigate).not.toHaveBeenCalled()
    })
  })

  describe('setPage and setAppliedFilters', () => {
    it('should allow direct page updates', () => {
      const { result } = renderHook(() =>
        useUrlFilterSync({
          urlFilters: undefined,
          urlPage: undefined,
          filterFields: mockFilterFields,
        })
      )

      act(() => {
        result.current.setPage(10)
      })

      expect(result.current.page).toBe(10)
    })

    it('should allow direct filter updates', () => {
      const { result } = renderHook(() =>
        useUrlFilterSync({
          urlFilters: undefined,
          urlPage: undefined,
          filterFields: mockFilterFields,
        })
      )

      const newFilters: Filter[] = [
        { id: 'test', label: 'Test', operator: 'eq', value: 'value' },
      ]

      act(() => {
        result.current.setAppliedFilters(newFilters)
      })

      expect(result.current.appliedFilters).toEqual(newFilters)
    })
  })
})
