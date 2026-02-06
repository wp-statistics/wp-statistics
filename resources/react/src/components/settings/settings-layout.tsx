import * as React from 'react'

interface SettingsLayoutProps {
  children: React.ReactNode
}

export function SettingsLayout({ children }: SettingsLayoutProps) {
  return <div className="flex-1 overflow-auto p-6">{children}</div>
}
