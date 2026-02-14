import { __, sprintf } from '@wordpress/i18n'

import { SettingsField } from '@/components/settings-ui'
import { Button } from '@/components/ui/button'
import { Textarea } from '@/components/ui/textarea'
import type { UseSettingsReturn } from '@/hooks/use-settings'
import { WordPress } from '@/lib/wordpress'

/**
 * IP exclusion textarea with "Add My IP" button.
 * Registered as a `type: 'component'` field in the exclusions tab.
 */
export function ExcludeIpField({ settings }: { settings: UseSettingsReturn }) {
  const userIp = WordPress.getInstance().getUserIp()
  const excludeIp = settings.getValue('exclude_ip', '') as string

  const setExcludeIp = (value: string) => settings.setValue('exclude_ip', value)

  const handleAddMyIp = () => {
    const lines = excludeIp.split('\n').map((l) => l.trim()).filter(Boolean)
    if (!lines.includes(userIp)) {
      setExcludeIp(lines.length ? excludeIp.trimEnd() + '\n' + userIp : userIp)
    }
  }

  return (
    <SettingsField
      id="exclude-ips"
      label={__('Excluded IP Addresses', 'wp-statistics')}
      description={__('Enter IP addresses, CIDR ranges, or wildcard patterns, one per line.', 'wp-statistics')}
      layout="stacked"
    >
      <Textarea
        id="exclude-ips"
        placeholder={"192.168.1.1\n10.0.0.0/8\n192.168.*.*"}
        value={excludeIp}
        onChange={(e) => setExcludeIp(e.target.value)}
        rows={4}
      />
      <Button variant="outline" size="sm" className="mt-2" onClick={handleAddMyIp}>
        {sprintf(__('Add My IP (%s)', 'wp-statistics'), userIp)}
      </Button>
    </SettingsField>
  )
}
