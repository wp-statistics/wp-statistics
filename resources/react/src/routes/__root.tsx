import { createRootRouteWithContext, Outlet, useNavigate, useRouterState } from '@tanstack/react-router'
import { useEffect, useMemo } from 'react'

import { AppSidebar } from '@/components/app-sidebar'
import type { FilterField } from '@/components/custom/filter-button'
import { Header } from '@/components/header'
import { getNavItemsFromConfig, settingsNavItems, toolsNavItems } from '@/components/secondary-nav-items'
import { SecondarySidebar } from '@/components/secondary-sidebar'
import { SidebarInset, SidebarProvider } from '@/components/ui/sidebar'
import { Toaster } from '@/components/ui/toaster'
import { GlobalFiltersProvider } from '@/contexts/global-filters-context'
import { WordPress } from '@/lib/wordpress'
import { useSettingsConfig } from '@/registry/settings-registry'

/** Routes that show individual visitor data (PII). Blocked for view_stats and own_content users. */
const PII_ROUTES = ['/visitors', '/online-visitors', '/top-visitors', '/logged-in-users', '/referred-visitors']
const PII_ROUTE_PREFIX = '/visitor/'

/** Routes allowed for own_content users (overview + content analytics). */
const OWN_CONTENT_ALLOWED_PREFIXES = [
  '/overview',
  '/content',
  '/categories',
  '/category/',
  '/authors',
  '/author/',
  '/top-authors',
  '/top-categories',
  '/page-analytics',
  '/category-analytics',
  '/author-analytics',
]

const RootLayout = () => {
  const routerState = useRouterState()
  const navigate = useNavigate()
  const wp = WordPress.getInstance()
  const isSettingsPage = routerState.location.pathname.startsWith('/settings')
  const isToolsPage = routerState.location.pathname.startsWith('/tools')
  const isNetworkAdmin = wp.isNetworkAdmin()

  // Route guard: restrict access based on user level
  const accessLevel = wp.getAccessLevel()
  const pathname = routerState.location.pathname

  useEffect(() => {
    // none: block all pages
    if (accessLevel === 'none') {
      void navigate({ to: '/overview', replace: true })
      return
    }

    // own_content: only overview + content analytics
    if (accessLevel === 'own_content') {
      const isAllowed = OWN_CONTENT_ALLOWED_PREFIXES.some(
        (prefix) => pathname === prefix || pathname.startsWith(prefix + '/')
      )
      if (!isAllowed) {
        void navigate({ to: '/overview', replace: true })
      }
      return
    }

    // view_stats: block PII routes
    if (accessLevel === 'view_stats') {
      const isPiiRoute = PII_ROUTES.includes(pathname) || pathname.startsWith(PII_ROUTE_PREFIX)
      if (isPiiRoute) {
        void navigate({ to: '/overview', replace: true })
      }
    }
  }, [accessLevel, pathname, navigate])

  // Build nav items from config when available, fall back to static arrays
  const { config } = useSettingsConfig()
  const dynamicSettingsNav = useMemo(
    () => (config ? getNavItemsFromConfig(config, 'settings') : settingsNavItems),
    [config]
  )
  const dynamicToolsNav = useMemo(
    () => (config ? getNavItemsFromConfig(config, 'tools') : toolsNavItems),
    [config]
  )

  // Determine which sidebar to show
  const sidebar = isNetworkAdmin
    ? null
    : isSettingsPage
      ? <SecondarySidebar items={dynamicSettingsNav} />
      : isToolsPage
        ? <SecondarySidebar items={dynamicToolsNav} />
        : <AppSidebar />

  // Get all filter fields so GlobalFiltersProvider can resolve labels from URL params
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
      <Toaster />
    </GlobalFiltersProvider>
  )
}

export const Route = createRootRouteWithContext<RouterContext>()({
  component: RootLayout,
})
