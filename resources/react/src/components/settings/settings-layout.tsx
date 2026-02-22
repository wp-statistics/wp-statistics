import * as React from 'react'

import { NoticeContainer } from '@/components/ui/notice-container'

interface SettingsLayoutProps {
  children: React.ReactNode
}

export function SettingsLayout({ children }: SettingsLayoutProps) {
  return (
    <div className="flex-1 overflow-auto p-6">
      <NoticeContainer className="mb-4 max-w-4xl" currentRoute="settings" />
      <div className="max-w-4xl">
        {children}
      </div>
    </div>
  )
}
