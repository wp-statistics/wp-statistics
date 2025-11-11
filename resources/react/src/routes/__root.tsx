import { createRootRouteWithContext, Outlet } from '@tanstack/react-router'

import { AppSidebar } from '@/components/app-sidebar'
import { Header } from '@/components/header'
import { SidebarInset, SidebarProvider } from '@/components/ui/sidebar'

const RootLayout = () => (
  <>
    <Header />
    <SidebarProvider>
      <div className="flex flex-1 relative min-h-[calc(100vh-var(--header-height,0px))]">
        <AppSidebar />
        <SidebarInset className="flex-1">
          <div className="flex flex-1 flex-col gap-4 p-4">
            <Outlet />
          </div>
        </SidebarInset>
      </div>
    </SidebarProvider>
  </>
)

export const Route = createRootRouteWithContext<RouterContext>()({
  component: RootLayout,
})
