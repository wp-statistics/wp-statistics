import { useState } from 'react'
import type { Meta, StoryObj } from '@storybook/react'
import { fn } from 'storybook/test'

import { FilterButton, type FilterField } from './filter-button'
import type { Filter } from './filter-bar'

// Mock filter fields that match the FilterField interface
const mockFilterFields: FilterField[] = [
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
      { value: 'jp', label: 'Japan' },
    ],
  },
  {
    name: 'os' as FilterFieldName,
    label: 'Operating System',
    inputType: 'dropdown' as FilterInputType,
    supportedOperators: ['is', 'is_not'] as FilterOperator[],
    groups: ['visitors'] as FilterGroup[],
    options: [
      { value: 'windows', label: 'Windows' },
      { value: 'macos', label: 'macOS' },
      { value: 'linux', label: 'Linux' },
      { value: 'android', label: 'Android' },
      { value: 'ios', label: 'iOS' },
    ],
  },
  {
    name: 'total_views' as FilterFieldName,
    label: 'Total Views',
    inputType: 'number' as FilterInputType,
    supportedOperators: ['is', 'is_not', 'gt', 'gte', 'lt', 'lte', 'between'] as FilterOperator[],
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
    name: 'visitor_status' as FilterFieldName,
    label: 'Visitor Status',
    inputType: 'dropdown' as FilterInputType,
    supportedOperators: ['is', 'is_not'] as FilterOperator[],
    groups: ['visitors'] as FilterGroup[],
    options: [
      { value: 'new', label: 'New Visitor' },
      { value: 'returning', label: 'Returning Visitor' },
    ],
  },
]

// Interactive wrapper component that manages state
const FilterButtonWrapper = ({
  fields,
  initialFilters = [],
  onApplyFilters,
}: {
  fields: FilterField[]
  initialFilters?: Filter[]
  onApplyFilters?: (filters: Filter[]) => void
}) => {
  const [appliedFilters, setAppliedFilters] = useState<Filter[]>(initialFilters)

  const handleApplyFilters = (filters: Filter[]) => {
    setAppliedFilters(filters)
    onApplyFilters?.(filters)
  }

  return (
    <div className="flex flex-col gap-4 items-start">
      <FilterButton fields={fields} appliedFilters={appliedFilters} onApplyFilters={handleApplyFilters} />
      {appliedFilters.length > 0 && (
        <div className="p-4 bg-muted rounded-md">
          <p className="text-sm font-medium mb-2">Applied Filters:</p>
          <pre className="text-xs overflow-auto">{JSON.stringify(appliedFilters, null, 2)}</pre>
        </div>
      )}
    </div>
  )
}


const meta = {
  title: 'Custom/FilterButton',
  component: FilterButton,
  parameters: {
    layout: 'padded',
  },
  tags: ['autodocs'],
  args: {
    onApplyFilters: fn(),
  },
} satisfies Meta<typeof FilterButton>

export default meta
type Story = StoryObj<typeof meta>

export const Default: Story = {
  args: {
    fields: mockFilterFields,
    appliedFilters: [],
  },
}

export const WithAppliedFilters: Story = {
  args: {
    fields: mockFilterFields,
    appliedFilters: [
      {
        id: 'browser-1',
        label: 'Browser',
        operator: '=',
        value: 'Chrome',
        rawValue: '1',
        rawOperator: 'is',
        valueLabels: { '1': 'Chrome' },
      },
      {
        id: 'country-2',
        label: 'Country',
        operator: '=',
        value: 'United States',
        rawValue: 'us',
        rawOperator: 'is',
        valueLabels: { us: 'United States' },
      },
    ],
  },
}

export const ManyAppliedFilters: Story = {
  args: {
    fields: mockFilterFields,
    appliedFilters: [
      { id: 'browser-1', label: 'Browser', operator: '=', value: 'Chrome', rawValue: '1', rawOperator: 'is' },
      { id: 'country-2', label: 'Country', operator: '=', value: 'United States', rawValue: 'us', rawOperator: 'is' },
      {
        id: 'os-3',
        label: 'Operating System',
        operator: '=',
        value: 'Windows',
        rawValue: 'windows',
        rawOperator: 'is',
      },
      { id: 'total_views-4', label: 'Total Views', operator: '>', value: '100', rawValue: '100', rawOperator: 'gt' },
      {
        id: 'visitor_status-5',
        label: 'Visitor Status',
        operator: '=',
        value: 'Returning Visitor',
        rawValue: 'returning',
        rawOperator: 'is',
      },
    ],
  },
}

export const Interactive: Story = {
  render: (args) => <FilterButtonWrapper fields={args.fields} onApplyFilters={args.onApplyFilters} />,
  args: {
    fields: mockFilterFields,
    appliedFilters: [],
  },
}

export const InteractiveWithInitialFilters: Story = {
  render: (args) => (
    <FilterButtonWrapper
      fields={args.fields}
      initialFilters={[
        {
          id: 'browser-1',
          label: 'Browser',
          operator: '=',
          value: 'Firefox',
          rawValue: '2',
          rawOperator: 'is',
          valueLabels: { '2': 'Firefox' },
        },
      ]}
      onApplyFilters={args.onApplyFilters}
    />
  ),
  args: {
    fields: mockFilterFields,
    appliedFilters: [],
  },
}

export const MinimalFields: Story = {
  args: {
    fields: [
      {
        name: 'status' as FilterFieldName,
        label: 'Status',
        inputType: 'dropdown' as FilterInputType,
        supportedOperators: ['is', 'is_not'] as FilterOperator[],
        groups: ['visitors'] as FilterGroup[],
        options: [
          { value: 'active', label: 'Active' },
          { value: 'inactive', label: 'Inactive' },
        ],
      },
    ],
    appliedFilters: [],
  },
}

export const NumericFieldsOnly: Story = {
  args: {
    fields: [
      {
        name: 'views' as FilterFieldName,
        label: 'Views',
        inputType: 'number' as FilterInputType,
        supportedOperators: ['is', 'gt', 'gte', 'lt', 'lte', 'between'] as FilterOperator[],
        groups: ['visitors'] as FilterGroup[],
      },
      {
        name: 'sessions' as FilterFieldName,
        label: 'Sessions',
        inputType: 'number' as FilterInputType,
        supportedOperators: ['is', 'gt', 'gte', 'lt', 'lte', 'between'] as FilterOperator[],
        groups: ['visitors'] as FilterGroup[],
      },
      {
        name: 'bounce_rate' as FilterFieldName,
        label: 'Bounce Rate',
        inputType: 'number' as FilterInputType,
        supportedOperators: ['is', 'gt', 'gte', 'lt', 'lte', 'between'] as FilterOperator[],
        groups: ['visitors'] as FilterGroup[],
      },
    ],
    appliedFilters: [],
  },
}

export const DateFieldsOnly: Story = {
  args: {
    fields: [
      {
        name: 'first_visit' as FilterFieldName,
        label: 'First Visit',
        inputType: 'date' as FilterInputType,
        supportedOperators: ['is', 'gt', 'lt', 'between'] as FilterOperator[],
        groups: ['visitors'] as FilterGroup[],
      },
      {
        name: 'last_visit' as FilterFieldName,
        label: 'Last Visit',
        inputType: 'date' as FilterInputType,
        supportedOperators: ['is', 'gt', 'lt', 'between'] as FilterOperator[],
        groups: ['visitors'] as FilterGroup[],
      },
    ],
    appliedFilters: [],
  },
}

export const TextFieldsOnly: Story = {
  args: {
    fields: [
      {
        name: 'url' as FilterFieldName,
        label: 'URL',
        inputType: 'text' as FilterInputType,
        supportedOperators: ['is', 'is_not', 'contains', 'not_contains'] as FilterOperator[],
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
        name: 'page_title' as FilterFieldName,
        label: 'Page Title',
        inputType: 'text' as FilterInputType,
        supportedOperators: ['is', 'is_not', 'contains', 'not_contains'] as FilterOperator[],
        groups: ['visitors'] as FilterGroup[],
      },
    ],
    appliedFilters: [],
  },
}

const searchableFields: FilterField[] = [
  {
    name: 'country' as FilterFieldName,
    label: 'Country',
    inputType: 'searchable' as FilterInputType,
    supportedOperators: ['is', 'is_not', 'in', 'not_in'] as FilterOperator[],
    groups: ['visitors'] as FilterGroup[],
  },
  {
    name: 'browser' as FilterFieldName,
    label: 'Browser',
    inputType: 'searchable' as FilterInputType,
    supportedOperators: ['is', 'is_not', 'in', 'not_in'] as FilterOperator[],
    groups: ['visitors'] as FilterGroup[],
  },
  {
    name: 'os' as FilterFieldName,
    label: 'Operating System',
    inputType: 'searchable' as FilterInputType,
    supportedOperators: ['is', 'is_not', 'in', 'not_in'] as FilterOperator[],
    groups: ['visitors'] as FilterGroup[],
  },
]

export const SearchableFields: Story = {
  render: (args) => (
    <FilterButtonWrapper fields={searchableFields} onApplyFilters={args.onApplyFilters} />
  ),
  args: {
    fields: searchableFields,
    appliedFilters: [],
  },
}
