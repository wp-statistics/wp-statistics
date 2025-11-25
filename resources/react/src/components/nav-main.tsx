import { Link, useLocation, useNavigate } from '@tanstack/react-router'
import { ChevronRight, type LucideIcon } from 'lucide-react'

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

  const handleParentClick = (
    e: React.MouseEvent,
    item: { items?: { url: string }[] },
    isChevronClick: boolean
  ) => {
    if (item.items?.length && !isChevronClick) {
      // Navigate to the first submenu item only if not clicking the chevron
      e.preventDefault()
      navigate({ to: item.items[0].url as any })
    }
  }

  return (
    <SidebarGroup>
      <SidebarMenu>
        {items.map((item) => {
          // Check if any submenu item is active
          const isSubmenuActive = item.items?.some((subItem) => location.pathname === subItem.url)

          return (
            <Collapsible key={item.title} asChild defaultOpen={item.isActive || isSubmenuActive}>
              <SidebarMenuItem>
                {item.items?.length ? (
                  <>
                    <div className="relative group/parent">
                      <SidebarMenuButton
                        tooltip={item.title}
                        className="text-sidebar-foreground group-hover/parent:bg-sidebar-accent group-hover/parent:text-sidebar-accent-foreground data-[state=open]:bg-sidebar-accent data-[state=open]:text-sidebar-accent-foreground pr-8"
                        onClick={(e) => handleParentClick(e, item, false)}
                      >
                        <item.icon />
                        <span>{item.title}</span>
                      </SidebarMenuButton>
                      <CollapsibleTrigger asChild>
                        <button
                          className="absolute right-2 top-1/2 -translate-y-1/2 p-1 rounded z-10 text-sidebar-foreground group-hover/parent:text-sidebar-accent-foreground"
                          onClick={(e) => e.stopPropagation()}
                        >
                          <ChevronRight className="h-4 w-4 transition-transform duration-200 group-data-[state=open]/collapsible:rotate-90" />
                        </button>
                      </CollapsibleTrigger>
                    </div>
                    <CollapsibleContent>
                      <SidebarMenuSub>
                        {item.items?.map((subItem) => {
                          const isSubItemActive = location.pathname === subItem.url
                          return (
                            <SidebarMenuSubItem key={subItem.title}>
                              <SidebarMenuSubButton
                                asChild
                                isActive={isSubItemActive}
                                className="text-sidebar-foreground hover:bg-transparent hover:text-sidebar-foreground data-[active=true]:text-sidebar-accent-foreground"
                              >
                                <Link to={subItem.url as any}>
                                  <span>{subItem.title}</span>
                                </Link>
                              </SidebarMenuSubButton>
                            </SidebarMenuSubItem>
                          )
                        })}
                      </SidebarMenuSub>
                    </CollapsibleContent>
                  </>
                ) : (
                  <SidebarMenuButton
                    asChild
                    isActive={item.isActive}
                    tooltip={item.title}
                    className="text-sidebar-foreground hover:bg-sidebar-accent hover:text-sidebar-accent-foreground data-[active=true]:bg-sidebar-accent data-[active=true]:text-sidebar-accent-foreground"
                  >
                    <Link to={item.url as any}>
                      <item.icon />
                      <span>{item.title}</span>
                    </Link>
                  </SidebarMenuButton>
                )}
              </SidebarMenuItem>
            </Collapsible>
          )
        })}
      </SidebarMenu>
    </SidebarGroup>
  )
}
