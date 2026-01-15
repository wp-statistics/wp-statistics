import { Link, useLocation } from '@tanstack/react-router'
import { Activity, Clock, Database, Info, Stethoscope,Upload } from 'lucide-react'
import * as React from 'react'

import { NoticeContainer } from '@/components/ui/notice-container'
import { cn } from '@/lib/utils'

interface ToolsTab {
  id: string
  label: string
  href: string
  icon: React.ElementType
}

const tabs: ToolsTab[] = [
  { id: 'system-info', label: 'System Info', href: '/tools/system-info', icon: Info },
  { id: 'diagnostics', label: 'Diagnostics', href: '/tools/diagnostics', icon: Stethoscope },
  { id: 'scheduled-tasks', label: 'Scheduled Tasks', href: '/tools/scheduled-tasks', icon: Clock },
  { id: 'background-jobs', label: 'Background Jobs', href: '/tools/background-jobs', icon: Activity },
  { id: 'import-export', label: 'Import / Export', href: '/tools/import-export', icon: Upload },
  { id: 'backups', label: 'Backups', href: '/tools/backups', icon: Database },
]

interface ToolsLayoutProps {
  children: React.ReactNode
}

export function ToolsLayout({ children }: ToolsLayoutProps) {
  const location = useLocation()
  const currentPath = location.pathname

  return (
    <div className="flex flex-col h-full">
      {/* Header */}
      <div className="border-b bg-background px-6 py-4">
        <h1 className="text-2xl font-semibold tracking-tight">Tools</h1>
        <p className="text-sm text-muted-foreground mt-1">Import, export, manage backups, and monitor system health.</p>
      </div>

      {/* Tab Navigation */}
      <div className="border-b bg-background">
        <nav className="flex gap-1 px-6" aria-label="Tools tabs">
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
      <div className="flex-1 overflow-auto p-6">
        <NoticeContainer className="mb-4" currentRoute="tools" />
        {children}
      </div>
    </div>
  )
}
