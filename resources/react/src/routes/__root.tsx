import { createRootRouteWithContext, Outlet } from '@tanstack/react-router'

import { AppSidebar } from '@/components/app-sidebar'
import { Header } from '@/components/header'
import { SidebarInset, SidebarProvider } from '@/components/ui/sidebar'

const RootLayout = () => (
  <>
    <Header />
    <SidebarProvider>
      <div className="flex flex-1 relative min-h-[calc(100vh-var(--header-height,0px))] overflow-hidden">
        <AppSidebar />
        <SidebarInset className="overflow-x-auto">
          <Outlet />
        </SidebarInset>
      </div>
    </SidebarProvider>
  </>
)

export const Route = createRootRouteWithContext<RouterContext>()({
  component: RootLayout,
})
