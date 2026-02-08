import { __ } from '@wordpress/i18n'

import { SettingsCard, SettingsPage, SettingsSelectField, SettingsToggleField } from '@/components/settings-ui'
import { useSetting, useSettings } from '@/hooks/use-settings'

export function DisplaySettings() {
  const settings = useSettings({ tab: 'display' })

  // Admin Interface Settings
  const [disableEditor, setDisableEditor] = useSetting(settings, 'disable_editor', false)
  const [disableColumn, setDisableColumn] = useSetting(settings, 'disable_column', false)
  const [enableUserColumn, setEnableUserColumn] = useSetting(settings, 'enable_user_column', false)
  const [displayNotifications, setDisplayNotifications] = useSetting(settings, 'display_notifications', true)
  const [hideNotices, setHideNotices] = useSetting(settings, 'hide_notices', false)

  // Admin Bar
  const [menuBar, setMenuBar] = useSetting(settings, 'menu_bar', true)

  // Frontend Display Settings
  const [showHits, setShowHits] = useSetting(settings, 'show_hits', false)
  const [displayHitsPosition, setDisplayHitsPosition] = useSetting(settings, 'display_hits_position', 'none')

  return (
    <SettingsPage settings={settings} saveDescription={__('Display settings have been updated.', 'wp-statistics')}>
      <SettingsCard
        title={__('Admin Interface', 'wp-statistics')}
        description={__('Configure how WP Statistics appears in the WordPress admin area.', 'wp-statistics')}
      >
        <SettingsToggleField
          id="disable-editor"
          label={__('View Stats in Editor', 'wp-statistics')}
          description={__('Show a summary of content view statistics in the post editor.', 'wp-statistics')}
          checked={!disableEditor}
          onCheckedChange={(checked) => setDisableEditor(!checked)}
        />

        <SettingsToggleField
          id="disable-column"
          label={__('Stats Column in Content List', 'wp-statistics')}
          description={__('Display the statistics column in the content list menus, showing page view or visitor counts.', 'wp-statistics')}
          checked={!disableColumn}
          onCheckedChange={(checked) => setDisableColumn(!checked)}
        />

        <SettingsToggleField
          id="enable-user-column"
          label={__('Views Column in User List', 'wp-statistics')}
          description={__('Display the "Views" column in the admin user list. Requires "Track Logged-In User Activity" to be enabled.', 'wp-statistics')}
          checked={!!enableUserColumn}
          onCheckedChange={setEnableUserColumn}
        />

        <SettingsToggleField
          id="display-notifications"
          label={__('WP Statistics Notifications', 'wp-statistics')}
          description={__('Display important notifications such as new version releases, feature updates, and news.', 'wp-statistics')}
          checked={!!displayNotifications}
          onCheckedChange={setDisplayNotifications}
        />

        <SettingsToggleField
          id="hide-notices"
          label={__('Disable Admin Notices', 'wp-statistics')}
          description={__('Hides configuration and optimization notices in the admin area. Critical database notices will still be shown.', 'wp-statistics')}
          checked={!!hideNotices}
          onCheckedChange={setHideNotices}
        />

        <SettingsToggleField
          id="menu-bar"
          label={__('Show Stats in Admin Bar', 'wp-statistics')}
          description={__('Display a quick statistics summary in the WordPress admin bar.', 'wp-statistics')}
          checked={!!menuBar}
          onCheckedChange={setMenuBar}
        />
      </SettingsCard>

      <SettingsCard
        title={__('Frontend Display', 'wp-statistics')}
        description={__('Configure how statistics appear on your website frontend.', 'wp-statistics')}
      >
        <SettingsToggleField
          id="show-hits"
          label={__('Views in Single Contents', 'wp-statistics')}
          description={__("Shows the view count on the content's page for visitor insight.", 'wp-statistics')}
          checked={!!showHits}
          onCheckedChange={setShowHits}
        />

        {showHits && (
          <SettingsSelectField
            id="display-hits-position"
            label={__('Display Position', 'wp-statistics')}
            description={__('Choose the position to show views on your content pages.', 'wp-statistics')}
            layout="stacked"
            nested
            value={displayHitsPosition as string}
            onValueChange={setDisplayHitsPosition}
            placeholder={__('Select position', 'wp-statistics')}
            options={[
              { value: 'none', label: __('Please select', 'wp-statistics') },
              { value: 'before_content', label: __('Before Content', 'wp-statistics') },
              { value: 'after_content', label: __('After Content', 'wp-statistics') },
            ]}
          />
        )}
      </SettingsCard>
    </SettingsPage>
  )
}
