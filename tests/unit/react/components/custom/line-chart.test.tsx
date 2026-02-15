import { fireEvent, render, screen } from '@testing-library/react'
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest'

import { LineChart, type LineChartDataPoint, type LineChartMetric } from '@components/custom/line-chart'

// Mock recharts - component uses ComposedChart, not LineChart
vi.mock('recharts', () => ({
  ComposedChart: ({ children }: { children: React.ReactNode }) => <div data-testid="recharts-line-chart">{children}</div>,
  Line: ({ dataKey }: { dataKey: string }) => <div data-testid={`line-${dataKey}`} />,
  Bar: ({ dataKey }: { dataKey: string }) => <div data-testid={`bar-${dataKey}`} />,
  XAxis: () => <div data-testid="x-axis" />,
  YAxis: () => <div data-testid="y-axis" />,
  CartesianGrid: () => <div data-testid="cartesian-grid" />,
}))

// Mock the chart UI components
vi.mock('@components/ui/chart', () => ({
  ChartContainer: ({ children, className }: { children: React.ReactNode; className?: string }) => (
    <div data-testid="chart-container" className={className}>
      {children}
    </div>
  ),
  ChartTooltip: () => <div data-testid="chart-tooltip" />,
}))

// Mock Panel components
vi.mock('@components/ui/panel', () => ({
  Panel: ({ children, className }: { children: React.ReactNode; className?: string }) => (
    <div data-testid="panel" className={className}>{children}</div>
  ),
  PanelHeader: ({ children, className }: { children: React.ReactNode; className?: string }) => (
    <div data-testid="panel-header" className={className}>{children}</div>
  ),
  PanelTitle: ({ children }: { children: React.ReactNode }) => <h3>{children}</h3>,
  PanelContent: ({ children }: { children: React.ReactNode }) => <div data-testid="panel-content">{children}</div>,
  PanelActions: ({ children }: { children: React.ReactNode }) => <div>{children}</div>,
}))

// Mock Select components
vi.mock('@components/ui/select', () => ({
  Select: ({ children, value }: { children: React.ReactNode; value?: string }) => (
    <div data-testid="select" data-value={value}>{children}</div>
  ),
  SelectTrigger: ({ children, className }: { children: React.ReactNode; className?: string }) => (
    <button role="combobox" className={className}>{children}</button>
  ),
  SelectValue: () => <span />,
  SelectContent: ({ children }: { children: React.ReactNode }) => <div>{children}</div>,
  SelectItem: ({ children, value }: { children: React.ReactNode; value: string }) => (
    <div data-value={value}>{children}</div>
  ),
}))

// Mock useBreakpoint hook
vi.mock('@/hooks/use-breakpoint', () => ({
  useBreakpoint: () => ({ isMobile: false }),
}))

// Mock utils
vi.mock('@/lib/utils', () => ({
  cn: (...args: unknown[]) => args.filter(Boolean).join(' '),
  formatCompactNumber: (n: number) => n.toLocaleString(),
  isToday: (dateStr: string) => {
    const today = new Date()
    const date = new Date(dateStr)
    return (
      date.getFullYear() === today.getFullYear() &&
      date.getMonth() === today.getMonth() &&
      date.getDate() === today.getDate()
    )
  },
}))

describe('LineChart Component', () => {
  // Sample test data
  const mockData: LineChartDataPoint[] = [
    { date: '2025-01-01', visitors: 100, visitorsPrevious: 80 },
    { date: '2025-01-02', visitors: 150, visitorsPrevious: 90 },
    { date: '2025-01-03', visitors: 120, visitorsPrevious: 100 },
  ]

  const mockMetrics: LineChartMetric[] = [
    {
      key: 'visitors',
      label: 'Visitors',
      color: 'var(--chart-1)',
      value: '370',
      previousValue: '270',
    },
  ]

  const multipleMetrics: LineChartMetric[] = [
    { key: 'visitors', label: 'Visitors', color: 'var(--chart-1)', value: '370', previousValue: '270' },
    { key: 'views', label: 'Views', color: 'var(--chart-2)', value: '1200', previousValue: '900' },
  ]

  beforeEach(() => {
    vi.clearAllMocks()
  })

  afterEach(() => {
    vi.useRealTimers()
  })

  describe('Basic Rendering', () => {
    it('renders with title when provided', () => {
      render(<LineChart data={mockData} metrics={mockMetrics} title="Traffic Trends" />)
      expect(screen.getByText('Traffic Trends')).toBeInTheDocument()
    })

    it('renders without title when not provided', () => {
      render(<LineChart data={mockData} metrics={mockMetrics} />)
      expect(screen.queryByText('Traffic Trends')).not.toBeInTheDocument()
    })

    it('renders metric labels', () => {
      render(<LineChart data={mockData} metrics={mockMetrics} />)
      expect(screen.getByText('Visitors')).toBeInTheDocument()
    })

    it('renders metric values', () => {
      render(<LineChart data={mockData} metrics={mockMetrics} />)
      expect(screen.getByText('370')).toBeInTheDocument()
    })

    it('renders previous period values when provided', () => {
      render(<LineChart data={mockData} metrics={mockMetrics} showPreviousPeriod={true} />)
      expect(screen.getByText('270')).toBeInTheDocument()
    })

    it('renders multiple metrics', () => {
      render(<LineChart data={mockData} metrics={multipleMetrics} />)
      expect(screen.getByText('Visitors')).toBeInTheDocument()
      expect(screen.getByText('Views')).toBeInTheDocument()
      expect(screen.getByText('370')).toBeInTheDocument()
      expect(screen.getByText('1200')).toBeInTheDocument()
    })

    it('renders chart container', () => {
      render(<LineChart data={mockData} metrics={mockMetrics} />)
      expect(screen.getByTestId('chart-container')).toBeInTheDocument()
    })
  })

  describe('Loading State', () => {
    it('shows loading spinner when loading=true', () => {
      render(<LineChart data={mockData} metrics={mockMetrics} loading={true} />)
      const loader = document.querySelector('.animate-spin')
      expect(loader).toBeInTheDocument()
    })

    it('hides chart when loading', () => {
      render(<LineChart data={mockData} metrics={mockMetrics} loading={true} />)
      expect(screen.queryByTestId('chart-container')).not.toBeInTheDocument()
    })

    it('shows chart when not loading', () => {
      render(<LineChart data={mockData} metrics={mockMetrics} loading={false} />)
      expect(screen.getByTestId('chart-container')).toBeInTheDocument()
    })
  })

  describe('Metric Toggle Behavior', () => {
    it('toggles current period metric visibility on click', () => {
      render(<LineChart data={mockData} metrics={mockMetrics} />)

      const toggle = screen.getByRole('button', { name: /Toggle Visitors visibility/i })
      expect(toggle).toHaveAttribute('aria-pressed', 'true')

      fireEvent.click(toggle)
      expect(toggle).toHaveAttribute('aria-pressed', 'false')

      fireEvent.click(toggle)
      expect(toggle).toHaveAttribute('aria-pressed', 'true')
    })

    it('toggles previous period metric visibility on click', () => {
      render(<LineChart data={mockData} metrics={mockMetrics} showPreviousPeriod={true} />)

      const toggle = screen.getByRole('button', { name: /Toggle Visitors previous period visibility/i })
      expect(toggle).toHaveAttribute('aria-pressed', 'true')

      fireEvent.click(toggle)
      expect(toggle).toHaveAttribute('aria-pressed', 'false')
    })
  })

  describe('Timeframe Selector', () => {
    it('shows timeframe selector when onTimeframeChange is provided', () => {
      const handleTimeframeChange = vi.fn()
      render(
        <LineChart data={mockData} metrics={mockMetrics} timeframe="daily" onTimeframeChange={handleTimeframeChange} />
      )

      expect(screen.getByRole('combobox')).toBeInTheDocument()
    })

    it('hides timeframe selector when onTimeframeChange is not provided', () => {
      render(<LineChart data={mockData} metrics={mockMetrics} timeframe="daily" />)

      expect(screen.queryByRole('combobox')).not.toBeInTheDocument()
    })
  })

  describe('Data Handling', () => {
    it('renders with empty data array', () => {
      render(<LineChart data={[]} metrics={mockMetrics} />)
      expect(screen.getByTestId('chart-container')).toBeInTheDocument()
    })

    it('renders with single data point', () => {
      const singlePoint = [{ date: '2025-01-01', visitors: 100 }]
      render(<LineChart data={singlePoint} metrics={mockMetrics} />)
      expect(screen.getByTestId('chart-container')).toBeInTheDocument()
    })

    it('handles null values in data (for line gaps)', () => {
      const dataWithNulls: LineChartDataPoint[] = [
        { date: '2025-01-01', visitors: 100, visitorsPrevious: 80 },
        { date: '2025-01-02', visitors: 150, visitorsPrevious: null },
        { date: '2025-01-03', visitors: 120, visitorsPrevious: null },
      ]
      render(<LineChart data={dataWithNulls} metrics={mockMetrics} showPreviousPeriod={true} />)
      expect(screen.getByTestId('chart-container')).toBeInTheDocument()
    })
  })

  describe('Previous Period / Comparison Mode', () => {
    it('shows previous period toggle button when showPreviousPeriod=true', () => {
      render(<LineChart data={mockData} metrics={mockMetrics} showPreviousPeriod={true} />)
      expect(screen.getByRole('button', { name: /Toggle Visitors previous period visibility/i })).toBeInTheDocument()
    })

    it('hides previous period toggle when metric has no previousValue', () => {
      const metricsWithoutPrevious: LineChartMetric[] = [
        { key: 'visitors', label: 'Visitors', value: '370' },
      ]
      render(<LineChart data={mockData} metrics={metricsWithoutPrevious} showPreviousPeriod={true} />)
      expect(
        screen.queryByRole('button', { name: /Toggle Visitors previous period visibility/i })
      ).not.toBeInTheDocument()
    })
  })

  describe('Accessibility', () => {
    it('has accessible toggle buttons with aria-label', () => {
      render(<LineChart data={mockData} metrics={mockMetrics} />)
      const toggle = screen.getByRole('button', { name: /Toggle Visitors visibility/i })
      expect(toggle).toHaveAttribute('aria-label')
    })

    it('has aria-pressed state on toggle buttons', () => {
      render(<LineChart data={mockData} metrics={mockMetrics} />)
      const toggle = screen.getByRole('button', { name: /Toggle Visitors visibility/i })
      expect(toggle).toHaveAttribute('aria-pressed')
    })

    it('updates aria-pressed when toggled', () => {
      render(<LineChart data={mockData} metrics={mockMetrics} />)
      const toggle = screen.getByRole('button', { name: /Toggle Visitors visibility/i })

      expect(toggle).toHaveAttribute('aria-pressed', 'true')
      fireEvent.click(toggle)
      expect(toggle).toHaveAttribute('aria-pressed', 'false')
    })
  })

  describe('Props Handling', () => {
    it('accepts compareDateTo prop', () => {
      render(
        <LineChart
          data={mockData}
          metrics={mockMetrics}
          showPreviousPeriod={true}
          compareDateTo="2024-12-15"
          dateTo="2025-01-03"
        />
      )
      expect(screen.getByTestId('chart-container')).toBeInTheDocument()
    })

    it('renders borderless variant when borderless=true', () => {
      render(<LineChart data={mockData} metrics={mockMetrics} borderless={true} />)
      expect(screen.getByTestId('chart-container')).toBeInTheDocument()
    })
  })
})
