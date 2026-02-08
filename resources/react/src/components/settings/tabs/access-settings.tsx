import { __ } from '@wordpress/i18n'
import { Lock } from 'lucide-react'

import { SettingsCard, SettingsInfoBox, SettingsPage } from '@/components/settings-ui'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { useSetting, useSettings } from '@/hooks/use-settings'

type AccessLevel = wpsReact['globals']['accessLevel']

const ACCESS_LEVELS: { value: AccessLevel; label: string }[] = [
  { value: 'none', label: __('No access', 'wp-statistics') },
  { value: 'own_content', label: __('Own content', 'wp-statistics') },
  { value: 'view_stats', label: __('View statistics', 'wp-statistics') },
  { value: 'view_all', label: __('View all data', 'wp-statistics') },
  { value: 'manage', label: __('Manage', 'wp-statistics') },
]

export function AccessSettings() {
  const settings = useSettings({ tab: 'access' })

  const [accessLevels, setAccessLevels] = useSetting<Record<string, AccessLevel>>(settings, 'access_levels', {})

  // Roles are provided by the backend as _roles (not saved, read-only metadata)
  const roles = (settings.getValue('_roles', []) as { slug: string; name: string }[])

  const handleLevelChange = (roleSlug: string, level: AccessLevel) => {
    setAccessLevels({ ...accessLevels, [roleSlug]: level })
  }

  return (
    <SettingsPage settings={settings} saveDescription={__('Access settings have been updated.', 'wp-statistics')}>
      <SettingsCard
        title={__('Roles & Permissions', 'wp-statistics')}
        description={__('Control what level of statistics access each user role receives.', 'wp-statistics')}
      >
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead className="w-[200px]">{__('Role', 'wp-statistics')}</TableHead>
              <TableHead>{__('Access level', 'wp-statistics')}</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            {roles.map((role) => {
              const isAdmin = role.slug === 'administrator'
              const currentLevel = isAdmin ? 'manage' : (accessLevels[role.slug] ?? 'none')

              return (
                <TableRow key={role.slug}>
                  <TableCell className="font-medium">{role.name}</TableCell>
                  <TableCell>
                    {isAdmin ? (
                      <div className="flex items-center gap-1.5 text-sm text-muted-foreground">
                        <Lock className="h-3.5 w-3.5" />
                        {__('Manage', 'wp-statistics')}
                      </div>
                    ) : (
                      <Select
                        value={currentLevel}
                        onValueChange={(value: string) => handleLevelChange(role.slug, value as AccessLevel)}
                      >
                        <SelectTrigger className="w-[200px]">
                          <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                          {ACCESS_LEVELS.map((level) => (
                            <SelectItem key={level.value} value={level.value}>
                              {level.label}
                            </SelectItem>
                          ))}
                        </SelectContent>
                      </Select>
                    )}
                  </TableCell>
                </TableRow>
              )
            })}
          </TableBody>
        </Table>

        <SettingsInfoBox title={__('Access levels:', 'wp-statistics')}>
          <ul className="list-disc list-inside space-y-1">
            <li>
              <span className="font-medium">{__('No access', 'wp-statistics')}</span> –{' '}
              {__('Cannot see any WP Statistics data', 'wp-statistics')}
            </li>
            <li>
              <span className="font-medium">{__('Own content', 'wp-statistics')}</span> –{' '}
              {__('View stats only for their authored posts', 'wp-statistics')}
            </li>
            <li>
              <span className="font-medium">{__('View statistics', 'wp-statistics')}</span> –{' '}
              {__('All reports except individual visitor details', 'wp-statistics')}
            </li>
            <li>
              <span className="font-medium">{__('View all data', 'wp-statistics')}</span> –{' '}
              {__('Full access including individual visitor data', 'wp-statistics')}
            </li>
            <li>
              <span className="font-medium">{__('Manage', 'wp-statistics')}</span> –{' '}
              {__('Full access plus settings, tools, and data management', 'wp-statistics')}
            </li>
          </ul>
        </SettingsInfoBox>
      </SettingsCard>
    </SettingsPage>
  )
}
