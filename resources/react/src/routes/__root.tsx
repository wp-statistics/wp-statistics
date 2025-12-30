import { createRootRouteWithContext, Outlet } from '@tanstack/react-router'

import { AppSidebar } from '@/components/app-sidebar'
import { Header } from '@/components/header'
import { SidebarInset, SidebarProvider } from '@/components/ui/sidebar'
import { GlobalFiltersProvider } from '@/contexts/global-filters-context'

const RootLayout = () => (
  <GlobalFiltersProvider>
    <SidebarProvider>
      <div className="flex flex-col h-[var(--wp-admin-menu-height)] w-full overflow-hidden">
        <Header />
        <div className="flex flex-1 relative overflow-hidden min-w-0">
          <AppSidebar />
          <SidebarInset className="overflow-auto w-full">
            <Outlet />
          </SidebarInset>
        </div>
      </div>
    </SidebarProvider>
  </GlobalFiltersProvider>
)

export const Route = createRootRouteWithContext<RouterContext>()({
  component: RootLayout,
})
