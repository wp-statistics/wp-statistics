import type { Meta, StoryObj } from '@storybook/react'
import { ColumnsIcon, FilterIcon, GaugeIcon, LayoutGridIcon } from 'lucide-react'
import { useState } from 'react'
import { expect, fn, userEvent, within } from 'storybook/test'

import {
  LockedMenuItem,
  OptionsDetailView,
  OptionsDrawer,
  OptionsMenuItem,
  OptionsToggleItem,
} from './options-drawer'
import { OptionsDrawerTrigger } from './options-drawer-trigger'

const meta = {
  title: 'Custom/OptionsDrawer',
  component: OptionsDrawer,
  parameters: {
    layout: 'fullscreen',
  },
  tags: ['autodocs'],
} satisfies Meta<typeof OptionsDrawer>

export default meta
type Story = StoryObj<typeof meta>

// Wrapper component to manage drawer state
function DrawerDemo({ children }: { children: React.ReactNode }) {
  const [open, setOpen] = useState(false)

  return (
    <div className="p-8">
      <OptionsDrawerTrigger onClick={() => setOpen(true)} />
      <OptionsDrawer open={open} onOpenChange={setOpen}>
        {children}
      </OptionsDrawer>
    </div>
  )
}

export const Default: Story = {
  render: () => (
    <DrawerDemo>
      <OptionsMenuItem
        icon={<ColumnsIcon className="h-4 w-4" />}
        title="Show/hide columns"
        summary="2 hidden"
        onClick={fn()}
      />
      <OptionsMenuItem icon={<FilterIcon className="h-4 w-4" />} title="Filters" summary="1 applied" onClick={fn()} />
      <OptionsMenuItem icon={<GaugeIcon className="h-4 w-4" />} title="Metrics" onClick={fn()} />
      <LockedMenuItem icon={<LayoutGridIcon className="h-4 w-4" />} label="Widgets" />
    </DrawerDemo>
  ),
}

export const WithResetButton: Story = {
  render: function WithResetButtonStory() {
    const [open, setOpen] = useState(false)

    return (
      <div className="p-8">
        <OptionsDrawerTrigger onClick={() => setOpen(true)} />
        <OptionsDrawer open={open} onOpenChange={setOpen} onReset={fn()}>
          <OptionsMenuItem icon={<ColumnsIcon className="h-4 w-4" />} title="Columns" onClick={fn()} />
          <OptionsMenuItem icon={<FilterIcon className="h-4 w-4" />} title="Filters" onClick={fn()} />
        </OptionsDrawer>
      </div>
    )
  },
}

export const MenuItems: Story = {
  render: () => (
    <div className="p-8 max-w-md border rounded-lg">
      <div className="text-xs text-muted-foreground mb-4">Options Menu Items</div>
      <div className="border rounded-lg overflow-hidden">
        <OptionsMenuItem icon={<ColumnsIcon className="h-4 w-4" />} title="Show/hide columns" onClick={fn()} />
        <OptionsMenuItem
          icon={<FilterIcon className="h-4 w-4" />}
          title="Filters"
          summary="3 applied"
          onClick={fn()}
        />
        <OptionsMenuItem
          icon={<GaugeIcon className="h-4 w-4" />}
          title="Metrics"
          summary="1 hidden"
          onClick={fn()}
        />
      </div>
    </div>
  ),
}

export const LockedMenuItems: Story = {
  render: () => (
    <DrawerDemo>
      <OptionsMenuItem icon={<ColumnsIcon className="h-4 w-4" />} title="Show/hide columns" onClick={fn()} />
      <LockedMenuItem icon={<GaugeIcon className="h-4 w-4" />} label="Metrics" />
      <LockedMenuItem icon={<LayoutGridIcon className="h-4 w-4" />} label="Widgets" />
    </DrawerDemo>
  ),
}

export const DetailView: Story = {
  render: () => (
    <div className="p-8 max-w-md border rounded-lg">
      <div className="text-xs text-muted-foreground mb-4">Detail View</div>
      <OptionsDetailView description="Toggle which metrics to display in the overview section.">
        <div>
          <OptionsToggleItem label="Page Views" checked={true} onCheckedChange={fn()} />
          <OptionsToggleItem label="Visitors" checked={true} onCheckedChange={fn()} />
          <OptionsToggleItem label="Bounce Rate" checked={false} onCheckedChange={fn()} />
          <OptionsToggleItem label="Average Duration" checked={false} onCheckedChange={fn()} />
        </div>
      </OptionsDetailView>
    </div>
  ),
}

export const ToggleItems: Story = {
  render: function ToggleItemsStory() {
    const [items, setItems] = useState([
      { id: 'views', label: 'Page Views', checked: true },
      { id: 'visitors', label: 'Visitors', checked: true },
      { id: 'bounce', label: 'Bounce Rate', checked: false },
      { id: 'duration', label: 'Average Duration', checked: false },
      { id: 'referrers', label: 'Top Referrers', checked: true },
    ])

    const handleToggle = (id: string) => {
      setItems(items.map((item) => (item.id === id ? { ...item, checked: !item.checked } : item)))
    }

    return (
      <div className="p-8 max-w-md border rounded-lg">
        <div className="text-xs text-muted-foreground mb-4">Interactive Toggle Items</div>
        <div className="border rounded-lg overflow-hidden px-4 py-2">
          {items.map((item) => (
            <OptionsToggleItem
              key={item.id}
              label={item.label}
              checked={item.checked}
              onCheckedChange={() => handleToggle(item.id)}
            />
          ))}
        </div>
        <div className="mt-4 text-xs text-muted-foreground">
          Active: {items.filter((i) => i.checked).length} / {items.length}
        </div>
      </div>
    )
  },
}

export const DisabledToggleItem: Story = {
  render: () => (
    <div className="p-8 max-w-md border rounded-lg">
      <div className="text-xs text-muted-foreground mb-4">Disabled Toggle Items</div>
      <div className="border rounded-lg overflow-hidden px-4 py-2">
        <OptionsToggleItem label="Enabled Item" checked={true} onCheckedChange={fn()} />
        <OptionsToggleItem label="Disabled Item (checked)" checked={true} onCheckedChange={fn()} disabled />
        <OptionsToggleItem label="Disabled Item (unchecked)" checked={false} onCheckedChange={fn()} disabled />
      </div>
    </div>
  ),
}

export const AllComponents: Story = {
  render: function AllComponentsStory() {
    const [open, setOpen] = useState(false)
    const [metrics, setMetrics] = useState([
      { id: 'views', label: 'Page Views', checked: true },
      { id: 'visitors', label: 'Visitors', checked: true },
      { id: 'bounce', label: 'Bounce Rate', checked: false },
    ])

    return (
      <div className="p-8">
        <h3 className="text-sm font-medium mb-4">Full Options Drawer Demo</h3>
        <OptionsDrawerTrigger onClick={() => setOpen(true)} isActive={metrics.some((m) => !m.checked)} />
        <OptionsDrawer open={open} onOpenChange={setOpen} onReset={() => setMetrics(metrics.map((m) => ({ ...m, checked: true })))}>
          <OptionsMenuItem
            icon={<ColumnsIcon className="h-4 w-4" />}
            title="Show/hide columns"
            summary="2 hidden"
            onClick={fn()}
          />
          <OptionsMenuItem
            icon={<FilterIcon className="h-4 w-4" />}
            title="Filters"
            summary="1 applied"
            onClick={fn()}
          />
          <OptionsMenuItem
            icon={<GaugeIcon className="h-4 w-4" />}
            title="Metrics"
            summary={`${metrics.filter((m) => !m.checked).length} hidden`}
            onClick={fn()}
          />
          <LockedMenuItem icon={<LayoutGridIcon className="h-4 w-4" />} label="Widgets" />
        </OptionsDrawer>
      </div>
    )
  },
}
