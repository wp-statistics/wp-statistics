import { describe, it, expect } from 'vitest'
import {
  createSearchValidator,
  searchValidators,
  type UrlFilter,
  type BaseSearchParams,
} from './route-validation'

describe('route-validation', () => {
  describe('createSearchValidator', () => {
    describe('filter parsing', () => {
      it('should parse valid filters from array', () => {
        const validator = createSearchValidator()
        const filters: UrlFilter[] = [
          { field: 'country', operator: 'eq', value: 'US' },
          { field: 'browser', operator: 'contains', value: 'Chrome' },
        ]

        const result = validator({ filters })

        expect(result.filters).toEqual(filters)
      })

      it('should parse valid filters from JSON string', () => {
        const validator = createSearchValidator()
        const filters: UrlFilter[] = [
          { field: 'country', operator: 'eq', value: 'US' },
        ]

        const result = validator({ filters: JSON.stringify(filters) })

        expect(result.filters).toEqual(filters)
      })

      it('should handle WordPress query param interference', () => {
        const validator = createSearchValidator()
        const filters: UrlFilter[] = [
          { field: 'country', operator: 'eq', value: 'US' },
        ]
        // Simulates WordPress appending ?page=wp-statistics to the JSON
        const malformedString = JSON.stringify(filters) + '?page=wp-statistics'

        const result = validator({ filters: malformedString })

        expect(result.filters).toEqual(filters)
      })

      it('should handle filters with displayValue', () => {
        const validator = createSearchValidator()
        const filters: UrlFilter[] = [
          { field: 'country', operator: 'eq', value: '5', displayValue: 'Iran' },
        ]

        const result = validator({ filters })

        expect(result.filters).toEqual(filters)
      })

      it('should handle filters with array values', () => {
        const validator = createSearchValidator()
        const filters: UrlFilter[] = [
          { field: 'browser', operator: 'in', value: ['Chrome', 'Firefox'] },
        ]

        const result = validator({ filters })

        expect(result.filters).toEqual(filters)
      })

      it('should filter out invalid filter objects', () => {
        const validator = createSearchValidator()
        const mixedFilters = [
          { field: 'country', operator: 'eq', value: 'US' },
          { field: 'browser' }, // missing operator and value
          { operator: 'eq', value: 'test' }, // missing field
          null,
          'invalid',
          { field: 'city', operator: 'eq', value: 'NYC' },
        ]

        const result = validator({ filters: mixedFilters })

        expect(result.filters).toHaveLength(2)
        expect(result.filters?.[0].field).toBe('country')
        expect(result.filters?.[1].field).toBe('city')
      })

      it('should return undefined for empty filters array', () => {
        const validator = createSearchValidator()

        const result = validator({ filters: [] })

        expect(result.filters).toBeUndefined()
      })

      it('should return undefined for invalid JSON string', () => {
        const validator = createSearchValidator()

        const result = validator({ filters: 'not-valid-json' })

        expect(result.filters).toBeUndefined()
      })

      it('should return undefined when filters is not provided', () => {
        const validator = createSearchValidator()

        const result = validator({})

        expect(result.filters).toBeUndefined()
      })
    })

    describe('page parsing', () => {
      it('should parse page from number', () => {
        const validator = createSearchValidator()

        const result = validator({ page: 5 })

        expect(result.page).toBe(5)
      })

      it('should parse page from string', () => {
        const validator = createSearchValidator()

        const result = validator({ page: '10' })

        expect(result.page).toBe(10)
      })

      it('should ignore page 0 or negative', () => {
        const validator = createSearchValidator()

        expect(validator({ page: 0 }).page).toBeUndefined()
        expect(validator({ page: -1 }).page).toBeUndefined()
        expect(validator({ page: '-5' }).page).toBeUndefined()
      })

      it('should ignore invalid page string', () => {
        const validator = createSearchValidator()

        expect(validator({ page: 'abc' }).page).toBeUndefined()
        expect(validator({ page: '' }).page).toBeUndefined()
      })

      it('should not include page when includePage is false', () => {
        const validator = createSearchValidator({ includePage: false })

        const result = validator({ page: 5 })

        expect(result.page).toBeUndefined()
      })
    })

    describe('combined parsing', () => {
      it('should parse both filters and page', () => {
        const validator = createSearchValidator()
        const filters: UrlFilter[] = [
          { field: 'country', operator: 'eq', value: 'US' },
        ]

        const result = validator({ filters, page: 3 })

        expect(result.filters).toEqual(filters)
        expect(result.page).toBe(3)
      })

      it('should handle empty search object', () => {
        const validator = createSearchValidator()

        const result = validator({})

        expect(result).toEqual({})
      })
    })
  })

  describe('searchValidators', () => {
    it('withPage should include page parsing', () => {
      const result = searchValidators.withPage({ page: 5 })

      expect(result.page).toBe(5)
    })

    it('filtersOnly should not include page parsing', () => {
      const result = searchValidators.filtersOnly({ page: 5 })

      expect(result.page).toBeUndefined()
    })
  })

  describe('type safety', () => {
    it('should work with custom search params type', () => {
      interface CustomSearchParams extends BaseSearchParams {
        filters?: UrlFilter[]
        page?: number
      }

      const validator = createSearchValidator<CustomSearchParams>()
      const result = validator({ page: 1 })

      // TypeScript should infer CustomSearchParams
      const _page: number | undefined = result.page
      expect(_page).toBe(1)
    })
  })
})
