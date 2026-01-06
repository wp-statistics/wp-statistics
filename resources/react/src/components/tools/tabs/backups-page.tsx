import * as React from 'react'
import {
  Loader2,
  Database,
  Download,
  Trash2,
  RotateCcw,
  Plus,
  FileJson,
  Calendar,
  HardDrive,
  AlertTriangle,
  CheckCircle2,
  XCircle,
  Info,
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
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'
import { Badge } from '@/components/ui/badge'

interface Backup {
  name: string
  size: string
  created_at: string
  cutoff_date?: string
  type?: 'archive_backup' | 'manual'
}

// Helper to get config
const getConfig = () => {
  const wpsReact = (window as any).wps_react
  const defaultActions = {
    backupsList: 'wp_statistics_backups_list',
    backupDelete: 'wp_statistics_backup_delete',
    backupDownload: 'wp_statistics_backup_download',
    backupRestore: 'wp_statistics_backup_restore',
    backupCreate: 'wp_statistics_backup_create',
  }
  return {
    ajaxUrl: wpsReact?.globals?.ajaxUrl || '/wp-admin/admin-ajax.php',
    nonce: wpsReact?.importExport?.nonce || wpsReact?.globals?.nonce || '',
    actions: { ...defaultActions, ...(wpsReact?.importExport?.actions || {}) },
  }
}

export function BackupsPage() {
  const [backups, setBackups] = React.useState<Backup[]>([])
  const [isLoading, setIsLoading] = React.useState(true)
  const [isCreating, setIsCreating] = React.useState(false)
  const [isRestoring, setIsRestoring] = React.useState<string | null>(null)
  const [deleteTarget, setDeleteTarget] = React.useState<string | null>(null)
  const [restoreTarget, setRestoreTarget] = React.useState<string | null>(null)
  const [statusMessage, setStatusMessage] = React.useState<{
    type: 'success' | 'error'
    message: string
  } | null>(null)

  // Fetch backups on mount
  React.useEffect(() => {
    fetchBackups()
  }, [])

  const fetchBackups = async () => {
    try {
      const config = getConfig()
      const response = await fetch(
        `${config.ajaxUrl}?action=${config.actions.backupsList}`,
        {
          method: 'POST',
          headers: {
            'X-WP-Nonce': config.nonce,
          },
        }
      )
      const data = await response.json()

      if (data.success && data.data?.backups) {
        setBackups(data.data.backups)
      }
    } catch (error) {
      console.error('Failed to fetch backups:', error)
      setStatusMessage({
        type: 'error',
        message: 'Failed to load backups. Please refresh the page.',
      })
    } finally {
      setIsLoading(false)
    }
  }

  const createBackup = async () => {
    setIsCreating(true)
    setStatusMessage(null)

    try {
      const config = getConfig()
      const formData = new FormData()
      formData.append('_wpnonce', config.nonce)

      const response = await fetch(
        `${config.ajaxUrl}?action=${config.actions.backupCreate}`,
        {
          method: 'POST',
          headers: {
            'X-WP-Nonce': config.nonce,
          },
          body: formData,
        }
      )
      const data = await response.json()

      if (data.success) {
        setStatusMessage({
          type: 'success',
          message: 'Backup created successfully.',
        })
        fetchBackups()
      } else {
        setStatusMessage({
          type: 'error',
          message: data.data?.message || 'Failed to create backup.',
        })
      }
    } catch (error) {
      setStatusMessage({
        type: 'error',
        message: 'Failed to create backup. Please try again.',
      })
    } finally {
      setIsCreating(false)
    }
  }

  const downloadBackup = (fileName: string) => {
    const config = getConfig()
    window.location.href = `${config.ajaxUrl}?action=${config.actions.backupDownload}&file_name=${encodeURIComponent(fileName)}&_wpnonce=${config.nonce}`
  }

  const deleteBackup = async () => {
    if (!deleteTarget) return

    setStatusMessage(null)

    try {
      const config = getConfig()
      const formData = new FormData()
      formData.append('file_name', deleteTarget)
      formData.append('_wpnonce', config.nonce)

      const response = await fetch(
        `${config.ajaxUrl}?action=${config.actions.backupDelete}`,
        {
          method: 'POST',
          headers: {
            'X-WP-Nonce': config.nonce,
          },
          body: formData,
        }
      )
      const data = await response.json()

      if (data.success) {
        setStatusMessage({
          type: 'success',
          message: 'Backup deleted successfully.',
        })
        fetchBackups()
      } else {
        setStatusMessage({
          type: 'error',
          message: data.data?.message || 'Failed to delete backup.',
        })
      }
    } catch (error) {
      setStatusMessage({
        type: 'error',
        message: 'Failed to delete backup. Please try again.',
      })
    } finally {
      setDeleteTarget(null)
    }
  }

  const restoreBackup = async () => {
    if (!restoreTarget) return

    setIsRestoring(restoreTarget)
    setStatusMessage(null)

    try {
      const config = getConfig()
      const formData = new FormData()
      formData.append('file_name', restoreTarget)
      formData.append('_wpnonce', config.nonce)

      const response = await fetch(
        `${config.ajaxUrl}?action=${config.actions.backupRestore}`,
        {
          method: 'POST',
          headers: {
            'X-WP-Nonce': config.nonce,
          },
          body: formData,
        }
      )
      const data = await response.json()

      if (data.success) {
        setStatusMessage({
          type: 'success',
          message: data.data?.message || 'Backup restored successfully.',
        })
      } else {
        setStatusMessage({
          type: 'error',
          message: data.data?.message || 'Failed to restore backup.',
        })
      }
    } catch (error) {
      setStatusMessage({
        type: 'error',
        message: 'Failed to restore backup. Please try again.',
      })
    } finally {
      setIsRestoring(null)
      setRestoreTarget(null)
    }
  }

  const formatDate = (dateString: string) => {
    try {
      const date = new Date(dateString)
      return date.toLocaleDateString(undefined, {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
      })
    } catch {
      return dateString
    }
  }

  if (isLoading) {
    return (
      <div className="flex items-center justify-center p-8">
        <Loader2 className="h-6 w-6 animate-spin" />
        <span className="ml-2">Loading backups...</span>
      </div>
    )
  }

  return (
    <div className="space-y-6">
      {/* Info Box */}
      <div className="rounded-lg border bg-muted/50 p-4">
        <div className="flex gap-3">
          <Info className="h-5 w-5 text-muted-foreground flex-shrink-0 mt-0.5" />
          <div>
            <h4 className="font-medium mb-1">About Backups</h4>
            <p className="text-sm text-muted-foreground">
              Backups are automatically created when using the "Archive after X days" data retention mode.
              You can also create manual backups at any time. Backups contain your raw statistics data
              and can be restored or downloaded for safekeeping.
            </p>
          </div>
        </div>
      </div>

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
              <XCircle className="h-4 w-4" />
            )}
            <span>{statusMessage.message}</span>
          </div>
        </div>
      )}

      {/* Backups Card */}
      <Card>
        <CardHeader className="flex flex-row items-center justify-between">
          <div>
            <CardTitle className="flex items-center gap-2">
              <Database className="h-5 w-5" />
              Backups
            </CardTitle>
            <CardDescription>
              Manage your statistics data backups. Download, restore, or delete backups as needed.
            </CardDescription>
          </div>
          <Button onClick={createBackup} disabled={isCreating}>
            {isCreating ? (
              <Loader2 className="mr-2 h-4 w-4 animate-spin" />
            ) : (
              <Plus className="mr-2 h-4 w-4" />
            )}
            Create Backup
          </Button>
        </CardHeader>
        <CardContent>
          {backups.length === 0 ? (
            <div className="flex flex-col items-center justify-center py-12 text-center">
              <Database className="h-12 w-12 text-muted-foreground/50 mb-4" />
              <h3 className="text-lg font-medium mb-1">No backups yet</h3>
              <p className="text-sm text-muted-foreground mb-4">
                Create your first backup to preserve your statistics data.
              </p>
              <Button onClick={createBackup} disabled={isCreating}>
                {isCreating ? (
                  <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                ) : (
                  <Plus className="mr-2 h-4 w-4" />
                )}
                Create Backup
              </Button>
            </div>
          ) : (
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Backup</TableHead>
                  <TableHead>Type</TableHead>
                  <TableHead>Size</TableHead>
                  <TableHead>Created</TableHead>
                  <TableHead className="text-right">Actions</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {backups.map((backup) => (
                  <TableRow key={backup.name}>
                    <TableCell>
                      <div className="flex items-center gap-2">
                        <FileJson className="h-4 w-4 text-muted-foreground" />
                        <span className="font-medium truncate max-w-[200px]" title={backup.name}>
                          {backup.name}
                        </span>
                      </div>
                    </TableCell>
                    <TableCell>
                      <Badge variant={backup.type === 'archive_backup' ? 'secondary' : 'outline'}>
                        {backup.type === 'archive_backup' ? 'Automatic' : 'Manual'}
                      </Badge>
                    </TableCell>
                    <TableCell>
                      <div className="flex items-center gap-1 text-muted-foreground">
                        <HardDrive className="h-3 w-3" />
                        <span>{backup.size}</span>
                      </div>
                    </TableCell>
                    <TableCell>
                      <div className="flex items-center gap-1 text-muted-foreground">
                        <Calendar className="h-3 w-3" />
                        <span>{formatDate(backup.created_at)}</span>
                      </div>
                    </TableCell>
                    <TableCell className="text-right">
                      <div className="flex items-center justify-end gap-1">
                        <Button
                          variant="ghost"
                          size="sm"
                          onClick={() => downloadBackup(backup.name)}
                          title="Download backup"
                        >
                          <Download className="h-4 w-4" />
                        </Button>
                        <Button
                          variant="ghost"
                          size="sm"
                          onClick={() => setRestoreTarget(backup.name)}
                          disabled={isRestoring === backup.name}
                          title="Restore backup"
                        >
                          {isRestoring === backup.name ? (
                            <Loader2 className="h-4 w-4 animate-spin" />
                          ) : (
                            <RotateCcw className="h-4 w-4" />
                          )}
                        </Button>
                        <Button
                          variant="ghost"
                          size="sm"
                          onClick={() => setDeleteTarget(backup.name)}
                          className="text-destructive hover:text-destructive"
                          title="Delete backup"
                        >
                          <Trash2 className="h-4 w-4" />
                        </Button>
                      </div>
                    </TableCell>
                  </TableRow>
                ))}
              </TableBody>
            </Table>
          )}
        </CardContent>
      </Card>

      {/* Delete Confirmation Dialog */}
      <Dialog open={!!deleteTarget} onOpenChange={() => setDeleteTarget(null)}>
        <DialogContent>
          <DialogHeader>
            <DialogTitle className="flex items-center gap-2">
              <AlertTriangle className="h-5 w-5 text-destructive" />
              Delete Backup
            </DialogTitle>
            <DialogDescription>
              Are you sure you want to delete this backup? This action cannot be undone.
              <br />
              <span className="font-medium">{deleteTarget}</span>
            </DialogDescription>
          </DialogHeader>
          <DialogFooter>
            <Button variant="outline" onClick={() => setDeleteTarget(null)}>
              Cancel
            </Button>
            <Button
              variant="destructive"
              onClick={deleteBackup}
            >
              Delete
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>

      {/* Restore Confirmation Dialog */}
      <Dialog open={!!restoreTarget} onOpenChange={() => setRestoreTarget(null)}>
        <DialogContent>
          <DialogHeader>
            <DialogTitle className="flex items-center gap-2">
              <RotateCcw className="h-5 w-5 text-primary" />
              Restore Backup
            </DialogTitle>
            <DialogDescription>
              This will restore the data from this backup. Existing data with the same IDs may be updated.
              <br />
              <span className="font-medium">{restoreTarget}</span>
            </DialogDescription>
          </DialogHeader>
          <DialogFooter>
            <Button variant="outline" onClick={() => setRestoreTarget(null)}>
              Cancel
            </Button>
            <Button onClick={restoreBackup}>
              Restore
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </div>
  )
}
