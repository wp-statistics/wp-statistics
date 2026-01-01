import type { Meta, StoryObj } from '@storybook/react'
import { ChevronsUpDown } from 'lucide-react'
import * as React from 'react'

import { Button } from './button'
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from './collapsible'

const meta = {
  title: 'UI/Collapsible',
  component: Collapsible,
  parameters: {
    layout: 'centered',
  },
  tags: ['autodocs'],
} satisfies Meta<typeof Collapsible>

export default meta
type Story = StoryObj<typeof meta>

export const Default: Story = {
  render: function DefaultExample() {
    const [isOpen, setIsOpen] = React.useState(false)

    return (
      <Collapsible open={isOpen} onOpenChange={setIsOpen} className="w-[350px] space-y-2">
        <div className="flex items-center justify-between space-x-4 px-4">
          <h4 className="text-sm font-semibold">@shadcn starred 3 repositories</h4>
          <CollapsibleTrigger asChild>
            <Button variant="ghost" size="sm" className="w-9 p-0">
              <ChevronsUpDown className="size-4" />
              <span className="sr-only">Toggle</span>
            </Button>
          </CollapsibleTrigger>
        </div>
        <div className="rounded-md border px-4 py-3 font-mono text-sm">@radix-ui/primitives</div>
        <CollapsibleContent className="space-y-2">
          <div className="rounded-md border px-4 py-3 font-mono text-sm">@radix-ui/colors</div>
          <div className="rounded-md border px-4 py-3 font-mono text-sm">@stitches/react</div>
        </CollapsibleContent>
      </Collapsible>
    )
  },
}

export const InitiallyOpen: Story = {
  render: function InitiallyOpenExample() {
    const [isOpen, setIsOpen] = React.useState(true)

    return (
      <Collapsible open={isOpen} onOpenChange={setIsOpen} className="w-[350px] space-y-2">
        <div className="flex items-center justify-between space-x-4 px-4">
          <h4 className="text-sm font-semibold">Settings</h4>
          <CollapsibleTrigger asChild>
            <Button variant="ghost" size="sm" className="w-9 p-0">
              <ChevronsUpDown className="size-4" />
              <span className="sr-only">Toggle</span>
            </Button>
          </CollapsibleTrigger>
        </div>
        <CollapsibleContent className="space-y-2">
          <div className="rounded-md border px-4 py-3 text-sm">
            <p className="font-medium">Notifications</p>
            <p className="text-muted-foreground">Enable email notifications</p>
          </div>
          <div className="rounded-md border px-4 py-3 text-sm">
            <p className="font-medium">Privacy</p>
            <p className="text-muted-foreground">Manage your privacy settings</p>
          </div>
          <div className="rounded-md border px-4 py-3 text-sm">
            <p className="font-medium">Security</p>
            <p className="text-muted-foreground">Two-factor authentication</p>
          </div>
        </CollapsibleContent>
      </Collapsible>
    )
  },
}

export const Simple: Story = {
  render: () => (
    <Collapsible className="w-[300px]">
      <CollapsibleTrigger className="flex w-full items-center justify-between rounded-md border p-4 font-medium">
        Can I use this in my project?
        <ChevronsUpDown className="size-4" />
      </CollapsibleTrigger>
      <CollapsibleContent className="px-4 py-2 text-sm text-muted-foreground">
        Yes. Free to use for personal and commercial projects. No attribution required.
      </CollapsibleContent>
    </Collapsible>
  ),
}

export const FAQ: Story = {
  render: function FAQExample() {
    const faqs = [
      {
        question: 'Is it accessible?',
        answer: 'Yes. It adheres to the WAI-ARIA design pattern.',
      },
      {
        question: 'Is it styled?',
        answer: 'Yes. It comes with default styles that matches the other components aesthetic.',
      },
      {
        question: 'Is it animated?',
        answer: 'Yes. It is animated by default, but you can disable it if you prefer.',
      },
    ]

    return (
      <div className="w-[400px] space-y-2">
        {faqs.map((faq, index) => (
          <Collapsible key={index} className="rounded-md border">
            <CollapsibleTrigger className="flex w-full items-center justify-between p-4 font-medium hover:bg-accent rounded-md">
              {faq.question}
              <ChevronsUpDown className="size-4" />
            </CollapsibleTrigger>
            <CollapsibleContent className="px-4 pb-4 text-sm text-muted-foreground">{faq.answer}</CollapsibleContent>
          </Collapsible>
        ))}
      </div>
    )
  },
}
