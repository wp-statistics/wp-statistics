import { useLocation } from '@tanstack/react-router'

import { NavMain } from '@/components/nav-main'
import { Sidebar, SidebarContent, SidebarFooter, SidebarTrigger } from '@/components/ui/sidebar'
import { getIcon } from '@/lib/icon-registry'
import { WordPress } from '@/lib/wordpress'

export function AppSidebar({ ...props }: React.ComponentProps<typeof Sidebar>) {
  const location = useLocation()
  const wordpress = WordPress.getInstance()
  const sidebarConfig = wordpress.getSidebarConfig()

  // Transform the WordPress sidebar config into the NavMain items format
  const navItems = Object.entries(sidebarConfig).map(([, config]) => {
    const items = config.subPages
      ? Object.entries(config.subPages).map(([, subPage]) => ({
          title: subPage.label,
          url: `/${subPage.slug}`,
        }))
      : undefined

    const itemUrl = `/${config.slug}`
    const isActive = location.pathname === itemUrl

    return {
      title: config.label,
      url: itemUrl,
      icon: getIcon(config.icon),
      isActive,
      items,
    }
  })

  return (
    <Sidebar variant="sidebar" collapsible="icon" {...props}>
      <SidebarContent className="pb-14">
        <NavMain items={navItems} />
      </SidebarContent>
      <SidebarFooter className="absolute bottom-0 left-0 right-0 shrink-0 border-t border-border bg-sidebar">
        <SidebarTrigger className="ms-auto" />
      </SidebarFooter>
    </Sidebar>
  )
}
