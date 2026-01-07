import * as React from 'react'
import { Loader2 } from 'lucide-react'

import { Button } from '@/components/ui/button'
import { NoticeBanner } from '@/components/ui/notice-banner'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Label } from '@/components/ui/label'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { useSettings, useSetting } from '@/hooks/use-settings'

// Common WordPress capabilities that can be used for access control
const CAPABILITIES = [
  { value: 'manage_network', label: 'manage_network', description: 'Super Admin (Network)' },
  { value: 'manage_options', label: 'manage_options', description: 'Administrator' },
  { value: 'edit_others_posts', label: 'edit_others_posts', description: 'Editor' },
  { value: 'publish_posts', label: 'publish_posts', description: 'Author' },
  { value: 'edit_posts', label: 'edit_posts', description: 'Contributor' },
  { value: 'read', label: 'read', description: 'Subscriber' },
]

export function AccessSettings() {
  const settings = useSettings({ tab: 'access' })

  // Access Level Settings
  const [readCapability, setReadCapability] = useSetting(settings, 'read_capability', 'manage_options')
  const [manageCapability, setManageCapability] = useSetting(settings, 'manage_capability', 'manage_options')

  const handleSave = async () => {
    const success = await settings.save()
    if (success) {
      // Could show a toast notification here
    }
  }

  if (settings.isLoading) {
    return (
      <div className="flex items-center justify-center p-8">
        <Loader2 className="h-6 w-6 animate-spin" />
        <span className="ml-2">Loading settings...</span>
      </div>
    )
  }

  return (
    <div className="space-y-6">
      <Card>
        <CardHeader>
          <CardTitle>Roles & Permissions</CardTitle>
          <CardDescription>Control which users can view statistics and manage plugin settings.</CardDescription>
        </CardHeader>
        <CardContent className="space-y-6">
          <div className="space-y-2">
            <Label htmlFor="read-capability">Minimum Role to View Statistics</Label>
            <Select value={readCapability as string} onValueChange={setReadCapability}>
              <SelectTrigger id="read-capability" className="w-full max-w-[300px]">
                <SelectValue placeholder="Select capability" />
              </SelectTrigger>
              <SelectContent>
                {CAPABILITIES.map((cap) => (
                  <SelectItem key={cap.value} value={cap.value}>
                    {cap.label}
                  </SelectItem>
                ))}
              </SelectContent>
            </Select>
            <p className="text-sm text-muted-foreground">
              Select the least privileged user role allowed to view WP Statistics. Higher roles will also have this
              permission.
            </p>
          </div>

          <div className="space-y-2">
            <Label htmlFor="manage-capability">Minimum Role to Manage Settings</Label>
            <Select value={manageCapability as string} onValueChange={setManageCapability}>
              <SelectTrigger id="manage-capability" className="w-full max-w-[300px]">
                <SelectValue placeholder="Select capability" />
              </SelectTrigger>
              <SelectContent>
                {CAPABILITIES.map((cap) => (
                  <SelectItem key={cap.value} value={cap.value}>
                    {cap.label}
                  </SelectItem>
                ))}
              </SelectContent>
            </Select>
            <p className="text-sm text-muted-foreground">
              Select the least privileged user role allowed to change WP Statistics settings. This should typically be
              reserved for trusted roles.
            </p>
          </div>

          <div className="rounded-md bg-muted p-4 text-sm">
            <p className="font-medium mb-2">Hints on Capabilities:</p>
            <ul className="list-disc list-inside space-y-1 text-muted-foreground">
              <li>
                <code className="text-xs">manage_network</code> - Super Admin role in a network setup
              </li>
              <li>
                <code className="text-xs">manage_options</code> - Administrator capability
              </li>
              <li>
                <code className="text-xs">edit_others_posts</code> - Editor role
              </li>
              <li>
                <code className="text-xs">publish_posts</code> - Author role
              </li>
              <li>
                <code className="text-xs">edit_posts</code> - Contributor role
              </li>
              <li>
                <code className="text-xs">read</code> - Subscriber role
              </li>
            </ul>
          </div>
        </CardContent>
      </Card>

      {settings.error && <NoticeBanner id="settings-error" message={settings.error} type="error" dismissible={false} />}

      <div className="flex justify-end">
        <Button onClick={handleSave} disabled={settings.isSaving}>
          {settings.isSaving && <Loader2 className="mr-2 h-4 w-4 animate-spin" />}
          Save Changes
        </Button>
      </div>
    </div>
  )
}
