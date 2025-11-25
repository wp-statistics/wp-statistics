import { useLocation } from '@tanstack/react-router'
import type { LucideIcon } from 'lucide-react'
import * as LucideIcons from 'lucide-react'

import { NavMain } from '@/components/nav-main'
import { Sidebar, SidebarContent, SidebarFooter, SidebarTrigger } from '@/components/ui/sidebar'
import { WordPress } from '@/lib/wordpress'

// Map icon names from the config to actual Lucide icon components
const getIconComponent = (iconName: string): LucideIcon => {
  const icon = (LucideIcons as any)[iconName]
  return icon || LucideIcons.Circle // Fallback to Circle if icon not found
}

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
      icon: getIconComponent(config.icon),
      isActive,
      items,
    }
  })

  return (
    <Sidebar variant="sidebar" collapsible="icon" {...props}>
      <SidebarContent>
        <NavMain items={navItems} />
      </SidebarContent>
      <SidebarFooter className="sticky bottom-0 border-t border-border bg-sidebar">
        <SidebarTrigger className="ms-auto" />
      </SidebarFooter>
    </Sidebar>
  )
}
