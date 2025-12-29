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
    <span className="absolute left-0 top-1/2 -translate-y-1/2 h-5 w-[3px] rounded-full bg-white shadow-[0_0_8px_rgba(255,255,255,0.5)]" />
  )
})

// Sub-menu item component - memoized to prevent re-renders
const SubMenuItem = React.memo(function SubMenuItem({
  subItem,
  isActive,
}: {
  subItem: { title: string; url: string }
  isActive: boolean
}) {
  return (
    <SidebarMenuSubItem>
      <SidebarMenuSubButton
        asChild
        isActive={isActive}
        className={`cursor-pointer text-[13px] bg-transparent hover:bg-transparent focus:ring-0 focus:outline-none focus-visible:outline focus-visible:outline-2 focus-visible:outline-white/30 ${
          isActive
            ? 'text-white font-medium'
            : 'text-sidebar-foreground/70 hover:text-white'
        }`}
      >
        <Link to={subItem.url as any}>
          <span>{subItem.title}</span>
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
  onNavigate,
}: {
  item: {
    title: string
    url: string
    icon: LucideIcon
    isActive?: boolean
    items?: {
      title: string
      url: string
    }[]
  }
  isSubmenuActive: boolean
  activeSubItemUrl: string | null
  onNavigate: (url: string) => void
}) {
  const [isOpen, setIsOpen] = React.useState(item.isActive || isSubmenuActive)

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
  const menuButtonClasses = `relative cursor-pointer pe-8 bg-transparent hover:bg-sidebar-hover hover:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-white/30 focus-visible:outline-offset-[-2px] focus:ring-0 [&_svg]:opacity-60 hover:[&_svg]:opacity-100 ${
    isSubmenuActive
      ? 'text-white [&_svg]:opacity-100'
      : 'text-sidebar-foreground'
  }`

  const chevronButtonClasses = `cursor-pointer absolute end-2 top-1/2 -translate-y-1/2 p-1 rounded z-10 group-data-[collapsible=icon]:hidden ${
    isSubmenuActive
      ? 'text-white'
      : 'text-sidebar-foreground hover:text-white'
  }`

  const singleItemClasses = `relative cursor-pointer bg-transparent hover:bg-sidebar-hover hover:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-white/30 focus-visible:outline-offset-[-2px] focus:ring-0 [&_svg]:opacity-60 hover:[&_svg]:opacity-100 text-sidebar-foreground data-[active=true]:bg-sidebar-active data-[active=true]:text-white data-[active=true]:[&_svg]:opacity-100`

  return (
    <Collapsible asChild open={isOpen} onOpenChange={setIsOpen}>
      <SidebarMenuItem className="group/collapsible">
        {item.items?.length ? (
          <>
            <div className="relative group/parent">
              <SidebarMenuButton
                tooltip={item.title}
                className={menuButtonClasses}
                onClick={handleMenuClick}
              >
                {isSubmenuActive && <ActiveIndicator />}
                <item.icon />
                <span>{item.title}</span>
              </SidebarMenuButton>
              <CollapsibleTrigger asChild>
                <button className={chevronButtonClasses}>
                  <ChevronRight className="h-4 w-4 transition-transform duration-200 rtl:-scale-x-100 group-data-[state=open]/collapsible:rotate-90" />
                </button>
              </CollapsibleTrigger>
            </div>
            <CollapsibleContent>
              <SidebarMenuSub>
                {item.items.map((subItem) => (
                  <SubMenuItem
                    key={subItem.title}
                    subItem={subItem}
                    isActive={activeSubItemUrl === subItem.url}
                  />
                ))}
              </SidebarMenuSub>
            </CollapsibleContent>
          </>
        ) : (
          <SidebarMenuButton
            asChild
            isActive={item.isActive}
            tooltip={item.title}
            className={singleItemClasses}
          >
            <Link to={item.url as any} className="outline-none focus:outline-none">
              {item.isActive && <ActiveIndicator />}
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
    isActive?: boolean
    items?: {
      title: string
      url: string
    }[]
  }[]
}) {
  const location = useLocation()
  const navigate = useNavigate()

  // Memoize the navigate callback
  const handleNavigate = React.useCallback(
    (url: string) => navigate({ to: url as any }),
    [navigate]
  )

  return (
    <SidebarGroup>
      <SidebarMenu>
        {items.map((item) => {
          const activeSubItemUrl = item.items?.find((subItem) => location.pathname === subItem.url)?.url || null
          const isSubmenuActive = activeSubItemUrl !== null

          return (
            <NavMenuItem
              key={item.title}
              item={item}
              isSubmenuActive={isSubmenuActive}
              activeSubItemUrl={activeSubItemUrl}
              onNavigate={handleNavigate}
            />
          )
        })}
      </SidebarMenu>
    </SidebarGroup>
  )
}
