import { createRootRouteWithContext, Outlet } from '@tanstack/react-router'

import { AppSidebar } from '@/components/app-sidebar'
import { Header } from '@/components/header'
import { SidebarInset, SidebarProvider } from '@/components/ui/sidebar'

const RootLayout = () => (
  <>
    <Header />
    <SidebarProvider>
      <div className="flex flex-1 relative h-[calc(100vh-var(--wp-admin-bar-height)-var(--header-height))] overflow-hidden">
        <AppSidebar />
        <SidebarInset className="overflow-auto">
          <Outlet />
        </SidebarInset>
      </div>
    </SidebarProvider>
  </>
)

export const Route = createRootRouteWithContext<RouterContext>()({
  component: RootLayout,
})
