import 'uplot/dist/uPlot.min.css'

import { Panel, PanelActions, PanelContent, PanelHeader, PanelTitle } from '@components/ui/panel'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@components/ui/select'
import { Loader2 } from 'lucide-react'
import * as React from 'react'
import uPlot from 'uplot'

import { useUPlot } from '@/hooks/use-uplot'
import { useUPlotTooltip } from '@/hooks/use-uplot-tooltip'
import { barsPath } from '@/lib/uplot-bars-plugin'
import { cn, formatCompactNumber, isToday } from '@/lib/utils'
import { parseDateString } from '@/lib/wp-date'

export interface LineChartDataPoint {
  date: string
  previousDate?: string | null
  [key: string]: string | number | null | undefined
}

export interface LineChartMetric {
  key: string
  label: string
  color?: string
  enabled?: boolean
  value?: string | number
  previousValue?: string | number
  type?: 'line' | 'bar'
}

export interface LineChartProps {
  data: LineChartDataPoint[]
  metrics: LineChartMetric[]
  title?: string
  showPreviousPeriod?: boolean
  timeframe?: 'daily' | 'weekly' | 'monthly'
  onTimeframeChange?: (timeframe: 'daily' | 'weekly' | 'monthly') => void
  className?: string
  loading?: boolean
  borderless?: boolean
  compareDateTo?: string
  dateTo?: string
  headerActions?: React.ReactNode
  headerRight?: React.ReactNode
}

// Chart colors: Blue, Green, Amber, Red, Purple
const defaultColors = ['var(--chart-1)', 'var(--chart-2)', 'var(--chart-3)', 'var(--chart-4)', 'var(--chart-5)']
const splinePath = uPlot.paths.spline!()

// Resolve an HSL string like "220 70% 50%" to a usable CSS value
function toUsableColor(resolved: string): string {
  if (!resolved) return '#888'
  if (resolved.startsWith('#') || resolved.startsWith('rgb') || resolved.startsWith('hsl(')) return resolved
  if (/^\d/.test(resolved)) return `hsl(${resolved})`
  return resolved
}

// Parse chart date labels as local timezone (avoids UTC off-by-one from Date constructor)
function parseChartDate(value: string): Date {
  const monthMatch = value.match(/^(\d{4})-(\d{2})$/)
  if (monthMatch) {
    return new Date(parseInt(monthMatch[1]), parseInt(monthMatch[2]) - 1, 1)
  }
  return parseDateString(value.substring(0, 10))
}

// WordPress site locale for date formatting (falls back to browser default)
const locale = document.documentElement.lang || undefined

// Format date label for X axis ticks
function formatDateLabel(value: string, timeframe: 'daily' | 'weekly' | 'monthly'): string {
  if (timeframe === 'weekly' && /^\d{6}$/.test(value)) {
    const year = parseInt(value.substring(0, 4), 10)
    const week = parseInt(value.substring(4, 6), 10)
    const firstDayOfYear = new Date(year, 0, 1)
    const dayOfWeek = firstDayOfYear.getDay()
    const daysToMonday = dayOfWeek === 0 ? 1 : dayOfWeek === 1 ? 0 : 8 - dayOfWeek
    const firstMonday = new Date(year, 0, 1 + daysToMonday)
    const startDate = new Date(firstMonday)
    startDate.setDate(firstMonday.getDate() + (week - 1) * 7)
    return startDate.toLocaleDateString(locale, { month: 'short', day: 'numeric' })
  }
  if (timeframe === 'monthly' && /^\d{6}$/.test(value)) {
    const year = parseInt(value.substring(0, 4), 10)
    const month = parseInt(value.substring(4, 6), 10) - 1
    const date = new Date(year, month, 1)
    return date.toLocaleDateString(locale, { month: 'long' })
  }
  const date = parseChartDate(value)
  if (timeframe === 'monthly') {
    return date.toLocaleDateString(locale, { month: 'long' })
  }
  if (timeframe === 'weekly') {
    return date.toLocaleDateString(locale, { month: 'short', day: 'numeric' })
  }
  return date.toLocaleDateString(locale, { month: 'short', day: 'numeric' })
}

// Format tooltip date header
function formatTooltipDate(
  label: string,
  timeframe: 'daily' | 'weekly' | 'monthly'
): { formattedDate: string; dayOfWeek: string } {
  let formattedDate: string
  let dayOfWeek = ''

  if (timeframe === 'weekly' && /^\d{6}$/.test(label)) {
    const year = parseInt(label.substring(0, 4), 10)
    const week = parseInt(label.substring(4, 6), 10)
    const firstDayOfYear = new Date(year, 0, 1)
    const dayOfWeekNum = firstDayOfYear.getDay()
    const daysToMonday = dayOfWeekNum === 0 ? 1 : dayOfWeekNum === 1 ? 0 : 8 - dayOfWeekNum
    const firstMonday = new Date(year, 0, 1 + daysToMonday)
    const startDate = new Date(firstMonday)
    startDate.setDate(firstMonday.getDate() + (week - 1) * 7)
    const endDate = new Date(startDate)
    endDate.setDate(startDate.getDate() + 6)
    formattedDate = `${startDate.toLocaleDateString(locale, { month: 'long', day: 'numeric' })} to ${endDate.toLocaleDateString(locale, { month: 'long', day: 'numeric' })}`
  } else if (timeframe === 'monthly' && /^\d{6}$/.test(label)) {
    const year = parseInt(label.substring(0, 4), 10)
    const month = parseInt(label.substring(4, 6), 10) - 1
    const startDate = new Date(year, month, 1)
    const endDate = new Date(year, month + 1, 0)
    formattedDate = `${startDate.toLocaleDateString(locale, { month: 'short', day: 'numeric' })} to ${endDate.toLocaleDateString(locale, { month: 'short', day: 'numeric' })}`
  } else {
    const date = parseChartDate(label)
    if (timeframe === 'monthly') {
      formattedDate = date.toLocaleDateString(locale, { month: 'short', day: 'numeric' })
      const endDate = new Date(date)
      endDate.setMonth(endDate.getMonth() + 1)
      endDate.setDate(0)
      formattedDate = `${formattedDate} to ${endDate.toLocaleDateString(locale, { month: 'short', day: 'numeric' })}`
    } else if (timeframe === 'weekly') {
      formattedDate = date.toLocaleDateString(locale, { month: 'long', day: 'numeric' })
      const endDate = new Date(date)
      endDate.setDate(endDate.getDate() + 6)
      formattedDate = `${formattedDate} to ${endDate.toLocaleDateString(locale, { month: 'long', day: 'numeric' })}`
    } else {
      formattedDate = date.toLocaleDateString(locale, { day: 'numeric', month: 'short' })
      dayOfWeek = date.toLocaleDateString(locale, { weekday: 'short' })
    }
  }

  return { formattedDate, dayOfWeek }
}

// Format previous period tooltip date
function formatPrevTooltipDate(
  previousDateStr: string,
  timeframe: 'daily' | 'weekly' | 'monthly',
  compareDateTo?: string
): { prevFormatted: string; prevDayOfWeek: string } {
  const previousDate = parseChartDate(previousDateStr)
  const actualPpEndDate = compareDateTo ? parseChartDate(compareDateTo) : null
  let prevFormatted: string
  let prevDayOfWeek = ''

  if (timeframe === 'monthly') {
    prevFormatted = previousDate.toLocaleDateString(locale, { month: 'short', day: 'numeric' })
    let prevEndDate = new Date(previousDate)
    prevEndDate.setMonth(prevEndDate.getMonth() + 1)
    prevEndDate.setDate(0)
    if (actualPpEndDate && actualPpEndDate < prevEndDate) prevEndDate = actualPpEndDate
    prevFormatted = `${prevFormatted} to ${prevEndDate.toLocaleDateString(locale, { month: 'short', day: 'numeric' })}`
  } else if (timeframe === 'weekly') {
    prevFormatted = previousDate.toLocaleDateString(locale, { month: 'long', day: 'numeric' })
    let prevEndDate = new Date(previousDate)
    prevEndDate.setDate(prevEndDate.getDate() + 6)
    if (actualPpEndDate && actualPpEndDate < prevEndDate) prevEndDate = actualPpEndDate
    prevFormatted = `${prevFormatted} to ${prevEndDate.toLocaleDateString(locale, { month: 'long', day: 'numeric' })}`
  } else {
    prevFormatted = previousDate.toLocaleDateString(locale, { day: 'numeric', month: 'short' })
    prevDayOfWeek = previousDate.toLocaleDateString(locale, { weekday: 'short' })
  }

  return { prevFormatted, prevDayOfWeek }
}

/**
 * Describes a series slot in the uPlot column data.
 * A single visible metric may produce multiple series (solid + dotted for incomplete data,
 * plus a previous-period series).
 */
interface SeriesSlot {
  metricIndex: number
  kind: 'current' | 'solid' | 'dotted' | 'previous'
}

export function LineChart({
  data,
  metrics,
  title,
  showPreviousPeriod = true,
  timeframe = 'daily',
  onTimeframeChange,
  className,
  loading = false,
  borderless = false,
  compareDateTo,
  dateTo,
  headerActions,
  headerRight,
}: LineChartProps) {
  const [visibleMetrics, setVisibleMetrics] = React.useState<Record<string, boolean>>(() =>
    metrics.reduce(
      (acc, metric) => ({
        ...acc,
        [metric.key]: metric.enabled !== false,
        [`${metric.key}Previous`]: showPreviousPeriod,
      }),
      {}
    )
  )

  const toggleMetric = React.useCallback((metricKey: string) => {
    setVisibleMetrics((prev) => ({
      ...prev,
      [metricKey]: !prev[metricKey],
    }))
  }, [])

  const hasIncompleteData = React.useMemo(() => {
    if (!dateTo || data.length < 2) return false
    return isToday(dateTo)
  }, [dateTo, data.length])

  // Container ref for resolving CSS colors
  const wrapperRef = React.useRef<HTMLDivElement>(null)
  const tooltipRef = React.useRef<HTMLDivElement>(null)
  const dataRef = React.useRef(data)
  dataRef.current = data

  const { tooltip, plugin: tooltipPlugin } = useUPlotTooltip()

  // Series config: stable when only data refreshes (no chart recreation needed)
  const seriesSlots = React.useMemo((): SeriesSlot[] => {
    const slots: SeriesSlot[] = []
    metrics.forEach((metric, metricIndex) => {
      const isBar = metric.type === 'bar'
      const isVisible = visibleMetrics[metric.key]
      const isPrevVisible = visibleMetrics[`${metric.key}Previous`]
      if (isBar) {
        if (isVisible) slots.push({ metricIndex, kind: 'current' })
      } else {
        if (isVisible) {
          if (hasIncompleteData) {
            slots.push({ metricIndex, kind: 'solid' })
            slots.push({ metricIndex, kind: 'dotted' })
          } else {
            slots.push({ metricIndex, kind: 'current' })
          }
        }
        if (isPrevVisible && showPreviousPeriod) {
          slots.push({ metricIndex, kind: 'previous' })
        }
      }
    })
    return slots
  }, [metrics, visibleMetrics, hasIncompleteData, showPreviousPeriod])

  // Column data: changes with data, drives setData (cheap update, no chart recreation)
  const uData = React.useMemo((): uPlot.AlignedData => {
    if (!data.length) return [[]] as uPlot.AlignedData
    const xValues = data.map((_, i) => i)
    const columns: (number | null)[][] = []
    seriesSlots.forEach((slot) => {
      const metric = metrics[slot.metricIndex]
      if (slot.kind === 'solid') {
        columns.push(data.map((d, i) => (i === data.length - 1 ? null : ((d[metric.key] as number) ?? null))))
      } else if (slot.kind === 'dotted') {
        columns.push(data.map((d, i) => (i >= data.length - 2 ? ((d[metric.key] as number) ?? null) : null)))
      } else if (slot.kind === 'previous') {
        columns.push(data.map((d) => (d[`${metric.key}Previous`] as number) ?? null))
      } else {
        columns.push(data.map((d) => (d[metric.key] as number) ?? null))
      }
    })
    return [xValues, ...columns] as uPlot.AlignedData
  }, [data, seriesSlots, metrics])

  // Build uPlot options factory — identity changes when config changes, driving chart recreation
  const makeOptions = React.useCallback((): Omit<uPlot.Options, 'width' | 'height'> => {
    const el = wrapperRef.current
    const computed = el ? getComputedStyle(el) : null

    const resolveColor = (value: string): string => {
      if (!value.startsWith('var(')) return toUsableColor(value)
      const varName = value.slice(4, -1).trim()
      return toUsableColor(computed?.getPropertyValue(varName).trim() ?? '')
    }

    const borderColor = resolveColor('var(--border)')
    const mutedFg = resolveColor('var(--muted-foreground)')

    const hasBarMetrics = seriesSlots.some((slot) => metrics[slot.metricIndex].type === 'bar')
    // Bars fill this fraction of chart height; the rest is headroom so lines aren't compressed
    const BAR_SCALE_RATIO = 0.4

    // Build series config: series[0] is always X axis
    const series: uPlot.Series[] = [{}]

    seriesSlots.forEach((slot) => {
      const metric = metrics[slot.metricIndex]
      const rawColor = metric.color || defaultColors[slot.metricIndex % defaultColors.length]
      const color = resolveColor(rawColor)
      const isBar = metric.type === 'bar'

      if (isBar) {
        series.push({
          label: metric.label,
          stroke: color,
          fill: color,
          alpha: 0.7,
          width: 0,
          paths: barsPath(),
          points: { show: false },
          scale: 'y2',
        })
      } else if (slot.kind === 'previous') {
        series.push({
          label: `${metric.label} (prev)`,
          stroke: color,
          width: 2,
          dash: [5, 5],
          alpha: 0.5,
          paths: splinePath,
          points: { show: false },
          spanGaps: false,
        })
      } else if (slot.kind === 'dotted') {
        series.push({
          label: `${metric.label} (incomplete)`,
          stroke: color,
          width: 2,
          dash: [3, 3],
          paths: splinePath,
          points: { show: false },
          spanGaps: false,
        })
      } else {
        // 'current' or 'solid'
        series.push({
          label: metric.label,
          stroke: color,
          width: 2,
          paths: splinePath,
          points: { show: false },
          spanGaps: false,
        })
      }
    })

    return {
      cursor: {
        x: true,
        y: false,
        points: { show: false },
      },
      legend: { show: false },
      padding: [16, 8, 0, 20],
      series,
      scales: {
        x: {
          time: false,
          distr: 2, // ordinal
        },
        y: {
          range: (_u: uPlot, min: number, max: number) => {
            // Prevent flat scale crash (uPlot issue #620)
            if (max === min) return [min, max + 1]
            // Add 10% padding above
            const pad = (max - min) * 0.1
            return [Math.min(0, min), max + pad]
          },
        },
        ...(hasBarMetrics && {
          y2: {
            range: (_u: uPlot, _min: number, max: number) => {
              if (max <= 0) return [0, 1]
              return [0, max / BAR_SCALE_RATIO]
            },
          },
        }),
      },
      axes: [
        // X axis (bottom)
        {
          side: 2,
          grid: { show: false },
          ticks: { show: false },
          stroke: mutedFg,
          font: '12px system-ui, sans-serif',
          gap: 8,
          space: timeframe === 'monthly' ? 90 : 60,
          splits: (_u: uPlot, _axisIdx: number, scaleMin: number, scaleMax: number) => {
            const maxTicks = 7
            const totalPoints = Math.round(scaleMax - scaleMin) + 1
            if (totalPoints <= maxTicks) {
              return Array.from({ length: totalPoints }, (_, i) => scaleMin + i)
            }
            const totalGap = Math.round(scaleMax - scaleMin)
            const numGaps = maxTicks - 1
            const baseGap = Math.floor(totalGap / numGaps)
            const remainder = totalGap % numGaps
            const splits: number[] = [scaleMin]
            let pos = scaleMin
            for (let i = 1; i <= numGaps; i++) {
              pos += baseGap + (i > numGaps - remainder ? 1 : 0)
              splits.push(pos)
            }
            return splits
          },
          values: (_u: uPlot, splits: number[]) =>
            splits.map((idx) => {
              const d = dataRef.current[idx]
              return d ? formatDateLabel(d.date, timeframe) : ''
            }),
        },
        // Y axis (right)
        {
          side: 1,
          grid: { show: true, stroke: borderColor, width: 1 },
          ticks: { show: false },
          stroke: mutedFg,
          font: '12px system-ui, sans-serif',
          gap: 8,
          size: 50,
          incrs: [1, 2, 5, 10, 20, 50, 100, 200, 500, 1000, 2000, 5000, 10000, 20000, 50000, 100000],
          values: (_u: uPlot, splits: number[]) => splits.map((v) => formatCompactNumber(v)),
        },
        ...(hasBarMetrics ? [{
          scale: 'y2',
          show: false,
          side: 3,
          size: 0,
          grid: { show: false },
          ticks: { show: false },
        }] : []),
      ],
      plugins: [tooltipPlugin],
    }
  }, [seriesSlots, metrics, timeframe, tooltipPlugin])

  const { containerRef } = useUPlot({ options: makeOptions, data: uData })

  // Build tooltip content
  const tooltipContent = React.useMemo(() => {
    if (!tooltip.visible || tooltip.idx == null || tooltip.idx >= data.length) return null

    const idx = tooltip.idx
    const dataPoint = data[idx]
    const { formattedDate, dayOfWeek } = formatTooltipDate(dataPoint.date, timeframe)

    // Previous period date
    const previousDateStr = dataPoint.previousDate as string | null | undefined
    let prevInfo: { prevFormatted: string; prevDayOfWeek: string } | null = null
    if (previousDateStr) {
      prevInfo = formatPrevTooltipDate(previousDateStr, timeframe, compareDateTo)
    }

    // Collect visible metric values
    type TooltipEntry = {
      metricIndex: number
      isPrevious: boolean
      value: number | null
    }
    const entries: TooltipEntry[] = []

    metrics.forEach((metric, metricIndex) => {
      const isBar = metric.type === 'bar'
      const isVisible = visibleMetrics[metric.key]
      const isPrevVisible = visibleMetrics[`${metric.key}Previous`]

      if (isVisible) {
        const value = (dataPoint[metric.key] as number) ?? null
        entries.push({ metricIndex, isPrevious: false, value })
      }
      if (isPrevVisible && showPreviousPeriod && !isBar) {
        const prevKey = `${metric.key}Previous`
        const value = (dataPoint[prevKey] as number) ?? null
        entries.push({ metricIndex, isPrevious: true, value })
      }
    })

    return (
      <div className="rounded bg-tooltip px-2.5 py-2 shadow-md">
        <div className="mb-2 text-xs font-medium text-tooltip-foreground">
          {dayOfWeek ? `${formattedDate} (${dayOfWeek})` : formattedDate}
        </div>
        <div className="space-y-1.5">
          {entries.map((entry) => {
            const metric = metrics[entry.metricIndex]
            if (entry.isPrevious && !prevInfo) return null

            const color = metric.color || defaultColors[entry.metricIndex % defaultColors.length]
            const isBarType = metric.type === 'bar'
            const displayLabel = entry.isPrevious
              ? prevInfo!.prevDayOfWeek
                ? `${prevInfo!.prevFormatted} (${prevInfo!.prevDayOfWeek})`
                : prevInfo!.prevFormatted
              : metric.label

            return (
              <div
                key={`${metric.key}-${entry.isPrevious ? 'prev' : 'curr'}`}
                className="flex items-center justify-between gap-4"
              >
                <div className="flex items-center gap-2 text-xs">
                  <svg width="12" height="12" className={entry.isPrevious ? 'shrink-0 opacity-50' : 'shrink-0'}>
                    {isBarType ? (
                      <rect x="0" y="2" width="12" height="8" fill={color} rx="1" />
                    ) : (
                      <line
                        x1="0"
                        y1="6"
                        x2="12"
                        y2="6"
                        style={{ stroke: color }}
                        strokeWidth="3"
                        strokeDasharray={entry.isPrevious ? '3 2' : '0'}
                      />
                    )}
                  </svg>
                  <span className="text-tooltip-foreground">{displayLabel}</span>
                </div>
                <span className="font-medium text-tooltip-foreground tabular-nums">
                  {entry.value != null ? entry.value : '—'}
                </span>
              </div>
            )
          })}
        </div>
      </div>
    )
  }, [tooltip.visible, tooltip.idx, data, metrics, visibleMetrics, timeframe, showPreviousPeriod, compareDateTo])

  return (
    <Panel variant={borderless ? 'borderless' : 'default'} className={className}>
      <PanelHeader className="flex-col items-start gap-3 md:gap-4">
        {(title || headerRight) && (
          <div className="flex w-full items-center justify-between">
            {title && <PanelTitle>{title}</PanelTitle>}
            {headerRight && <PanelActions>{headerRight}</PanelActions>}
          </div>
        )}
        <div
          className={cn(
            'flex w-full',
            'flex-col gap-3 md:flex-row md:items-center md:justify-between md:gap-4'
          )}
        >
          <div
            className={cn(
              'flex items-center',
              'flex-wrap gap-3 md:gap-6'
            )}
          >
            {metrics.map((metric, index) => {
              const color = metric.color || defaultColors[index % defaultColors.length]
              const isCurrentVisible = visibleMetrics[metric.key]
              const isPreviousVisible = visibleMetrics[`${metric.key}Previous`]
              const isBarType = metric.type === 'bar'
              return (
                <div key={metric.key} className="flex flex-col gap-1">
                  <span className="text-xs font-medium text-muted-foreground leading-none">{metric.label}</span>
                  <div className="flex items-baseline gap-2">
                    {metric.value != null && (
                      <button
                        onClick={() => toggleMetric(metric.key)}
                        aria-label={`Toggle ${metric.label} visibility`}
                        aria-pressed={isCurrentVisible}
                        className={cn(
                          'flex items-center gap-1.5 cursor-pointer transition-opacity',
                          'min-h-[44px] md:min-h-0 py-2 md:py-0',
                          !isCurrentVisible && 'opacity-50'
                        )}
                      >
                        {isBarType ? (
                          <svg width="12" height="12" className="shrink-0">
                            <rect x="0" y="4" width="12" height="8" fill={color} rx="1" />
                          </svg>
                        ) : (
                          <svg width="12" height="3" className="shrink-0">
                            <line x1="0" y1="1.5" x2="12" y2="1.5" style={{ stroke: color }} strokeWidth="3" />
                          </svg>
                        )}
                        <span
                          className={cn(
                            'text-sm font-semibold text-foreground leading-none tabular-nums',
                            !isCurrentVisible && 'line-through'
                          )}
                        >
                          {metric.value}
                        </span>
                      </button>
                    )}
                    {metric.previousValue != null && !isBarType && (
                      <button
                        onClick={() => toggleMetric(`${metric.key}Previous`)}
                        aria-label={`Toggle ${metric.label} previous period visibility`}
                        aria-pressed={isPreviousVisible}
                        className={cn(
                          'flex items-center gap-1.5 cursor-pointer transition-opacity',
                          'min-h-[44px] md:min-h-0 py-2 md:py-0',
                          !isPreviousVisible && 'opacity-50'
                        )}
                      >
                        <svg width="12" height="3" className="shrink-0 opacity-50">
                          <line
                            x1="0"
                            y1="1.5"
                            x2="12"
                            y2="1.5"
                            style={{ stroke: color }}
                            strokeWidth="3"
                            strokeDasharray="3 2"
                          />
                        </svg>
                        <span
                          className={cn(
                            'text-xs text-muted-foreground leading-none tabular-nums',
                            !isPreviousVisible && 'line-through'
                          )}
                        >
                          {metric.previousValue}
                        </span>
                      </button>
                    )}
                  </div>
                </div>
              )
            })}
          </div>
          <div className="flex items-center gap-2 md:gap-3">
            {headerActions}
            {onTimeframeChange && (
              <Select value={timeframe} onValueChange={onTimeframeChange}>
                <SelectTrigger className="w-[90px] md:w-[100px] h-10 md:h-8 text-xs font-medium">
                  <SelectValue />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="daily">Daily</SelectItem>
                  <SelectItem value="weekly">Weekly</SelectItem>
                  <SelectItem value="monthly">Monthly</SelectItem>
                </SelectContent>
              </Select>
            )}
          </div>
        </div>
      </PanelHeader>
      <PanelContent>
        {loading ? (
          <div className="flex h-[180px] md:h-[220px] lg:h-[250px] items-center justify-center">
            <Loader2 className="h-6 w-6 animate-spin text-muted-foreground" />
          </div>
        ) : (
          <div ref={wrapperRef} className="relative h-[180px] md:h-[220px] lg:h-[250px] w-full animate-in fade-in duration-500">
            <div ref={containerRef} className="absolute inset-0" />
            {tooltip.visible && tooltipContent && (
              <div
                ref={tooltipRef}
                className="pointer-events-none absolute z-10"
                style={{
                  left: Math.min(
                    tooltip.left,
                    (wrapperRef.current?.offsetWidth ?? 400) - (tooltipRef.current?.offsetWidth ?? 200)
                  ),
                  top: Math.max(0, tooltip.top - (tooltipRef.current?.offsetHeight ?? 0) - 8),
                }}
              >
                {tooltipContent}
              </div>
            )}
          </div>
        )}
      </PanelContent>
    </Panel>
  )
}
