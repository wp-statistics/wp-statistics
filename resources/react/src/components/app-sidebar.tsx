import { useQuery } from '@tanstack/react-query'
import { useMemo } from 'react'

import { NavMain } from '@/components/nav-main'
import { Sidebar, SidebarContent, SidebarFooter, SidebarTrigger } from '@/components/ui/sidebar'
import { getIcon } from '@/lib/icon-registry'
import { WordPress } from '@/lib/wordpress'
import { getOnlineVisitorsQueryOptions } from '@/services/visitor-insight/get-online-visitors'

export function AppSidebar({ ...props }: React.ComponentProps<typeof Sidebar>) {
  const wordpress = WordPress.getInstance()
  const sidebarConfig = wordpress.getSidebarConfig()

  // Fetch online visitors count for sidebar badge
  const { data: onlineVisitorsData } = useQuery({
    ...getOnlineVisitorsQueryOptions({ per_page: 1, columns: ['visitor_id'] }),
    // Don't refetch too aggressively in sidebar
    refetchInterval: 60 * 1000, // 1 minute
  })
  const onlineVisitorsCount = onlineVisitorsData?.data?.meta?.total_rows ?? 0

  // Transform the WordPress sidebar config into the NavMain items format
  // Note: isActive is calculated in NavMain to avoid re-creating this array on route changes
  const navItems = useMemo(() => Object.entries(sidebarConfig).map(([, config]) => {
    const items = config.subPages
      ? Object.entries(config.subPages).map(([, subPage]) => ({
          title: subPage.label,
          url: `/${subPage.slug}`,
          // Add badge for online-visitors with live pulse animation
          ...(subPage.slug === 'online-visitors' && onlineVisitorsCount > 0 && { badge: onlineVisitorsCount, badgeLive: true }),
        }))
      : undefined

    return {
      title: config.label,
      url: `/${config.slug}`,
      icon: getIcon(config.icon),
      items,
    }
  }), [sidebarConfig, onlineVisitorsCount])

  return (
    <Sidebar variant="sidebar" collapsible="icon" {...props}>
      <SidebarContent className="pb-12">
        <NavMain items={navItems} />
      </SidebarContent>
      <SidebarFooter className="fixed bottom-0 left-0 w-(--sidebar-width) shrink-0 border-t border-border bg-sidebar transition-[width] duration-200 ease-linear group-data-[collapsible=icon]:w-(--sidebar-width-icon)">
        <SidebarTrigger className="ms-auto" />
      </SidebarFooter>
    </Sidebar>
  )
}
