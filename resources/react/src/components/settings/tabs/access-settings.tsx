import { __ } from '@wordpress/i18n'

import { SettingsCard, SettingsInfoBox, SettingsPage, SettingsSelectField } from '@/components/settings-ui'
import { useSetting, useSettings } from '@/hooks/use-settings'

const CAPABILITIES = [
  { value: 'manage_network', label: `manage_network (${__('Super Admin (Network)', 'wp-statistics')})` },
  { value: 'manage_options', label: `manage_options (${__('Administrator', 'wp-statistics')})` },
  { value: 'edit_others_posts', label: `edit_others_posts (${__('Editor', 'wp-statistics')})` },
  { value: 'publish_posts', label: `publish_posts (${__('Author', 'wp-statistics')})` },
  { value: 'edit_posts', label: `edit_posts (${__('Contributor', 'wp-statistics')})` },
  { value: 'read', label: `read (${__('Subscriber', 'wp-statistics')})` },
]

export function AccessSettings() {
  const settings = useSettings({ tab: 'access' })

  const [readCapability, setReadCapability] = useSetting(settings, 'read_capability', 'manage_options')
  const [manageCapability, setManageCapability] = useSetting(settings, 'manage_capability', 'manage_options')

  return (
    <SettingsPage settings={settings} saveDescription={__('Access settings have been updated.', 'wp-statistics')}>
      <SettingsCard
        title={__('Roles & Permissions', 'wp-statistics')}
        description={__('Control which users can view statistics and manage plugin settings.', 'wp-statistics')}
      >
        <SettingsSelectField
          id="read-capability"
          label={__('Minimum Role to View Statistics', 'wp-statistics')}
          description={__('Select the least privileged user role allowed to view WP Statistics. Higher roles will also have this permission.', 'wp-statistics')}
          layout="stacked"
          value={readCapability as string}
          onValueChange={setReadCapability}
          placeholder={__('Select capability', 'wp-statistics')}
          options={CAPABILITIES}
        />

        <SettingsSelectField
          id="manage-capability"
          label={__('Minimum Role to Manage Settings', 'wp-statistics')}
          description={__('Select the least privileged user role allowed to change WP Statistics settings. This should typically be reserved for trusted roles.', 'wp-statistics')}
          layout="stacked"
          value={manageCapability as string}
          onValueChange={setManageCapability}
          placeholder={__('Select capability', 'wp-statistics')}
          options={CAPABILITIES}
        />

        <SettingsInfoBox title={__('Hints on Capabilities:', 'wp-statistics')}>
          <ul className="list-disc list-inside space-y-1">
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
        </SettingsInfoBox>
      </SettingsCard>
    </SettingsPage>
  )
}
