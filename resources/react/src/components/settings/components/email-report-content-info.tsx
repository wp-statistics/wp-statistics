import { __ } from '@wordpress/i18n'

import { SettingsInfoBox } from '@/components/settings-ui'

/**
 * Read-only info box showing what the free email report includes.
 * In premium, this component gets replaced by EmailReportSections (interactive toggles).
 */
export function EmailReportContentInfo() {
  return (
    <SettingsInfoBox title={__('Report Contents', 'wp-statistics')}>
      <p className="mb-2">{__('Your email report will include:', 'wp-statistics')}</p>
      <ul className="list-disc pl-5 space-y-1">
        <li>{__('Quick Stats (visitors, page views)', 'wp-statistics')}</li>
        <li>{__('Daily Visitors Chart', 'wp-statistics')}</li>
        <li>{__('Top 10 Pages', 'wp-statistics')}</li>
        <li>{__('Top Referrers', 'wp-statistics')}</li>
      </ul>
    </SettingsInfoBox>
  )
}
