import type { Meta, StoryObj } from '@storybook/react'
import { useState } from 'react'
import { fn } from 'storybook/test'

import { withWordPressContext } from '../../../../../.storybook/decorators/with-wordpress-context'

import type { FilterField, FilterRowData } from './filter-row'
import { FilterRow } from './filter-row'

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
      { value: '5', label: 'Opera' },
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
      { value: 'fr', label: 'France' },
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
    name: 'last_visit' as FilterFieldName,
    label: 'Last Visit',
    inputType: 'date' as FilterInputType,
    supportedOperators: ['is', 'gt', 'lt', 'between'] as FilterOperator[],
    groups: ['visitors'] as FilterGroup[],
  },
  {
    name: 'referrer' as FilterFieldName,
    label: 'Referrer',
    inputType: 'text' as FilterInputType,
    supportedOperators: ['is', 'is_not', 'contains', 'not_contains'] as FilterOperator[],
    groups: ['visitors'] as FilterGroup[],
  },
  {
    name: 'page' as FilterFieldName,
    label: 'Page',
    inputType: 'searchable' as FilterInputType,
    supportedOperators: ['is', 'is_not', 'in', 'not_in'] as FilterOperator[],
    groups: ['visitors'] as FilterGroup[],
  },
]

// Interactive wrapper component
function FilterRowWrapper({
  initialFilter,
  fields,
  onUpdate,
  onRemove,
}: {
  initialFilter: FilterRowData
  fields: FilterField[]
  onUpdate?: (filter: FilterRowData) => void
  onRemove?: (id: string) => void
}) {
  const [filter, setFilter] = useState(initialFilter)

  const handleUpdate = (updatedFilter: FilterRowData) => {
    setFilter(updatedFilter)
    onUpdate?.(updatedFilter)
  }

  const handleRemove = (id: string) => {
    onRemove?.(id)
  }

  return (
    <div className="w-full max-w-3xl space-y-4">
      <FilterRow filter={filter} fields={fields} onUpdate={handleUpdate} onRemove={handleRemove} />
      <div className="p-4 bg-muted rounded-md">
        <p className="text-sm font-medium mb-2">Current Filter State:</p>
        <pre className="text-xs overflow-auto">{JSON.stringify(filter, null, 2)}</pre>
      </div>
    </div>
  )
}

const meta = {
  title: 'Custom/FilterRow',
  component: FilterRow,
  parameters: {
    layout: 'padded',
  },
  decorators: [withWordPressContext],
  tags: ['autodocs'],
  args: {
    onUpdate: fn(),
    onRemove: fn(),
  },
} satisfies Meta<typeof FilterRow>

export default meta
type Story = StoryObj<typeof meta>

export const Default: Story = {
  render: (args) => (
    <FilterRowWrapper
      initialFilter={{
        id: 'filter-1',
        fieldName: 'browser' as FilterFieldName,
        operator: 'is' as FilterOperator,
        value: '',
      }}
      fields={mockFields}
      onUpdate={args.onUpdate}
      onRemove={args.onRemove}
    />
  ),
  args: {
    filter: {
      id: 'filter-1',
      fieldName: 'browser' as FilterFieldName,
      operator: 'is' as FilterOperator,
      value: '',
    },
    fields: mockFields,
  },
}

export const DropdownField: Story = {
  render: (args) => (
    <FilterRowWrapper
      initialFilter={{
        id: 'filter-1',
        fieldName: 'browser' as FilterFieldName,
        operator: 'is' as FilterOperator,
        value: '1',
        valueLabels: { '1': 'Chrome' },
      }}
      fields={mockFields}
      onUpdate={args.onUpdate}
      onRemove={args.onRemove}
    />
  ),
  args: {
    filter: {
      id: 'filter-1',
      fieldName: 'browser' as FilterFieldName,
      operator: 'is' as FilterOperator,
      value: '1',
    },
    fields: mockFields,
  },
}

export const NumberField: Story = {
  render: (args) => (
    <FilterRowWrapper
      initialFilter={{
        id: 'filter-1',
        fieldName: 'total_views' as FilterFieldName,
        operator: 'gt' as FilterOperator,
        value: '100',
      }}
      fields={mockFields}
      onUpdate={args.onUpdate}
      onRemove={args.onRemove}
    />
  ),
  args: {
    filter: {
      id: 'filter-1',
      fieldName: 'total_views' as FilterFieldName,
      operator: 'gt' as FilterOperator,
      value: '100',
    },
    fields: mockFields,
  },
}

export const RangeOperator: Story = {
  render: (args) => (
    <FilterRowWrapper
      initialFilter={{
        id: 'filter-1',
        fieldName: 'total_views' as FilterFieldName,
        operator: 'between' as FilterOperator,
        value: { min: '50', max: '200' },
      }}
      fields={mockFields}
      onUpdate={args.onUpdate}
      onRemove={args.onRemove}
    />
  ),
  args: {
    filter: {
      id: 'filter-1',
      fieldName: 'total_views' as FilterFieldName,
      operator: 'between' as FilterOperator,
      value: { min: '50', max: '200' },
    },
    fields: mockFields,
  },
}

export const DateField: Story = {
  render: (args) => (
    <FilterRowWrapper
      initialFilter={{
        id: 'filter-1',
        fieldName: 'last_visit' as FilterFieldName,
        operator: 'gt' as FilterOperator,
        value: '2025-01-01',
      }}
      fields={mockFields}
      onUpdate={args.onUpdate}
      onRemove={args.onRemove}
    />
  ),
  args: {
    filter: {
      id: 'filter-1',
      fieldName: 'last_visit' as FilterFieldName,
      operator: 'gt' as FilterOperator,
      value: '2025-01-01',
    },
    fields: mockFields,
  },
}

export const DateRangeField: Story = {
  render: (args) => (
    <FilterRowWrapper
      initialFilter={{
        id: 'filter-1',
        fieldName: 'last_visit' as FilterFieldName,
        operator: 'between' as FilterOperator,
        value: { min: '2025-01-01', max: '2025-01-31' },
      }}
      fields={mockFields}
      onUpdate={args.onUpdate}
      onRemove={args.onRemove}
    />
  ),
  args: {
    filter: {
      id: 'filter-1',
      fieldName: 'last_visit' as FilterFieldName,
      operator: 'between' as FilterOperator,
      value: { min: '2025-01-01', max: '2025-01-31' },
    },
    fields: mockFields,
  },
}

export const TextField: Story = {
  render: (args) => (
    <FilterRowWrapper
      initialFilter={{
        id: 'filter-1',
        fieldName: 'referrer' as FilterFieldName,
        operator: 'contains' as FilterOperator,
        value: 'google',
      }}
      fields={mockFields}
      onUpdate={args.onUpdate}
      onRemove={args.onRemove}
    />
  ),
  args: {
    filter: {
      id: 'filter-1',
      fieldName: 'referrer' as FilterFieldName,
      operator: 'contains' as FilterOperator,
      value: 'google',
    },
    fields: mockFields,
  },
}

export const SearchableField: Story = {
  render: (args) => (
    <FilterRowWrapper
      initialFilter={{
        id: 'filter-1',
        fieldName: 'page' as FilterFieldName,
        operator: 'is' as FilterOperator,
        value: '',
      }}
      fields={mockFields}
      onUpdate={args.onUpdate}
      onRemove={args.onRemove}
    />
  ),
  args: {
    filter: {
      id: 'filter-1',
      fieldName: 'page' as FilterFieldName,
      operator: 'is' as FilterOperator,
      value: '',
    },
    fields: mockFields,
  },
  parameters: {
    docs: {
      description: {
        story: 'Searchable field with autocomplete. Type to search and select from results.',
      },
    },
  },
}

export const MultiSelectSearchable: Story = {
  render: (args) => (
    <FilterRowWrapper
      initialFilter={{
        id: 'filter-1',
        fieldName: 'page' as FilterFieldName,
        operator: 'in' as FilterOperator,
        value: [],
      }}
      fields={mockFields}
      onUpdate={args.onUpdate}
      onRemove={args.onRemove}
    />
  ),
  args: {
    filter: {
      id: 'filter-1',
      fieldName: 'page' as FilterFieldName,
      operator: 'in' as FilterOperator,
      value: [],
    },
    fields: mockFields,
  },
  parameters: {
    docs: {
      description: {
        story: 'Searchable field with multi-select enabled. Use "is any of" operator.',
      },
    },
  },
}
