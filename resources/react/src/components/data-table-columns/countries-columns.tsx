/**
 * Column definitions for the Countries data table.
 * Geographic analytics grouped by country.
 */

import type { ColumnDef } from '@tanstack/react-table'
import { __ } from '@wordpress/i18n'

import { DataTableColumnHeader } from '@/components/custom/data-table-column-header'
import { DurationCell, LocationCell, NumericCell } from '@/components/data-table-columns'
import { COLUMN_SIZES } from '@/lib/column-sizes'
import { type ColumnConfig, getDefaultApiColumns } from '@/lib/column-utils'
import type { CountryRecord } from '@/services/geographic/get-countries'

/**
 * Context identifier for user preferences
 */
export const COUNTRIES_CONTEXT = 'countries'

/**
 * Context identifier for European countries user preferences
 */
export const EUROPEAN_COUNTRIES_CONTEXT = 'european-countries'

/**
 * Columns hidden by default (can be shown via column management)
 */
export const COUNTRIES_DEFAULT_HIDDEN_COLUMNS: string[] = ['bounceRate', 'sessionDuration', 'percentOfTotal']

/**
 * Columns that show PP comparison by default (to reduce visual clutter)
 * Users can enable/disable comparison per column via column config
 */
export const COUNTRIES_DEFAULT_COMPARISON_COLUMNS: string[] = ['visitors']

/**
 * Columns that support PP comparison display
 */
export const COUNTRIES_COMPARABLE_COLUMNS: string[] = [
  'visitors',
  'views',
  'viewsPerVisitor',
  'bounceRate',
  'sessionDuration',
]

/**
 * Column configuration for API column optimization
 */
export const COUNTRIES_COLUMN_CONFIG: ColumnConfig = {
  baseColumns: ['country_code', 'country_name'],
  columnDependencies: {
    country: ['country_code', 'country_name'],
    visitors: ['visitors'],
    views: ['views'],
    viewsPerVisitor: ['visitors', 'views'],
    bounceRate: ['bounce_rate'],
    sessionDuration: ['avg_session_duration'],
    percentOfTotal: ['visitors'],
  },
  context: COUNTRIES_CONTEXT,
}

/**
 * Default API columns when no preferences are set
 */
export const COUNTRIES_DEFAULT_API_COLUMNS = getDefaultApiColumns(COUNTRIES_COLUMN_CONFIG)

/**
 * Country data interface for the table
 */
export interface CountryData {
  id: string
  countryCode: string
  countryName: string
  visitors: number
  views: number
  bounceRate: number
  sessionDuration: number
  percentOfTotal: number
  // Previous period values for comparison
  previousVisitors?: number
  previousViews?: number
  previousBounceRate?: number
  previousSessionDuration?: number
}

/**
 * Transform API response to CountryData interface
 */
export function transformCountryData(record: CountryRecord, totalVisitors: number): CountryData {
  const previous = record.previous
  const visitors = Number(record.visitors) || 0
  const countryCode = record.country_code || '000'

  return {
    id: `country-${countryCode}`,
    countryCode,
    countryName: record.country_name || 'Unknown',
    visitors,
    views: Number(record.views) || 0,
    bounceRate: Math.round(Number(record.bounce_rate) || 0),
    sessionDuration: Math.round(Number(record.avg_session_duration) || 0),
    percentOfTotal: totalVisitors > 0 ? (visitors / totalVisitors) * 100 : 0,
    // Previous period values (only set if comparison data exists)
    ...(previous && {
      previousVisitors: previous.visitors !== undefined ? Number(previous.visitors) : undefined,
      previousViews: previous.views !== undefined ? Number(previous.views) : undefined,
      previousBounceRate: previous.bounce_rate !== undefined ? Math.round(Number(previous.bounce_rate)) : undefined,
      previousSessionDuration:
        previous.avg_session_duration !== undefined ? Math.round(Number(previous.avg_session_duration)) : undefined,
    }),
  }
}

/**
 * Options for creating Countries columns
 */
export interface CountriesColumnsOptions {
  /** Plugin URL for flag images */
  pluginUrl: string
  /** Date range comparison label for tooltip (from useComparisonDateLabel) */
  comparisonLabel?: string
  /** Columns that should display PP comparison (defaults to COUNTRIES_DEFAULT_COMPARISON_COLUMNS) */
  comparisonColumns?: string[]
}

/**
 * Create column definitions for the Countries table
 */
export function createCountriesColumns(options: CountriesColumnsOptions): ColumnDef<CountryData>[] {
  const { pluginUrl, comparisonLabel, comparisonColumns = COUNTRIES_DEFAULT_COMPARISON_COLUMNS } = options

  // Helper to check if a column should show comparison
  const showComparison = (columnId: string) => comparisonColumns.includes(columnId)

  return [
    {
      accessorKey: 'country',
      header: ({ column, table }) => <DataTableColumnHeader column={column} table={table} />,
      size: COLUMN_SIZES.location,
      cell: ({ row }) => (
        <LocationCell
          data={{
            countryCode: row.original.countryCode,
            countryName: row.original.countryName,
          }}
          pluginUrl={pluginUrl}
        />
      ),
      enableSorting: false,
      meta: {
        title: __('Country', 'wp-statistics'),
        priority: 'primary',
        cardPosition: 'header',
      },
    },
    {
      accessorKey: 'visitors',
      header: ({ column, table }) => <DataTableColumnHeader column={column} table={table} className="text-right" />,
      size: COLUMN_SIZES.views,
      cell: ({ row }) => (
        <NumericCell
          value={row.original.visitors}
          previousValue={showComparison('visitors') ? row.original.previousVisitors : undefined}
          comparisonLabel={comparisonLabel}
        />
      ),
      meta: {
        title: __('Visitors', 'wp-statistics'),
        priority: 'primary',
        cardPosition: 'body',
        isComparable: true,
        showComparison: showComparison('visitors'),
      },
    },
    {
      accessorKey: 'views',
      header: ({ column, table }) => <DataTableColumnHeader column={column} table={table} className="text-right" />,
      size: COLUMN_SIZES.views,
      cell: ({ row }) => (
        <NumericCell
          value={row.original.views}
          previousValue={showComparison('views') ? row.original.previousViews : undefined}
          comparisonLabel={comparisonLabel}
        />
      ),
      meta: {
        title: __('Views', 'wp-statistics'),
        priority: 'primary',
        cardPosition: 'body',
        isComparable: true,
        showComparison: showComparison('views'),
      },
    },
    {
      accessorKey: 'viewsPerVisitor',
      header: ({ column, table }) => <DataTableColumnHeader column={column} table={table} className="text-right" />,
      size: 90,
      cell: ({ row }) => {
        const vpv = row.original.visitors > 0 ? row.original.views / row.original.visitors : 0
        // Calculate previous views per visitor if both previous values exist and comparison is enabled
        const previousVpv =
          showComparison('viewsPerVisitor') &&
          row.original.previousVisitors !== undefined &&
          row.original.previousViews !== undefined &&
          row.original.previousVisitors > 0
            ? row.original.previousViews / row.original.previousVisitors
            : undefined
        return (
          <NumericCell
            value={vpv}
            decimals={1}
            previousValue={previousVpv}
            comparisonLabel={comparisonLabel}
          />
        )
      },
      meta: {
        title: __('Views/Visitor', 'wp-statistics'),
        priority: 'secondary',
        mobileLabel: __('V/Visitor', 'wp-statistics'),
        isComparable: true,
        showComparison: showComparison('viewsPerVisitor'),
      },
    },
    {
      accessorKey: 'bounceRate',
      header: ({ column, table }) => <DataTableColumnHeader column={column} table={table} className="text-right" />,
      size: COLUMN_SIZES.bounceRate,
      cell: ({ row }) => (
        <NumericCell
          value={row.original.bounceRate}
          suffix="%"
          previousValue={showComparison('bounceRate') ? row.original.previousBounceRate : undefined}
          comparisonLabel={comparisonLabel}
        />
      ),
      meta: {
        title: __('Bounce Rate', 'wp-statistics'),
        priority: 'secondary',
        mobileLabel: __('Bounce', 'wp-statistics'),
        isComparable: true,
        showComparison: showComparison('bounceRate'),
      },
    },
    {
      accessorKey: 'sessionDuration',
      header: ({ column, table }) => <DataTableColumnHeader column={column} table={table} className="text-right" />,
      size: COLUMN_SIZES.duration,
      cell: ({ row }) => (
        <DurationCell
          seconds={row.original.sessionDuration}
          previousSeconds={showComparison('sessionDuration') ? row.original.previousSessionDuration : undefined}
          comparisonLabel={comparisonLabel}
        />
      ),
      meta: {
        title: __('Avg. Duration', 'wp-statistics'),
        priority: 'secondary',
        mobileLabel: __('Duration', 'wp-statistics'),
        isComparable: true,
        showComparison: showComparison('sessionDuration'),
      },
    },
    {
      accessorKey: 'percentOfTotal',
      header: ({ column, table }) => <DataTableColumnHeader column={column} table={table} className="text-right" />,
      size: 80,
      cell: ({ row }) => (
        <NumericCell
          value={row.original.percentOfTotal}
          decimals={1}
          suffix="%"
        />
      ),
      meta: {
        title: __('% of Total', 'wp-statistics'),
        priority: 'secondary',
        mobileLabel: __('% Total', 'wp-statistics'),
      },
    },
  ]
}
