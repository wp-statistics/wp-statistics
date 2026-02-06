import { createRootRouteWithContext, Outlet, useRouterState } from '@tanstack/react-router'
import { useMemo } from 'react'

import { AppSidebar } from '@/components/app-sidebar'
import type { FilterField } from '@/components/custom/filter-button'
import { Header } from '@/components/header'
import { settingsNavItems, toolsNavItems } from '@/components/secondary-nav-items'
import { SecondarySidebar } from '@/components/secondary-sidebar'
import { SidebarInset, SidebarProvider } from '@/components/ui/sidebar'
import { GlobalFiltersProvider } from '@/contexts/global-filters-context'
import { WordPress } from '@/lib/wordpress'

const RootLayout = () => {
  const routerState = useRouterState()
  const isSettingsPage = routerState.location.pathname.startsWith('/settings')
  const isToolsPage = routerState.location.pathname.startsWith('/tools')
  const isNetworkAdmin = WordPress.getInstance().isNetworkAdmin()

  // Determine which sidebar to show
  const sidebar = isNetworkAdmin
    ? null
    : isSettingsPage
      ? <SecondarySidebar items={settingsNavItems} />
      : isToolsPage
        ? <SecondarySidebar items={toolsNavItems} />
        : <AppSidebar />

  // Get all filter fields so GlobalFiltersProvider can resolve labels from URL params
  const wp = WordPress.getInstance()
  const allFilterFields = useMemo<FilterField[]>(() => {
    const fields = wp.getFilterFields()
    return Object.values(fields) as FilterField[]
  }, [wp])

  return (
    <GlobalFiltersProvider filterFields={allFilterFields}>
      <SidebarProvider>
        <div className="flex flex-col h-[var(--wp-admin-menu-height)] w-full overflow-hidden">
          <Header />
          <div className="flex flex-1 relative overflow-hidden min-w-0">
            {sidebar}
            <SidebarInset className="overflow-auto w-full">
              <Outlet />
            </SidebarInset>
          </div>
        </div>
      </SidebarProvider>
    </GlobalFiltersProvider>
  )
}

export const Route = createRootRouteWithContext<RouterContext>()({
  component: RootLayout,
})
