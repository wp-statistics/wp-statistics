import { describe, expect, it } from 'vitest'

import {
  queryKeys,
  createDateParams,
  createListParams,
  type DateParams,
  type ListQueryParams,
} from '@lib/query-keys'

describe('query-keys', () => {
  describe('queryKeys structure', () => {
    it('should have a root "all" key', () => {
      expect(queryKeys.all).toEqual(['wp-statistics'])
    })

    it('should have pageInsights namespace', () => {
      expect(queryKeys.pageInsights.all()).toEqual(['wp-statistics', 'page-insights'])
    })

    it('should have visitors namespace', () => {
      expect(queryKeys.visitors.all()).toEqual(['wp-statistics', 'visitors'])
    })

    it('should have referrals namespace', () => {
      expect(queryKeys.referrals.all()).toEqual(['wp-statistics', 'referrals'])
    })

    it('should have geographic namespace', () => {
      expect(queryKeys.geographic.all()).toEqual(['wp-statistics', 'geographic'])
    })

    it('should have content namespace', () => {
      expect(queryKeys.content.all()).toEqual(['wp-statistics', 'content'])
    })

    it('should have devices namespace', () => {
      expect(queryKeys.devices.all()).toEqual(['wp-statistics', 'devices'])
    })

    it('should have network namespace', () => {
      expect(queryKeys.network.all()).toEqual(['wp-statistics', 'network'])
    })

    it('should have search namespace', () => {
      expect(queryKeys.search.all()).toEqual(['wp-statistics', 'search'])
    })

    it('should have filters namespace', () => {
      expect(queryKeys.filters.all()).toEqual(['wp-statistics', 'filters'])
    })
  })

  describe('pageInsights keys', () => {
    const dateParams: DateParams = {
      dateFrom: '2024-01-01',
      dateTo: '2024-01-31',
    }

    it('should generate overview key', () => {
      const key = queryKeys.pageInsights.overview({ ...dateParams, filters: {} })
      expect(key).toEqual([
        'wp-statistics',
        'page-insights',
        'overview',
        { ...dateParams, filters: {} },
      ])
    })

    it('should generate topPages key', () => {
      const params: ListQueryParams = {
        ...dateParams,
        page: 1,
        perPage: 10,
        orderBy: 'visitors',
        order: 'desc',
      }
      const key = queryKeys.pageInsights.topPages(params)
      expect(key).toEqual(['wp-statistics', 'page-insights', 'top-pages', params])
    })

    it('should generate notFound key', () => {
      const key = queryKeys.pageInsights.notFound({ ...dateParams, page: 1, perPage: 10 })
      expect(key).toEqual([
        'wp-statistics',
        'page-insights',
        '404',
        { ...dateParams, page: 1, perPage: 10 },
      ])
    })
  })

  describe('visitors keys', () => {
    it('should generate overview key', () => {
      const key = queryKeys.visitors.overview({
        dateFrom: '2024-01-01',
        dateTo: '2024-01-31',
      })
      expect(key[0]).toBe('wp-statistics')
      expect(key[1]).toBe('visitors')
      expect(key[2]).toBe('overview')
    })

    it('should generate online key', () => {
      const key = queryKeys.visitors.online({
        page: 1,
        perPage: 10,
        orderBy: 'time',
        order: 'desc',
      })
      expect(key).toEqual([
        'wp-statistics',
        'visitors',
        'online',
        { page: 1, perPage: 10, orderBy: 'time', order: 'desc' },
      ])
    })

    it('should generate trafficTrends key', () => {
      const key = queryKeys.visitors.trafficTrends()
      expect(key).toEqual(['wp-statistics', 'visitors', 'traffic-trends'])
    })
  })

  describe('network keys', () => {
    it('should generate stats key with date params', () => {
      const key = queryKeys.network.stats({
        dateFrom: '2024-01-01',
        dateTo: '2024-01-31',
      })
      expect(key).toEqual([
        'wp-statistics',
        'network',
        'stats',
        { dateFrom: '2024-01-01', dateTo: '2024-01-31' },
      ])
    })

    it('should include compare dates when provided', () => {
      const key = queryKeys.network.stats({
        dateFrom: '2024-01-01',
        dateTo: '2024-01-31',
        compareDateFrom: '2023-12-01',
        compareDateTo: '2023-12-31',
      })
      expect(key[3]).toEqual({
        dateFrom: '2024-01-01',
        dateTo: '2024-01-31',
        compareDateFrom: '2023-12-01',
        compareDateTo: '2023-12-31',
      })
    })
  })

  describe('filters keys', () => {
    it('should generate options key', () => {
      const key = queryKeys.filters.options({ filter: 'country', search: 'US' })
      expect(key).toEqual([
        'wp-statistics',
        'filters',
        'options',
        { filter: 'country', search: 'US' },
      ])
    })
  })

  describe('createDateParams', () => {
    it('should create basic date params', () => {
      const params = createDateParams('2024-01-01', '2024-01-31')
      expect(params).toEqual({
        dateFrom: '2024-01-01',
        dateTo: '2024-01-31',
      })
    })

    it('should include compare dates when provided', () => {
      const params = createDateParams(
        '2024-01-01',
        '2024-01-31',
        '2023-12-01',
        '2023-12-31'
      )
      expect(params).toEqual({
        dateFrom: '2024-01-01',
        dateTo: '2024-01-31',
        compareDateFrom: '2023-12-01',
        compareDateTo: '2023-12-31',
      })
    })

    it('should include comparison mode when provided', () => {
      const params = createDateParams(
        '2024-01-01',
        '2024-01-31',
        '2023-12-01',
        '2023-12-31',
        'previous_period'
      )
      expect(params).toEqual({
        dateFrom: '2024-01-01',
        dateTo: '2024-01-31',
        compareDateFrom: '2023-12-01',
        compareDateTo: '2023-12-31',
        comparisonMode: 'previous_period',
      })
    })

    it('should not include undefined compare dates', () => {
      const params = createDateParams('2024-01-01', '2024-01-31', undefined, undefined)
      expect(params).toEqual({
        dateFrom: '2024-01-01',
        dateTo: '2024-01-31',
      })
      expect('compareDateFrom' in params).toBe(false)
      expect('compareDateTo' in params).toBe(false)
    })
  })

  describe('createListParams', () => {
    it('should create basic list params', () => {
      const params = createListParams(
        '2024-01-01',
        '2024-01-31',
        1,
        10,
        'visitors',
        'desc'
      )
      expect(params).toEqual({
        dateFrom: '2024-01-01',
        dateTo: '2024-01-31',
        page: 1,
        perPage: 10,
        orderBy: 'visitors',
        order: 'desc',
      })
    })

    it('should include optional params when provided', () => {
      const params = createListParams(
        '2024-01-01',
        '2024-01-31',
        1,
        10,
        'visitors',
        'desc',
        {
          compareDateFrom: '2023-12-01',
          compareDateTo: '2023-12-31',
          context: 'dashboard',
          columns: ['visitors', 'views'],
        }
      )
      expect(params).toEqual({
        dateFrom: '2024-01-01',
        dateTo: '2024-01-31',
        page: 1,
        perPage: 10,
        orderBy: 'visitors',
        order: 'desc',
        compareDateFrom: '2023-12-01',
        compareDateTo: '2023-12-31',
        context: 'dashboard',
        columns: ['visitors', 'views'],
      })
    })

    it('should include filters when non-empty', () => {
      const filters = { country: { eq: 'US' } }
      const params = createListParams(
        '2024-01-01',
        '2024-01-31',
        1,
        10,
        'visitors',
        'desc',
        { filters }
      )
      expect(params.filters).toEqual(filters)
    })

    it('should not include empty filters', () => {
      const params = createListParams(
        '2024-01-01',
        '2024-01-31',
        1,
        10,
        'visitors',
        'desc',
        { filters: {} }
      )
      expect('filters' in params).toBe(false)
    })

    it('should not include empty columns array', () => {
      const params = createListParams(
        '2024-01-01',
        '2024-01-31',
        1,
        10,
        'visitors',
        'desc',
        { columns: [] }
      )
      expect('columns' in params).toBe(false)
    })
  })

  describe('key uniqueness', () => {
    it('should generate different keys for different params', () => {
      const key1 = queryKeys.pageInsights.topPages({
        dateFrom: '2024-01-01',
        dateTo: '2024-01-31',
        page: 1,
        perPage: 10,
        orderBy: 'visitors',
        order: 'desc',
      })
      const key2 = queryKeys.pageInsights.topPages({
        dateFrom: '2024-01-01',
        dateTo: '2024-01-31',
        page: 2, // Different page
        perPage: 10,
        orderBy: 'visitors',
        order: 'desc',
      })

      expect(JSON.stringify(key1)).not.toBe(JSON.stringify(key2))
    })

    it('should generate same keys for same params', () => {
      const params: ListQueryParams = {
        dateFrom: '2024-01-01',
        dateTo: '2024-01-31',
        page: 1,
        perPage: 10,
        orderBy: 'visitors',
        order: 'desc',
      }
      const key1 = queryKeys.pageInsights.topPages(params)
      const key2 = queryKeys.pageInsights.topPages(params)

      expect(JSON.stringify(key1)).toBe(JSON.stringify(key2))
    })
  })

  describe('hierarchical invalidation', () => {
    it('should support invalidating all page insights queries', () => {
      const allKey = queryKeys.pageInsights.all()
      const topPagesKey = queryKeys.pageInsights.topPages({
        dateFrom: '2024-01-01',
        dateTo: '2024-01-31',
        page: 1,
        perPage: 10,
        orderBy: 'visitors',
        order: 'desc',
      })

      // The topPagesKey should start with allKey
      expect(topPagesKey.slice(0, allKey.length)).toEqual(allKey)
    })

    it('should support invalidating all queries', () => {
      const rootKey = queryKeys.all
      const networkKey = queryKeys.network.stats({
        dateFrom: '2024-01-01',
        dateTo: '2024-01-31',
      })

      // The networkKey should start with rootKey
      expect(networkKey.slice(0, rootKey.length)).toEqual(rootKey)
    })
  })
})
