import type { ChartConfig } from '@components/ui/chart'
import { ChartContainer, ChartTooltip } from '@components/ui/chart'
import { Panel, PanelContent, PanelHeader, PanelTitle } from '@components/ui/panel'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@components/ui/select'
import { Loader2 } from 'lucide-react'
import * as React from 'react'
import { CartesianGrid, Line, LineChart as RechartsLineChart, XAxis, YAxis } from 'recharts'

import { useBreakpoint } from '@/hooks/use-breakpoint'
import { cn, formatCompactNumber, isToday } from '@/lib/utils'

export interface LineChartDataPoint {
  date: string
  previousDate?: string | null // For tooltip display when comparing periods of different lengths
  [key: string]: string | number | null | undefined
}

export interface LineChartMetric {
  key: string
  label: string
  color?: string
  enabled?: boolean
  value?: string | number
  previousValue?: string | number
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
  /** Actual previous period end date - used to cap tooltip display for partial periods */
  compareDateTo?: string
  /** End date of the current period - used to detect incomplete data (shows dotted line when ending today) */
  dateTo?: string
}

// Type for chart tooltip payload entries
interface TooltipPayloadEntry {
  dataKey: string
  value: number | string
  color: string
  name?: string
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
}: LineChartProps) {
  const { isMobile } = useBreakpoint()
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

  // Chart colors: Blue, Green, Amber, Red, Purple
  const defaultColors = ['var(--chart-1)', 'var(--chart-2)', 'var(--chart-3)', 'var(--chart-4)', 'var(--chart-5)']

  // Build chart config from metrics
  const chartConfig = metrics.reduce((acc, metric, index) => {
    const color = metric.color || defaultColors[index % defaultColors.length]
    return {
      ...acc,
      [metric.key]: {
        label: metric.label,
        color: color,
      },
      [`${metric.key}Previous`]: {
        label: `${metric.label} (Previous)`,
        color: color,
      },
    }
  }, {} as ChartConfig)

  const toggleMetric = (metricKey: string) => {
    setVisibleMetrics((prev) => ({
      ...prev,
      [metricKey]: !prev[metricKey],
    }))
  }

  // Detect if the current period is incomplete (ends on today)
  const hasIncompleteData = React.useMemo(() => {
    if (!dateTo || data.length < 2) return false
    return isToday(dateTo)
  }, [dateTo, data.length])

  // Transform data to add _solid and _dotted keys for incomplete data handling
  // This avoids using custom `data` props on Line components which causes x-axis duplication
  const chartData = React.useMemo(() => {
    if (!hasIncompleteData) return data

    return data.map((point, idx) => {
      const isLastPoint = idx === data.length - 1
      const isSecondToLast = idx === data.length - 2
      const newPoint: LineChartDataPoint = { ...point }

      // For each current period metric, create _solid and _dotted versions
      metrics.forEach((metric) => {
        const value = point[metric.key]
        // _solid: value for all points except last (so solid line stops before last point)
        newPoint[`${metric.key}_solid`] = isLastPoint ? null : value
        // _dotted: value only for last two points (so dotted line only shows last segment)
        newPoint[`${metric.key}_dotted`] = isSecondToLast || isLastPoint ? value : null
      })

      return newPoint
    })
  }, [data, hasIncompleteData, metrics])

  // Memoize current period lines
  const currentLines = React.useMemo(
    () =>
      metrics.flatMap((metric, index) => {
        if (!visibleMetrics[metric.key]) return []
        const color = metric.color || defaultColors[index % defaultColors.length]

        if (!hasIncompleteData) {
          // No incomplete data - single solid line using original key
          return [
            <Line key={metric.key} type="monotone" dataKey={metric.key} stroke={color} strokeWidth={2} dot={false} />,
          ]
        }

        // Incomplete data - solid line + dotted segment overlay using transformed keys
        // Use synchronized animation parameters to ensure both lines animate together
        return [
          <Line
            key={`${metric.key}-solid`}
            type="monotone"
            dataKey={`${metric.key}_solid`}
            stroke={color}
            strokeWidth={2}
            dot={false}
            connectNulls={false}
            animationBegin={0}
            animationDuration={1500}
            animationEasing="ease"
          />,
          <Line
            key={`${metric.key}-dotted`}
            type="monotone"
            dataKey={`${metric.key}_dotted`}
            stroke={color}
            strokeWidth={2}
            strokeDasharray="3 3"
            dot={false}
            connectNulls={false}
            legendType="none"
            animationBegin={0}
            animationDuration={1500}
            animationEasing="ease"
          />,
        ]
      }),
    [metrics, visibleMetrics, defaultColors, hasIncompleteData]
  )

  // Memoize previous period lines to prevent unnecessary re-renders
  const previousLines = React.useMemo(
    () =>
      metrics.map((metric, index) => {
        const previousKey = `${metric.key}Previous`
        if (!visibleMetrics[previousKey]) return null
        const color = metric.color || defaultColors[index % defaultColors.length]
        return (
          <Line
            key={previousKey}
            type="monotone"
            dataKey={previousKey}
            stroke={color}
            strokeWidth={2}
            strokeDasharray="5 5"
            dot={false}
            opacity={0.5}
            connectNulls={false}
          />
        )
      }),
    [metrics, visibleMetrics, defaultColors]
  )

  return (
    <Panel variant={borderless ? 'borderless' : 'default'} className={className}>
      <PanelHeader className="flex-col items-start gap-3 md:gap-4">
        {title && <PanelTitle>{title}</PanelTitle>}
        <div
          className={cn(
            'flex w-full',
            // Stack on mobile, row on tablet+
            'flex-col gap-3 md:flex-row md:items-center md:justify-between md:gap-4'
          )}
        >
          <div
            className={cn(
              'flex items-center',
              // Wrap metrics on mobile, gap adjustment
              'flex-wrap gap-3 md:gap-6'
            )}
          >
            {metrics.map((metric, index) => {
              const color = metric.color || defaultColors[index % defaultColors.length]
              const isCurrentVisible = visibleMetrics[metric.key]
              const isPreviousVisible = visibleMetrics[`${metric.key}Previous`]
              return (
                <div key={metric.key} className="flex flex-col gap-1">
                  <span className="text-xs font-medium text-neutral-500 leading-none">{metric.label}</span>
                  <div className="flex items-baseline gap-2">
                    {metric.value != null && (
                      <button
                        onClick={() => toggleMetric(metric.key)}
                        aria-label={`Toggle ${metric.label} visibility`}
                        aria-pressed={isCurrentVisible}
                        className={cn(
                          'flex items-center gap-1.5 cursor-pointer transition-opacity',
                          // Ensure 44px minimum touch target on mobile
                          'min-h-[44px] md:min-h-0 py-2 md:py-0',
                          !isCurrentVisible && 'opacity-50'
                        )}
                      >
                        <svg width="12" height="3" className="shrink-0">
                          <line x1="0" y1="1.5" x2="12" y2="1.5" style={{ stroke: color }} strokeWidth="3" />
                        </svg>
                        <span
                          className={cn(
                            'text-sm font-semibold text-neutral-900 leading-none tabular-nums',
                            !isCurrentVisible && 'line-through'
                          )}
                        >
                          {metric.value}
                        </span>
                      </button>
                    )}
                    {metric.previousValue != null && (
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
                            'text-xs text-neutral-500 leading-none tabular-nums',
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
          <ChartContainer config={chartConfig} className="h-[180px] md:h-[220px] lg:h-[250px] w-full">
            <RechartsLineChart data={chartData} margin={{ left: 24 }}>
              <CartesianGrid vertical={false} horizontal={true} stroke="#e5e7eb" strokeDasharray="0" />
              <XAxis
                dataKey="date"
                tickLine={false}
                axisLine={false}
                tickMargin={8}
                minTickGap={32}
                interval={data.length <= 8 ? 0 : Math.ceil(data.length / 8) - 1}
                tick={({ x, y, payload, index, visibleTicksCount }) => {
                  // Determine text anchor based on position
                  // First tick: start (left-aligned), Last tick: end (right-aligned), Middle: middle
                  const isFirst = index === 0
                  const isLast = visibleTicksCount ? index === visibleTicksCount - 1 : false
                  const textAnchor = isFirst ? 'start' : isLast ? 'end' : 'middle'

                  // Format the label (same logic as tickFormatter)
                  const value = payload.value
                  let formattedLabel: string

                  // Handle week format "YYYYWW" (e.g., "202539" = week 39 of 2025)
                  if (timeframe === 'weekly' && /^\d{6}$/.test(value)) {
                    const year = parseInt(value.substring(0, 4), 10)
                    const week = parseInt(value.substring(4, 6), 10)
                    const firstDayOfYear = new Date(year, 0, 1)
                    const dayOfWeek = firstDayOfYear.getDay()
                    const daysToMonday = dayOfWeek === 0 ? 1 : dayOfWeek === 1 ? 0 : 8 - dayOfWeek
                    const firstMonday = new Date(year, 0, 1 + daysToMonday)
                    const startDate = new Date(firstMonday)
                    startDate.setDate(firstMonday.getDate() + (week - 1) * 7)
                    const endDate = new Date(startDate)
                    endDate.setDate(startDate.getDate() + 6)
                    formattedLabel = `${startDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })} - ${endDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })}`
                  } else if (timeframe === 'monthly' && /^\d{6}$/.test(value)) {
                    // Handle month format "YYYYMM"
                    const year = parseInt(value.substring(0, 4), 10)
                    const month = parseInt(value.substring(4, 6), 10) - 1
                    const date = new Date(year, month, 1)
                    formattedLabel = date.toLocaleDateString('en-US', { month: 'long' })
                  } else {
                    const date = new Date(value)
                    if (timeframe === 'monthly') {
                      formattedLabel = date.toLocaleDateString('en-US', { month: 'long' })
                    } else if (timeframe === 'weekly') {
                      const endDate = new Date(date)
                      endDate.setDate(endDate.getDate() + 6)
                      formattedLabel = `${date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })} - ${endDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })}`
                    } else {
                      formattedLabel = date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
                    }
                  }

                  return (
                    <text x={x} y={y + 8} fill="#9ca3af" fontSize={12} textAnchor={textAnchor}>
                      {formattedLabel}
                    </text>
                  )
                }}
                tickFormatter={(value) => {
                  // Handle week format "YYYYWW" (e.g., "202539" = week 39 of 2025)
                  if (timeframe === 'weekly' && /^\d{6}$/.test(value)) {
                    const year = parseInt(value.substring(0, 4), 10)
                    const week = parseInt(value.substring(4, 6), 10)
                    // Get the first day of the year
                    const firstDayOfYear = new Date(year, 0, 1)
                    // Calculate days to add: (week - 1) * 7, then adjust to Monday
                    const dayOfWeek = firstDayOfYear.getDay()
                    const daysToMonday = dayOfWeek === 0 ? 1 : dayOfWeek === 1 ? 0 : 8 - dayOfWeek
                    const firstMonday = new Date(year, 0, 1 + daysToMonday)
                    const startDate = new Date(firstMonday)
                    startDate.setDate(firstMonday.getDate() + (week - 1) * 7)
                    const endDate = new Date(startDate)
                    endDate.setDate(startDate.getDate() + 6)
                    return `${startDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })} - ${endDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })}`
                  }
                  // Handle month format "YYYYMM" (e.g., "202509" = September 2025)
                  if (timeframe === 'monthly' && /^\d{6}$/.test(value)) {
                    const year = parseInt(value.substring(0, 4), 10)
                    const month = parseInt(value.substring(4, 6), 10) - 1
                    const date = new Date(year, month, 1)
                    return date.toLocaleDateString('en-US', { month: 'long' })
                  }
                  const date = new Date(value)
                  if (timeframe === 'monthly') {
                    return date.toLocaleDateString('en-US', {
                      month: 'long',
                    })
                  } else if (timeframe === 'weekly') {
                    // Format as "Month Day to Month Day"
                    const endDate = new Date(date)
                    endDate.setDate(endDate.getDate() + 6)
                    return `${date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })} - ${endDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })}`
                  } else {
                    return date.toLocaleDateString('en-US', {
                      month: 'short',
                      day: 'numeric',
                    })
                  }
                }}
              />
              <YAxis
                orientation="right"
                tickLine={false}
                axisLine={false}
                tickMargin={8}
                tickCount={5}
                allowDecimals={false}
                tick={{ fill: '#9ca3af', fontSize: 12 }}
                tickFormatter={(value) => formatCompactNumber(value)}
              />
              <ChartTooltip
                content={({ active, payload, label }) => {
                  if (!active || !payload || !payload.length) return null

                  let formattedDate: string
                  let dayOfWeek: string = ''

                  // Handle week format "YYYYWW" (e.g., "202539" = week 39 of 2025)
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
                    formattedDate = `${startDate.toLocaleDateString('en-US', { month: 'long', day: 'numeric' })} to ${endDate.toLocaleDateString('en-US', { month: 'long', day: 'numeric' })}`
                  }
                  // Handle month format "YYYYMM" (e.g., "202509" = September 2025)
                  else if (timeframe === 'monthly' && /^\d{6}$/.test(label)) {
                    const year = parseInt(label.substring(0, 4), 10)
                    const month = parseInt(label.substring(4, 6), 10) - 1
                    const startDate = new Date(year, month, 1)
                    const endDate = new Date(year, month + 1, 0) // Last day of month
                    formattedDate = `${startDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })} to ${endDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })}`
                  } else {
                    const date = new Date(label)
                    if (timeframe === 'monthly') {
                      formattedDate = date.toLocaleDateString('en-US', {
                        month: 'short',
                        day: 'numeric',
                      })
                      const endDate = new Date(date)
                      endDate.setMonth(endDate.getMonth() + 1)
                      endDate.setDate(0) // Last day of the month
                      const endFormatted = endDate.toLocaleDateString('en-US', {
                        month: 'short',
                        day: 'numeric',
                      })
                      formattedDate = `${formattedDate} to ${endFormatted}`
                    } else if (timeframe === 'weekly') {
                      formattedDate = date.toLocaleDateString('en-US', {
                        month: 'long',
                        day: 'numeric',
                      })
                      const endDate = new Date(date)
                      endDate.setDate(endDate.getDate() + 6)
                      const endFormatted = endDate.toLocaleDateString('en-US', {
                        month: 'long',
                        day: 'numeric',
                      })
                      formattedDate = `${formattedDate} to ${endFormatted}`
                    } else {
                      formattedDate = date.toLocaleDateString('en-US', {
                        day: 'numeric',
                        month: 'short',
                      })
                      dayOfWeek = date.toLocaleDateString('en-US', { weekday: 'short' })
                    }
                  }

                  // Group payload by metric (current + previous together)
                  const groupedData: TooltipPayloadEntry[] = []
                  metrics.forEach((metric) => {
                    // Find current entry - check original key first, then _solid/_dotted variants
                    let currentEntry = payload.find((p) => (p as TooltipPayloadEntry).dataKey === metric.key) as
                      | TooltipPayloadEntry
                      | undefined
                    if (!currentEntry) {
                      // When hasIncompleteData is true, check _solid and _dotted keys
                      const solidEntry = payload.find(
                        (p) => (p as TooltipPayloadEntry).dataKey === `${metric.key}_solid`
                      ) as TooltipPayloadEntry | undefined
                      const dottedEntry = payload.find(
                        (p) => (p as TooltipPayloadEntry).dataKey === `${metric.key}_dotted`
                      ) as TooltipPayloadEntry | undefined
                      // Use whichever has a non-null value
                      if (solidEntry && solidEntry.value != null) {
                        currentEntry = { ...solidEntry, dataKey: metric.key }
                      } else if (dottedEntry && dottedEntry.value != null) {
                        currentEntry = { ...dottedEntry, dataKey: metric.key }
                      }
                    }
                    const previousEntry = payload.find(
                      (p) => (p as TooltipPayloadEntry).dataKey === `${metric.key}Previous`
                    ) as TooltipPayloadEntry | undefined

                    if (currentEntry) {
                      groupedData.push(currentEntry)
                    }
                    if (previousEntry) {
                      groupedData.push(previousEntry)
                    }
                  })

                  // Get previous period date from data (index-based alignment)
                  // When comparing periods of different lengths, previousDate may be null
                  const dataPoint = data.find((d) => d.date === label)
                  const previousDateStr = dataPoint?.previousDate as string | null | undefined
                  let prevFormatted: string | null = null
                  let prevDayOfWeek: string = ''

                  if (previousDateStr) {
                    const previousDate = new Date(previousDateStr)
                    // Cap end date to actual PP end date (compareDateTo) to handle partial periods
                    const actualPpEndDate = compareDateTo ? new Date(compareDateTo) : null

                    if (timeframe === 'monthly') {
                      prevFormatted = previousDate.toLocaleDateString('en-US', {
                        month: 'short',
                        day: 'numeric',
                      })
                      let prevEndDate = new Date(previousDate)
                      prevEndDate.setMonth(prevEndDate.getMonth() + 1)
                      prevEndDate.setDate(0) // Last day of month
                      // Cap to actual PP end date if it's earlier
                      if (actualPpEndDate && actualPpEndDate < prevEndDate) {
                        prevEndDate = actualPpEndDate
                      }
                      const prevEndFormatted = prevEndDate.toLocaleDateString('en-US', {
                        month: 'short',
                        day: 'numeric',
                      })
                      prevFormatted = `${prevFormatted} To ${prevEndFormatted}`
                    } else if (timeframe === 'weekly') {
                      prevFormatted = previousDate.toLocaleDateString('en-US', {
                        month: 'long',
                        day: 'numeric',
                      })
                      let prevEndDate = new Date(previousDate)
                      prevEndDate.setDate(prevEndDate.getDate() + 6) // End of week
                      // Cap to actual PP end date if it's earlier
                      if (actualPpEndDate && actualPpEndDate < prevEndDate) {
                        prevEndDate = actualPpEndDate
                      }
                      const prevEndFormatted = prevEndDate.toLocaleDateString('en-US', {
                        month: 'long',
                        day: 'numeric',
                      })
                      prevFormatted = `${prevFormatted} To ${prevEndFormatted}`
                    } else {
                      prevFormatted = previousDate.toLocaleDateString('en-US', {
                        day: 'numeric',
                        month: 'short',
                      })
                      prevDayOfWeek = previousDate.toLocaleDateString('en-US', { weekday: 'short' })
                    }
                  }

                  return (
                    <div className="rounded bg-neutral-800 px-2.5 py-2 shadow-md">
                      <div className="mb-2 text-xs font-medium text-neutral-100">
                        {dayOfWeek ? `${formattedDate} (${dayOfWeek})` : formattedDate}
                      </div>
                      <div className="space-y-1.5">
                        {groupedData.map((entry) => {
                          const isPrevious = entry.dataKey.includes('Previous')
                          const baseKey = entry.dataKey.replace('Previous', '')
                          const baseMetric = metrics.find((m) => m.key === baseKey)

                          if (!baseMetric) return null

                          // Skip rendering previous period row if no data for this index
                          // (happens when PP is shorter than main period)
                          if (isPrevious && !prevFormatted) return null

                          const color = baseMetric.color || entry.color
                          const displayLabel = isPrevious
                            ? prevDayOfWeek
                              ? `${prevFormatted} (${prevDayOfWeek})`
                              : prevFormatted
                            : baseMetric.label

                          return (
                            <div key={entry.dataKey} className="flex items-center justify-between gap-4">
                              <div className="flex items-center gap-2 text-xs">
                                <svg width="12" height="3" className={isPrevious ? 'shrink-0 opacity-50' : 'shrink-0'}>
                                  <line
                                    x1="0"
                                    y1="1.5"
                                    x2="12"
                                    y2="1.5"
                                    style={{ stroke: color }}
                                    strokeWidth="3"
                                    strokeDasharray={isPrevious ? '3 2' : '0'}
                                  />
                                </svg>
                                <span className="text-neutral-100">{displayLabel}</span>
                              </div>
                              <span className="font-medium text-neutral-100 tabular-nums">{entry.value}</span>
                            </div>
                          )
                        })}
                      </div>
                    </div>
                  )
                }}
              />
              {currentLines}
              {previousLines}
            </RechartsLineChart>
          </ChartContainer>
        )}
      </PanelContent>
    </Panel>
  )
}
