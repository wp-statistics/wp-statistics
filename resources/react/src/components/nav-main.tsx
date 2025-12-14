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

function NavMenuItem({
  item,
  isSubmenuActive,
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
  onNavigate: (url: string) => void
}) {
  const location = useLocation()
  const [isOpen, setIsOpen] = React.useState(item.isActive || isSubmenuActive)

  // Update isOpen when isSubmenuActive changes
  React.useEffect(() => {
    if (isSubmenuActive) {
      setIsOpen(true)
    }
  }, [isSubmenuActive])

  const handleMenuClick = () => {
    if (item.items?.length) {
      setIsOpen(true)
      onNavigate(item.items[0].url)
    }
  }

  return (
    <Collapsible asChild open={isOpen} onOpenChange={setIsOpen}>
      <SidebarMenuItem className="group/collapsible">
        {item.items?.length ? (
          <>
            <div className="relative group/parent">
              <SidebarMenuButton
                tooltip={item.title}
                className={`cursor-pointer [&]:!bg-transparent [&]:hover:!bg-transparent [&]:active:!bg-transparent [&]:focus:!bg-transparent [&]:focus-visible:!ring-0 pe-8 ${
                  isSubmenuActive ? '[&]:!text-white [&]:hover:!text-white [&]:active:!text-white [&]:focus:!text-white' : '[&]:!text-sidebar-foreground [&]:hover:!text-sidebar-foreground [&]:active:!text-sidebar-foreground [&]:focus:!text-sidebar-foreground'
                }`}
                onClick={handleMenuClick}
              >
                <item.icon />
                <span>{item.title}</span>
              </SidebarMenuButton>
              <CollapsibleTrigger asChild>
                <button className={`cursor-pointer absolute end-2 top-1/2 -translate-y-1/2 p-1 rounded z-10 group-data-[collapsible=icon]:hidden ${
                  isSubmenuActive ? '!text-white hover:!text-white' : '[&]:!text-sidebar-foreground hover:!text-sidebar-foreground'
                }`}>
                  <ChevronRight className="h-4 w-4 transition-transform duration-200 rtl:-scale-x-100 group-data-[state=open]/collapsible:rotate-90" />
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
                        className="cursor-pointer text-sidebar-foreground hover:bg-transparent hover:text-sidebar-foreground data-[active=true]:bg-transparent data-[active=true]:text-white focus:outline-none focus:ring-0 focus:bg-transparent focus:text-sidebar-foreground focus-visible:ring-0 focus-visible:outline-none active:bg-transparent active:text-sidebar-foreground"
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
            className="cursor-pointer [&]:!text-sidebar-foreground [&]:!bg-transparent [&]:!font-normal [&]:hover:!bg-transparent [&]:hover:!text-sidebar-foreground [&]:active:!bg-transparent [&]:active:!text-sidebar-foreground [&]:focus:!bg-transparent [&]:focus:!text-sidebar-foreground [&]:focus:!ring-0 [&]:focus:!outline-none [&]:focus-visible:!ring-0 [&]:focus-visible:!outline-none [&]:!ring-0 [&]:!outline-none [&[data-active=true]]:!bg-transparent [&[data-active=true]]:!text-white [&[data-active=true]]:!font-normal"
          >
            <Link to={item.url as any} className="!outline-none !ring-0 focus:!outline-none focus:!ring-0">
              <item.icon />
              <span>{item.title}</span>
            </Link>
          </SidebarMenuButton>
        )}
      </SidebarMenuItem>
    </Collapsible>
  )
}

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

  return (
    <SidebarGroup>
      <SidebarMenu>
        {items.map((item) => {
          const isSubmenuActive = item.items?.some((subItem) => location.pathname === subItem.url) || false

          return (
            <NavMenuItem
              key={item.title}
              item={item}
              isSubmenuActive={isSubmenuActive}
              onNavigate={(url) => navigate({ to: url as any })}
            />
          )
        })}
      </SidebarMenu>
    </SidebarGroup>
  )
}
