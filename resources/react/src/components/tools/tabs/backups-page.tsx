import { __ } from '@wordpress/i18n'
import {
  AlertTriangle,
  Calendar,
  Database,
  Download,
  FileJson,
  HardDrive,
  Info,
  Loader2,
  Plus,
  RotateCcw,
  Trash2,
} from 'lucide-react'
import * as React from 'react'

import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'
import { NoticeBanner } from '@/components/ui/notice-banner'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { useToast } from '@/hooks/use-toast'
import { WordPress } from '@/lib/wordpress'
import { callImportExportApi } from '@/services/tools'

interface Backup {
  name: string
  size: string
  created_at: string
  cutoff_date?: string
  type?: 'archive_backup' | 'manual'
}

export function BackupsPage() {
  const [backups, setBackups] = React.useState<Backup[]>([])
  const [isLoading, setIsLoading] = React.useState(true)
  const [isCreating, setIsCreating] = React.useState(false)
  const [isRestoring, setIsRestoring] = React.useState<string | null>(null)
  const [deleteTarget, setDeleteTarget] = React.useState<string | null>(null)
  const [restoreTarget, setRestoreTarget] = React.useState<string | null>(null)
  const { toast } = useToast()

  // Fetch backups on mount
  React.useEffect(() => {
    fetchBackups()
  // eslint-disable-next-line react-hooks/exhaustive-deps -- fetch once on mount
  }, [])

  const fetchBackups = async () => {
    try {
      const data = await callImportExportApi('list_backups')

      if (data.success && data.data?.backups) {
        setBackups(data.data.backups)
      }
    } catch (error) {
      console.error('Failed to fetch backups:', error)
      toast({
        title: __('Error', 'wp-statistics'),
        description: __('Failed to load backups. Please refresh the page.', 'wp-statistics'),
        variant: 'destructive',
      })
    } finally {
      setIsLoading(false)
    }
  }

  const createBackup = async () => {
    setIsCreating(true)

    try {
      const data = await callImportExportApi('create_backup')

      if (data.success) {
        toast({ title: __('Backup created successfully.', 'wp-statistics') })
        fetchBackups()
      } else {
        toast({
          title: __('Error', 'wp-statistics'),
          description: data.data?.message || __('Failed to create backup.', 'wp-statistics'),
          variant: 'destructive',
        })
      }
    } catch {
      toast({
        title: __('Error', 'wp-statistics'),
        description: __('Failed to create backup. Please try again.', 'wp-statistics'),
        variant: 'destructive',
      })
    } finally {
      setIsCreating(false)
    }
  }

  const downloadBackup = (fileName: string) => {
    const wp = WordPress.getInstance()
    window.location.href = `${wp.getAjaxUrl()}?action=wp_statistics_import_export&sub_action=download_backup&file_name=${encodeURIComponent(fileName)}&wps_nonce=${wp.getNonce()}`
  }

  const deleteBackup = async () => {
    if (!deleteTarget) return

    try {
      const data = await callImportExportApi('delete_backup', { file_name: deleteTarget })

      if (data.success) {
        toast({ title: __('Backup deleted successfully.', 'wp-statistics') })
        fetchBackups()
      } else {
        toast({
          title: __('Error', 'wp-statistics'),
          description: data.data?.message || __('Failed to delete backup.', 'wp-statistics'),
          variant: 'destructive',
        })
      }
    } catch {
      toast({
        title: __('Error', 'wp-statistics'),
        description: __('Failed to delete backup. Please try again.', 'wp-statistics'),
        variant: 'destructive',
      })
    } finally {
      setDeleteTarget(null)
    }
  }

  const restoreBackup = async () => {
    if (!restoreTarget) return

    setIsRestoring(restoreTarget)

    try {
      const data = await callImportExportApi('restore_backup', { file_name: restoreTarget })

      if (data.success) {
        toast({
          title: __('Backup restored successfully.', 'wp-statistics'),
          description: data.data?.message,
        })
      } else {
        toast({
          title: __('Error', 'wp-statistics'),
          description: data.data?.message || __('Failed to restore backup.', 'wp-statistics'),
          variant: 'destructive',
        })
      }
    } catch {
      toast({
        title: __('Error', 'wp-statistics'),
        description: __('Failed to restore backup. Please try again.', 'wp-statistics'),
        variant: 'destructive',
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
        <span className="ml-2">{__('Loading backups...', 'wp-statistics')}</span>
      </div>
    )
  }

  return (
    <div className="space-y-6">
      {/* Info Box */}
      <NoticeBanner
        title={__('About Backups', 'wp-statistics')}
        message={__('Backups are automatically created when using the "Archive after X days" data retention mode. You can also create manual backups at any time. Backups contain your raw statistics data and can be restored or downloaded for safekeeping.', 'wp-statistics')}
        type="neutral"
        icon={Info}
        dismissible={false}
      />

      {/* Backups Card */}
      <Card>
        <CardHeader className="flex flex-row items-center justify-between">
          <div>
            <CardTitle className="flex items-center gap-2">
              <Database className="h-5 w-5" />
              {__('Backups', 'wp-statistics')}
            </CardTitle>
            <CardDescription>
              {__('Manage your statistics data backups. Download, restore, or delete backups as needed.', 'wp-statistics')}
            </CardDescription>
          </div>
          <Button onClick={createBackup} disabled={isCreating}>
            {isCreating ? <Loader2 className="mr-2 h-4 w-4 animate-spin" /> : <Plus className="mr-2 h-4 w-4" />}
            {__('Create Backup', 'wp-statistics')}
          </Button>
        </CardHeader>
        <CardContent>
          {backups.length === 0 ? (
            <div className="flex flex-col items-center justify-center py-12 text-center">
              <Database className="h-12 w-12 text-muted-foreground/50 mb-4" />
              <h3 className="text-lg font-medium mb-1">{__('No backups yet', 'wp-statistics')}</h3>
              <p className="text-sm text-muted-foreground mb-4">
                {__('Create your first backup to preserve your statistics data.', 'wp-statistics')}
              </p>
              <Button onClick={createBackup} disabled={isCreating}>
                {isCreating ? <Loader2 className="mr-2 h-4 w-4 animate-spin" /> : <Plus className="mr-2 h-4 w-4" />}
                {__('Create Backup', 'wp-statistics')}
              </Button>
            </div>
          ) : (
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>{__('Backup', 'wp-statistics')}</TableHead>
                  <TableHead>{__('Type', 'wp-statistics')}</TableHead>
                  <TableHead>{__('Size', 'wp-statistics')}</TableHead>
                  <TableHead>{__('Created', 'wp-statistics')}</TableHead>
                  <TableHead className="text-right">{__('Actions', 'wp-statistics')}</TableHead>
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
                        {backup.type === 'archive_backup' ? __('Automatic', 'wp-statistics') : __('Manual', 'wp-statistics')}
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
                          title={__('Download backup', 'wp-statistics')}
                        >
                          <Download className="h-4 w-4" />
                        </Button>
                        <Button
                          variant="ghost"
                          size="sm"
                          onClick={() => setRestoreTarget(backup.name)}
                          disabled={isRestoring === backup.name}
                          title={__('Restore backup', 'wp-statistics')}
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
                          title={__('Delete backup', 'wp-statistics')}
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
              {__('Delete Backup', 'wp-statistics')}
            </DialogTitle>
            <DialogDescription>
              {__('Are you sure you want to delete this backup? This action cannot be undone.', 'wp-statistics')}
              <br />
              <span className="font-medium">{deleteTarget}</span>
            </DialogDescription>
          </DialogHeader>
          <DialogFooter>
            <Button variant="outline" onClick={() => setDeleteTarget(null)}>
              {__('Cancel', 'wp-statistics')}
            </Button>
            <Button variant="destructive" onClick={deleteBackup}>
              {__('Delete', 'wp-statistics')}
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
              {__('Restore Backup', 'wp-statistics')}
            </DialogTitle>
            <DialogDescription>
              {__('This will restore the data from this backup. Existing data with the same IDs may be updated.', 'wp-statistics')}
              <br />
              <span className="font-medium">{restoreTarget}</span>
            </DialogDescription>
          </DialogHeader>
          <DialogFooter>
            <Button variant="outline" onClick={() => setRestoreTarget(null)}>
              {__('Cancel', 'wp-statistics')}
            </Button>
            <Button onClick={restoreBackup}>{__('Restore', 'wp-statistics')}</Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </div>
  )
}
