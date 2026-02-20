import * as React from 'react'

import { NoticeContainer } from '@/components/ui/notice-container'

interface ToolsLayoutProps {
  children: React.ReactNode
}

export function ToolsLayout({ children }: ToolsLayoutProps) {
  return (
    <div className="flex-1 overflow-auto p-6">
      <NoticeContainer className="mb-4 max-w-4xl" currentRoute="tools" />
      <div className="max-w-4xl">
        {children}
      </div>
    </div>
  )
}
