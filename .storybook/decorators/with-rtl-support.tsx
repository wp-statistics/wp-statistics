import React from 'react'
import type { DecoratorFunction } from '@storybook/types'

/**
 * Decorator to sync Storybook RTL state with document.dir
 * This ensures components that check document.dir work correctly
 */
export const withRTLSupport: DecoratorFunction = (Story, context) => {
  const direction = context.globals.direction || 'ltr'

  React.useEffect(() => {
    document.dir = direction
    document.documentElement.dir = direction

    // Optional: Add RTL class for CSS selectors
    if (direction === 'rtl') {
      document.documentElement.classList.add('rtl')
    } else {
      document.documentElement.classList.remove('rtl')
    }
  }, [direction])

  return (
    <div dir={direction}>
      <Story />
    </div>
  )
}
