import type { Table } from '@tanstack/react-table'
import { keepPreviousData, useQuery } from '@tanstack/react-query'
import { createLazyFileRoute } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'
import { useCallback, useMemo, useRef, useState } from 'react'

import { DataTable } from '@/components/custom/data-table'
import { ErrorMessage } from '@/components/custom/error-message'
import { TableOptionsDrawer, useTableOptions, type PageFilterConfig } from '@/components/custom/options-drawer'
import { ReportPageHeader } from '@/components/custom/report-page-header'
import {
  createTopCategoriesColumns,
  type TopCategory,
  TOP_CATEGORIES_COLUMN_CONFIG,
  TOP_CATEGORIES_COMPARABLE_COLUMNS,
  TOP_CATEGORIES_CONTEXT,
  TOP_CATEGORIES_DEFAULT_API_COLUMNS,
  TOP_CATEGORIES_DEFAULT_COMPARISON_COLUMNS,
  TOP_CATEGORIES_DEFAULT_HIDDEN_COLUMNS,
  transformTopCategoryData,
} from '@/components/data-table-columns/top-categories-columns'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { NoticeContainer } from '@/components/ui/notice-container'
import { PanelSkeleton, TableSkeleton } from '@/components/ui/skeletons'
import { useComparisonDateLabel } from '@/hooks/use-comparison-date-label'
import { useDataTablePreferences } from '@/hooks/use-data-table-preferences'
import { useGlobalFilters } from '@/hooks/use-global-filters'
import { useUrlSortSync } from '@/hooks/use-url-sort-sync'
import { getApiSortField } from '@/lib/column-utils'
import { extractMeta, extractRows } from '@/lib/response-helpers'
import { WordPress } from '@/lib/wordpress'
import { getTopCategoriesQueryOptions } from '@/services/content-analytics/get-top-categories'

const PER_PAGE = 20

export const Route = createLazyFileRoute('/(content-analytics)/top-categories')({
  component: RouteComponent,
})

function RouteComponent() {
  const {
    filters: appliedFilters,
    page,
    setPage,
    handlePageChange,
    isInitialized,
    apiDateParams,
  } = useGlobalFilters()

  const { sorting, handleSortingChange, orderBy, order } = useUrlSortSync({
    defaultSort: [{ id: 'views', desc: true }],
    onPageReset: () => setPage(1),
  })

  // Map column ID to API field name for sorting
  const apiOrderBy = getApiSortField(orderBy, TOP_CATEGORIES_COLUMN_CONFIG)

  // Table ref for Options drawer column management
  const tableRef = useRef<Table<TopCategory> | null>(null)

  // Get comparison date label for tooltip display
  const { label: comparisonLabel } = useComparisonDateLabel()

  const wp = WordPress.getInstance()
  const isPremium = wp.getIsPremium()

  // Get available taxonomies based on premium status
  const availableTaxonomies = useMemo(() => {
    const allTaxonomies = wp.getTaxonomies()
    if (isPremium) {
      return allTaxonomies // All including custom
    }
    // Free: Only category and post_tag
    return allTaxonomies.filter((t) => t.value === 'category' || t.value === 'post_tag')
  }, [wp, isPremium])

  // Get initial taxonomy from URL filter (from Categories report link)
  const getInitialTaxonomy = () => {
    const taxonomyFilter = appliedFilters?.find((f) => f.name === 'taxonomy_type')
    if (taxonomyFilter?.value) {
      const allTaxonomies = wp.getTaxonomies()
      const validTaxonomies = isPremium
        ? allTaxonomies
        : allTaxonomies.filter((t) => t.value === 'category' || t.value === 'post_tag')
      if (validTaxonomies.some((t) => t.value === taxonomyFilter.value)) {
        return taxonomyFilter.value
      }
    }
    return 'category'
  }

  // Taxonomy state - initialized from URL filter if present
  const [selectedTaxonomy, setSelectedTaxonomy] = useState<string>(getInitialTaxonomy)

  // Handle taxonomy change
  const handleTaxonomyChange = useCallback((value: string) => {
    setSelectedTaxonomy(value)
    setPage(1) // Reset to first page when taxonomy changes
  }, [setPage])

  // Page filters for Options drawer - taxonomy selector
  const pageFilters = useMemo<PageFilterConfig[]>(() => {
    return [
      {
        id: 'taxonomy',
        label: __('Taxonomy Type', 'wp-statistics'),
        value: selectedTaxonomy,
        options: availableTaxonomies,
        onChange: handleTaxonomyChange,
      },
    ]
  }, [selectedTaxonomy, availableTaxonomies, handleTaxonomyChange])

  // Base columns for preferences hook (stable definition for column IDs)
  const baseColumns = useMemo(() => createTopCategoriesColumns({ comparisonLabel }), [comparisonLabel])

  // Fetch data from API
  const {
    data: response,
    isLoading,
    isFetching,
    isError,
    error,
  } = useQuery({
    ...getTopCategoriesQueryOptions({
      page,
      per_page: PER_PAGE,
      order_by: apiOrderBy,
      order: order as 'asc' | 'desc',
      date_from: apiDateParams.date_from,
      date_to: apiDateParams.date_to,
      previous_date_from: apiDateParams.previous_date_from,
      previous_date_to: apiDateParams.previous_date_to,
      taxonomy: selectedTaxonomy,
      filters: appliedFilters || [],
      context: TOP_CATEGORIES_CONTEXT,
      columns: TOP_CATEGORIES_DEFAULT_API_COLUMNS,
    }),
    placeholderData: keepPreviousData,
    enabled: isInitialized,
  })

  // Use the preferences hook for column management
  const {
    columnOrder,
    initialColumnVisibility,
    comparisonColumns,
    handleColumnVisibilityChange,
    handleColumnOrderChange,
    handleComparisonColumnsChange,
    handleColumnPreferencesReset,
  } = useDataTablePreferences({
    context: TOP_CATEGORIES_CONTEXT,
    columns: baseColumns,
    defaultHiddenColumns: TOP_CATEGORIES_DEFAULT_HIDDEN_COLUMNS,
    defaultApiColumns: TOP_CATEGORIES_DEFAULT_API_COLUMNS,
    columnConfig: TOP_CATEGORIES_COLUMN_CONFIG,
    sorting,
    defaultSortColumn: 'views',
    preferencesFromApi: response?.data?.meta?.preferences?.columns,
    hasApiResponse: !!response?.data,
    defaultComparisonColumns: TOP_CATEGORIES_DEFAULT_COMPARISON_COLUMNS,
    comparisonColumnsFromApi: (response?.data?.meta?.preferences as { comparison_columns?: string[] } | undefined)?.comparison_columns,
  })

  // Options drawer with column management - config is passed once and returned for drawer
  const options = useTableOptions({
    filterGroup: 'content',
    table: tableRef.current,
    hideFilters: true,
    pageFilters,
    initialColumnOrder: columnOrder,
    defaultHiddenColumns: TOP_CATEGORIES_DEFAULT_HIDDEN_COLUMNS,
    comparableColumns: TOP_CATEGORIES_COMPARABLE_COLUMNS,
    comparisonColumns,
    defaultComparisonColumns: TOP_CATEGORIES_DEFAULT_COMPARISON_COLUMNS,
    onColumnVisibilityChange: handleColumnVisibilityChange,
    onColumnOrderChange: handleColumnOrderChange,
    onComparisonColumnsChange: handleComparisonColumnsChange,
    onReset: handleColumnPreferencesReset,
  })

  // Final columns with comparison settings applied
  const columns = useMemo(
    () => createTopCategoriesColumns({ comparisonLabel, comparisonColumns }),
    [comparisonLabel, comparisonColumns]
  )

  // Transform API data to component interface
  const tableData = useMemo(() => {
    return extractRows(response).map(transformTopCategoryData)
  }, [response])

  const meta = extractMeta(response)
  const totalRows = meta?.totalRows ?? 0
  const totalPages = meta?.totalPages ?? 1

  const showSkeleton = isLoading && !response

  return (
    <div className="min-w-0">
      <ReportPageHeader
        title={__('Top Categories', 'wp-statistics')}
        filterGroup="content"
        optionsTriggerProps={options.triggerProps}
        showFilterButton={false}
      >
        <Select value={selectedTaxonomy} onValueChange={handleTaxonomyChange}>
          <SelectTrigger className="h-8 px-3 text-xs font-medium bg-background border border-neutral-200 rounded-md hover:bg-neutral-50 focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2">
            <SelectValue placeholder={__('Select taxonomy', 'wp-statistics')} />
          </SelectTrigger>
          <SelectContent>
            {availableTaxonomies.map((taxonomy) => (
              <SelectItem key={taxonomy.value} value={taxonomy.value}>
                {taxonomy.label}
              </SelectItem>
            ))}
          </SelectContent>
        </Select>
      </ReportPageHeader>

      {/* Options Drawer with Column Management */}
      <TableOptionsDrawer {...options} />

      <div className="p-3">
        <NoticeContainer className="mb-2" currentRoute="top-categories" />

        {isError ? (
          <div className="p-2 text-center">
            <ErrorMessage message={__('Failed to load top categories', 'wp-statistics')} />
            <p className="text-sm text-muted-foreground">{error?.message}</p>
          </div>
        ) : showSkeleton ? (
          <PanelSkeleton titleWidth="w-24">
            <TableSkeleton rows={10} columns={7} />
          </PanelSkeleton>
        ) : (
          <DataTable
            columns={columns}
            data={tableData}
            sorting={sorting}
            onSortingChange={handleSortingChange}
            manualSorting={true}
            manualPagination={true}
            pageCount={totalPages}
            page={page}
            onPageChange={handlePageChange}
            totalRows={totalRows}
            rowLimit={PER_PAGE}
            showColumnManagement={false}
            showPagination={true}
            isFetching={isFetching}
            hiddenColumns={TOP_CATEGORIES_DEFAULT_HIDDEN_COLUMNS}
            initialColumnVisibility={initialColumnVisibility}
            columnOrder={columnOrder.length > 0 ? columnOrder : undefined}
            onColumnVisibilityChange={handleColumnVisibilityChange}
            onColumnOrderChange={handleColumnOrderChange}
            onColumnPreferencesReset={handleColumnPreferencesReset}
            comparableColumns={TOP_CATEGORIES_COMPARABLE_COLUMNS}
            comparisonColumns={comparisonColumns}
            defaultComparisonColumns={TOP_CATEGORIES_DEFAULT_COMPARISON_COLUMNS}
            onComparisonColumnsChange={handleComparisonColumnsChange}
            emptyStateMessage={__('No categories found for the selected period', 'wp-statistics')}
            stickyHeader={true}
            borderless
            tableRef={tableRef}
          />
        )}
      </div>
    </div>
  )
}
