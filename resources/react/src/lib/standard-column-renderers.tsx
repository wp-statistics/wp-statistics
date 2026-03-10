/**
 * Standard Column Renderers
 *
 * Maps PHP column `type` strings to existing cell components.
 * Used by the PHP-to-JS registration bridge to build ColumnDef[] from PHP config.
 */

import type { ColumnDef } from '@tanstack/react-table'

import { DataTableColumnHeader } from '@/components/custom/data-table-column-header'
import { AuthorCell, DurationCell, LocationCell, NumericCell, PageCell, ReferrerCell, TermCell, UriCell } from '@/components/data-table-columns'
import { getChannelDisplayName } from '@/components/data-table-columns/source-categories-columns'
import { COLUMN_SIZES } from '@/lib/column-sizes'
import { WordPress } from '@/lib/wordpress'

const DATE_FORMATTER = new Intl.DateTimeFormat(undefined, {
  month: 'short',
  day: 'numeric',
  year: 'numeric',
})

/**
 * Resolve a dot-path like 'previous.sessions' from a record object
 */
function getNestedValue(obj: Record<string, unknown>, path: string): unknown {
  return path.split('.').reduce<unknown>((current, key) => {
    if (current && typeof current === 'object') {
      return (current as Record<string, unknown>)[key]
    }
    return undefined
  }, obj)
}

/**
 * Create ColumnDef[] from PHP column definitions
 */
export function createColumnsFromConfig(
  columnDefs: PhpReportColumn[],
  options: {
    comparisonLabel?: string
    comparisonColumns?: string[]
  }
): ColumnDef<Record<string, unknown>>[] {
  const { comparisonLabel, comparisonColumns = [] } = options
  const showComparison = (columnId: string) => comparisonColumns.includes(columnId)

  return columnDefs.map((col) => {
    const isComparable = col.comparable === true
    const isSortable = col.sortable !== false
    const size = col.size ? COLUMN_SIZES[col.size as keyof typeof COLUMN_SIZES] : undefined
    // API field name to read from row data (defaults to column key)
    const field = col.dataField || col.key

    const base: Partial<ColumnDef<Record<string, unknown>>> = {
      accessorKey: col.key,
      enableSorting: isSortable,
      ...(size && { size }),
      meta: {
        title: col.title,
        priority: (col.priority || 'primary') as 'primary' | 'secondary' | 'hidden',
        ...(col.mobileLabel && { mobileLabel: col.mobileLabel }),
        ...(col.cardPosition && { cardPosition: col.cardPosition as 'header' | 'body' | 'footer' }),
        ...(isComparable && { isComparable: true }),
        ...(isComparable && { showComparison: showComparison(col.key) }),
      },
    }

    switch (col.type) {
      case 'page-link':
        return {
          ...base,
          header: ({ column, table }) => <DataTableColumnHeader column={column} table={table} />,
          cell: ({ row }) => (
            <PageCell
              data={{
                title: String(row.original[col.key] || row.original.page_title || ''),
                url: String(row.original.page_uri || '/'),
                pageType: row.original.page_type as string | undefined,
                pageWpId: row.original.page_wp_id as number | undefined,
                resourceId: row.original.resource_id as number | undefined,
              }}
              externalUrl={String(row.original.page_uri || '')}
            />
          ),
        } as ColumnDef<Record<string, unknown>>

      case 'numeric': {
        const previousKey = col.previousKey
        return {
          ...base,
          header: ({ column, table }) => (
            <DataTableColumnHeader column={column} table={table} className="text-right" />
          ),
          ...(size ? {} : { size: COLUMN_SIZES.views }),
          cell: ({ row }) => {
            const value = Number(row.original[field]) || 0
            const previousValue =
              isComparable && showComparison(col.key) && previousKey
                ? Number(getNestedValue(row.original, previousKey)) || undefined
                : undefined
            return (
              <NumericCell
                value={value}
                previousValue={previousValue}
                comparisonLabel={comparisonLabel}
                decimals={col.decimals}
              />
            )
          },
        } as ColumnDef<Record<string, unknown>>
      }

      case 'percentage': {
        const previousKey = col.previousKey
        return {
          ...base,
          header: ({ column, table }) => (
            <DataTableColumnHeader column={column} table={table} className="text-right" />
          ),
          ...(size ? {} : { size: COLUMN_SIZES.bounceRate }),
          cell: ({ row }) => {
            const value = Number(row.original[field]) || 0
            const previousValue =
              isComparable && showComparison(col.key) && previousKey
                ? Number(getNestedValue(row.original, previousKey)) || undefined
                : undefined
            return (
              <NumericCell
                value={value}
                suffix="%"
                decimals={col.decimals ?? 1}
                previousValue={previousValue}
                comparisonLabel={comparisonLabel}
              />
            )
          },
        } as ColumnDef<Record<string, unknown>>
      }

      case 'duration': {
        const previousKey = col.previousKey
        return {
          ...base,
          header: ({ column, table }) => (
            <DataTableColumnHeader column={column} table={table} className="text-right" />
          ),
          ...(size ? {} : { size: COLUMN_SIZES.duration }),
          cell: ({ row }) => {
            const seconds = Number(row.original[field]) || 0
            const previousSeconds =
              isComparable && showComparison(col.key) && previousKey
                ? Number(getNestedValue(row.original, previousKey)) || undefined
                : undefined
            return (
              <DurationCell
                seconds={seconds}
                previousSeconds={previousSeconds}
                comparisonLabel={comparisonLabel}
              />
            )
          },
        } as ColumnDef<Record<string, unknown>>
      }

      case 'location':
        return {
          ...base,
          ...(size ? {} : { size: COLUMN_SIZES.location }),
          header: ({ column, table }) => <DataTableColumnHeader column={column} table={table} />,
          cell: ({ row }) => {
            const countryCode = String(row.original.country_code || '000')
            return (
              <LocationCell
                data={{
                  countryCode,
                  countryName: String(row.original.country_name || 'Unknown'),
                  regionName: row.original.region_name ? String(row.original.region_name) : undefined,
                  cityName: row.original.city_name ? String(row.original.city_name) : undefined,
                }}
                pluginUrl={WordPress.getInstance().getPluginUrl()}
                linkTo={col.linkTo}
                linkParams={col.linkParamField ? { countryCode: String(row.original[col.linkParamField] || '') } : undefined}
              />
            )
          },
        } as ColumnDef<Record<string, unknown>>

      case 'referrer':
        return {
          ...base,
          ...(size ? {} : { size: COLUMN_SIZES.referrer }),
          header: ({ column, table }) => <DataTableColumnHeader column={column} table={table} />,
          cell: ({ row }) => (
            <ReferrerCell
              data={{
                domain: String(row.original.referrer_domain || ''),
                category: String(row.original.referrer_channel || 'referral'),
              }}
            />
          ),
        } as ColumnDef<Record<string, unknown>>

      case 'computed-ratio': {
        return {
          ...base,
          header: ({ column, table }) => (
            <DataTableColumnHeader column={column} table={table} className="text-right" />
          ),
          ...(size ? {} : { size: COLUMN_SIZES.viewsPerSession }),
          cell: ({ row }) => {
            const num = Number(row.original[col.numerator!]) || 0
            const denom = Number(row.original[col.denominator!]) || 0
            const value = denom > 0 ? num / denom : 0

            let previousValue: number | undefined
            if (isComparable && showComparison(col.key) && col.previousNumerator && col.previousDenominator) {
              const prevNum = Number(getNestedValue(row.original, col.previousNumerator)) || 0
              const prevDenom = Number(getNestedValue(row.original, col.previousDenominator)) || 0
              previousValue = prevDenom > 0 ? prevNum / prevDenom : undefined
            }

            return (
              <NumericCell
                value={value}
                decimals={col.decimals ?? 1}
                previousValue={previousValue}
                comparisonLabel={comparisonLabel}
              />
            )
          },
        } as ColumnDef<Record<string, unknown>>
      }

      case 'source-category':
        return {
          ...base,
          header: ({ column, table }) => <DataTableColumnHeader column={column} table={table} />,
          cell: ({ row }) => (
            <span className="truncate text-xs font-medium text-neutral-700">
              {getChannelDisplayName(String(row.original[field] ?? ''))}
            </span>
          ),
        } as ColumnDef<Record<string, unknown>>

      case 'uri':
        return {
          ...base,
          header: ({ column, table }) => <DataTableColumnHeader column={column} table={table} />,
          cell: ({ row }) => (
            <UriCell uri={String(row.original[field] ?? '')} />
          ),
        } as ColumnDef<Record<string, unknown>>

      case 'author':
        return {
          ...base,
          header: ({ column, table }) => <DataTableColumnHeader column={column} table={table} />,
          cell: ({ row }) => (
            <AuthorCell
              authorId={Number(row.original.author_id) || 0}
              authorName={String(row.original.author_name || '')}
              authorAvatar={row.original.author_avatar ? String(row.original.author_avatar) : null}
            />
          ),
        } as ColumnDef<Record<string, unknown>>

      case 'term':
        return {
          ...base,
          header: ({ column, table }) => <DataTableColumnHeader column={column} table={table} />,
          cell: ({ row }) => (
            <TermCell
              termId={Number(row.original.term_id) || 0}
              termName={String(row.original.term_name || '')}
            />
          ),
        } as ColumnDef<Record<string, unknown>>

      case 'date':
        return {
          ...base,
          header: ({ column, table }) => (
            <DataTableColumnHeader column={column} table={table} className="text-right" />
          ),
          ...(size ? {} : { size: 100 }),
          cell: ({ row }) => {
            const raw = row.original[field]
            if (!raw) return <span className="text-xs text-neutral-400">—</span>
            const date = new Date(String(raw))
            if (isNaN(date.getTime())) return <span className="text-xs text-neutral-400">—</span>
            return <span className="text-xs text-neutral-700 whitespace-nowrap">{DATE_FORMATTER.format(date)}</span>
          },
        } as ColumnDef<Record<string, unknown>>

      case 'text':
      default:
        return {
          ...base,
          header: ({ column, table }) => <DataTableColumnHeader column={column} table={table} />,
          cell: ({ row }) => (
            <span className="truncate text-xs font-medium text-neutral-700">
              {String(row.original[field] ?? '')}
            </span>
          ),
        } as ColumnDef<Record<string, unknown>>
    }
  })
}
