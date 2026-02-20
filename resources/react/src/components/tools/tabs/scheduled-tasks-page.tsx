import { __, sprintf } from '@wordpress/i18n'
import { Calendar, Clock, Info, Loader2, Play, RefreshCw } from 'lucide-react'
import * as React from 'react'

import { SettingsCard } from '@/components/settings-ui'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { NoticeBanner } from '@/components/ui/notice-banner'
import { Skeleton } from '@/components/ui/skeleton'
import { PanelSkeleton, TableSkeleton } from '@/components/ui/skeletons'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { useToast } from '@/hooks/use-toast'
import { callToolsApi } from '@/services/tools'

interface ScheduledTask {
  hook: string
  label: string
  recurrence: string
  scheduled: boolean
  enabled: boolean
  next_run: string | null
}

export function ScheduledTasksPage() {
  const [tasks, setTasks] = React.useState<ScheduledTask[]>([])
  const [isLoading, setIsLoading] = React.useState(true)
  const [runningTask, setRunningTask] = React.useState<string | null>(null)
  const { toast } = useToast()

  // Fetch tasks on mount
  React.useEffect(() => {
    fetchTasks()
  // eslint-disable-next-line react-hooks/exhaustive-deps -- fetch once on mount
  }, [])

  const fetchTasks = async () => {
    try {
      const data = await callToolsApi('scheduled_tasks')

      if (data.success) {
        setTasks(data.data.tasks || [])
      }
    } catch {
      toast({
        title: __('Error', 'wp-statistics'),
        description: __('Failed to load scheduled tasks. Please refresh the page.', 'wp-statistics'),
        variant: 'destructive',
      })
    } finally {
      setIsLoading(false)
    }
  }

  const runTask = async (hook: string) => {
    setRunningTask(hook)

    try {
      const data = await callToolsApi('run_task', { hook })

      if (data.success) {
        toast({
          title: __('Task executed', 'wp-statistics'),
          description: data.data?.message || __('Task executed successfully.', 'wp-statistics'),
        })
        // Refresh tasks to update next run times
        await fetchTasks()
      } else {
        toast({
          title: __('Error', 'wp-statistics'),
          description: data.data?.message || __('Failed to run task.', 'wp-statistics'),
          variant: 'destructive',
        })
      }
    } catch {
      toast({
        title: __('Error', 'wp-statistics'),
        description: __('Failed to run task. Please try again.', 'wp-statistics'),
        variant: 'destructive',
      })
    } finally {
      setRunningTask(null)
    }
  }

  const formatNextRun = (nextRun: string | null) => {
    if (!nextRun) return '-'

    try {
      const date = new Date(nextRun)
      return date.toLocaleDateString(undefined, {
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
      })
    } catch {
      return nextRun
    }
  }

  const getRecurrenceBadge = (recurrence: string) => {
    const variants: Record<string, 'default' | 'secondary' | 'outline'> = {
      daily: 'default',
      weekly: 'secondary',
      monthly: 'outline',
    }
    return <Badge variant={variants[recurrence] || 'outline'}>{recurrence}</Badge>
  }

  if (isLoading) {
    return (
      <div className="space-y-6">
        <Skeleton className="h-16 w-full rounded-lg" />
        <PanelSkeleton titleWidth="w-36">
          <TableSkeleton rows={6} columns={5} />
        </PanelSkeleton>
      </div>
    )
  }

  return (
    <div className="space-y-6">
      <NoticeBanner
        title={__('About Scheduled Tasks', 'wp-statistics')}
        message={__('These tasks run automatically via WordPress cron. Some tasks may be disabled based on your plugin settings. You can manually trigger any task using the "Run Now" button.', 'wp-statistics')}
        type="neutral"
        icon={Info}
        dismissible={false}
      />

      {/* Scheduled Tasks Card */}
      <SettingsCard
        title={__('Scheduled Tasks', 'wp-statistics')}
        icon={Clock}
        description={sprintf(__('%1$d of %2$d tasks are currently enabled.', 'wp-statistics'), tasks.filter((t) => t.enabled).length, tasks.length)}
        action={
          <Button variant="outline" size="sm" onClick={fetchTasks} disabled={isLoading}>
            <RefreshCw className="mr-2 h-4 w-4" />
            {__('Refresh', 'wp-statistics')}
          </Button>
        }
      >
          {tasks.length === 0 ? (
            <div className="flex flex-col items-center justify-center py-12 text-center">
              <Clock className="h-12 w-12 text-muted-foreground/50 mb-4" />
              <h3 className="text-lg font-medium mb-1">{__('No scheduled tasks', 'wp-statistics')}</h3>
              <p className="text-sm text-muted-foreground">{__('No cron jobs have been registered.', 'wp-statistics')}</p>
            </div>
          ) : (
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>{__('Task', 'wp-statistics')}</TableHead>
                  <TableHead>{__('Recurrence', 'wp-statistics')}</TableHead>
                  <TableHead>{__('Status', 'wp-statistics')}</TableHead>
                  <TableHead>{__('Next Run', 'wp-statistics')}</TableHead>
                  <TableHead className="text-right">{__('Actions', 'wp-statistics')}</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {tasks.map((task) => (
                  <TableRow key={task.hook}>
                    <TableCell>
                      <div>
                        <p className="font-medium">{task.label}</p>
                        <code className="text-xs text-muted-foreground">{task.hook}</code>
                      </div>
                    </TableCell>
                    <TableCell>{getRecurrenceBadge(task.recurrence)}</TableCell>
                    <TableCell>
                      {task.enabled ? (
                        <Badge variant="default" className="bg-emerald-600 hover:bg-emerald-700">
                          {__('Enabled', 'wp-statistics')}
                        </Badge>
                      ) : (
                        <Badge variant="secondary">{__('Disabled', 'wp-statistics')}</Badge>
                      )}
                    </TableCell>
                    <TableCell>
                      {task.scheduled && task.next_run ? (
                        <div className="flex items-center gap-1 text-muted-foreground">
                          <Calendar className="h-3 w-3" />
                          <span className="text-sm">{formatNextRun(task.next_run)}</span>
                        </div>
                      ) : (
                        <span className="text-muted-foreground">-</span>
                      )}
                    </TableCell>
                    <TableCell className="text-right">
                      <Button
                        variant="outline"
                        size="sm"
                        onClick={() => runTask(task.hook)}
                        disabled={runningTask === task.hook}
                      >
                        {runningTask === task.hook ? (
                          <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                        ) : (
                          <Play className="mr-2 h-4 w-4" />
                        )}
                        {__('Run Now', 'wp-statistics')}
                      </Button>
                    </TableCell>
                  </TableRow>
                ))}
              </TableBody>
            </Table>
          )}
      </SettingsCard>
    </div>
  )
}
