import { NavMain } from '@/components/nav-main'
import type { SecondaryNavItem } from '@/components/secondary-nav-items'
import { Sidebar, SidebarContent, SidebarFooter, SidebarTrigger } from '@/components/ui/sidebar'

interface SecondarySidebarProps extends React.ComponentProps<typeof Sidebar> {
  items: SecondaryNavItem[]
}

export function SecondarySidebar({ items, ...props }: SecondarySidebarProps) {
  return (
    <Sidebar variant="sidebar" collapsible="icon" {...props}>
      <SidebarContent>
        <NavMain items={items} />
      </SidebarContent>
      <SidebarFooter>
        <SidebarTrigger className="ms-auto" />
      </SidebarFooter>
    </Sidebar>
  )
}
