import * as React from 'react'
import {
  Loader2,
  Clock,
  Play,
  Calendar,
  RefreshCw,
} from 'lucide-react'

import { Button } from '@/components/ui/button'
import { NoticeBanner } from '@/components/ui/notice-banner'
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

interface ScheduledTask {
  hook: string
  label: string
  recurrence: string
  scheduled: boolean
  enabled: boolean
  next_run: string | null
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

export function ScheduledTasksPage() {
  const [tasks, setTasks] = React.useState<ScheduledTask[]>([])
  const [isLoading, setIsLoading] = React.useState(true)
  const [runningTask, setRunningTask] = React.useState<string | null>(null)
  const [statusMessage, setStatusMessage] = React.useState<{
    type: 'success' | 'error'
    message: string
  } | null>(null)

  // Fetch tasks on mount
  React.useEffect(() => {
    fetchTasks()
  }, [])

  const fetchTasks = async () => {
    try {
      const data = await callToolsApi('scheduled_tasks')

      if (data.success) {
        setTasks(data.data.tasks || [])
      }
    } catch (error) {
      console.error('Failed to fetch scheduled tasks:', error)
      setStatusMessage({
        type: 'error',
        message: 'Failed to load scheduled tasks. Please refresh the page.',
      })
    } finally {
      setIsLoading(false)
    }
  }

  const runTask = async (hook: string) => {
    setRunningTask(hook)
    setStatusMessage(null)

    try {
      const data = await callToolsApi('run_task', { hook })

      if (data.success) {
        setStatusMessage({
          type: 'success',
          message: data.data?.message || 'Task executed successfully.',
        })
        // Refresh tasks to update next run times
        await fetchTasks()
      } else {
        setStatusMessage({
          type: 'error',
          message: data.data?.message || 'Failed to run task.',
        })
      }
    } catch (error) {
      setStatusMessage({
        type: 'error',
        message: 'Failed to run task. Please try again.',
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
    return (
      <Badge variant={variants[recurrence] || 'outline'}>
        {recurrence}
      </Badge>
    )
  }

  if (isLoading) {
    return (
      <div className="flex items-center justify-center p-8">
        <Loader2 className="h-6 w-6 animate-spin" />
        <span className="ml-2">Loading scheduled tasks...</span>
      </div>
    )
  }

  return (
    <div className="space-y-6">
      {/* Info Box */}
      <div className="rounded-lg border bg-muted/50 p-4">
        <div className="flex gap-3">
          <Clock className="h-5 w-5 text-muted-foreground flex-shrink-0 mt-0.5" />
          <div>
            <h4 className="font-medium mb-1">About Scheduled Tasks</h4>
            <p className="text-sm text-muted-foreground">
              These tasks run automatically via WordPress cron. Some tasks may be disabled
              based on your plugin settings. You can manually trigger any task using the
              "Run Now" button.
            </p>
          </div>
        </div>
      </div>

      {/* Status Message */}
      {statusMessage && (
        <NoticeBanner
          id="scheduled-tasks-status"
          message={statusMessage.message}
          type={statusMessage.type}
          dismissible
          onDismiss={() => setStatusMessage(null)}
        />
      )}

      {/* Scheduled Tasks Card */}
      <Card>
        <CardHeader className="flex flex-row items-center justify-between">
          <div>
            <CardTitle className="flex items-center gap-2">
              <Clock className="h-5 w-5" />
              Scheduled Tasks
            </CardTitle>
            <CardDescription>
              {tasks.filter(t => t.enabled).length} of {tasks.length} tasks are currently enabled.
            </CardDescription>
          </div>
          <Button
            variant="outline"
            size="sm"
            onClick={fetchTasks}
            disabled={isLoading}
          >
            <RefreshCw className="mr-2 h-4 w-4" />
            Refresh
          </Button>
        </CardHeader>
        <CardContent>
          {tasks.length === 0 ? (
            <div className="flex flex-col items-center justify-center py-12 text-center">
              <Clock className="h-12 w-12 text-muted-foreground/50 mb-4" />
              <h3 className="text-lg font-medium mb-1">No scheduled tasks</h3>
              <p className="text-sm text-muted-foreground">
                No cron jobs have been registered.
              </p>
            </div>
          ) : (
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Task</TableHead>
                  <TableHead>Recurrence</TableHead>
                  <TableHead>Status</TableHead>
                  <TableHead>Next Run</TableHead>
                  <TableHead className="text-right">Actions</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {tasks.map((task) => (
                  <TableRow key={task.hook}>
                    <TableCell>
                      <div>
                        <p className="font-medium">{task.label}</p>
                        <code className="text-xs text-muted-foreground">
                          {task.hook}
                        </code>
                      </div>
                    </TableCell>
                    <TableCell>
                      {getRecurrenceBadge(task.recurrence)}
                    </TableCell>
                    <TableCell>
                      {task.enabled ? (
                        <Badge variant="default" className="bg-green-500 hover:bg-green-600">
                          Enabled
                        </Badge>
                      ) : (
                        <Badge variant="secondary">
                          Disabled
                        </Badge>
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
                        Run Now
                      </Button>
                    </TableCell>
                  </TableRow>
                ))}
              </TableBody>
            </Table>
          )}
        </CardContent>
      </Card>
    </div>
  )
}
