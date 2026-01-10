import { Link, useLocation, useNavigate } from '@tanstack/react-router'
import { ChevronRight, type LucideIcon } from 'lucide-react'
import * as React from 'react'

import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible'
import {
  SidebarGroup,
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
  SidebarMenuSub,
  SidebarMenuSubButton,
  SidebarMenuSubItem,
} from '@/components/ui/sidebar'

// Active indicator component
const ActiveIndicator = React.memo(function ActiveIndicator() {
  return (
    <span className="absolute left-0 top-1/2 -translate-y-1/2 h-5 w-[3px] rounded-full bg-primary shadow-[0_0_8px_rgba(79,70,229,0.3)]" />
  )
})

// Badge component for menu items
const MenuBadge = React.memo(function MenuBadge({
  count,
  live = false,
  isActive = false,
}: {
  count: number
  live?: boolean
  isActive?: boolean
}) {
  if (count <= 0) return null

  // Format large numbers with commas
  const formattedCount = count.toLocaleString()

  if (live) {
    return (
      <span className="ms-auto ps-3 inline-flex items-center gap-1.5 overflow-visible">
        <span className="relative flex h-2 w-2 overflow-visible shrink-0">
          <span className="absolute inline-flex h-full w-full rounded-full bg-red-500 animate-live-pulse" />
          <span className="relative inline-flex h-2 w-2 rounded-full bg-red-500" />
        </span>
        <span className={`text-xs font-medium ${isActive ? 'text-sidebar-accent-foreground' : 'text-sidebar-foreground/70'}`}>
          {formattedCount}
        </span>
      </span>
    )
  }

  return (
    <span className="ms-auto inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 text-[11px] font-medium rounded-full bg-[#3B82F6] text-white">
      {count > 99999 ? '99k+' : formattedCount}
    </span>
  )
})

// Sub-menu item component - memoized to prevent re-renders
const SubMenuItem = React.memo(function SubMenuItem({
  subItem,
  isActive,
}: {
  subItem: { title: string; url: string; badge?: number; badgeLive?: boolean }
  isActive: boolean
}) {
  return (
    <SidebarMenuSubItem>
      <SidebarMenuSubButton
        asChild
        isActive={isActive}
        className={`cursor-pointer text-sm bg-transparent hover:bg-transparent focus:ring-0 focus:outline-none focus-visible:outline focus-visible:outline-2 focus-visible:outline-primary/30 overflow-visible ${
          isActive ? 'text-sidebar-accent-foreground font-medium' : 'text-sidebar-foreground/70 hover:text-sidebar-foreground'
        }`}
      >
        <Link to={subItem.url as any} className="overflow-visible">
          <span>{subItem.title}</span>
          {subItem.badge !== undefined && (
            <MenuBadge count={subItem.badge} live={subItem.badgeLive} isActive={isActive} />
          )}
        </Link>
      </SidebarMenuSubButton>
    </SidebarMenuSubItem>
  )
})

// Menu item component - memoized
const NavMenuItem = React.memo(function NavMenuItem({
  item,
  isSubmenuActive,
  activeSubItemUrl,
  isSingleItemActive,
  onNavigate,
}: {
  item: {
    title: string
    url: string
    icon: LucideIcon
    items?: {
      title: string
      url: string
      badge?: number
      badgeLive?: boolean
    }[]
  }
  isSubmenuActive: boolean
  activeSubItemUrl: string | null
  isSingleItemActive: boolean
  onNavigate: (url: string) => void
}) {
  const [isOpen, setIsOpen] = React.useState(isSubmenuActive)

  // Update isOpen when isSubmenuActive changes
  React.useEffect(() => {
    if (isSubmenuActive) {
      setIsOpen(true)
    }
  }, [isSubmenuActive])

  const handleMenuClick = React.useCallback(() => {
    if (item.items?.length) {
      setIsOpen(true)
      onNavigate(item.items[0].url)
    }
  }, [item.items, onNavigate])

  // Stable class strings
  const menuButtonClasses = `relative cursor-pointer pe-8 bg-transparent hover:bg-sidebar-hover hover:text-sidebar-foreground focus-visible:outline focus-visible:outline-2 focus-visible:outline-primary/30 focus-visible:outline-offset-[-2px] focus:ring-0 [&_svg]:opacity-60 hover:[&_svg]:opacity-100 ${
    isSubmenuActive ? 'text-sidebar-accent-foreground [&_svg]:opacity-100' : 'text-sidebar-foreground'
  }`

  const chevronButtonClasses = `cursor-pointer absolute end-2 top-1/2 -translate-y-1/2 p-1 rounded z-10 group-data-[collapsible=icon]:hidden ${
    isSubmenuActive ? 'text-sidebar-accent-foreground' : 'text-sidebar-foreground hover:text-sidebar-foreground'
  }`

  const singleItemClasses = `relative cursor-pointer bg-transparent hover:bg-sidebar-hover hover:text-sidebar-foreground focus-visible:outline focus-visible:outline-2 focus-visible:outline-primary/30 focus-visible:outline-offset-[-2px] focus:ring-0 [&_svg]:opacity-60 hover:[&_svg]:opacity-100 text-sidebar-foreground data-[active=true]:bg-sidebar-active data-[active=true]:text-sidebar-accent-foreground data-[active=true]:[&_svg]:opacity-100`

  return (
    <Collapsible asChild open={isOpen} onOpenChange={setIsOpen}>
      <SidebarMenuItem className="group/collapsible">
        {item.items?.length ? (
          <>
            <div className="relative group/parent">
              <SidebarMenuButton tooltip={item.title} className={menuButtonClasses} onClick={handleMenuClick}>
                {isSubmenuActive && <ActiveIndicator />}
                <item.icon />
                <span>{item.title}</span>
              </SidebarMenuButton>
              <CollapsibleTrigger asChild>
                <button className={chevronButtonClasses} aria-label={isOpen ? 'Collapse submenu' : 'Expand submenu'}>
                  <ChevronRight className="h-4 w-4 transition-transform duration-200 rtl:-scale-x-100 group-data-[state=open]/collapsible:rotate-90" />
                </button>
              </CollapsibleTrigger>
            </div>
            <CollapsibleContent>
              <SidebarMenuSub>
                {item.items.map((subItem) => (
                  <SubMenuItem key={subItem.title} subItem={subItem} isActive={activeSubItemUrl === subItem.url} />
                ))}
              </SidebarMenuSub>
            </CollapsibleContent>
          </>
        ) : (
          <SidebarMenuButton asChild isActive={isSingleItemActive} tooltip={item.title} className={singleItemClasses}>
            <Link to={item.url as any} className="outline-none focus:outline-none">
              {isSingleItemActive && <ActiveIndicator />}
              <item.icon />
              <span>{item.title}</span>
            </Link>
          </SidebarMenuButton>
        )}
      </SidebarMenuItem>
    </Collapsible>
  )
})

export function NavMain({
  items,
}: {
  items: {
    title: string
    url: string
    icon: LucideIcon
    items?: {
      title: string
      url: string
      badge?: number
      badgeLive?: boolean
    }[]
  }[]
}) {
  const location = useLocation()
  const navigate = useNavigate()

  // Memoize the navigate callback
  const handleNavigate = React.useCallback((url: string) => navigate({ to: url as any }), [navigate])

  return (
    <SidebarGroup>
      <SidebarMenu>
        {items.map((item) => {
          // Calculate active states here to avoid prop drilling and re-renders
          const activeSubItemUrl = item.items?.find((subItem) => location.pathname === subItem.url)?.url || null
          const isSubmenuActive = activeSubItemUrl !== null
          // For single items (no subitems), check if current path matches
          const isSingleItemActive = !item.items?.length && location.pathname === item.url

          return (
            <NavMenuItem
              key={item.title}
              item={item}
              isSubmenuActive={isSubmenuActive}
              activeSubItemUrl={activeSubItemUrl}
              isSingleItemActive={isSingleItemActive}
              onNavigate={handleNavigate}
            />
          )
        })}
      </SidebarMenu>
    </SidebarGroup>
  )
}
