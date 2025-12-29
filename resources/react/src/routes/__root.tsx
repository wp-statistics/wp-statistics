import { createRootRouteWithContext, Outlet } from '@tanstack/react-router'

import { AppSidebar } from '@/components/app-sidebar'
import { Header } from '@/components/header'
import { SidebarInset, SidebarProvider } from '@/components/ui/sidebar'

const RootLayout = () => (
  <SidebarProvider>
    <div className="flex flex-col h-[calc(var(--app-height)-var(--wp-admin-bar-height))] w-full overflow-hidden">
      <Header />
      <div className="flex flex-1 relative overflow-hidden min-w-0">
        <AppSidebar />
        <SidebarInset className="overflow-auto w-full">
          <Outlet />
        </SidebarInset>
      </div>
    </div>
  </SidebarProvider>
)

export const Route = createRootRouteWithContext<RouterContext>()({
  component: RootLayout,
})
