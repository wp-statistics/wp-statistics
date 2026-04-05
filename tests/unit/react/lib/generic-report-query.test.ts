import { describe, it, expect, vi, beforeEach } from 'vitest'

// vi.hoisted ensures mockPost is available when vi.mock factories run (they're hoisted above imports)
const { mockPost } = vi.hoisted(() => ({
  mockPost: vi.fn(),
}))

vi.mock('@/lib/client-request', () => ({
  clientRequest: { post: mockPost },
}))

// Mock WordPress singleton
vi.mock('@/lib/wordpress', () => ({
  WordPress: {
    getInstance: () => ({
      getAnalyticsAction: () => 'wp_statistics_test_analytics',
    }),
  },
}))

// Mock transformFiltersToApi — pass through a simplified version
vi.mock('@/lib/api-filter-transform', () => ({
  transformFiltersToApi: (filters: Array<{ id: string; operator: string; value: unknown }>) => {
    const result: Record<string, Record<string, unknown>> = {}
    for (const f of filters) {
      const key = f.id.split('-')[0]
      result[key] = { [f.operator]: f.value }
    }
    return result
  },
}))

import { createGenericQueryOptions } from '@lib/generic-report-query'

// Shared test fixtures
const baseParams = {
  page: 1,
  per_page: 25,
  order_by: 'views',
  order: 'desc' as const,
  date_from: '2025-01-01',
  date_to: '2025-01-31',
}

const compareParams = {
  ...baseParams,
  previous_date_from: '2024-12-01',
  previous_date_to: '2024-12-31',
}

describe('createGenericQueryOptions', () => {
  beforeEach(() => {
    mockPost.mockReset()
    mockPost.mockResolvedValue({ data: { success: true, data: { rows: [], total: 0 }, meta: undefined } })
  })

  // ---------------------------------------------------------------
  // Simple format tests
  // ---------------------------------------------------------------
  describe('simple format', () => {
    const simpleDataSource = {
      sources: ['views', 'visitors'],
      group_by: ['resource'],
    }

    it('returns a function', () => {
      const factory = createGenericQueryOptions('test-report', simpleDataSource)
      expect(typeof factory).toBe('function')
    })

    it('produces correct queryKey with all params', () => {
      const factory = createGenericQueryOptions('test-report', simpleDataSource)
      const opts = factory(baseParams)

      expect(opts.queryKey).toEqual([
        'test-report',
        1,
        25,
        'views',
        'desc',
        '2025-01-01',
        '2025-01-31',
        undefined,
        undefined,
        ['views', 'visitors'],
        ['resource'],
        null, // no filters
        null, // no queryOverrides
      ])
    })

    it('calls clientRequest.post with correct body', async () => {
      const factory = createGenericQueryOptions('test-report', simpleDataSource)
      const opts = factory(baseParams)

      await opts.queryFn!({ signal: new AbortController().signal, meta: undefined } as never)

      expect(mockPost).toHaveBeenCalledWith(
        '',
        {
          sources: ['views', 'visitors'],
          group_by: ['resource'],
          date_from: '2025-01-01',
          date_to: '2025-01-31',
          compare: false,
          page: 1,
          per_page: 25,
          order_by: 'views',
          order: 'DESC',
          format: 'table',
          show_totals: false,
        },
        { params: { action: 'wp_statistics_test_analytics' } }
      )
    })

    it('includes compare and previous dates when comparison dates are provided', async () => {
      const factory = createGenericQueryOptions('test-report', simpleDataSource)
      const opts = factory(compareParams)

      await opts.queryFn!({ signal: new AbortController().signal, meta: undefined } as never)

      const body = mockPost.mock.calls[0][1]
      expect(body.compare).toBe(true)
      expect(body.previous_date_from).toBe('2024-12-01')
      expect(body.previous_date_to).toBe('2024-12-31')
    })

    it('includes transformed filters in the body when filters are provided', async () => {
      const factory = createGenericQueryOptions('test-report', simpleDataSource)
      const paramsWithFilters = {
        ...baseParams,
        filters: [{ id: 'os-filter-1', operator: 'eq', value: 'Windows' }],
      }
      const opts = factory(paramsWithFilters)

      await opts.queryFn!({ signal: new AbortController().signal, meta: undefined } as never)

      const body = mockPost.mock.calls[0][1]
      expect(body.filters).toEqual({ os: { eq: 'Windows' } })
    })

    it('does not include filters key when no filters', async () => {
      const factory = createGenericQueryOptions('test-report', simpleDataSource)
      const opts = factory(baseParams)

      await opts.queryFn!({ signal: new AbortController().signal, meta: undefined } as never)

      const body = mockPost.mock.calls[0][1]
      expect(body).not.toHaveProperty('filters')
    })

    it('maps order_by through columnMapping', async () => {
      const dataSource = {
        sources: ['views'],
        group_by: ['resource'],
        columnMapping: { visitorInfo: 'visitor_id' },
      }
      const factory = createGenericQueryOptions('test-report', dataSource)
      const opts = factory({ ...baseParams, order_by: 'visitorInfo' })

      await opts.queryFn!({ signal: new AbortController().signal, meta: undefined } as never)

      const body = mockPost.mock.calls[0][1]
      expect(body.order_by).toBe('visitor_id')
    })

    it('includes context from reportConfig', async () => {
      const factory = createGenericQueryOptions('test-report', simpleDataSource, { context: 'entry-pages' })
      const opts = factory(baseParams)

      await opts.queryFn!({ signal: new AbortController().signal, meta: undefined } as never)

      const body = mockPost.mock.calls[0][1]
      expect(body.context).toBe('entry-pages')
    })

    it('merges apiFilters with UI filters', async () => {
      const factory = createGenericQueryOptions('test-report', simpleDataSource)
      const opts = factory({
        ...baseParams,
        filters: [{ id: 'os-filter-1', operator: 'eq', value: 'Windows' }],
        apiFilters: { post_type: { eq: 'page' } },
      })

      await opts.queryFn!({ signal: new AbortController().signal, meta: undefined } as never)

      const body = mockPost.mock.calls[0][1]
      expect(body.filters).toEqual({
        os: { eq: 'Windows' },
        post_type: { eq: 'page' },
      })
    })
  })

  // ---------------------------------------------------------------
  // Batch format tests
  // ---------------------------------------------------------------
  describe('batch format', () => {
    const batchDataSource = {
      queryId: 'main_table',
      queries: [
        { id: 'main_table', sources: ['views'], group_by: ['resource'], format: 'table' },
        { id: 'chart_data', sources: ['views'], group_by: ['date'], format: 'chart' },
        { id: 'totals', sources: ['views'], group_by: ['resource'], format: 'flat' },
      ] as Array<{ id: string; sources: string[]; group_by: string[]; format: string; compare?: boolean; [k: string]: unknown }>,
    }

    const makeBatchResponse = (mainResult?: unknown, extras?: Record<string, unknown>) => ({
      data: {
        success: true,
        items: {
          main_table: mainResult ?? {
            success: true,
            data: { rows: [{ id: 1 }], total: 1 },
            meta: { page: 1, per_page: 25, total_rows: 1, total_pages: 1 },
          },
          chart_data: {
            success: true,
            data: { rows: [{ date: '2025-01-01', value: 10 }] },
          },
          totals: {
            success: true,
            data: { rows: [{ total_views: 100 }] },
          },
          ...extras,
        },
      },
    })

    it('uses batch format when queries exist', () => {
      const factory = createGenericQueryOptions('test-report', batchDataSource)
      const opts = factory(baseParams)

      // Batch queryKey includes mainQueryId and timeframe
      expect(opts.queryKey).toContain('main_table')
    })

    it('injects pagination, sorting, order into main query only', async () => {
      mockPost.mockResolvedValueOnce(makeBatchResponse())
      const factory = createGenericQueryOptions('test-report', batchDataSource)
      const opts = factory(baseParams)

      await opts.queryFn!({ signal: new AbortController().signal, meta: undefined } as never)

      const body = mockPost.mock.calls[0][1]
      const mainQuery = body.queries.find((q: { id: string }) => q.id === 'main_table')
      const chartQuery = body.queries.find((q: { id: string }) => q.id === 'chart_data')
      const totalsQuery = body.queries.find((q: { id: string }) => q.id === 'totals')

      expect(mainQuery.page).toBe(1)
      expect(mainQuery.per_page).toBe(25)
      expect(mainQuery.order_by).toBe('views')
      expect(mainQuery.order).toBe('DESC')

      expect(chartQuery).not.toHaveProperty('page')
      expect(chartQuery).not.toHaveProperty('per_page')
      expect(totalsQuery).not.toHaveProperty('page')
      expect(totalsQuery).not.toHaveProperty('order_by')
    })

    it('overrides chart query group_by based on timeframe', async () => {
      mockPost.mockResolvedValueOnce(makeBatchResponse())
      const factory = createGenericQueryOptions('test-report', batchDataSource)
      const opts = factory({ ...baseParams, timeframe: 'weekly' })

      await opts.queryFn!({ signal: new AbortController().signal, meta: undefined } as never)

      const body = mockPost.mock.calls[0][1]
      const chartQuery = body.queries.find((q: { id: string }) => q.id === 'chart_data')
      expect(chartQuery.group_by).toEqual(['week'])
    })

    it('injects comparison dates into chart queries when hasCompare', async () => {
      mockPost.mockResolvedValueOnce(makeBatchResponse())
      const factory = createGenericQueryOptions('test-report', batchDataSource)
      const opts = factory({ ...compareParams, timeframe: 'daily' })

      await opts.queryFn!({ signal: new AbortController().signal, meta: undefined } as never)

      const body = mockPost.mock.calls[0][1]
      const chartQuery = body.queries.find((q: { id: string }) => q.id === 'chart_data')

      expect(chartQuery.compare).toBe(true)
      expect(chartQuery.previous_date_from).toBe('2024-12-01')
      expect(chartQuery.previous_date_to).toBe('2024-12-31')
    })

    it('maps daily timeframe to date group_by', async () => {
      mockPost.mockResolvedValueOnce(makeBatchResponse())
      const factory = createGenericQueryOptions('test-report', batchDataSource)
      const opts = factory({ ...baseParams, timeframe: 'daily' })

      await opts.queryFn!({ signal: new AbortController().signal, meta: undefined } as never)

      const body = mockPost.mock.calls[0][1]
      const chartQuery = body.queries.find((q: { id: string }) => q.id === 'chart_data')
      expect(chartQuery.group_by).toEqual(['date'])
    })

    it('maps monthly timeframe to month group_by', async () => {
      mockPost.mockResolvedValueOnce(makeBatchResponse())
      const factory = createGenericQueryOptions('test-report', batchDataSource)
      const opts = factory({ ...baseParams, timeframe: 'monthly' })

      await opts.queryFn!({ signal: new AbortController().signal, meta: undefined } as never)

      const body = mockPost.mock.calls[0][1]
      const chartQuery = body.queries.find((q: { id: string }) => q.id === 'chart_data')
      expect(chartQuery.group_by).toEqual(['month'])
    })

    it('injects filters into every sub-query', async () => {
      mockPost.mockResolvedValueOnce(makeBatchResponse())
      const factory = createGenericQueryOptions('test-report', batchDataSource)
      const opts = factory({
        ...baseParams,
        filters: [{ id: 'os-filter-1', operator: 'eq', value: 'Windows' }],
      })

      await opts.queryFn!({ signal: new AbortController().signal, meta: undefined } as never)

      const body = mockPost.mock.calls[0][1]
      for (const query of body.queries) {
        expect(query.filters).toEqual({ os: { eq: 'Windows' } })
      }
    })

    it('does not include filters at the top-level batch body', async () => {
      mockPost.mockResolvedValueOnce(makeBatchResponse())
      const factory = createGenericQueryOptions('test-report', batchDataSource)
      const opts = factory({
        ...baseParams,
        filters: [{ id: 'os-filter-1', operator: 'eq', value: 'Windows' }],
      })

      await opts.queryFn!({ signal: new AbortController().signal, meta: undefined } as never)

      const body = mockPost.mock.calls[0][1]
      expect(body).not.toHaveProperty('filters')
    })

    it('normalizes response: extracts main query into standard shape', async () => {
      mockPost.mockResolvedValueOnce(makeBatchResponse())
      const factory = createGenericQueryOptions('test-report', batchDataSource)
      const opts = factory(baseParams)

      const result = await opts.queryFn!({ signal: new AbortController().signal, meta: undefined } as never)

      expect(result.data.success).toBe(true)
      expect(result.data.data.rows).toEqual([{ id: 1 }])
      expect(result.data.meta).toEqual({ page: 1, per_page: 25, total_rows: 1, total_pages: 1 })
    })

    it('preserves non-main items in _batchItems', async () => {
      mockPost.mockResolvedValueOnce(makeBatchResponse())
      const factory = createGenericQueryOptions('test-report', batchDataSource)
      const opts = factory(baseParams)

      const result = await opts.queryFn!({ signal: new AbortController().signal, meta: undefined } as never)

      expect(result.data._batchItems).toHaveProperty('chart_data')
      expect(result.data._batchItems).toHaveProperty('totals')
      expect(result.data._batchItems).not.toHaveProperty('main_table')
    })

    it('returns empty rows with _batchItems when main query result is missing', async () => {
      // Remove main_table from the response items
      const response = {
        data: {
          success: true,
          items: {
            chart_data: { success: true, data: { rows: [] } },
            totals: { success: true, data: { rows: [] } },
          },
        },
      }
      mockPost.mockResolvedValueOnce(response)
      const factory = createGenericQueryOptions('test-report', batchDataSource)
      const opts = factory(baseParams)

      const result = await opts.queryFn!({ signal: new AbortController().signal, meta: undefined } as never)

      expect(result.data.success).toBe(false)
      expect(result.data.data.rows).toEqual([])
      expect(result.data.data.total).toBe(0)
      expect(result.data.meta).toBeUndefined()
      expect(result.data._batchItems).toHaveProperty('chart_data')
      expect(result.data._batchItems).toHaveProperty('totals')
    })

    it('includes timeframe in queryKey for batch queries', () => {
      const factory = createGenericQueryOptions('test-report', batchDataSource)
      const opts = factory({ ...baseParams, timeframe: 'weekly' })

      expect(opts.queryKey).toContain('weekly')
    })

    it('respects explicit q.compare override on a query', async () => {
      const dataSource = {
        queryId: 'main_table',
        queries: [
          { id: 'main_table', sources: ['views'], group_by: ['resource'], format: 'table', compare: false },
          { id: 'chart_data', sources: ['views'], group_by: ['date'], format: 'chart', compare: false },
        ] as Array<{ id: string; sources: string[]; group_by: string[]; format: string; compare: boolean; [k: string]: unknown }>,
      }
      mockPost.mockResolvedValueOnce({
        data: {
          success: true,
          items: {
            main_table: { success: true, data: { rows: [], total: 0 } },
            chart_data: { success: true, data: { rows: [] } },
          },
        },
      })
      const factory = createGenericQueryOptions('test-report', dataSource)
      // Even though comparison dates are provided, compare should be false because q.compare is explicitly false
      const opts = factory(compareParams)

      await opts.queryFn!({ signal: new AbortController().signal, meta: undefined } as never)

      const body = mockPost.mock.calls[0][1]
      const mainQuery = body.queries.find((q: { id: string }) => q.id === 'main_table')
      const chartQuery = body.queries.find((q: { id: string }) => q.id === 'chart_data')

      expect(mainQuery.compare).toBe(false)
      expect(chartQuery.compare).toBe(false)
    })

    it('injects context from reportConfig into main query', async () => {
      mockPost.mockResolvedValueOnce(makeBatchResponse())
      const factory = createGenericQueryOptions('test-report', batchDataSource, { context: 'entry-pages' })
      const opts = factory(baseParams)

      await opts.queryFn!({ signal: new AbortController().signal, meta: undefined } as never)

      const body = mockPost.mock.calls[0][1]
      const mainQuery = body.queries.find((q: { id: string }) => q.id === 'main_table')
      const chartQuery = body.queries.find((q: { id: string }) => q.id === 'chart_data')

      expect(mainQuery.context).toBe('entry-pages')
      expect(chartQuery).not.toHaveProperty('context')
    })
  })

  // ---------------------------------------------------------------
  // Edge cases
  // ---------------------------------------------------------------
  describe('edge cases', () => {
    it('uses first query id as main when queryId is not set', async () => {
      const dataSource = {
        queries: [
          { id: 'first_query', sources: ['views'], group_by: ['resource'], format: 'table' },
          { id: 'second_query', sources: ['views'], group_by: ['date'], format: 'chart' },
        ] as Array<{ id: string; sources: string[]; group_by: string[]; format: string; [k: string]: unknown }>,
      }
      mockPost.mockResolvedValueOnce({
        data: {
          success: true,
          items: {
            first_query: { success: true, data: { rows: [{ id: 1 }], total: 1 }, meta: { page: 1 } },
            second_query: { success: true, data: { rows: [] } },
          },
        },
      })
      const factory = createGenericQueryOptions('test-report', dataSource)
      const opts = factory(baseParams)

      // queryKey should contain the first query's id
      expect(opts.queryKey).toContain('first_query')

      await opts.queryFn!({ signal: new AbortController().signal, meta: undefined } as never)

      const body = mockPost.mock.calls[0][1]
      const firstQuery = body.queries.find((q: { id: string }) => q.id === 'first_query')
      // First query gets pagination because it's the main query
      expect(firstQuery.page).toBe(1)
      expect(firstQuery.per_page).toBe(25)
    })

    it('merges apiFilters with transformed UI filters', async () => {
      const simpleDataSource = { sources: ['views'], group_by: ['resource'] }
      const factory = createGenericQueryOptions('test-report', simpleDataSource)
      const opts = factory({
        ...baseParams,
        filters: [{ id: 'browser-filter-1', operator: 'eq', value: 'Chrome' }],
        apiFilters: { post_type: { eq: 'post' }, author: { eq: '5' } },
      })

      await opts.queryFn!({ signal: new AbortController().signal, meta: undefined } as never)

      const body = mockPost.mock.calls[0][1]
      expect(body.filters).toEqual({
        browser: { eq: 'Chrome' },
        post_type: { eq: 'post' },
        author: { eq: '5' },
      })
    })

    it('defaults to daily group_by when timeframe is undefined', async () => {
      const dataSource = {
        queryId: 'table',
        queries: [
          { id: 'table', sources: ['views'], group_by: ['resource'], format: 'table' },
          { id: 'chart', sources: ['views'], group_by: ['date'], format: 'chart' },
        ] as Array<{ id: string; sources: string[]; group_by: string[]; format: string; [k: string]: unknown }>,
      }
      mockPost.mockResolvedValueOnce({
        data: {
          success: true,
          items: {
            table: { success: true, data: { rows: [], total: 0 } },
            chart: { success: true, data: { rows: [] } },
          },
        },
      })
      const factory = createGenericQueryOptions('test-report', dataSource)
      // timeframe is not provided (undefined)
      const opts = factory(baseParams)

      await opts.queryFn!({ signal: new AbortController().signal, meta: undefined } as never)

      const body = mockPost.mock.calls[0][1]
      const chartQuery = body.queries.find((q: { id: string }) => q.id === 'chart')
      expect(chartQuery.group_by).toEqual(['date'])
    })

    it('includes filters in queryKey when provided', () => {
      const simpleDataSource = { sources: ['views'], group_by: ['resource'] }
      const factory = createGenericQueryOptions('test-report', simpleDataSource)
      const opts = factory({
        ...baseParams,
        filters: [{ id: 'os-filter-1', operator: 'eq', value: 'Windows' }],
      })

      // The filters object should be in the query key (second-to-last, before queryOverrides)
      const filtersInKey = opts.queryKey[opts.queryKey.length - 2]
      expect(filtersInKey).toEqual({ os: { eq: 'Windows' } })
    })

    it('sets queryKey filters to null when no filters provided', () => {
      const simpleDataSource = { sources: ['views'], group_by: ['resource'] }
      const factory = createGenericQueryOptions('test-report', simpleDataSource)
      const opts = factory(baseParams)

      const filtersInKey = opts.queryKey[opts.queryKey.length - 1]
      expect(filtersInKey).toBeNull()
    })

    it('uppercases order in the request body', async () => {
      const simpleDataSource = { sources: ['views'], group_by: ['resource'] }
      const factory = createGenericQueryOptions('test-report', simpleDataSource)
      const opts = factory({ ...baseParams, order: 'asc' })

      await opts.queryFn!({ signal: new AbortController().signal, meta: undefined } as never)

      const body = mockPost.mock.calls[0][1]
      expect(body.order).toBe('ASC')
    })

    it('passes unmapped order_by when no columnMapping match', async () => {
      const dataSource = {
        sources: ['views'],
        group_by: ['resource'],
        columnMapping: { visitorInfo: 'visitor_id' },
      }
      const factory = createGenericQueryOptions('test-report', dataSource)
      const opts = factory({ ...baseParams, order_by: 'unmapped_column' })

      await opts.queryFn!({ signal: new AbortController().signal, meta: undefined } as never)

      const body = mockPost.mock.calls[0][1]
      expect(body.order_by).toBe('unmapped_column')
    })
  })
})
