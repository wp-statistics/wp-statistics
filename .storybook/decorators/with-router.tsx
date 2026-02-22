import { createMemoryHistory, createRootRoute, createRouter, RouterProvider } from '@tanstack/react-router'
import type { Decorator } from '@storybook/react'
import { useMemo } from 'react'

export const withRouter: Decorator = (Story) => {
  const router = useMemo(() => {
    const rootRoute = createRootRoute({ component: Story })
    return createRouter({
      routeTree: rootRoute,
      history: createMemoryHistory({ initialEntries: ['/'] }),
    })
  }, [Story])

  return <RouterProvider router={router} />
}
