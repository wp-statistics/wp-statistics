import type { ChartConfig } from '@components/ui/chart'
import { ChartContainer, ChartTooltip } from '@components/ui/chart'
import { Panel, PanelContent, PanelHeader, PanelTitle } from '@components/ui/panel'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@components/ui/select'
import { Loader2 } from 'lucide-react'
import * as React from 'react'
import { CartesianGrid, Line, LineChart as RechartsLineChart, XAxis, YAxis } from 'recharts'

export interface LineChartDataPoint {
  date: string
  [key: string]: string | number
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

  const toggleAllPreviousPeriod = () => {
    setVisibleMetrics((prev) => {
      const newState = { ...prev }
      const anyPreviousVisible = metrics.some((metric) => prev[`${metric.key}Previous`])

      metrics.forEach((metric) => {
        newState[`${metric.key}Previous`] = !anyPreviousVisible
      })

      return newState
    })
  }

  const isAnyPreviousVisible = metrics.some((metric) => visibleMetrics[`${metric.key}Previous`])

  // Memoize current period lines to prevent unnecessary re-renders
  const currentLines = React.useMemo(
    () =>
      metrics.map((metric, index) => {
        if (!visibleMetrics[metric.key]) return null
        const color = metric.color || defaultColors[index % defaultColors.length]
        return <Line key={metric.key} type="monotone" dataKey={metric.key} stroke={color} strokeWidth={2} dot={false} />
      }),
    [metrics, visibleMetrics, defaultColors]
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
          />
        )
      }),
    [metrics, visibleMetrics, defaultColors]
  )

  return (
    <Panel className={className}>
      <PanelHeader className="flex-col items-start gap-4">
        {title && <PanelTitle>{title}</PanelTitle>}
        <div className="flex items-center justify-between gap-4 w-full">
            <div className="flex items-center gap-6">
              {metrics.map((metric, index) => {
                const color = metric.color || defaultColors[index % defaultColors.length]
                const isCurrentVisible = visibleMetrics[metric.key]
                const isPreviousVisible = visibleMetrics[`${metric.key}Previous`]
                return (
                  <div key={metric.key} className="flex flex-col gap-1">
                    <span className="text-xs font-medium text-neutral-500 leading-none">{metric.label}</span>
                    <div className="flex items-baseline gap-2">
                      {metric.value && (
                        <button
                          onClick={() => toggleMetric(metric.key)}
                          className={`flex items-center gap-1.5 cursor-pointer transition-opacity ${!isCurrentVisible ? 'opacity-50' : ''}`}
                        >
                          <svg width="12" height="3" className="shrink-0">
                            <line x1="0" y1="1.5" x2="12" y2="1.5" style={{ stroke: color }} strokeWidth="3" />
                          </svg>
                          <span
                            className={`text-sm font-semibold text-neutral-900 leading-none tabular-nums ${!isCurrentVisible ? 'line-through' : ''}`}
                          >
                            {metric.value}
                          </span>
                        </button>
                      )}
                      {metric.previousValue && (
                        <button
                          onClick={() => toggleMetric(`${metric.key}Previous`)}
                          className={`flex items-center gap-1.5 cursor-pointer transition-opacity ${!isPreviousVisible ? 'opacity-50' : ''}`}
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
                            className={`text-sm text-neutral-500 leading-none tabular-nums ${!isPreviousVisible ? 'line-through' : ''}`}
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
            <div className="flex items-center gap-3">
              {showPreviousPeriod && (
                <button
                  onClick={toggleAllPreviousPeriod}
                  className={`flex items-center gap-1.5 text-xs text-neutral-500 transition-colors cursor-pointer ${!isAnyPreviousVisible ? 'opacity-50' : ''}`}
                >
                  <svg width="12" height="3" className="shrink-0 opacity-50">
                    <line
                      x1="0"
                      y1="1.5"
                      x2="12"
                      y2="1.5"
                      stroke="currentColor"
                      strokeWidth="3"
                      strokeDasharray="3 2"
                    />
                  </svg>
                  <span className={!isAnyPreviousVisible ? 'line-through' : ''}>Previous period</span>
                </button>
              )}
              {onTimeframeChange && (
                <Select value={timeframe} onValueChange={onTimeframeChange}>
                  <SelectTrigger className="w-[100px] h-8 text-sm font-medium">
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
          <div className="flex h-[250px] items-center justify-center">
            <Loader2 className="h-6 w-6 animate-spin text-muted-foreground" />
          </div>
        ) : (
          <ChartContainer config={chartConfig} className="h-[250px] w-full">
          <RechartsLineChart data={data} margin={{ left: 24 }}>
            <CartesianGrid vertical={false} horizontal={true} stroke="#e5e7eb" strokeDasharray="0" />
            <XAxis
              dataKey="date"
              tickLine={false}
              axisLine={false}
              tickMargin={8}
              minTickGap={timeframe === 'monthly' ? 0 : 32}
              interval={timeframe === 'monthly' ? 0 : 'preserveStartEnd'}
              tick={{ fill: '#9ca3af', fontSize: 12 }}
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
                const groupedData: any[] = []
                metrics.forEach((metric) => {
                  const currentEntry = payload.find((p: any) => p.dataKey === metric.key)
                  const previousEntry = payload.find((p: any) => p.dataKey === `${metric.key}Previous`)

                  if (currentEntry) {
                    groupedData.push(currentEntry)
                  }
                  if (previousEntry) {
                    groupedData.push(previousEntry)
                  }
                })

                // Calculate previous period date
                const currentDate = new Date(label)
                const previousDate = new Date(currentDate)
                let prevFormatted: string
                let prevDayOfWeek: string

                if (timeframe === 'monthly') {
                  previousDate.setMonth(previousDate.getMonth() - 1)
                  prevFormatted = previousDate.toLocaleDateString('en-US', {
                    month: 'short',
                    day: 'numeric',
                  })
                  const prevEndDate = new Date(previousDate)
                  prevEndDate.setMonth(prevEndDate.getMonth() + 1)
                  prevEndDate.setDate(0)
                  const prevEndFormatted = prevEndDate.toLocaleDateString('en-US', {
                    month: 'short',
                    day: 'numeric',
                  })
                  prevFormatted = `${prevFormatted} To ${prevEndFormatted}`
                  prevDayOfWeek = ''
                } else if (timeframe === 'weekly') {
                  previousDate.setDate(previousDate.getDate() - 35) // Go back 5 weeks
                  prevFormatted = previousDate.toLocaleDateString('en-US', {
                    month: 'long',
                    day: 'numeric',
                  })
                  const prevEndDate = new Date(previousDate)
                  prevEndDate.setDate(prevEndDate.getDate() + 6)
                  const prevEndFormatted = prevEndDate.toLocaleDateString('en-US', {
                    month: 'long',
                    day: 'numeric',
                  })
                  prevFormatted = `${prevFormatted} To ${prevEndFormatted}`
                  prevDayOfWeek = ''
                } else {
                  previousDate.setDate(previousDate.getDate() - 30)
                  prevFormatted = previousDate.toLocaleDateString('en-US', {
                    day: 'numeric',
                    month: 'short',
                  })
                  prevDayOfWeek = previousDate.toLocaleDateString('en-US', { weekday: 'short' })
                }

                return (
                  <div className="rounded-lg border bg-background p-3 shadow-md">
                    <div className="mb-3 text-sm leading-normal font-medium text-neutral-700">
                      {dayOfWeek ? `${formattedDate} (${dayOfWeek})` : formattedDate}
                    </div>
                    <div className="space-y-2">
                      {groupedData.map((entry: any) => {
                        const isPrevious = entry.dataKey.includes('Previous')
                        const baseKey = entry.dataKey.replace('Previous', '')
                        const baseMetric = metrics.find((m) => m.key === baseKey)

                        if (!baseMetric) return null

                        const color = baseMetric.color || entry.color
                        const displayLabel = isPrevious
                          ? prevDayOfWeek
                            ? `${prevFormatted} (${prevDayOfWeek})`
                            : prevFormatted
                          : baseMetric.label

                        return (
                          <div key={entry.dataKey} className="flex items-center justify-between gap-6">
                            <div className="flex items-center gap-2 text-sm">
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
                              <span className="text-neutral-500">{displayLabel}</span>
                            </div>
                            <span className="font-semibold text-neutral-900 tabular-nums">{entry.value}</span>
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
