import { render, screen } from '@testing-library/react'
import { describe, expect, it, vi } from 'vitest'

import { Metrics, type MetricItem } from '@components/custom/metrics'

// Mock useBreakpoint hook
vi.mock('@/hooks/use-breakpoint', () => ({
  useBreakpoint: () => ({ isMobile: false, isTablet: false }),
}))

// Mock utils
vi.mock('@/lib/utils', () => ({
  cn: (...args: unknown[]) => args.filter(Boolean).join(' '),
}))

describe('Metrics Component', () => {
  const singleMetric: MetricItem[] = [
    { label: 'Visitors', value: '1,234' },
  ]

  const metricsWithPercentage: MetricItem[] = [
    { label: 'Visitors', value: '1,234', percentage: 15.5, isNegative: false },
    { label: 'Views', value: '5,678', percentage: 8.2, isNegative: true },
  ]

  const metricsWithTooltip: MetricItem[] = [
    {
      label: 'Visitors',
      value: '1,234',
      percentage: 15.5,
      isNegative: false,
      comparisonDateLabel: 'vs Jan 1 - Jan 7',
      previousValue: '1,070',
    },
  ]

  const metricsWithZeroPercentage: MetricItem[] = [
    { label: 'Visitors', value: '1,234', percentage: 0, isNegative: false },
  ]

  const manyMetrics: MetricItem[] = Array.from({ length: 15 }, (_, i) => ({
    label: `Metric ${i + 1}`,
    value: String((i + 1) * 100),
  }))

  describe('Basic Rendering', () => {
    it('renders a single metric', () => {
      render(<Metrics metrics={singleMetric} />)
      expect(screen.getByText('Visitors')).toBeInTheDocument()
      expect(screen.getByText('1,234')).toBeInTheDocument()
    })

    it('renders multiple metrics', () => {
      render(<Metrics metrics={metricsWithPercentage} />)
      expect(screen.getByText('Visitors')).toBeInTheDocument()
      expect(screen.getByText('Views')).toBeInTheDocument()
      expect(screen.getByText('1,234')).toBeInTheDocument()
      expect(screen.getByText('5,678')).toBeInTheDocument()
    })

    it('renders with empty metrics array', () => {
      const { container } = render(<Metrics metrics={[]} />)
      expect(container.querySelector('.grid')).toBeInTheDocument()
    })
  })

  describe('Metrics Limit', () => {
    it('limits display to 12 metrics maximum', () => {
      render(<Metrics metrics={manyMetrics} />)

      // Should show first 12
      expect(screen.getByText('Metric 1')).toBeInTheDocument()
      expect(screen.getByText('Metric 12')).toBeInTheDocument()

      // Should not show 13-15
      expect(screen.queryByText('Metric 13')).not.toBeInTheDocument()
      expect(screen.queryByText('Metric 14')).not.toBeInTheDocument()
      expect(screen.queryByText('Metric 15')).not.toBeInTheDocument()
    })
  })

  describe('Percentage Badge', () => {
    it('shows percentage badge when percentage is provided', () => {
      render(<Metrics metrics={metricsWithPercentage} />)
      expect(screen.getByText('15.5%')).toBeInTheDocument()
      expect(screen.getByText('8.2%')).toBeInTheDocument()
    })

    it('does not show percentage badge when percentage is not provided', () => {
      render(<Metrics metrics={singleMetric} />)
      expect(screen.queryByText('%')).not.toBeInTheDocument()
    })

    it('shows percentage badge for zero percentage', () => {
      render(<Metrics metrics={metricsWithZeroPercentage} />)
      expect(screen.getByText('0%')).toBeInTheDocument()
    })

    it('rounds percentage when >= 100', () => {
      const metricsWithLargePercentage: MetricItem[] = [
        { label: 'Visitors', value: '1,234', percentage: 156.7 },
      ]
      render(<Metrics metrics={metricsWithLargePercentage} />)
      expect(screen.getByText('157%')).toBeInTheDocument()
    })
  })

  describe('Chevron Direction', () => {
    it('shows up chevron for positive (non-negative) percentage', () => {
      render(<Metrics metrics={[{ label: 'Test', value: '100', percentage: 10, isNegative: false }]} />)
      // ChevronUp has a specific path, we check by its presence in the percentage container
      const percentageElement = screen.getByText('10%').parentElement
      expect(percentageElement).toBeInTheDocument()
      // ChevronUp should be present (has class h-3 w-3)
      const chevron = percentageElement?.querySelector('svg')
      expect(chevron).toBeInTheDocument()
    })

    it('shows down chevron for negative percentage', () => {
      render(<Metrics metrics={[{ label: 'Test', value: '100', percentage: 10, isNegative: true }]} />)
      const percentageElement = screen.getByText('10%').parentElement
      const chevron = percentageElement?.querySelector('svg')
      expect(chevron).toBeInTheDocument()
    })

    it('does not show chevron for zero percentage', () => {
      render(<Metrics metrics={metricsWithZeroPercentage} />)
      const percentageElement = screen.getByText('0%').parentElement
      const chevron = percentageElement?.querySelector('svg')
      expect(chevron).not.toBeInTheDocument()
    })
  })

  describe('Tooltip', () => {
    it('renders tooltip content when tooltipContent is provided', async () => {
      const metricsWithTooltipContent: MetricItem[] = [
        { label: 'Visitors', value: '1,234', tooltipContent: 'Total unique visitors' },
      ]
      render(<Metrics metrics={metricsWithTooltipContent} />)

      // Info icon button should be present
      const infoButton = screen.getByRole('button', { name: /More information about Visitors/i })
      expect(infoButton).toBeInTheDocument()
    })

    it('does not render info icon when tooltipContent is not provided', () => {
      render(<Metrics metrics={singleMetric} />)
      expect(screen.queryByRole('button', { name: /More information/i })).not.toBeInTheDocument()
    })

    it('shows tooltip trigger on percentage badge when comparisonDateLabel is provided', () => {
      render(<Metrics metrics={metricsWithTooltip} />)

      const percentageBadge = screen.getByText('15.5%')
      expect(percentageBadge).toHaveClass('cursor-help')
      // Radix tooltip trigger has data-slot attribute
      expect(percentageBadge).toHaveAttribute('data-slot', 'tooltip-trigger')
    })

    it('does not show cursor-help when comparisonDateLabel is not provided', () => {
      render(<Metrics metrics={metricsWithPercentage} />)
      const percentageBadge = screen.getByText('15.5%')
      expect(percentageBadge).not.toHaveClass('cursor-help')
    })
  })

  describe('Column Configuration', () => {
    it('uses default 3 columns', () => {
      const { container } = render(<Metrics metrics={metricsWithPercentage} />)
      expect(container.querySelector('.grid-cols-3')).toBeInTheDocument()
    })

    it('respects columns prop', () => {
      const { container: c1 } = render(<Metrics metrics={metricsWithPercentage} columns={1} />)
      expect(c1.querySelector('.grid-cols-1')).toBeInTheDocument()

      const { container: c2 } = render(<Metrics metrics={metricsWithPercentage} columns={2} />)
      expect(c2.querySelector('.grid-cols-2')).toBeInTheDocument()

      const { container: c4 } = render(<Metrics metrics={metricsWithPercentage} columns={4} />)
      expect(c4.querySelector('.grid-cols-4')).toBeInTheDocument()

      const { container: c6 } = render(<Metrics metrics={metricsWithPercentage} columns={6} />)
      expect(c6.querySelector('.grid-cols-6')).toBeInTheDocument()

      const { container: c12 } = render(<Metrics metrics={metricsWithPercentage} columns={12} />)
      expect(c12.querySelector('.grid-cols-12')).toBeInTheDocument()
    })
  })

  describe('Custom Icon', () => {
    it('renders custom icon when provided', () => {
      const metricsWithIcon: MetricItem[] = [
        { label: 'Visitors', value: '1,234', icon: <span data-testid="custom-icon">★</span> },
      ]
      render(<Metrics metrics={metricsWithIcon} />)
      expect(screen.getByTestId('custom-icon')).toBeInTheDocument()
    })
  })

  describe('Optional Props', () => {
    it('handles metric with all optional props missing', () => {
      const minimalMetric: MetricItem[] = [{ label: 'Test', value: 100 }]
      render(<Metrics metrics={minimalMetric} />)
      expect(screen.getByText('Test')).toBeInTheDocument()
      expect(screen.getByText('100')).toBeInTheDocument()
    })

    it('handles metric with all optional props present', () => {
      const fullMetric: MetricItem[] = [
        {
          label: 'Visitors',
          value: '1,234',
          percentage: 15.5,
          isNegative: false,
          icon: <span data-testid="icon">★</span>,
          tooltipContent: 'Help text',
          comparisonDateLabel: 'vs last week',
          previousValue: '1,000',
        },
      ]
      render(<Metrics metrics={fullMetric} />)
      expect(screen.getByText('Visitors')).toBeInTheDocument()
      expect(screen.getByText('1,234')).toBeInTheDocument()
      expect(screen.getByText('15.5%')).toBeInTheDocument()
      expect(screen.getByTestId('icon')).toBeInTheDocument()
      expect(screen.getByRole('button', { name: /More information/i })).toBeInTheDocument()
    })

    it('accepts numeric value', () => {
      const numericMetric: MetricItem[] = [{ label: 'Count', value: 42 }]
      render(<Metrics metrics={numericMetric} />)
      expect(screen.getByText('42')).toBeInTheDocument()
    })

    it('accepts string percentage', () => {
      const stringPercentageMetric: MetricItem[] = [{ label: 'Test', value: '100', percentage: '25.5' }]
      render(<Metrics metrics={stringPercentageMetric} />)
      expect(screen.getByText('25.5%')).toBeInTheDocument()
    })
  })

  describe('Custom className', () => {
    it('applies custom className', () => {
      const { container } = render(<Metrics metrics={singleMetric} className="custom-class" />)
      expect(container.querySelector('.custom-class')).toBeInTheDocument()
    })
  })
})
