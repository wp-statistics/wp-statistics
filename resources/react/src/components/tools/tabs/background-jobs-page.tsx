import * as React from 'react'
import {
  Loader2,
  Activity,
  CheckCircle2,
  Clock,
  RefreshCw,
  PlayCircle,
  PauseCircle,
} from 'lucide-react'

import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'
import { Badge } from '@/components/ui/badge'
import { Progress } from '@/components/ui/progress'

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

// Helper to get config
const getConfig = () => {
  const wpsReact = (window as any).wps_react
  return {
    ajaxUrl: wpsReact?.globals?.ajaxUrl || '/wp-admin/admin-ajax.php',
    nonce: wpsReact?.globals?.nonce || '',
  }
}

// Helper to call tools endpoint with sub_action
const callToolsApi = async (subAction: string, params: Record<string, string> = {}) => {
  const config = getConfig()
  const formData = new FormData()
  formData.append('wps_nonce', config.nonce)
  formData.append('sub_action', subAction)
  Object.entries(params).forEach(([key, value]) => {
    formData.append(key, value)
  })

  const response = await fetch(`${config.ajaxUrl}?action=wp_statistics_tools`, {
    method: 'POST',
    body: formData,
  })
  return response.json()
}

export function BackgroundJobsPage() {
  const [jobs, setJobs] = React.useState<BackgroundJob[]>([])
  const [isLoading, setIsLoading] = React.useState(true)
  const [isRefreshing, setIsRefreshing] = React.useState(false)
  const [statusMessage, setStatusMessage] = React.useState<{
    type: 'success' | 'error'
    message: string
  } | null>(null)

  // Fetch jobs on mount
  React.useEffect(() => {
    fetchJobs()
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
          setStatusMessage({
            type: 'error',
            message: data.data?.message || 'Failed to fetch background jobs.',
          })
        }
      }
    } catch (error) {
      console.error('Failed to fetch background jobs:', error)
      if (!silent) {
        setStatusMessage({
          type: 'error',
          message: 'Failed to load background jobs. Please refresh the page.',
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
          <Badge variant="default" className="bg-blue-500 hover:bg-blue-600">
            <PlayCircle className="mr-1 h-3 w-3" />
            Running
          </Badge>
        )
      case 'queued':
        return (
          <Badge variant="secondary">
            <Clock className="mr-1 h-3 w-3" />
            Queued
          </Badge>
        )
      default:
        return (
          <Badge variant="outline">
            <PauseCircle className="mr-1 h-3 w-3" />
            Idle
          </Badge>
        )
    }
  }

  const getActiveJobsCount = () => {
    return jobs.filter((job) => job.status === 'running' || job.status === 'queued').length
  }

  if (isLoading) {
    return (
      <div className="flex items-center justify-center p-8">
        <Loader2 className="h-6 w-6 animate-spin" />
        <span className="ml-2">Loading background jobs...</span>
      </div>
    )
  }

  return (
    <div className="space-y-6">
      {/* Status Message */}
      {statusMessage && (
        <div
          className={`rounded-lg border p-4 ${
            statusMessage.type === 'error'
              ? 'border-destructive/50 bg-destructive/10 text-destructive'
              : 'border-green-500/50 bg-green-50 text-green-700 dark:bg-green-950/30 dark:text-green-400'
          }`}
        >
          <div className="flex gap-2 items-center">
            {statusMessage.type === 'success' ? (
              <CheckCircle2 className="h-4 w-4" />
            ) : (
              <Activity className="h-4 w-4" />
            )}
            <span>{statusMessage.message}</span>
          </div>
        </div>
      )}

      {/* Background Jobs Card */}
      <Card>
        <CardHeader className="flex flex-row items-center justify-between">
          <div>
            <CardTitle className="flex items-center gap-2">
              <Activity className="h-5 w-5" />
              Background Jobs
            </CardTitle>
            <CardDescription>
              {getActiveJobsCount() > 0
                ? `${getActiveJobsCount()} job${getActiveJobsCount() > 1 ? 's' : ''} currently active.`
                : 'No active background jobs.'}
            </CardDescription>
          </div>
          <Button
            variant="outline"
            size="sm"
            onClick={() => fetchJobs()}
            disabled={isRefreshing}
          >
            {isRefreshing ? (
              <Loader2 className="mr-2 h-4 w-4 animate-spin" />
            ) : (
              <RefreshCw className="mr-2 h-4 w-4" />
            )}
            Refresh
          </Button>
        </CardHeader>
        <CardContent>
          {jobs.length === 0 ? (
            <div className="flex flex-col items-center justify-center py-12 text-center">
              <Activity className="h-12 w-12 text-muted-foreground/50 mb-4" />
              <h3 className="text-lg font-medium mb-1">No background jobs</h3>
              <p className="text-sm text-muted-foreground">
                Background jobs will appear here when they are running.
              </p>
            </div>
          ) : (
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Job</TableHead>
                  <TableHead>Status</TableHead>
                  <TableHead>Progress</TableHead>
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
                            {job.progress.completed.toLocaleString()} / {job.progress.total.toLocaleString()}
                            {' '}({job.progress.percentage}%)
                          </div>
                        </div>
                      ) : job.status === 'queued' ? (
                        <span className="text-sm text-muted-foreground">Waiting to start...</span>
                      ) : (
                        <span className="text-sm text-muted-foreground">-</span>
                      )}
                    </TableCell>
                  </TableRow>
                ))}
              </TableBody>
            </Table>
          )}
        </CardContent>
      </Card>

      {/* Info Card */}
      <Card>
        <CardHeader>
          <CardTitle className="text-base">About Background Jobs</CardTitle>
        </CardHeader>
        <CardContent className="text-sm text-muted-foreground space-y-2">
          <p>
            Background jobs process large datasets without blocking your browser. They run
            automatically via WordPress cron and continue even if you close this page.
          </p>
          <p>
            Jobs include: GeoIP database updates, visitor location updates, source channel
            processing, daily summary calculations, and resource cache updates.
          </p>
        </CardContent>
      </Card>
    </div>
  )
}
