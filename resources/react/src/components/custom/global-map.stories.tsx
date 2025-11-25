import type { Meta, StoryObj } from '@storybook/react'
import { GlobalMap } from './global-map'
import type { CountryData, GlobalMapData } from './global-map'

// Sample data - flags will be loaded from server when pluginUrl is provided
const sampleCountries: CountryData[] = [
  { code: 'US', name: 'United States', visitors: 25000 },
  { code: 'FR', name: 'France', visitors: 23000 },
  { code: 'GB', name: 'United Kingdom', visitors: 18000 },
  { code: 'DE', name: 'Germany', visitors: 15000 },
  { code: 'CA', name: 'Canada', visitors: 12000 },
  { code: 'AU', name: 'Australia', visitors: 10000 },
  { code: 'JP', name: 'Japan', visitors: 9000 },
  { code: 'IN', name: 'India', visitors: 8000 },
  { code: 'BR', name: 'Brazil', visitors: 7000 },
  { code: 'IT', name: 'Italy', visitors: 6500 },
  { code: 'ES', name: 'Spain', visitors: 6000 },
  { code: 'MX', name: 'Mexico', visitors: 5500 },
  { code: 'NL', name: 'Netherlands', visitors: 5000 },
  { code: 'SE', name: 'Sweden', visitors: 4500 },
  { code: 'CH', name: 'Switzerland', visitors: 4000 },
  { code: 'BE', name: 'Belgium', visitors: 3500 },
  { code: 'PL', name: 'Poland', visitors: 3000 },
  { code: 'AT', name: 'Austria', visitors: 2500 },
  { code: 'NO', name: 'Norway', visitors: 2000 },
  { code: 'DK', name: 'Denmark', visitors: 1800 },
  { code: 'FI', name: 'Finland', visitors: 1500 },
  { code: 'IE', name: 'Ireland', visitors: 1200 },
  { code: 'PT', name: 'Portugal', visitors: 1000 },
  { code: 'GR', name: 'Greece', visitors: 900 },
  { code: 'CZ', name: 'Czech Republic', visitors: 800 },
  { code: 'RO', name: 'Romania', visitors: 700 },
  { code: 'HU', name: 'Hungary', visitors: 600 },
  { code: 'NZ', name: 'New Zealand', visitors: 500 },
  { code: 'SG', name: 'Singapore', visitors: 450 },
  { code: 'ZA', name: 'South Africa', visitors: 400 },
]

const sampleData: GlobalMapData = {
  countries: sampleCountries,
}

const meta = {
  title: 'Custom/GlobalMap',
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
    pluginUrl: {
      control: 'text',
      description: 'Base URL for flag images',
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

export const MinimalConfiguration: Story = {
  args: {
    data: sampleData,
    showZoomControls: false,
    showLegend: false,
  },
}

export const FullFeatured: Story = {
  args: {
    data: sampleData,
    title: 'Global Visitor Distribution',
    metric: 'Visitors',
    showZoomControls: true,
    showLegend: true,
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
        { code: 'US', name: 'United States', visitors: 15000 },
        { code: 'FR', name: 'France', visitors: 8000 },
        { code: 'GB', name: 'United Kingdom', visitors: 5000 },
      ],
    },
    title: 'Top 3 Countries',
  },
}
