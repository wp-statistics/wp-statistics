import type { Meta, StoryObj } from '@storybook/react'
import { Bot, Globe, MonitorSmartphone, UserIcon } from 'lucide-react'
import { useState } from 'react'
import { expect, fn, userEvent, within } from 'storybook/test'

import type { FilterRowData } from '@/components/custom/filter-row'
import type { QuickFilterDefinition } from '@/config/quick-filter-definitions'

import { QuickFilters, type QuickFiltersProps } from './quick-filters'

// Mock quick filter definitions for stories
const mockDefinitions: QuickFilterDefinition[] = [
  {
    id: 'mobile',
    fieldName: 'device_type' as FilterFieldName,
    operator: 'is' as FilterOperator,
    value: 'mobile',
    label: 'Mobile',
    valueLabel: 'Mobile',
    icon: MonitorSmartphone,
  },
  {
    id: 'desktop',
    fieldName: 'device_type' as FilterFieldName,
    operator: 'is' as FilterOperator,
    value: 'desktop',
    label: 'Desktop',
    valueLabel: 'Desktop',
    icon: MonitorSmartphone,
  },
  {
    id: 'logged_in',
    fieldName: 'logged_in' as FilterFieldName,
    operator: 'is' as FilterOperator,
    value: '1',
    label: 'Logged-in',
    valueLabel: 'Logged-in',
    icon: UserIcon,
  },
  {
    id: 'bot',
    fieldName: 'is_bot' as FilterFieldName,
    operator: 'is' as FilterOperator,
    value: '0',
    label: 'Exclude Bots',
    valueLabel: 'Human',
    icon: Bot,
  },
  {
    id: 'organic',
    fieldName: 'referrer_type' as FilterFieldName,
    operator: 'is' as FilterOperator,
    value: 'organic',
    label: 'Organic',
    valueLabel: 'Organic',
    icon: Globe,
  },
]

const meta = {
  title: 'Custom/QuickFilters',
  component: QuickFilters,
  parameters: {
    layout: 'centered',
  },
  tags: ['autodocs'],
} satisfies Meta<typeof QuickFilters>

export default meta
type Story = StoryObj<typeof meta>

export const Default: Story = {
  args: {
    definitions: mockDefinitions,
    activeFilters: [],
    onToggle: fn(),
    lockedFilters: [],
  },
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)
    await expect(canvas.getByText('Mobile')).toBeInTheDocument()
    await expect(canvas.getByText('Desktop')).toBeInTheDocument()
    await expect(canvas.getByText('Logged-in')).toBeInTheDocument()
  },
}

export const WithActiveFilters: Story = {
  args: {
    definitions: mockDefinitions,
    activeFilters: [
      {
        id: 'filter-1',
        fieldName: 'device_type' as FilterFieldName,
        operator: 'is' as FilterOperator,
        value: 'mobile',
      },
    ],
    onToggle: fn(),
    lockedFilters: [],
  },
}

export const WithLockedFilter: Story = {
  args: {
    definitions: mockDefinitions,
    activeFilters: [],
    onToggle: fn(),
    lockedFilters: [
      {
        id: 'locked-1',
        label: 'User Type',
        operator: 'is',
        value: 'Logged-in',
      },
    ],
  },
}

export const MultipleActive: Story = {
  args: {
    definitions: mockDefinitions,
    activeFilters: [
      {
        id: 'filter-1',
        fieldName: 'device_type' as FilterFieldName,
        operator: 'is' as FilterOperator,
        value: 'mobile',
      },
      {
        id: 'filter-2',
        fieldName: 'is_bot' as FilterFieldName,
        operator: 'is' as FilterOperator,
        value: '0',
      },
    ],
    onToggle: fn(),
    lockedFilters: [],
  },
}

export const Interactive: Story = {
  render: function InteractiveStory() {
    const [activeFilters, setActiveFilters] = useState<FilterRowData[]>([])

    const handleToggle = (definition: QuickFilterDefinition) => {
      const existingIndex = activeFilters.findIndex(
        (f) => f.fieldName === definition.fieldName && f.value === definition.value
      )

      if (existingIndex >= 0) {
        setActiveFilters(activeFilters.filter((_, i) => i !== existingIndex))
      } else {
        setActiveFilters([
          ...activeFilters,
          {
            id: `filter-${Date.now()}`,
            fieldName: definition.fieldName,
            operator: definition.operator,
            value: definition.value,
          },
        ])
      }
    }

    return (
      <div className="space-y-4">
        <QuickFilters
          definitions={mockDefinitions}
          activeFilters={activeFilters}
          onToggle={handleToggle}
          lockedFilters={[]}
        />
        <div className="text-xs text-muted-foreground">Active filters: {activeFilters.length}</div>
      </div>
    )
  },
}

export const InteractiveWithLocked: Story = {
  render: function InteractiveWithLockedStory() {
    const [activeFilters, setActiveFilters] = useState<FilterRowData[]>([])

    const handleToggle = (definition: QuickFilterDefinition) => {
      const existingIndex = activeFilters.findIndex(
        (f) => f.fieldName === definition.fieldName && f.value === definition.value
      )

      if (existingIndex >= 0) {
        setActiveFilters(activeFilters.filter((_, i) => i !== existingIndex))
      } else {
        setActiveFilters([
          ...activeFilters,
          {
            id: `filter-${Date.now()}`,
            fieldName: definition.fieldName,
            operator: definition.operator,
            value: definition.value,
          },
        ])
      }
    }

    return (
      <div className="space-y-4">
        <QuickFilters
          definitions={mockDefinitions}
          activeFilters={activeFilters}
          onToggle={handleToggle}
          lockedFilters={[
            {
              id: 'locked-1',
              label: 'User Type',
              operator: 'is',
              value: 'Logged-in',
            },
          ]}
        />
        <div className="text-xs text-muted-foreground">
          Active filters: {activeFilters.length} | Locked filter: "Logged-in" (cannot be toggled)
        </div>
      </div>
    )
  },
}

export const InFilterPanel: Story = {
  render: () => (
    <div className="w-[380px] border rounded-lg">
      <div className="px-4 py-3 border-b bg-neutral-50">
        <h3 className="text-sm font-medium">Filters</h3>
      </div>
      <div className="px-4 py-3 border-b">
        <span className="text-xs font-medium text-neutral-500 mb-2 block">Quick filters</span>
        <QuickFilters
          definitions={mockDefinitions}
          activeFilters={[
            {
              id: 'filter-1',
              fieldName: 'device_type' as FilterFieldName,
              operator: 'is' as FilterOperator,
              value: 'mobile',
            },
          ]}
          onToggle={fn()}
          lockedFilters={[]}
        />
      </div>
      <div className="px-4 py-3">
        <p className="text-sm text-muted-foreground">Additional filter controls would go here...</p>
      </div>
    </div>
  ),
}

export const AllStates: Story = {
  render: () => (
    <div className="space-y-6">
      <div>
        <p className="text-xs text-muted-foreground mb-2">No filters active</p>
        <QuickFilters definitions={mockDefinitions} activeFilters={[]} onToggle={fn()} lockedFilters={[]} />
      </div>
      <div>
        <p className="text-xs text-muted-foreground mb-2">One filter active</p>
        <QuickFilters
          definitions={mockDefinitions}
          activeFilters={[
            {
              id: 'filter-1',
              fieldName: 'device_type' as FilterFieldName,
              operator: 'is' as FilterOperator,
              value: 'mobile',
            },
          ]}
          onToggle={fn()}
          lockedFilters={[]}
        />
      </div>
      <div>
        <p className="text-xs text-muted-foreground mb-2">Multiple filters active</p>
        <QuickFilters
          definitions={mockDefinitions}
          activeFilters={[
            {
              id: 'filter-1',
              fieldName: 'device_type' as FilterFieldName,
              operator: 'is' as FilterOperator,
              value: 'mobile',
            },
            {
              id: 'filter-2',
              fieldName: 'logged_in' as FilterFieldName,
              operator: 'is' as FilterOperator,
              value: '1',
            },
            {
              id: 'filter-3',
              fieldName: 'is_bot' as FilterFieldName,
              operator: 'is' as FilterOperator,
              value: '0',
            },
          ]}
          onToggle={fn()}
          lockedFilters={[]}
        />
      </div>
      <div>
        <p className="text-xs text-muted-foreground mb-2">With locked filter</p>
        <QuickFilters
          definitions={mockDefinitions}
          activeFilters={[]}
          onToggle={fn()}
          lockedFilters={[
            {
              id: 'locked-1',
              label: 'User Type',
              operator: 'is',
              value: 'Logged-in',
            },
          ]}
        />
      </div>
    </div>
  ),
}
