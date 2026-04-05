import { __, sprintf } from '@wordpress/i18n'
import { Link } from '@tanstack/react-router'
import {
  AlertTriangle,
  CheckCircle2,
  ExternalLink,
  ShieldCheck,
  XCircle,
} from 'lucide-react'
import * as React from 'react'

import { SettingsCard } from '@/components/settings-ui'
import { Button } from '@/components/ui/button'
import { NoticeBanner } from '@/components/ui/notice-banner'
import { Skeleton } from '@/components/ui/skeleton'
import { PanelSkeleton } from '@/components/ui/skeletons'
import { useToast } from '@/hooks/use-toast'
import { cn } from '@/lib/utils'
import { callToolsApi } from '@/services/tools'

interface PrivacyCheck {
  key: string
  label: string
  status: 'pass' | 'warning' | 'fail'
  message: string
  category: string
  settingsLink: string
}

interface PrivacyAuditResponse {
  checks: PrivacyCheck[]
  categories: Record<string, string>
  summary: {
    passCount: number
    warningCount: number
    failCount: number
  }
}

const statusConfig = {
  pass: {
    icon: CheckCircle2,
    color: 'text-emerald-600 dark:text-emerald-400',
    bgColor: 'bg-emerald-50 dark:bg-emerald-950/30',
    borderColor: 'border-emerald-200 dark:border-emerald-800',
  },
  warning: {
    icon: AlertTriangle,
    color: 'text-amber-600 dark:text-amber-400',
    bgColor: 'bg-amber-50 dark:bg-amber-950/30',
    borderColor: 'border-amber-200 dark:border-amber-800',
  },
  fail: {
    icon: XCircle,
    color: 'text-destructive dark:text-red-400',
    bgColor: 'bg-red-50 dark:bg-red-950/30',
    borderColor: 'border-red-200 dark:border-red-800',
  },
}

function PrivacyCheckItem({ check }: { check: PrivacyCheck }) {
  const config = statusConfig[check.status]
  const StatusIcon = config.icon

  return (
    <div className={cn('rounded-lg border', config.borderColor, config.bgColor)}>
      <div className="flex items-center gap-4 p-4">
        <StatusIcon className={cn('h-5 w-5 shrink-0', config.color)} />
        <div className="flex-1 min-w-0">
          <h4 className="font-medium mb-1">{check.label}</h4>
          <p className="text-sm text-muted-foreground">{check.message}</p>
        </div>
        {check.status !== 'pass' && (
          <Button variant="outline" size="sm" className="shrink-0 h-8" asChild>
            <Link to={check.settingsLink}>
              {__('Review Setting', 'wp-statistics')}
              <ExternalLink className="ml-1 h-3 w-3" />
            </Link>
          </Button>
        )}
      </div>
    </div>
  )
}

export function PrivacyAuditPage() {
  const [data, setData] = React.useState<PrivacyAuditResponse | null>(null)
  const [isLoading, setIsLoading] = React.useState(true)
  const { toast } = useToast()

  React.useEffect(() => {
    fetchAudit()
  }, [])

  const fetchAudit = async () => {
    try {
      const response = await callToolsApi('privacy_audit')

      if (response.success) {
        setData(response.data as PrivacyAuditResponse)
      }
    } catch {
      toast({
        title: __('Error', 'wp-statistics'),
        description: __('Failed to load privacy audit.', 'wp-statistics'),
        variant: 'destructive',
      })
    } finally {
      setIsLoading(false)
    }
  }

  if (isLoading) {
    return (
      <div className="space-y-6">
        <Skeleton className="h-16 w-full rounded-lg" />
        <PanelSkeleton titleWidth="w-40">
          <div className="space-y-3">
            {[...Array(5)].map((_, i) => (
              <Skeleton key={i} className="h-16 w-full rounded-lg" />
            ))}
          </div>
        </PanelSkeleton>
      </div>
    )
  }

  if (!data) {
    return null
  }

  const { checks, categories, summary } = data

  // Group checks by category
  const grouped: Record<string, PrivacyCheck[]> = {}
  for (const check of checks) {
    if (!grouped[check.category]) {
      grouped[check.category] = []
    }
    grouped[check.category].push(check)
  }

  return (
    <div className="space-y-6">
      <NoticeBanner
        title={__('Privacy Audit', 'wp-statistics')}
        message={__('This audit evaluates your current privacy settings against best practices for data protection. Review any warnings or failures and adjust your settings accordingly.', 'wp-statistics')}
        type="neutral"
        icon={ShieldCheck}
        dismissible={false}
      />

      <SettingsCard
        title={__('Audit Results', 'wp-statistics')}
        icon={ShieldCheck}
      >
        <div className="flex items-center gap-4 text-sm mb-4">
          <span className="flex items-center gap-1">
            <CheckCircle2 className="h-4 w-4 text-emerald-600" />
            {sprintf(__('%d Passed', 'wp-statistics'), summary.passCount)}
          </span>
          {summary.warningCount > 0 && (
            <span className="flex items-center gap-1">
              <AlertTriangle className="h-4 w-4 text-amber-600" />
              {sprintf(
                summary.warningCount > 1
                  ? __('%d Warnings', 'wp-statistics')
                  : __('%d Warning', 'wp-statistics'),
                summary.warningCount
              )}
            </span>
          )}
          {summary.failCount > 0 && (
            <span className="flex items-center gap-1">
              <XCircle className="h-4 w-4 text-destructive" />
              {sprintf(__('%d Failed', 'wp-statistics'), summary.failCount)}
            </span>
          )}
        </div>

        <div className="space-y-6">
          {Object.entries(categories).map(([categoryKey, categoryLabel]) => {
            const categoryChecks = grouped[categoryKey]
            if (!categoryChecks || categoryChecks.length === 0) return null

            return (
              <div key={categoryKey}>
                <h3 className="text-sm font-medium text-muted-foreground mb-3">
                  {categoryLabel}
                </h3>
                <div className="space-y-3">
                  {categoryChecks.map((check) => (
                    <PrivacyCheckItem key={check.key} check={check} />
                  ))}
                </div>
              </div>
            )
          })}
        </div>
      </SettingsCard>
    </div>
  )
}
