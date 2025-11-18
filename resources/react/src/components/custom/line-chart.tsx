import * as React from 'react'
import { CartesianGrid, Line, LineChart as RechartsLineChart, XAxis, YAxis } from 'recharts'

import { Card, CardContent, CardHeader, CardTitle } from '@components/ui/card'
import type { ChartConfig } from '@components/ui/chart'
import {
  ChartContainer,
  ChartLegend,
  ChartLegendContent,
  ChartTooltip,
  ChartTooltipContent,
} from '@components/ui/chart'
import { Button } from '@components/ui/button'

export interface LineChartDataPoint {
  date: string
  [key: string]: string | number
}

export interface LineChartMetric {
  key: string
  label: string
  color?: string
  enabled?: boolean
}

export interface LineChartProps {
  data: LineChartDataPoint[]
  metrics: LineChartMetric[]
  title?: string
  showPreviousPeriod?: boolean
  showLegend?: boolean
  timeframe?: 'Daily' | 'Weekly' | 'Monthly'
  onTimeframeChange?: (timeframe: 'Daily' | 'Weekly' | 'Monthly') => void
  className?: string
}

export function LineChart({
  data,
  metrics,
  title,
  showPreviousPeriod = true,
  showLegend = true,
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
        <div className="flex items-center gap-4">
          {title && <CardTitle className="text-base font-medium">{title}</CardTitle>}
          <div className="flex items-center gap-2">
            <span className="text-sm text-muted-foreground">Visitors</span>
            <span className="text-lg font-semibold">5</span>
            <span className="text-sm text-blue-600">↓ 4</span>
            <span className="text-sm text-muted-foreground ml-4">Views</span>
            <span className="text-lg font-semibold">15</span>
            <span className="text-sm text-blue-600">↔ 2</span>
          </div>
        </div>
        <div className="flex items-center gap-2">
          {showPreviousPeriod && (
            <Button
              variant="ghost"
              size="sm"
              onClick={togglePreviousPeriod}
              className="h-8 text-xs text-muted-foreground hover:text-foreground"
              data-active={previousPeriodVisible}
            >
              -- Previous period
            </Button>
          )}
          {onTimeframeChange && (
            <select
              value={timeframe}
              onChange={(e) => onTimeframeChange(e.target.value as 'Daily' | 'Weekly' | 'Monthly')}
              className="h-8 rounded-md border border-input bg-background px-3 py-1 text-sm"
            >
              <option value="Daily">Daily</option>
              <option value="Weekly">Weekly</option>
              <option value="Monthly">Monthly</option>
            </select>
          )}
        </div>
      </CardHeader>
      <CardContent className="px-2 pb-4 pt-0 sm:px-6">
        <ChartContainer config={chartConfig} className="h-[250px] w-full">
          <RechartsLineChart
            data={data}
            margin={{
              left: 12,
              right: 12,
              top: 12,
              bottom: 12,
            }}
          >
            <CartesianGrid vertical={false} strokeDasharray="3 3" stroke="#e5e7eb" />
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
              tickLine={false}
              axisLine={false}
              tickMargin={8}
              tickCount={8}
              tick={{ fill: '#9ca3af', fontSize: 12 }}
            />
            <ChartTooltip
              content={
                <ChartTooltipContent
                  className="w-[180px]"
                  labelFormatter={(value) => {
                    return new Date(value).toLocaleDateString('en-US', {
                      month: 'short',
                      day: 'numeric',
                      year: 'numeric',
                    })
                  }}
                />
              }
            />
            {showLegend && (
              <ChartLegend
                content={<ChartLegendContent />}
                onClick={(data) => {
                  if (data.dataKey) {
                    const key = data.dataKey.toString().replace('Previous', '')
                    setVisibleMetrics((prev) => ({
                      ...prev,
                      [key]: !prev[key],
                    }))
                  }
                }}
              />
            )}
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
