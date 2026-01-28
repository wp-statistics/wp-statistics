/**
 * Column definitions for the Cities data table.
 * Geographic analytics grouped by city.
 */

import type { ColumnDef } from '@tanstack/react-table'
import { __ } from '@wordpress/i18n'

import { DataTableColumnHeader } from '@/components/custom/data-table-column-header'
import { LocationCell, NumericCell } from '@/components/data-table-columns'
import { COLUMN_SIZES } from '@/lib/column-sizes'
import { type ColumnConfig, getDefaultApiColumns } from '@/lib/column-utils'
import type { CityRecord } from '@/services/geographic/get-cities'

/**
 * Context identifier for user preferences
 */
export const CITIES_CONTEXT = 'cities'

/**
 * Columns hidden by default (can be shown via column management)
 */
export const CITIES_DEFAULT_HIDDEN_COLUMNS: string[] = []

/**
 * Columns that show PP comparison by default (to reduce visual clutter)
 * Users can enable/disable comparison per column via column config
 */
export const CITIES_DEFAULT_COMPARISON_COLUMNS: string[] = ['visitors']

/**
 * Columns that support PP comparison display
 */
export const CITIES_COMPARABLE_COLUMNS: string[] = ['visitors', 'views']

/**
 * Column configuration for API column optimization
 */
export const CITIES_COLUMN_CONFIG: ColumnConfig = {
  baseColumns: ['city_id', 'city_name'],
  columnDependencies: {
    city: ['city_id', 'city_name'],
    region: ['city_region_name'],
    country: ['country_code', 'country_name'],
    visitors: ['visitors'],
    views: ['views'],
  },
  context: CITIES_CONTEXT,
}

/**
 * Default API columns when no preferences are set
 */
export const CITIES_DEFAULT_API_COLUMNS = getDefaultApiColumns(CITIES_COLUMN_CONFIG)

/**
 * City data interface for the table
 */
export interface CityData {
  id: string
  cityName: string
  regionName: string
  countryCode: string
  countryName: string
  visitors: number
  views: number
  // Previous period values for comparison
  previousVisitors?: number
  previousViews?: number
}

/**
 * Transform API response to CityData interface
 */
export function transformCityData(record: CityRecord): CityData {
  const previous = record.previous

  return {
    id: `city-${record.city_id || 0}`,
    cityName: record.city_name || __('Unknown', 'wp-statistics'),
    regionName: record.city_region_name || '',
    countryCode: record.country_code || '000',
    countryName: record.country_name || __('Unknown', 'wp-statistics'),
    visitors: Number(record.visitors) || 0,
    views: Number(record.views) || 0,
    // Previous period values (only set if comparison data exists)
    ...(previous && {
      previousVisitors: previous.visitors !== undefined ? Number(previous.visitors) : undefined,
      previousViews: previous.views !== undefined ? Number(previous.views) : undefined,
    }),
  }
}

/**
 * Options for creating Cities columns
 */
export interface CitiesColumnsOptions {
  /** Plugin URL for flag images */
  pluginUrl: string
  /** Date range comparison label for tooltip (from useComparisonDateLabel) */
  comparisonLabel?: string
  /** Columns that should display PP comparison (defaults to CITIES_DEFAULT_COMPARISON_COLUMNS) */
  comparisonColumns?: string[]
}

/**
 * Create column definitions for the Cities table
 */
export function createCitiesColumns(options: CitiesColumnsOptions): ColumnDef<CityData>[] {
  const { pluginUrl, comparisonLabel, comparisonColumns = CITIES_DEFAULT_COMPARISON_COLUMNS } = options

  // Helper to check if a column should show comparison
  const showComparison = (columnId: string) => comparisonColumns.includes(columnId)

  return [
    {
      accessorKey: 'city',
      header: ({ column, table }) => <DataTableColumnHeader column={column} table={table} />,
      size: 160,
      cell: ({ row }) => (
        <span className="text-xs font-medium text-neutral-700 truncate">
          {row.original.cityName}
        </span>
      ),
      enableSorting: false,
      meta: {
        title: __('City', 'wp-statistics'),
        priority: 'primary',
        cardPosition: 'header',
      },
    },
    {
      accessorKey: 'region',
      header: ({ column, table }) => <DataTableColumnHeader column={column} table={table} />,
      size: 140,
      cell: ({ row }) => (
        <span className="text-xs font-medium text-neutral-700 truncate">
          {row.original.regionName || '—'}
        </span>
      ),
      enableSorting: false,
      meta: {
        title: __('Region', 'wp-statistics'),
        priority: 'secondary',
        cardPosition: 'body',
      },
    },
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
        priority: 'secondary',
        cardPosition: 'body',
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
  ]
}
