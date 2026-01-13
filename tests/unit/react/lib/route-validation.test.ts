import { describe, it, expect } from 'vitest'
import { createSearchValidator, searchValidators, type BaseSearchParams } from '@lib/route-validation'

describe('route-validation', () => {
  describe('createSearchValidator', () => {
    describe('bracket notation filter pass-through', () => {
      it('should pass through single filter with bracket notation', () => {
        const validator = createSearchValidator()

        const result = validator({
          'filter[country]': 'eq:US',
        })

        // Bracket notation params are passed through as strings
        expect(result['filter[country]']).toBe('eq:US')
        // Should NOT have 'filters' array (prevents TanStack Router JSON serialization)
        expect(result).not.toHaveProperty('filters')
      })

      it('should pass through multiple filters with bracket notation', () => {
        const validator = createSearchValidator()

        const result = validator({
          'filter[country]': 'in:JP,CN',
          'filter[browser]': 'eq:Chrome',
        })

        expect(result['filter[country]']).toBe('in:JP,CN')
        expect(result['filter[browser]']).toBe('eq:Chrome')
        expect(result).not.toHaveProperty('filters')
      })

      it('should pass through multi-value filters with bracket notation', () => {
        const validator = createSearchValidator()

        const result = validator({
          'filter[country]': 'in:US,JP,CN',
        })

        expect(result['filter[country]']).toBe('in:US,JP,CN')
      })

      it('should pass through bracket notation with special characters in value', () => {
        const validator = createSearchValidator()

        const result = validator({
          'filter[referrer]': 'eq:https://example.com/path?query=1',
        })

        expect(result['filter[referrer]']).toBe('eq:https://example.com/path?query=1')
      })

      it('should ignore invalid bracket notation values', () => {
        const validator = createSearchValidator()

        const result = validator({
          'filter[country]': 'invalid', // missing colon - but still passed through
          'filter[browser]': 'eq:Chrome',
        })

        // Invalid values are still passed through (parsing happens in context)
        expect(result['filter[country]']).toBe('invalid')
        expect(result['filter[browser]']).toBe('eq:Chrome')
      })

      it('should prefer bracket notation over legacy JSON', () => {
        const validator = createSearchValidator()

        const result = validator({
          'filter[country]': 'eq:JP',
          filters: JSON.stringify([{ field: 'country', operator: 'eq', value: 'US' }]),
        })

        // Bracket notation should take precedence, legacy 'filters' is ignored
        expect(result['filter[country]']).toBe('eq:JP')
        expect(result).not.toHaveProperty('filters')
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

        const result = validator({
          'filter[country]': 'eq:US',
          page: 3,
        })

        expect(result['filter[country]']).toBe('eq:US')
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
