import { __, sprintf } from '@wordpress/i18n'

import { SettingsField, SettingsInfoBox } from '@/components/settings-ui'
import { Button } from '@/components/ui/button'
import { NoticeBanner } from '@/components/ui/notice-banner'
import { Textarea } from '@/components/ui/textarea'
import type { UseSettingsReturn } from '@/hooks/use-settings'

const DEFAULT_TRACKED_PARAMS = 'ref\nsource\nutm_source\nutm_medium\nutm_campaign\nutm_content\nutm_term\nutm_id'
const COMMON_RESERVED = ['s', 'p', 'page', 'paged', 'page_id', 'cat', 'tag', 'author', 'feed', 'preview']

/**
 * Query parameters textarea with reset-to-defaults button and reserved-term warnings.
 * Registered as a `type: 'component'` field in the exclusions tab.
 */
export function QueryParamsField({ settings }: { settings: UseSettingsReturn }) {
  const queryParamsAllowList = settings.getValue('query_params_allow_list', '') as string

  const setQueryParamsAllowList = (value: string) => settings.setValue('query_params_allow_list', value)

  const reservedWarnings = queryParamsAllowList
    .split('\n')
    .map((l) => l.trim())
    .filter((l) => COMMON_RESERVED.includes(l))

  return (
    <>
      <SettingsField
        id="query-params"
        label={__('Tracked Query Parameters', 'wp-statistics')}
        description={__('Enter parameter names to track separately and remove from stored page URLs, one per line. Default: ref, source, utm_source, utm_medium, utm_campaign, utm_content, utm_term, utm_id.', 'wp-statistics')}
        layout="stacked"
      >
        <Textarea
          id="query-params"
          placeholder={DEFAULT_TRACKED_PARAMS}
          value={queryParamsAllowList}
          onChange={(e) => setQueryParamsAllowList(e.target.value)}
          rows={5}
        />
        <Button variant="outline" size="sm" className="mt-2" onClick={() => setQueryParamsAllowList(DEFAULT_TRACKED_PARAMS)}>
          {__('Reset to Defaults', 'wp-statistics')}
        </Button>
      </SettingsField>
      {reservedWarnings.length > 0 && (
        <NoticeBanner
          type="warning"
          dismissible={false}
          message={sprintf(
            __('%s are WordPress reserved terms and will be ignored during tracking.', 'wp-statistics'),
            reservedWarnings.join(', ')
          )}
        />
      )}
      <SettingsInfoBox>
        {__('Ad-platform parameters like fbclid, gclid, and msclkid are automatically removed from all stored URLs. This is always active and not configurable.', 'wp-statistics')}
      </SettingsInfoBox>
    </>
  )
}
