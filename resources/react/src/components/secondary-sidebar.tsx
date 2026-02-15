import { NavMain } from '@/components/nav-main'
import type { SecondaryNavItem } from '@/components/secondary-nav-items'
import { Sidebar, SidebarContent, SidebarFooter, SidebarTrigger } from '@/components/ui/sidebar'

interface SecondarySidebarProps extends React.ComponentProps<typeof Sidebar> {
  items: SecondaryNavItem[]
}

export function SecondarySidebar({ items, ...props }: SecondarySidebarProps) {
  return (
    <Sidebar variant="sidebar" collapsible="icon" {...props}>
      <SidebarContent className="pb-12">
        <NavMain items={items} />
      </SidebarContent>
      <SidebarFooter className="fixed bottom-0 left-(--wp-admin-sidebar-width) z-20 w-(--sidebar-width) shrink-0 bg-sidebar transition-[width] duration-200 ease-linear group-data-[collapsible=icon]:w-(--sidebar-width-icon)">
        <SidebarTrigger className="ms-auto" />
      </SidebarFooter>
    </Sidebar>
  )
}
