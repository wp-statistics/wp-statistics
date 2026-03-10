import { createLazyFileRoute } from '@tanstack/react-router'

import { SocialTypeSelect } from '@/components/custom/social-type-select'
import { ReportPageRenderer } from '@/components/report-page-renderer'
import { useContentRegistry } from '@/contexts/content-registry-context'
import { useSocialTypeFilter } from '@/hooks/use-social-type-filter'

export const Route = createLazyFileRoute('/(referrals)/social-media')({
  component: RouteComponent,
})

function RouteComponent() {
  const { getReport } = useContentRegistry()
  const report = getReport('social-media')

  const {
    value: socialType,
    onChange: onSocialTypeChange,
    options: socialTypeOptions,
    getApiFilter,
    pageFilterConfig,
  } = useSocialTypeFilter()

  if (!report?.config) return null

  return (
    <ReportPageRenderer
      config={{
        ...report.config,
        pageFilters: [pageFilterConfig],
        headerActions: () => (
          <div className="hidden lg:flex">
            <SocialTypeSelect
              value={socialType}
              onValueChange={onSocialTypeChange}
              options={socialTypeOptions}
            />
          </div>
        ),
      }}
      apiFilters={getApiFilter()}
    />
  )
}
