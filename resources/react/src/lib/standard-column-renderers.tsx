/**
 * Standard Column Renderers
 *
 * Maps PHP column `type` strings to existing cell components.
 * Used by the PHP-to-JS registration bridge to build ColumnDef[] from PHP config.
 */

import type { ColumnDef } from '@tanstack/react-table'
import { ChevronRight } from 'lucide-react'

import { DataTableColumnHeader } from '@/components/custom/data-table-column-header'
import { AuthorCell, DurationCell, EntryPageCell, JourneyCell, LastVisitCell, LocationCell, NumericCell, PageCell, ReferrerCell, StatusCell, TermCell, UriCell, VisitorInfoCell } from '@/components/data-table-columns'
import { getChannelDisplayName } from '@/components/data-table-columns/source-categories-columns'
import { COLUMN_SIZES } from '@/lib/column-sizes'
import { parseEntryPage } from '@/lib/url-utils'
import { WordPress } from '@/lib/wordpress'
import { parseDateTimeString } from '@/lib/wp-date'

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
    /** When true, first column gets an expand/collapse chevron toggle */
    expandable?: boolean
  }
): ColumnDef<Record<string, unknown>>[] {
  const { comparisonLabel, comparisonColumns = [], expandable } = options
  const showComparison = (columnId: string) => comparisonColumns.includes(columnId)

  const columns = columnDefs.map((col) => {
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
        const computeFrom = col.computeFrom
        return {
          ...base,
          header: ({ column, table }) => (
            <DataTableColumnHeader column={column} table={table} className="text-right" />
          ),
          ...(size ? {} : { size: COLUMN_SIZES.duration }),
          cell: ({ row }) => {
            let seconds: number
            if (computeFrom) {
              const start = new Date(String(row.original[computeFrom.startField] || ''))
              const end = new Date(String(row.original[computeFrom.endField] || ''))
              seconds = isNaN(start.getTime()) || isNaN(end.getTime())
                ? 0
                : Math.max(0, Math.floor((end.getTime() - start.getTime()) / 1000))
            } else {
              seconds = Number(row.original[field]) || 0
            }
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

      case 'visitor-info': {
        const wp = WordPress.getInstance()
        return {
          ...base,
          ...(size ? {} : { size: 220 }),
          header: ({ column, table }) => <DataTableColumnHeader column={column} table={table} />,
          cell: ({ row }) => {
            const r = row.original
            return (
              <VisitorInfoCell
                data={{
                  country: {
                    code: String(r.country_code || '000').toLowerCase(),
                    name: String(r.country_name || ''),
                    city: String(r.city_name || ''),
                  },
                  os: {
                    icon: String(r.os_name || 'unknown').toLowerCase().replace(/[\s/]+/g, '_'),
                    name: String(r.os_name || 'Unknown'),
                  },
                  browser: {
                    icon: String(r.browser_name || 'unknown').toLowerCase(),
                    name: String(r.browser_name || 'Unknown'),
                    version: String(r.browser_version || ''),
                  },
                  user: r.user_id
                    ? {
                        id: Number(r.user_id),
                        username: String(r.user_login || ''),
                        role: String(r.user_role || ''),
                      }
                    : undefined,
                  identifier: String(r.visitor_ip || r.visitor_hash || ''),
                  visitorHash: String(r.visitor_hash || ''),
                  ipAddress: String(r.visitor_ip || ''),
                }}
                config={{ pluginUrl: wp.getPluginUrl(), trackLoggedInEnabled: wp.isTrackLoggedInEnabled(), storeIpEnabled: wp.isStoreIpEnabled() }}
              />
            )
          },
        } as ColumnDef<Record<string, unknown>>
      }

      case 'last-visit':
        return {
          ...base,
          ...(size ? {} : { size: 100 }),
          header: ({ column, table }) => <DataTableColumnHeader column={column} table={table} />,
          cell: ({ row }) => {
            const raw = String(row.original[field] || '')
            if (!raw) return <span className="text-xs text-neutral-400">&mdash;</span>
            return <LastVisitCell date={parseDateTimeString(raw)} />
          },
        } as ColumnDef<Record<string, unknown>>

      case 'visitor-status':
        return {
          ...base,
          ...(size ? {} : { size: 90 }),
          header: ({ column, table }) => <DataTableColumnHeader column={column} table={table} />,
          cell: ({ row }) => {
            const r = row.original
            const status = (String(r.visitor_status || 'returning')) as 'new' | 'returning'
            const firstVisitStr = String(r.first_visit || r.last_visit || '')
            return <StatusCell status={status} firstVisit={parseDateTimeString(firstVisitStr)} />
          },
        } as ColumnDef<Record<string, unknown>>

      case 'journey': {
        return {
          ...base,
          ...(size ? {} : { size: 200 }),
          header: ({ column, table }) => <DataTableColumnHeader column={column} table={table} />,
          cell: ({ row }) => {
            const r = row.original
            const entryParsed = parseEntryPage(String(r.entry_page || '/'), String(r.entry_page_title || ''))
            return (
              <JourneyCell
                data={{
                  entryPage: {
                    title: entryParsed.title,
                    url: String(r.entry_page || '/'),
                    hasQueryString: entryParsed.hasQueryString,
                    queryString: entryParsed.queryString,
                    utmCampaign: entryParsed.utmCampaign,
                    pageType: r.entry_page_type as string | undefined,
                    pageWpId: r.entry_page_wp_id as number | null,
                    resourceId: r.entry_page_resource_id as number | null,
                  },
                  exitPage: {
                    title: String(r.exit_page_title || r.exit_page || '/'),
                    url: String(r.exit_page || '/'),
                    pageType: r.exit_page_type as string | undefined,
                    pageWpId: r.exit_page_wp_id as number | null,
                    resourceId: r.exit_page_resource_id as number | null,
                  },
                  isBounce: String(r.entry_page || '') === String(r.exit_page || '') && Number(r.total_views) <= 1,
                }}
              />
            )
          },
        } as ColumnDef<Record<string, unknown>>
      }

      case 'entry-page': {
        return {
          ...base,
          ...(size ? {} : { size: 200 }),
          header: ({ column, table }) => <DataTableColumnHeader column={column} table={table} />,
          cell: ({ row }) => {
            const r = row.original
            const parsed = parseEntryPage(String(r[field] || '/'), String(r[`${field}_title`] || ''))
            return (
              <EntryPageCell
                data={{
                  title: parsed.title,
                  url: String(r[field] || '/'),
                  hasQueryString: parsed.hasQueryString,
                  queryString: parsed.queryString,
                  utmCampaign: parsed.utmCampaign,
                  pageType: r[`${field}_type`] as string | undefined,
                  pageWpId: r[`${field}_wp_id`] as number | null,
                  resourceId: r[`${field}_resource_id`] as number | null,
                }}
              />
            )
          },
        } as ColumnDef<Record<string, unknown>>
      }

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

  // Wrap first column's cell with expand/collapse toggle when expandable rows are enabled
  if (expandable && columns.length > 0) {
    const first = columns[0]
    const originalCell = first.cell
    first.cell = (props) => {
      const { row } = props
      return (
        <div className="flex items-center gap-2">
          {row.getCanExpand() ? (
            <button
              onClick={(e) => { e.stopPropagation(); row.toggleExpanded() }}
              className="flex items-center justify-center w-5 h-5 shrink-0 text-neutral-400 hover:text-neutral-600"
            >
              <ChevronRight className={`h-3.5 w-3.5 transition-transform ${row.getIsExpanded() ? 'rotate-90' : ''}`} />
            </button>
          ) : (
            <span className="w-5 shrink-0" />
          )}
          {typeof originalCell === 'function' ? (originalCell as (info: typeof props) => React.ReactNode)(props) : null}
        </div>
      )
    }
  }

  return columns
}
