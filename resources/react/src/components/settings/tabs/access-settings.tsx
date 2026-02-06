import { __ } from '@wordpress/i18n'
import { Loader2 } from 'lucide-react'
import * as React from 'react'

import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Label } from '@/components/ui/label'
import { NoticeBanner } from '@/components/ui/notice-banner'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { useSetting, useSettings } from '@/hooks/use-settings'
import { useToast } from '@/hooks/use-toast'

// Common WordPress capabilities that can be used for access control
const CAPABILITIES = [
  { value: 'manage_network', label: 'manage_network', description: __('Super Admin (Network)', 'wp-statistics') },
  { value: 'manage_options', label: 'manage_options', description: __('Administrator', 'wp-statistics') },
  { value: 'edit_others_posts', label: 'edit_others_posts', description: __('Editor', 'wp-statistics') },
  { value: 'publish_posts', label: 'publish_posts', description: __('Author', 'wp-statistics') },
  { value: 'edit_posts', label: 'edit_posts', description: __('Contributor', 'wp-statistics') },
  { value: 'read', label: 'read', description: __('Subscriber', 'wp-statistics') },
]

export function AccessSettings() {
  const settings = useSettings({ tab: 'access' })
  const { toast } = useToast()

  // Access Level Settings
  const [readCapability, setReadCapability] = useSetting(settings, 'read_capability', 'manage_options')
  const [manageCapability, setManageCapability] = useSetting(settings, 'manage_capability', 'manage_options')

  const handleSave = async () => {
    const success = await settings.save()
    if (success) {
      toast({
        title: __('Settings saved', 'wp-statistics'),
        description: __('Access settings have been updated.', 'wp-statistics'),
      })
    }
  }

  if (settings.isLoading) {
    return (
      <div className="flex items-center justify-center p-8">
        <Loader2 className="h-6 w-6 animate-spin" />
        <span className="ml-2">{__('Loading settings...', 'wp-statistics')}</span>
      </div>
    )
  }

  return (
    <div className="space-y-6">
      <Card>
        <CardHeader>
          <CardTitle>{__('Roles & Permissions', 'wp-statistics')}</CardTitle>
          <CardDescription>{__('Control which users can view statistics and manage plugin settings.', 'wp-statistics')}</CardDescription>
        </CardHeader>
        <CardContent className="space-y-6">
          <div className="space-y-2">
            <Label htmlFor="read-capability">{__('Minimum Role to View Statistics', 'wp-statistics')}</Label>
            <Select value={readCapability as string} onValueChange={setReadCapability}>
              <SelectTrigger id="read-capability" className="w-full max-w-[300px]">
                <SelectValue placeholder={__('Select capability', 'wp-statistics')} />
              </SelectTrigger>
              <SelectContent>
                {CAPABILITIES.map((cap) => (
                  <SelectItem key={cap.value} value={cap.value}>
                    {cap.label} ({cap.description})
                  </SelectItem>
                ))}
              </SelectContent>
            </Select>
            <p className="text-sm text-muted-foreground">
              {__('Select the least privileged user role allowed to view WP Statistics. Higher roles will also have this permission.', 'wp-statistics')}
            </p>
          </div>

          <div className="space-y-2">
            <Label htmlFor="manage-capability">{__('Minimum Role to Manage Settings', 'wp-statistics')}</Label>
            <Select value={manageCapability as string} onValueChange={setManageCapability}>
              <SelectTrigger id="manage-capability" className="w-full max-w-[300px]">
                <SelectValue placeholder={__('Select capability', 'wp-statistics')} />
              </SelectTrigger>
              <SelectContent>
                {CAPABILITIES.map((cap) => (
                  <SelectItem key={cap.value} value={cap.value}>
                    {cap.label} ({cap.description})
                  </SelectItem>
                ))}
              </SelectContent>
            </Select>
            <p className="text-sm text-muted-foreground">
              {__('Select the least privileged user role allowed to change WP Statistics settings. This should typically be reserved for trusted roles.', 'wp-statistics')}
            </p>
          </div>

          <div className="rounded-md bg-muted p-4 text-sm">
            <p className="font-medium mb-2">{__('Hints on Capabilities:', 'wp-statistics')}</p>
            <ul className="list-disc list-inside space-y-1 text-muted-foreground">
              <li>
                <code className="text-xs">manage_network</code> - {__('Super Admin role in a network setup', 'wp-statistics')}
              </li>
              <li>
                <code className="text-xs">manage_options</code> - {__('Administrator capability', 'wp-statistics')}
              </li>
              <li>
                <code className="text-xs">edit_others_posts</code> - {__('Editor role', 'wp-statistics')}
              </li>
              <li>
                <code className="text-xs">publish_posts</code> - {__('Author role', 'wp-statistics')}
              </li>
              <li>
                <code className="text-xs">edit_posts</code> - {__('Contributor role', 'wp-statistics')}
              </li>
              <li>
                <code className="text-xs">read</code> - {__('Subscriber role', 'wp-statistics')}
              </li>
            </ul>
          </div>
        </CardContent>
      </Card>

      {settings.error && <NoticeBanner id="settings-error" message={settings.error} type="error" dismissible={false} />}

      <div className="flex justify-end">
        <Button onClick={handleSave} disabled={settings.isSaving}>
          {settings.isSaving && <Loader2 className="mr-2 h-4 w-4 animate-spin" />}
          {__('Save Changes', 'wp-statistics')}
        </Button>
      </div>
    </div>
  )
}
