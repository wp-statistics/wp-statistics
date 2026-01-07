import { Link, useLocation } from '@tanstack/react-router'
import { Settings, Shield, Bell, Ban, Wrench, Monitor, Users, Database } from 'lucide-react'
import * as React from 'react'

import { cn } from '@/lib/utils'

interface SettingsTab {
  id: string
  label: string
  href: string
  icon: React.ElementType
}

const tabs: SettingsTab[] = [
  { id: 'general', label: 'General', href: '/settings/general', icon: Settings },
  { id: 'display', label: 'Display', href: '/settings/display', icon: Monitor },
  { id: 'privacy', label: 'Privacy', href: '/settings/privacy', icon: Shield },
  { id: 'notifications', label: 'Notifications', href: '/settings/notifications', icon: Bell },
  { id: 'exclusions', label: 'Exclusions', href: '/settings/exclusions', icon: Ban },
  { id: 'access', label: 'Access', href: '/settings/access', icon: Users },
  { id: 'data-management', label: 'Data', href: '/settings/data-management', icon: Database },
  { id: 'advanced', label: 'Advanced', href: '/settings/advanced', icon: Wrench },
]

interface SettingsLayoutProps {
  children: React.ReactNode
}

export function SettingsLayout({ children }: SettingsLayoutProps) {
  const location = useLocation()
  const currentPath = location.pathname

  return (
    <div className="flex flex-col h-full">
      {/* Header */}
      <div className="border-b bg-background px-6 py-4">
        <h1 className="text-2xl font-semibold tracking-tight">Settings</h1>
        <p className="text-sm text-muted-foreground mt-1">Manage your WP Statistics preferences and configuration.</p>
      </div>

      {/* Tab Navigation */}
      <div className="border-b bg-background">
        <nav className="flex gap-1 px-6" aria-label="Settings tabs">
          {tabs.map((tab) => {
            const isActive = currentPath === tab.href
            const Icon = tab.icon

            return (
              <Link
                key={tab.id}
                to={tab.href}
                className={cn(
                  'flex items-center gap-2 px-4 py-3 text-sm font-medium border-b-2 -mb-px transition-colors',
                  isActive
                    ? 'border-primary text-primary'
                    : 'border-transparent text-muted-foreground hover:text-foreground hover:border-muted-foreground/50'
                )}
                aria-current={isActive ? 'page' : undefined}
              >
                <Icon className="h-4 w-4" />
                {tab.label}
              </Link>
            )
          })}
        </nav>
      </div>

      {/* Tab Content */}
      <div className="flex-1 overflow-auto p-6">{children}</div>
    </div>
  )
}
