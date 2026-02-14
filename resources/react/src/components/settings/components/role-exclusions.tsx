import { __, sprintf } from '@wordpress/i18n'

import { SettingsToggleField } from '@/components/settings-ui'
import type { UseSettingsReturn } from '@/hooks/use-settings'

/**
 * Dynamic role exclusion toggles.
 * Reads the `_roles` metadata from settings and renders a toggle per role,
 * plus an "Anonymous Users" toggle.
 *
 * Registered as a `type: 'component'` field in the exclusions tab.
 */
export function RoleExclusions({ settings }: { settings: UseSettingsReturn }) {
  const roles = settings.getValue('_roles', []) as { slug: string; name: string }[]

  return (
    <>
      {roles.map((role) => {
        const key = `exclude_${role.slug}`
        return (
          <SettingsToggleField
            key={role.slug}
            id={`exclude-${role.slug}`}
            label={role.name}
            description={sprintf(__('Exclude users with %s role.', 'wp-statistics'), role.name.toLowerCase())}
            checked={!!settings.getValue(key, false)}
            onCheckedChange={(v) => settings.setValue(key, v)}
          />
        )
      })}
      <SettingsToggleField
        id="exclude-anonymous"
        label={__('Anonymous Users', 'wp-statistics')}
        description={__('Exclude users who are not logged in.', 'wp-statistics')}
        checked={!!settings.getValue('exclude_anonymous_users', false)}
        onCheckedChange={(v) => settings.setValue('exclude_anonymous_users', v)}
      />
    </>
  )
}
