import type { Meta, StoryObj } from '@storybook/react'
import { Info } from 'lucide-react'

import { Button } from '@/components/ui/button'

import { TooltipWrapper } from './tooltip-wrapper'

const meta = {
  title: 'Custom/TooltipWrapper',
  component: TooltipWrapper,
  parameters: {
    layout: 'centered',
  },
  tags: ['autodocs'],
} satisfies Meta<typeof TooltipWrapper>

export default meta
type Story = StoryObj<typeof meta>

export const Default: Story = {
  args: {
    content: 'This is a tooltip',
    children: <Button variant="outline">Hover me</Button>,
  },
}

export const TopPosition: Story = {
  args: {
    content: 'Tooltip on top',
    side: 'top',
    children: <Button variant="outline">Top</Button>,
  },
}

export const RightPosition: Story = {
  args: {
    content: 'Tooltip on right',
    side: 'right',
    children: <Button variant="outline">Right</Button>,
  },
}

export const BottomPosition: Story = {
  args: {
    content: 'Tooltip on bottom',
    side: 'bottom',
    children: <Button variant="outline">Bottom</Button>,
  },
}

export const LeftPosition: Story = {
  args: {
    content: 'Tooltip on left',
    side: 'left',
    children: <Button variant="outline">Left</Button>,
  },
}

export const WithIcon: Story = {
  args: {
    content: 'More information here',
    children: (
      <button className="flex items-center">
        <Info className="h-4 w-4 text-muted-foreground" />
      </button>
    ),
  },
}

export const WithRichContent: Story = {
  args: {
    content: (
      <div className="space-y-1">
        <p className="font-semibold">Rich Tooltip</p>
        <p className="text-xs">With multiple lines of content</p>
      </div>
    ),
    children: <Button variant="secondary">Rich Content</Button>,
  },
}

export const NoArrow: Story = {
  args: {
    content: 'No arrow tooltip',
    showArrow: false,
    children: <Button variant="ghost">No Arrow</Button>,
  },
}

export const LongContent: Story = {
  args: {
    content: 'This is a very long tooltip message that provides additional context about the element',
    children: <Button variant="outline">Long Tooltip</Button>,
  },
}

export const AllPositions: Story = {
  render: () => (
    <div className="grid grid-cols-3 gap-8 p-8">
      <div />
      <TooltipWrapper content="Top" side="top">
        <Button variant="outline" className="w-full">
          Top
        </Button>
      </TooltipWrapper>
      <div />

      <TooltipWrapper content="Left" side="left">
        <Button variant="outline" className="w-full">
          Left
        </Button>
      </TooltipWrapper>
      <div className="flex items-center justify-center text-sm text-muted-foreground">Center</div>
      <TooltipWrapper content="Right" side="right">
        <Button variant="outline" className="w-full">
          Right
        </Button>
      </TooltipWrapper>

      <div />
      <TooltipWrapper content="Bottom" side="bottom">
        <Button variant="outline" className="w-full">
          Bottom
        </Button>
      </TooltipWrapper>
      <div />
    </div>
  ),
}
