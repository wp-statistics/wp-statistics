import { __ } from '@wordpress/i18n'
import {
  AlertTriangle,
  CheckCircle2,
  ChevronDown,
  ChevronUp,
  Clock,
  ExternalLink,
  Loader2,
  RefreshCw,
  Stethoscope,
  Wrench,
  XCircle,
} from 'lucide-react'
import * as React from 'react'

import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible'
import { NoticeBanner } from '@/components/ui/notice-banner'
import { useToast } from '@/hooks/use-toast'
import { cn } from '@/lib/utils'
import { callToolsApi } from '@/services/tools'

interface DiagnosticCheck {
  key: string
  label: string
  description: string
  status: 'pass' | 'warning' | 'fail'
  message: string
  details: Record<string, unknown>
  helpUrl: string | null
  timestamp: number
  isLightweight: boolean
}

interface DiagnosticsResponse {
  checks: DiagnosticCheck[]
  lastFullCheck: number | null
  hasIssues: boolean
  failCount: number
  warningCount: number
}

const statusConfig = {
  pass: {
    icon: CheckCircle2,
    color: 'text-emerald-600 dark:text-emerald-400',
    bgColor: 'bg-green-50 dark:bg-green-950/30',
    borderColor: 'border-green-200 dark:border-green-800',
    label: __('Passed', 'wp-statistics'),
  },
  warning: {
    icon: AlertTriangle,
    color: 'text-yellow-600 dark:text-yellow-400',
    bgColor: 'bg-yellow-50 dark:bg-yellow-950/30',
    borderColor: 'border-yellow-200 dark:border-yellow-800',
    label: __('Warning', 'wp-statistics'),
  },
  fail: {
    icon: XCircle,
    color: 'text-red-600 dark:text-red-400',
    bgColor: 'bg-red-50 dark:bg-red-950/30',
    borderColor: 'border-red-200 dark:border-red-800',
    label: __('Failed', 'wp-statistics'),
  },
}

interface DiagnosticCheckItemProps {
  check: DiagnosticCheck
  isRunning: boolean
  isRepairing: boolean
  onRetest: () => void
  onRepair?: () => void
}

function DiagnosticCheckItem({ check, isRunning, isRepairing, onRetest, onRepair }: DiagnosticCheckItemProps) {
  const [isOpen, setIsOpen] = React.useState(false)
  const config = statusConfig[check.status]
  const StatusIcon = config.icon
  const hasDetails = check.details && Object.keys(check.details).length > 0
  const canRepair = check.details?.canRepair === true && check.status !== 'pass'

  return (
    <div className={cn('rounded-lg border', config.borderColor, config.bgColor)}>
      <div className="flex items-center gap-4 p-4">
        <StatusIcon className={cn('h-5 w-5 shrink-0', config.color)} />
        <div className="flex-1 min-w-0">
          <div className="flex items-center gap-2 mb-1">
            <h4 className="font-medium truncate">{check.label}</h4>
            {!check.isLightweight && (
              <Badge variant="outline" className="text-xs">
                {__('Manual', 'wp-statistics')}
              </Badge>
            )}
          </div>
          <p className="text-sm text-muted-foreground">{check.message}</p>
        </div>
        <div className="flex items-center gap-2 shrink-0">
          {check.helpUrl && (
            <Button variant="ghost" size="sm" asChild className="h-8 px-2">
              <a href={check.helpUrl} target="_blank" rel="noopener noreferrer">
                <ExternalLink className="h-4 w-4" />
              </a>
            </Button>
          )}
          {canRepair && onRepair && (
            <Button variant="default" size="sm" onClick={onRepair} disabled={isRepairing || isRunning} className="h-8">
              {isRepairing ? <Loader2 className="mr-1 h-3 w-3 animate-spin" /> : <Wrench className="mr-1 h-3 w-3" />}
              {__('Repair', 'wp-statistics')}
            </Button>
          )}
          <Button variant="outline" size="sm" onClick={onRetest} disabled={isRunning || isRepairing} className="h-8">
            {isRunning ? <Loader2 className="mr-1 h-3 w-3 animate-spin" /> : <RefreshCw className="mr-1 h-3 w-3" />}
            {__('Re-test', 'wp-statistics')}
          </Button>
          {hasDetails && (
            <Collapsible open={isOpen} onOpenChange={setIsOpen}>
              <CollapsibleTrigger asChild>
                <Button variant="ghost" size="sm" className="h-8 px-2">
                  {isOpen ? <ChevronUp className="h-4 w-4" /> : <ChevronDown className="h-4 w-4" />}
                </Button>
              </CollapsibleTrigger>
            </Collapsible>
          )}
        </div>
      </div>

      {hasDetails && (
        <Collapsible open={isOpen} onOpenChange={setIsOpen}>
          <CollapsibleContent>
            <div className="border-t px-4 py-3 bg-background/50">
              <h5 className="text-xs font-medium text-muted-foreground mb-2">{__('Details', 'wp-statistics')}</h5>
              <pre className="text-xs bg-muted p-3 rounded overflow-auto max-h-48">
                {JSON.stringify(check.details, null, 2)}
              </pre>
            </div>
          </CollapsibleContent>
        </Collapsible>
      )}
    </div>
  )
}

export function DiagnosticsPage() {
  const [checks, setChecks] = React.useState<DiagnosticCheck[]>([])
  const [isLoading, setIsLoading] = React.useState(true)
  const [isRunningAll, setIsRunningAll] = React.useState(false)
  const [runningCheck, setRunningCheck] = React.useState<string | null>(null)
  const [repairingCheck, setRepairingCheck] = React.useState<string | null>(null)
  const [lastFullCheck, setLastFullCheck] = React.useState<number | null>(null)
  const [failCount, setFailCount] = React.useState(0)
  const [warningCount, setWarningCount] = React.useState(0)
  const { toast } = useToast()

  // Fetch diagnostics on mount
  React.useEffect(() => {
    fetchDiagnostics()
  }, [])

  const fetchDiagnostics = async () => {
    try {
      const data = await callToolsApi('diagnostics')

      if (data.success) {
        const response = data.data as DiagnosticsResponse
        setChecks(response.checks || [])
        setLastFullCheck(response.lastFullCheck)
        setFailCount(response.failCount)
        setWarningCount(response.warningCount)
      }
    } catch (error) {
      console.error('Failed to fetch diagnostics:', error)
    } finally {
      setIsLoading(false)
    }
  }

  const runAllChecks = async () => {
    setIsRunningAll(true)

    try {
      const data = await callToolsApi('diagnostics_run')

      if (data.success) {
        const response = data.data as DiagnosticsResponse
        setChecks(response.checks || [])
        setLastFullCheck(response.lastFullCheck)
        setFailCount(response.failCount)
        setWarningCount(response.warningCount)
        toast({ title: __('Diagnostics complete', 'wp-statistics') })
      }
    } catch (error) {
      console.error('Failed to run diagnostics:', error)
      toast({ title: __('Error', 'wp-statistics'), description: __('Failed to run diagnostics.', 'wp-statistics'), variant: 'destructive' })
    } finally {
      setIsRunningAll(false)
    }
  }

  const runSingleCheck = async (checkKey: string) => {
    setRunningCheck(checkKey)

    try {
      const data = await callToolsApi('diagnostics_run_check', { check: checkKey })

      if (data.success && data.data?.check) {
        const updatedCheck = data.data.check as DiagnosticCheck
        setChecks((prev) => prev.map((c) => (c.key === checkKey ? updatedCheck : c)))
        // Recalculate counts
        const updated = checks.map((c) => (c.key === checkKey ? updatedCheck : c))
        setFailCount(updated.filter((c) => c.status === 'fail').length)
        setWarningCount(updated.filter((c) => c.status === 'warning').length)
      }
    } catch (error) {
      console.error('Failed to run check:', error)
      toast({ title: __('Error', 'wp-statistics'), description: __('Failed to run check.', 'wp-statistics'), variant: 'destructive' })
    } finally {
      setRunningCheck(null)
    }
  }

  const repairCheck = async (checkKey: string) => {
    setRepairingCheck(checkKey)

    try {
      const data = await callToolsApi('diagnostics_repair', { check: checkKey })

      if (data.success) {
        toast({ title: __('Repair complete', 'wp-statistics') })
        // Re-run the check to get updated status
        await runSingleCheck(checkKey)
      } else {
        toast({ title: __('Error', 'wp-statistics'), description: __('Repair failed.', 'wp-statistics'), variant: 'destructive' })
      }
    } catch (error) {
      console.error('Failed to repair:', error)
      toast({ title: __('Error', 'wp-statistics'), description: __('Repair failed.', 'wp-statistics'), variant: 'destructive' })
    } finally {
      setRepairingCheck(null)
    }
  }

  const formatLastCheck = (timestamp: number | null) => {
    if (!timestamp) return __('Never', 'wp-statistics')

    const date = new Date(timestamp * 1000)
    const now = new Date()
    const diffMs = now.getTime() - date.getTime()
    const diffHours = Math.floor(diffMs / (1000 * 60 * 60))

    if (diffHours < 1) {
      const diffMins = Math.floor(diffMs / (1000 * 60))
      return diffMins <= 1 ? __('Just now', 'wp-statistics') : `${diffMins} ${__('minutes ago', 'wp-statistics')}`
    } else if (diffHours < 24) {
      return `${diffHours} ${diffHours > 1 ? __('hours ago', 'wp-statistics') : __('hour ago', 'wp-statistics')}`
    } else {
      return date.toLocaleDateString(undefined, {
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
      })
    }
  }

  const passCount = checks.filter((c) => c.status === 'pass').length

  if (isLoading) {
    return (
      <div className="flex items-center justify-center p-8">
        <Loader2 className="h-6 w-6 animate-spin" />
        <span className="ml-2">{__('Loading diagnostics...', 'wp-statistics')}</span>
      </div>
    )
  }

  return (
    <div className="space-y-6">
      {/* Info Box */}
      <NoticeBanner
        title={__('System Diagnostics', 'wp-statistics')}
        message={__('These checks help identify potential issues that may affect WP Statistics functionality. Lightweight checks run automatically, while others require manual execution to avoid performance impact.', 'wp-statistics')}
        type="neutral"
        icon={Stethoscope}
        dismissible={false}
      />

      {/* Summary Card */}
      <Card>
        <CardHeader className="flex flex-row items-center justify-between pb-4">
          <div className="space-y-1">
            <CardTitle className="flex items-center gap-2">
              <Stethoscope className="h-5 w-5" />
              {__('Diagnostic Results', 'wp-statistics')}
            </CardTitle>
            <CardDescription className="flex items-center gap-4">
              <span className="flex items-center gap-1">
                <CheckCircle2 className="h-4 w-4 text-green-500" />
                {passCount} {__('Passed', 'wp-statistics')}
              </span>
              {warningCount > 0 && (
                <span className="flex items-center gap-1">
                  <AlertTriangle className="h-4 w-4 text-yellow-500" />
                  {warningCount} {warningCount > 1 ? __('Warnings', 'wp-statistics') : __('Warning', 'wp-statistics')}
                </span>
              )}
              {failCount > 0 && (
                <span className="flex items-center gap-1">
                  <XCircle className="h-4 w-4 text-red-500" />
                  {failCount} {__('Failed', 'wp-statistics')}
                </span>
              )}
            </CardDescription>
          </div>
          <div className="flex items-center gap-4">
            <div className="flex items-center gap-1 text-sm text-muted-foreground">
              <Clock className="h-4 w-4" />
              {__('Last full check:', 'wp-statistics')} {formatLastCheck(lastFullCheck)}
            </div>
            <Button onClick={runAllChecks} disabled={isRunningAll}>
              {isRunningAll ? (
                <Loader2 className="mr-2 h-4 w-4 animate-spin" />
              ) : (
                <RefreshCw className="mr-2 h-4 w-4" />
              )}
              {__('Run All Checks', 'wp-statistics')}
            </Button>
          </div>
        </CardHeader>
        <CardContent>
          <div className="space-y-3">
            {checks.length === 0 ? (
              <div className="flex flex-col items-center justify-center py-12 text-center">
                <Stethoscope className="h-12 w-12 text-muted-foreground/50 mb-4" />
                <h3 className="text-lg font-medium mb-1">{__('No diagnostic checks available', 'wp-statistics')}</h3>
                <p className="text-sm text-muted-foreground">
                  {__('Try running the diagnostics to see system health information.', 'wp-statistics')}
                </p>
              </div>
            ) : (
              checks.map((check) => (
                <DiagnosticCheckItem
                  key={check.key}
                  check={check}
                  isRunning={runningCheck === check.key || isRunningAll}
                  isRepairing={repairingCheck === check.key}
                  onRetest={() => runSingleCheck(check.key)}
                  onRepair={check.details?.canRepair ? () => repairCheck(check.key) : undefined}
                />
              ))
            )}
          </div>
        </CardContent>
      </Card>
    </div>
  )
}
