import * as React from 'react'
import { CartesianGrid, Line, LineChart as RechartsLineChart, XAxis, YAxis } from 'recharts'

import { Card, CardContent, CardHeader, CardTitle } from '@components/ui/card'
import type { ChartConfig } from '@components/ui/chart'
import { ChartContainer, ChartTooltip } from '@components/ui/chart'

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
  timeframe?: 'Daily' | 'Weekly' | 'Monthly'
  onTimeframeChange?: (timeframe: 'Daily' | 'Weekly' | 'Monthly') => void
  className?: string
}

export function LineChart({
  data,
  metrics,
  title,
  showPreviousPeriod = true,
  timeframe = 'Daily',
  onTimeframeChange,
  className,
}: LineChartProps) {
  const [previousPeriodVisible, setPreviousPeriodVisible] = React.useState(showPreviousPeriod)
  const [visibleMetrics, setVisibleMetrics] = React.useState<Record<string, boolean>>(
    metrics.reduce(
      (acc, metric) => ({
        ...acc,
        [metric.key]: metric.enabled !== false,
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

  const togglePreviousPeriod = () => {
    setPreviousPeriodVisible(!previousPeriodVisible)
  }

  return (
    <Card className={className}>
      <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-4">
        <div className="flex flex-col gap-4 w-full">
          {title && <CardTitle>{title}</CardTitle>}
          <div className="flex items-center justify-between gap-4 w-full">
            <div className="flex items-center gap-6">
              {metrics.map((metric, index) => {
                const color = metric.color || defaultColors[index % defaultColors.length]
                return (
                  <div key={metric.key} className="flex flex-col gap-1">
                    <span className="text-xs italic text-muted-foreground leading-none">{metric.label}</span>
                    <div className="flex items-baseline gap-2">
                      {metric.value && (
                        <div className="flex items-center gap-1.5">
                          <svg width="12" height="3" className="shrink-0">
                            <line x1="0" y1="1.5" x2="12" y2="1.5" style={{ stroke: color }} strokeWidth="3" />
                          </svg>
                          <span className="text-sm font-medium leading-none">{metric.value}</span>
                        </div>
                      )}
                      {metric.previousValue && (
                        <div className="flex items-center gap-1.5">
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
                          <span className="text-sm text-muted-foreground leading-none">{metric.previousValue}</span>
                        </div>
                      )}
                    </div>
                  </div>
                )
              })}
            </div>
            <div className="flex items-center gap-3">
              {showPreviousPeriod && (
                <button
                  onClick={togglePreviousPeriod}
                  className="flex items-center gap-1.5 text-sm italic text-muted-foreground hover:text-foreground transition-colors cursor-pointer"
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
                  <span>Previous period</span>
                </button>
              )}
              {onTimeframeChange && (
                <div className="relative">
                  <select
                    value={timeframe}
                    onChange={(e) => onTimeframeChange(e.target.value as 'Daily' | 'Weekly' | 'Monthly')}
                    className="appearance-none h-10 rounded-lg border border-input bg-background pl-4 pr-10 py-2 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-ring cursor-pointer"
                  >
                    <option value="Daily">Daily</option>
                    <option value="Weekly">Weekly</option>
                    <option value="Monthly">Monthly</option>
                  </select>
                  <svg
                    className="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 pointer-events-none text-muted-foreground"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
                  </svg>
                </div>
              )}
            </div>
          </div>
        </div>
      </CardHeader>
      <CardContent className="">
        <ChartContainer config={chartConfig} className="h-[250px] w-full">
          <RechartsLineChart data={data} margin={{}}>
            <CartesianGrid vertical={false} horizontal={true} stroke="#e5e7eb" strokeDasharray="0" />
            <XAxis
              dataKey="date"
              tickLine={false}
              axisLine={false}
              tickMargin={8}
              minTickGap={32}
              tick={{ fill: '#9ca3af', fontSize: 12 }}
              tickFormatter={(value) => {
                const date = new Date(value)
                return date.toLocaleDateString('en-US', {
                  month: 'short',
                  day: 'numeric',
                })
              }}
            />
            <YAxis
              orientation="right"
              tickLine={false}
              axisLine={false}
              tickMargin={8}
              tickCount={8}
              tick={{ fill: '#9ca3af', fontSize: 12 }}
            />
            <ChartTooltip
              content={({ active, payload, label }) => {
                if (!active || !payload || !payload.length) return null

                const date = new Date(label)
                const formattedDate = date.toLocaleDateString('en-US', {
                  day: 'numeric',
                  month: 'short',
                })
                const dayOfWeek = date.toLocaleDateString('en-US', { weekday: 'short' })

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
                previousDate.setDate(previousDate.getDate() - 7)
                const prevFormatted = previousDate.toLocaleDateString('en-US', {
                  day: 'numeric',
                  month: 'short',
                })
                const prevDayOfWeek = previousDate.toLocaleDateString('en-US', { weekday: 'short' })

                return (
                  <div className="rounded-lg border bg-background p-3 shadow-md">
                    <div className="mb-3 text-sm leading-normal italic font-normal">
                      {formattedDate} ({dayOfWeek})
                    </div>
                    <div className="space-y-2">
                      {groupedData.map((entry: any) => {
                        const isPrevious = entry.dataKey.includes('Previous')
                        const baseKey = entry.dataKey.replace('Previous', '')
                        const baseMetric = metrics.find((m) => m.key === baseKey)

                        if (!baseMetric) return null

                        const color = baseMetric.color || entry.color
                        const displayLabel = isPrevious ? `${prevFormatted} (${prevDayOfWeek})` : baseMetric.label

                        return (
                          <div key={entry.dataKey} className="flex items-center justify-between gap-6">
                            <div className="flex items-center gap-2 text-sm italic">
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
                              <span className="font-normal text-muted-foreground">{displayLabel}</span>
                            </div>
                            <span className="font-semibold text-card-foreground">{entry.value}</span>
                          </div>
                        )
                      })}
                    </div>
                  </div>
                )
              }}
            />
            {metrics.map((metric, index) => {
              if (!visibleMetrics[metric.key]) return null
              const color = metric.color || defaultColors[index % defaultColors.length]
              return (
                <Line
                  key={metric.key}
                  type="monotone"
                  dataKey={metric.key}
                  stroke={color}
                  strokeWidth={2}
                  dot={false}
                />
              )
            })}
            {previousPeriodVisible &&
              metrics.map((metric, index) => {
                if (!visibleMetrics[metric.key]) return null
                const previousKey = `${metric.key}Previous`
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
              })}
          </RechartsLineChart>
        </ChartContainer>
      </CardContent>
    </Card>
  )
}
