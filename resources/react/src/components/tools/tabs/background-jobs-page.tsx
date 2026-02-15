import { __, sprintf } from '@wordpress/i18n'
import { Activity, Clock, Loader2, PauseCircle, PlayCircle, RefreshCw } from 'lucide-react'
import * as React from 'react'

import { SettingsCard } from '@/components/settings-ui'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Progress } from '@/components/ui/progress'
import { PanelSkeleton, TableSkeleton } from '@/components/ui/skeletons'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { useToast } from '@/hooks/use-toast'
import { callToolsApi } from '@/services/tools'

interface BackgroundJob {
  key: string
  label: string
  description: string
  status: 'idle' | 'running' | 'queued'
  progress: {
    total: number
    completed: number
    remain: number
    percentage: number
  } | null
}

export function BackgroundJobsPage() {
  const [jobs, setJobs] = React.useState<BackgroundJob[]>([])
  const [isLoading, setIsLoading] = React.useState(true)
  const [isRefreshing, setIsRefreshing] = React.useState(false)
  const { toast } = useToast()

  // Fetch jobs on mount
  React.useEffect(() => {
    fetchJobs()
  // eslint-disable-next-line react-hooks/exhaustive-deps -- fetch once on mount
  }, [])

  // Auto-refresh when there are running jobs
  React.useEffect(() => {
    const hasRunningJobs = jobs.some((job) => job.status === 'running' || job.status === 'queued')

    if (hasRunningJobs) {
      const interval = setInterval(() => {
        fetchJobs(true) // Silent refresh
      }, 5000)

      return () => clearInterval(interval)
    }
  // eslint-disable-next-line react-hooks/exhaustive-deps -- re-check on jobs change; fetchJobs is stable
  }, [jobs])

  const fetchJobs = async (silent = false) => {
    if (!silent) {
      setIsRefreshing(true)
    }

    try {
      const data = await callToolsApi('background_jobs')

      if (data.success) {
        setJobs(data.data.jobs || [])
      } else {
        if (!silent) {
          toast({
            title: __('Error', 'wp-statistics'),
            description: data.data?.message || __('Failed to fetch background jobs.', 'wp-statistics'),
            variant: 'destructive',
          })
        }
      }
    } catch {
      if (!silent) {
        toast({
          title: __('Error', 'wp-statistics'),
          description: __('Failed to load background jobs. Please refresh the page.', 'wp-statistics'),
          variant: 'destructive',
        })
      }
    } finally {
      setIsLoading(false)
      setIsRefreshing(false)
    }
  }

  const getStatusBadge = (status: BackgroundJob['status']) => {
    switch (status) {
      case 'running':
        return (
          <Badge variant="default" className="bg-primary hover:bg-primary/90">
            <PlayCircle className="mr-1 h-3 w-3" />
            {__('Running', 'wp-statistics')}
          </Badge>
        )
      case 'queued':
        return (
          <Badge variant="secondary">
            <Clock className="mr-1 h-3 w-3" />
            {__('Queued', 'wp-statistics')}
          </Badge>
        )
      default:
        return (
          <Badge variant="outline">
            <PauseCircle className="mr-1 h-3 w-3" />
            {__('Idle', 'wp-statistics')}
          </Badge>
        )
    }
  }

  const getActiveJobsCount = () => {
    return jobs.filter((job) => job.status === 'running' || job.status === 'queued').length
  }

  if (isLoading) {
    return (
      <div className="space-y-5">
        <PanelSkeleton titleWidth="w-36">
          <TableSkeleton rows={5} columns={3} />
        </PanelSkeleton>
      </div>
    )
  }

  return (
    <div className="space-y-5">
      {/* Background Jobs Card */}
      <SettingsCard
        title={__('Background Jobs', 'wp-statistics')}
        icon={Activity}
        description={
          getActiveJobsCount() > 0
            ? sprintf(__('%d jobs currently active.', 'wp-statistics'), getActiveJobsCount())
            : __('No active background jobs.', 'wp-statistics')
        }
        action={
          <Button variant="outline" size="sm" onClick={() => fetchJobs()} disabled={isRefreshing}>
            {isRefreshing ? <Loader2 className="mr-2 h-4 w-4 animate-spin" /> : <RefreshCw className="mr-2 h-4 w-4" />}
            {__('Refresh', 'wp-statistics')}
          </Button>
        }
      >
          {jobs.length === 0 ? (
            <div className="flex flex-col items-center justify-center py-12 text-center">
              <Activity className="h-12 w-12 text-muted-foreground/50 mb-4" />
              <h3 className="text-lg font-medium mb-1">{__('No background jobs', 'wp-statistics')}</h3>
              <p className="text-sm text-muted-foreground">{__('Background jobs will appear here when they are running.', 'wp-statistics')}</p>
            </div>
          ) : (
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>{__('Job', 'wp-statistics')}</TableHead>
                  <TableHead>{__('Status', 'wp-statistics')}</TableHead>
                  <TableHead>{__('Progress', 'wp-statistics')}</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {jobs.map((job) => (
                  <TableRow key={job.key}>
                    <TableCell>
                      <div className="space-y-1">
                        <div className="font-medium">{job.label}</div>
                        <div className="text-sm text-muted-foreground">{job.description}</div>
                      </div>
                    </TableCell>
                    <TableCell>{getStatusBadge(job.status)}</TableCell>
                    <TableCell className="w-[200px]">
                      {job.status === 'running' && job.progress ? (
                        <div className="space-y-2">
                          <Progress value={job.progress.percentage} className="h-2" />
                          <div className="text-xs text-muted-foreground">
                            {job.progress.completed.toLocaleString()} / {job.progress.total.toLocaleString()} (
                            {job.progress.percentage}%)
                          </div>
                        </div>
                      ) : job.status === 'queued' ? (
                        <span className="text-sm text-muted-foreground">{__('Waiting to start...', 'wp-statistics')}</span>
                      ) : (
                        <span className="text-sm text-muted-foreground">-</span>
                      )}
                    </TableCell>
                  </TableRow>
                ))}
              </TableBody>
            </Table>
          )}
      </SettingsCard>

      {/* Info Card */}
      <SettingsCard title={__('About Background Jobs', 'wp-statistics')}>
        <div className="text-sm text-muted-foreground space-y-2">
          <p>
            {__('Background jobs process large datasets without blocking your browser. They run automatically via WordPress cron and continue even if you close this page.', 'wp-statistics')}
          </p>
          <p>
            {__('Jobs include: GeoIP database updates, visitor location updates, source channel processing, daily summary calculations, and resource cache updates.', 'wp-statistics')}
          </p>
        </div>
      </SettingsCard>
    </div>
  )
}
