import type { Meta, StoryObj } from '@storybook/react'
import { useState } from 'react'
import { expect, fn, userEvent, within } from 'storybook/test'

import { withWordPressContext } from '../../../../../.storybook/decorators/with-wordpress-context'
import { FilterPanel } from './filter-panel'
import type { FilterField, FilterRowData } from './filter-row'

// Mock filter fields
const mockFields: FilterField[] = [
  {
    name: 'browser' as FilterFieldName,
    label: 'Browser',
    inputType: 'dropdown' as FilterInputType,
    supportedOperators: ['is', 'is_not'] as FilterOperator[],
    groups: ['visitors'] as FilterGroup[],
    options: [
      { value: '1', label: 'Chrome' },
      { value: '2', label: 'Firefox' },
      { value: '3', label: 'Safari' },
      { value: '4', label: 'Edge' },
    ],
  },
  {
    name: 'country' as FilterFieldName,
    label: 'Country',
    inputType: 'dropdown' as FilterInputType,
    supportedOperators: ['is', 'is_not'] as FilterOperator[],
    groups: ['visitors'] as FilterGroup[],
    options: [
      { value: 'us', label: 'United States' },
      { value: 'gb', label: 'United Kingdom' },
      { value: 'de', label: 'Germany' },
    ],
  },
  {
    name: 'total_views' as FilterFieldName,
    label: 'Total Views',
    inputType: 'number' as FilterInputType,
    supportedOperators: ['is', 'gt', 'gte', 'lt', 'lte', 'between'] as FilterOperator[],
    groups: ['visitors'] as FilterGroup[],
  },
  {
    name: 'referrer' as FilterFieldName,
    label: 'Referrer',
    inputType: 'text' as FilterInputType,
    supportedOperators: ['is', 'contains', 'not_contains'] as FilterOperator[],
    groups: ['visitors'] as FilterGroup[],
  },
]

// Interactive wrapper
function FilterPanelWrapper({
  initialFilters = [],
  fields,
  onApply,
  onClearAll,
}: {
  initialFilters?: FilterRowData[]
  fields: FilterField[]
  onApply?: () => void
  onClearAll?: () => void
}) {
  const [filters, setFilters] = useState(initialFilters)

  return (
    <div className="w-full max-w-2xl">
      <FilterPanel
        filters={filters}
        fields={fields}
        onFiltersChange={setFilters}
        onApply={() => {
          onApply?.()
        }}
        onClearAll={onClearAll}
      />
      <div className="mt-4 p-4 bg-muted rounded-md">
        <p className="text-sm font-medium mb-2">Current Filters State:</p>
        <pre className="text-xs overflow-auto">{JSON.stringify(filters, null, 2)}</pre>
      </div>
    </div>
  )
}

const meta = {
  title: 'Custom/FilterPanel',
  component: FilterPanel,
  parameters: {
    layout: 'padded',
  },
  decorators: [withWordPressContext],
  tags: ['autodocs'],
  args: {
    onApply: fn(),
    onClearAll: fn(),
    onCancel: fn(),
  },
} satisfies Meta<typeof FilterPanel>

export default meta
type Story = StoryObj<typeof meta>

export const Empty: Story = {
  render: (args) => <FilterPanelWrapper fields={mockFields} onApply={args.onApply} onClearAll={args.onClearAll} />,
  args: {
    filters: [],
    fields: mockFields,
    onFiltersChange: fn(),
  },
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)

    // Find the "Add Filter" button
    const addFilterButton = canvas.getByRole('button', { name: /add filter/i })
    await expect(addFilterButton).toBeInTheDocument()

    // Click to add a filter
    await userEvent.click(addFilterButton)

    // Verify a filter row was added (field + operator comboboxes should appear)
    const comboboxes = canvas.getAllByRole('combobox')
    await expect(comboboxes.length).toBeGreaterThanOrEqual(1)

    // Verify Apply button exists
    await expect(canvas.getByRole('button', { name: /apply/i })).toBeInTheDocument()
  },
}

export const OneFilter: Story = {
  render: (args) => (
    <FilterPanelWrapper
      initialFilters={[
        {
          id: 'filter-1',
          fieldName: 'browser' as FilterFieldName,
          operator: 'is' as FilterOperator,
          value: '1',
          valueLabels: { '1': 'Chrome' },
        },
      ]}
      fields={mockFields}
      onApply={args.onApply}
      onClearAll={args.onClearAll}
    />
  ),
  args: {
    filters: [],
    fields: mockFields,
    onFiltersChange: fn(),
  },
}

export const MultipleFilters: Story = {
  render: (args) => (
    <FilterPanelWrapper
      initialFilters={[
        {
          id: 'filter-1',
          fieldName: 'browser' as FilterFieldName,
          operator: 'is' as FilterOperator,
          value: '1',
          valueLabels: { '1': 'Chrome' },
        },
        {
          id: 'filter-2',
          fieldName: 'country' as FilterFieldName,
          operator: 'is' as FilterOperator,
          value: 'us',
          valueLabels: { us: 'United States' },
        },
        {
          id: 'filter-3',
          fieldName: 'total_views' as FilterFieldName,
          operator: 'gt' as FilterOperator,
          value: '100',
        },
      ]}
      fields={mockFields}
      onApply={args.onApply}
      onClearAll={args.onClearAll}
    />
  ),
  args: {
    filters: [],
    fields: mockFields,
    onFiltersChange: fn(),
  },
}

export const WithRangeFilter: Story = {
  render: (args) => (
    <FilterPanelWrapper
      initialFilters={[
        {
          id: 'filter-1',
          fieldName: 'total_views' as FilterFieldName,
          operator: 'between' as FilterOperator,
          value: { min: '50', max: '200' },
        },
      ]}
      fields={mockFields}
      onApply={args.onApply}
      onClearAll={args.onClearAll}
    />
  ),
  args: {
    filters: [],
    fields: mockFields,
    onFiltersChange: fn(),
  },
}
