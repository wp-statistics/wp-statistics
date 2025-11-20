import type { Meta, StoryObj } from '@storybook/react'
import { GlobalMap } from './global-map'
import type { CountryData, GlobalMapData } from './global-map'

// Sample data matching the screenshot
const sampleCountries: CountryData[] = [
  { code: 'US', name: 'United States', flag: '🇺🇸', visitors: 25000 },
  { code: 'FR', name: 'France', flag: '🇫🇷', visitors: 23000 },
  { code: 'GB', name: 'United Kingdom', flag: '🇬🇧', visitors: 18000 },
  { code: 'DE', name: 'Germany', flag: '🇩🇪', visitors: 15000 },
  { code: 'CA', name: 'Canada', flag: '🇨🇦', visitors: 12000 },
  { code: 'AU', name: 'Australia', flag: '🇦🇺', visitors: 10000 },
  { code: 'JP', name: 'Japan', flag: '🇯🇵', visitors: 9000 },
  { code: 'IN', name: 'India', flag: '🇮🇳', visitors: 8000 },
  { code: 'BR', name: 'Brazil', flag: '🇧🇷', visitors: 7000 },
  { code: 'IT', name: 'Italy', flag: '🇮🇹', visitors: 6500 },
  { code: 'ES', name: 'Spain', flag: '🇪🇸', visitors: 6000 },
  { code: 'MX', name: 'Mexico', flag: '🇲🇽', visitors: 5500 },
  { code: 'NL', name: 'Netherlands', flag: '🇳🇱', visitors: 5000 },
  { code: 'SE', name: 'Sweden', flag: '🇸🇪', visitors: 4500 },
  { code: 'CH', name: 'Switzerland', flag: '🇨🇭', visitors: 4000 },
  { code: 'BE', name: 'Belgium', flag: '🇧🇪', visitors: 3500 },
  { code: 'PL', name: 'Poland', flag: '🇵🇱', visitors: 3000 },
  { code: 'AT', name: 'Austria', flag: '🇦🇹', visitors: 2500 },
  { code: 'NO', name: 'Norway', flag: '🇳🇴', visitors: 2000 },
  { code: 'DK', name: 'Denmark', flag: '🇩🇰', visitors: 1800 },
  { code: 'FI', name: 'Finland', flag: '🇫🇮', visitors: 1500 },
  { code: 'IE', name: 'Ireland', flag: '🇮🇪', visitors: 1200 },
  { code: 'PT', name: 'Portugal', flag: '🇵🇹', visitors: 1000 },
  { code: 'GR', name: 'Greece', flag: '🇬🇷', visitors: 900 },
  { code: 'CZ', name: 'Czech Republic', flag: '🇨🇿', visitors: 800 },
  { code: 'RO', name: 'Romania', flag: '🇷🇴', visitors: 700 },
  { code: 'HU', name: 'Hungary', flag: '🇭🇺', visitors: 600 },
  { code: 'NZ', name: 'New Zealand', flag: '🇳🇿', visitors: 500 },
  { code: 'SG', name: 'Singapore', flag: '🇸🇬', visitors: 450 },
  { code: 'ZA', name: 'South Africa', flag: '🇿🇦', visitors: 400 },
]

const sampleData: GlobalMapData = {
  countries: sampleCountries,
}

const meta = {
  title: 'Components/GlobalMap',
  component: GlobalMap,
  parameters: {
    layout: 'padded',
  },
  tags: ['autodocs'],
  argTypes: {
    title: {
      control: 'text',
      description: 'Optional title for the map',
    },
    metric: {
      control: 'text',
      description: 'The metric being visualized (e.g., Visitors, Views)',
    },
    showZoomControls: {
      control: 'boolean',
      description: 'Show/hide zoom in/out buttons',
    },
    showLegend: {
      control: 'boolean',
      description: 'Show/hide color scale legend',
    },
    showTimePeriod: {
      control: 'boolean',
      description: 'Show/hide time period selector',
    },
    timePeriod: {
      control: 'select',
      options: ['Last 7 days', 'Last 30 days', 'Last 90 days', 'Last 12 months'],
      description: 'Selected time period',
    },
  },
} satisfies Meta<typeof GlobalMap>

export default meta
type Story = StoryObj<typeof meta>

export const Default: Story = {
  args: {
    data: sampleData,
  },
}

export const WithTitle: Story = {
  args: {
    data: sampleData,
    title: 'Global Visitor Distribution',
  },
}

export const CustomMetric: Story = {
  args: {
    data: sampleData,
    metric: 'Page Views',
    title: 'Global Page Views',
  },
}

export const WithoutZoomControls: Story = {
  args: {
    data: sampleData,
    showZoomControls: false,
  },
}

export const WithoutLegend: Story = {
  args: {
    data: sampleData,
    showLegend: false,
  },
}

export const WithoutTimePeriod: Story = {
  args: {
    data: sampleData,
    showTimePeriod: false,
  },
}

export const Last7Days: Story = {
  args: {
    data: sampleData,
    timePeriod: 'Last 7 days',
  },
}

export const Last12Months: Story = {
  args: {
    data: sampleData,
    timePeriod: 'Last 12 months',
  },
}

export const MinimalConfiguration: Story = {
  args: {
    data: sampleData,
    showZoomControls: false,
    showLegend: false,
    showTimePeriod: false,
  },
}

export const FullFeatured: Story = {
  args: {
    data: sampleData,
    title: 'Global Visitor Distribution',
    metric: 'Visitors',
    showZoomControls: true,
    showLegend: true,
    showTimePeriod: true,
    timePeriod: 'Last 30 days',
  },
}

export const EmptyData: Story = {
  args: {
    data: { countries: [] },
    title: 'No Data Available',
  },
}

export const LimitedData: Story = {
  args: {
    data: {
      countries: [
        { code: 'US', name: 'United States', flag: '🇺🇸', visitors: 15000 },
        { code: 'FR', name: 'France', flag: '🇫🇷', visitors: 8000 },
        { code: 'GB', name: 'United Kingdom', flag: '🇬🇧', visitors: 5000 },
      ],
    },
    title: 'Top 3 Countries',
  },
}
