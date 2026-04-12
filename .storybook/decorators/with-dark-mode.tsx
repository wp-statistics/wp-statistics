import React from 'react'
import type { DecoratorFunction } from '@storybook/types'

/**
 * Decorator to toggle dark mode in Storybook.
 * Adds/removes the .dark class so CSS variable-based theming applies.
 */
export const withDarkMode: DecoratorFunction = (Story, context) => {
  const theme = context.globals.theme || 'light'

  React.useEffect(() => {
    if (theme === 'dark') {
      document.documentElement.classList.add('dark')
    } else {
      document.documentElement.classList.remove('dark')
    }
  }, [theme])

  return (
    <div className={theme === 'dark' ? 'dark bg-background text-foreground' : ''}>
      <Story />
    </div>
  )
}
