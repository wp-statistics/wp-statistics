import { __ } from '@wordpress/i18n'
import { Loader2 } from 'lucide-react'
import * as React from 'react'

import { Button } from '@/components/ui/button'
import { NoticeBanner } from '@/components/ui/notice-banner'
import { PanelSkeleton, TableSkeleton } from '@/components/ui/skeletons'
import type { UseSettingsReturn } from '@/hooks/use-settings'
import { useToast } from '@/hooks/use-toast'

interface SettingsPageProps {
  settings: UseSettingsReturn
  saveDescription: string
  children: React.ReactNode
}

export function SettingsPage({ settings, saveDescription, children }: SettingsPageProps) {
  const { toast } = useToast()

  const handleSave = async () => {
    const success = await settings.save()
    if (success) {
      toast({
        title: __('Settings saved', 'wp-statistics'),
        description: saveDescription,
      })
    }
  }

  if (settings.isLoading) {
    return (
      <div className="space-y-5">
        <PanelSkeleton titleWidth="w-40">
          <TableSkeleton rows={4} columns={2} showHeader={false} />
        </PanelSkeleton>
        <PanelSkeleton titleWidth="w-32">
          <TableSkeleton rows={3} columns={2} showHeader={false} />
        </PanelSkeleton>
      </div>
    )
  }

  return (
    <div className="space-y-5">
      {children}

      {settings.error && <NoticeBanner id="settings-error" message={settings.error} type="error" dismissible={false} />}

      <div className="flex justify-start">
        <Button onClick={handleSave} disabled={settings.isSaving}>
          {settings.isSaving && <Loader2 className="mr-2 h-4 w-4 animate-spin" />}
          {__('Save Changes', 'wp-statistics')}
        </Button>
      </div>
    </div>
  )
}
