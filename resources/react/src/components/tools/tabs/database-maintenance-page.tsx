import { __, sprintf } from '@wordpress/i18n'
import { Bot, Loader2, ShieldAlert, Trash2, Wrench } from 'lucide-react'
import * as React from 'react'

import { SettingsCard, SettingsActionField } from '@/components/settings-ui'
import { Button } from '@/components/ui/button'
import { ConfirmDialog } from '@/components/ui/confirm-dialog'
import { Input } from '@/components/ui/input'
import { NoticeBanner } from '@/components/ui/notice-banner'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Skeleton } from '@/components/ui/skeleton'
import { PanelSkeleton } from '@/components/ui/skeletons'
import { useToast } from '@/hooks/use-toast'
import { callToolsApi } from '@/services/tools'

interface MaintenanceInfo {
  hasUserIds: boolean
  eventsTableExists: boolean
  eventNames: string[]
}

type ConfirmActionType = 'removeUserIds' | 'deleteEvents' | 'deleteBotSessions' | null

export function DatabaseMaintenancePage() {
  const [maintenanceInfo, setMaintenanceInfo] = React.useState<MaintenanceInfo | null>(null)
  const [isLoading, setIsLoading] = React.useState(true)

  // Operation states
  const [isRemovingUserIds, setIsRemovingUserIds] = React.useState(false)
  const [isDeletingEvents, setIsDeletingEvents] = React.useState(false)
  const [isDeletingBotSessions, setIsDeletingBotSessions] = React.useState(false)
  // Form state
  const [selectedEventName, setSelectedEventName] = React.useState<string>('')
  const [viewThreshold, setViewThreshold] = React.useState<string>('100')

  // Confirmation dialog
  const [confirmAction, setConfirmAction] = React.useState<ConfirmActionType>(null)

  const { toast } = useToast()

  const fetchMaintenanceInfo = async () => {
    try {
      const data = await callToolsApi('maintenance_info')
      if (data.success) {
        setMaintenanceInfo(data.data as MaintenanceInfo)
      }
    } catch {
      toast({
        title: __('Error', 'wp-statistics'),
        description: __('Failed to load maintenance info.', 'wp-statistics'),
        variant: 'destructive',
      })
    } finally {
      setIsLoading(false)
    }
  }

  // eslint-disable-next-line react-hooks/exhaustive-deps -- fetch once on mount
  React.useEffect(() => {
    fetchMaintenanceInfo()
  }, [])

  // ── Handlers ──────────────────────────────────────────────────────────

  const handleRemoveUserIds = async () => {
    setConfirmAction(null)
    setIsRemovingUserIds(true)
    try {
      const data = await callToolsApi('remove_user_ids')
      if (data.success) {
        toast({
          title: __('User IDs removed', 'wp-statistics'),
          description: data.data?.message,
        })
        await fetchMaintenanceInfo()
      } else {
        toast({
          title: __('Error', 'wp-statistics'),
          description: data.data?.message || __('Failed to remove user IDs.', 'wp-statistics'),
          variant: 'destructive',
        })
      }
    } catch {
      toast({
        title: __('Error', 'wp-statistics'),
        description: __('Failed to remove user IDs.', 'wp-statistics'),
        variant: 'destructive',
      })
    } finally {
      setIsRemovingUserIds(false)
    }
  }

  const handleDeleteEvents = async () => {
    setConfirmAction(null)
    setIsDeletingEvents(true)
    try {
      const data = await callToolsApi('delete_events_by_name', { event_name: selectedEventName })
      if (data.success) {
        toast({
          title: __('Event data deleted', 'wp-statistics'),
          description: data.data?.message,
        })
        setSelectedEventName('')
        await fetchMaintenanceInfo()
      } else {
        toast({
          title: __('Error', 'wp-statistics'),
          description: data.data?.message || __('Failed to delete event data.', 'wp-statistics'),
          variant: 'destructive',
        })
      }
    } catch {
      toast({
        title: __('Error', 'wp-statistics'),
        description: __('Failed to delete event data.', 'wp-statistics'),
        variant: 'destructive',
      })
    } finally {
      setIsDeletingEvents(false)
    }
  }

  const handleDeleteBotSessions = async () => {
    setConfirmAction(null)
    setIsDeletingBotSessions(true)
    try {
      const data = await callToolsApi('delete_bot_sessions', { view_threshold: viewThreshold })
      if (data.success) {
        toast({
          title: __('Bot cleanup complete', 'wp-statistics'),
          description: data.data?.message,
        })
        await fetchMaintenanceInfo()
      } else {
        toast({
          title: __('Error', 'wp-statistics'),
          description: data.data?.message || __('Failed to clean up bot sessions.', 'wp-statistics'),
          variant: 'destructive',
        })
      }
    } catch {
      toast({
        title: __('Error', 'wp-statistics'),
        description: __('Failed to clean up bot sessions.', 'wp-statistics'),
        variant: 'destructive',
      })
    } finally {
      setIsDeletingBotSessions(false)
    }
  }

  // ── Confirm dialog config ─────────────────────────────────────────────

  const confirmConfig: Record<
    Exclude<ConfirmActionType, null>,
    { title: string; description: React.ReactNode; confirmLabel: string; handler: () => void }
  > = {
    removeUserIds: {
      title: __('Remove User IDs', 'wp-statistics'),
      description: __('This will permanently remove all user ID associations from session records. This action cannot be undone.', 'wp-statistics'),
      confirmLabel: __('Remove User IDs', 'wp-statistics'),
      handler: handleRemoveUserIds,
    },
    deleteEvents: {
      title: __('Delete Event Data', 'wp-statistics'),
      description: (
        <>
          {__('This will permanently delete all event records for:', 'wp-statistics')}
          <br />
          <span className="font-semibold">{selectedEventName}</span>
        </>
      ),
      confirmLabel: __('Delete Events', 'wp-statistics'),
      handler: handleDeleteEvents,
    },
    deleteBotSessions: {
      title: __('Clean Up Bot Sessions', 'wp-statistics'),
      description: sprintf(
        __('This will delete all sessions with more than %s views, along with their associated views, parameters, events, and orphaned visitors. This action cannot be undone.', 'wp-statistics'),
        viewThreshold
      ),
      confirmLabel: __('Delete Bot Sessions', 'wp-statistics'),
      handler: handleDeleteBotSessions,
    },
  }

  // ── Loading skeleton ──────────────────────────────────────────────────

  if (isLoading) {
    return (
      <div className="space-y-6">
        <Skeleton className="h-16 w-full rounded-lg" />
        {[...Array(3)].map((_, i) => (
          <PanelSkeleton key={i} titleWidth="w-48">
            <Skeleton className="h-14 w-full" />
          </PanelSkeleton>
        ))}
      </div>
    )
  }

  const parsedThreshold = parseInt(viewThreshold, 10)
  const isThresholdValid = !isNaN(parsedThreshold) && parsedThreshold >= 10

  // ── Render ────────────────────────────────────────────────────────────

  return (
    <div className="space-y-6">
      <NoticeBanner
        title={__('Database Maintenance', 'wp-statistics')}
        message={__('Use these tools to clean up data, manage privacy compliance, and maintain database health. All actions are irreversible — proceed with caution.', 'wp-statistics')}
        type="neutral"
        icon={Wrench}
        dismissible={false}
      />

      {/* Card 1: Remove User IDs */}
      <SettingsCard
        title={__('Remove User IDs', 'wp-statistics')}
        description={__('Anonymize session records by removing WordPress user ID associations.', 'wp-statistics')}
        icon={ShieldAlert}
      >
        <SettingsActionField
          label={__('Remove User IDs from Sessions', 'wp-statistics')}
          description={__('Permanently removes all stored user IDs from session records to anonymize visit data. Use this for GDPR compliance or to decouple analytics from user accounts.', 'wp-statistics')}
        >
          <Button
            variant="destructive"
            size="sm"
            onClick={() => setConfirmAction('removeUserIds')}
            disabled={isRemovingUserIds || !maintenanceInfo?.hasUserIds}
          >
            {isRemovingUserIds ? <Loader2 className="mr-2 h-4 w-4 animate-spin" /> : <ShieldAlert className="mr-2 h-4 w-4" />}
            {__('Remove User IDs', 'wp-statistics')}
          </Button>
        </SettingsActionField>
        {maintenanceInfo && !maintenanceInfo.hasUserIds && (
          <p className="text-sm text-muted-foreground">
            {__('No user IDs found in session records.', 'wp-statistics')}
          </p>
        )}
      </SettingsCard>

      {/* Card 2: Delete Event Data (premium only) */}
      {maintenanceInfo?.eventsTableExists && (
        <SettingsCard
          title={__('Delete Event Data', 'wp-statistics')}
          description={__('Remove all records for a specific tracked event.', 'wp-statistics')}
          icon={Trash2}
        >
          {maintenanceInfo.eventNames.length > 0 ? (
            <SettingsActionField
              label={__('Select Event to Delete', 'wp-statistics')}
              description={__('All records for the selected event will be permanently deleted from the database.', 'wp-statistics')}
            >
              <div className="flex items-center gap-2">
                <Select value={selectedEventName} onValueChange={setSelectedEventName}>
                  <SelectTrigger className="w-[200px]">
                    <SelectValue placeholder={__('Select an event', 'wp-statistics')} />
                  </SelectTrigger>
                  <SelectContent>
                    {maintenanceInfo.eventNames.map((name) => (
                      <SelectItem key={name} value={name}>
                        {name}
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
                <Button
                  variant="destructive"
                  size="sm"
                  onClick={() => setConfirmAction('deleteEvents')}
                  disabled={isDeletingEvents || !selectedEventName}
                >
                  {isDeletingEvents ? <Loader2 className="mr-2 h-4 w-4 animate-spin" /> : <Trash2 className="mr-2 h-4 w-4" />}
                  {__('Delete Event Data', 'wp-statistics')}
                </Button>
              </div>
            </SettingsActionField>
          ) : (
            <p className="text-sm text-muted-foreground">
              {__('No event data found in the database.', 'wp-statistics')}
            </p>
          )}
        </SettingsCard>
      )}

      {/* Card 3: Bot Session Cleanup */}
      <SettingsCard
        title={__('Bot Session Cleanup', 'wp-statistics')}
        description={__('Remove sessions with unusually high view counts that likely indicate bot traffic.', 'wp-statistics')}
        icon={Bot}
      >
        <SettingsActionField
          label={__('View Threshold', 'wp-statistics')}
          description={__('Sessions with more views than this threshold will be treated as bot traffic. Their views, parameters, events, sessions, and any orphaned visitors will be permanently removed. Minimum threshold: 10.', 'wp-statistics')}
        >
          <div className="flex items-center gap-2">
            <Input
              type="number"
              min={10}
              value={viewThreshold}
              onChange={(e) => setViewThreshold(e.target.value)}
              placeholder="100"
              className="w-[100px]"
            />
            <Button
              variant="destructive"
              size="sm"
              onClick={() => setConfirmAction('deleteBotSessions')}
              disabled={isDeletingBotSessions || !isThresholdValid}
            >
              {isDeletingBotSessions ? <Loader2 className="mr-2 h-4 w-4 animate-spin" /> : <Bot className="mr-2 h-4 w-4" />}
              {__('Clean Up Bot Sessions', 'wp-statistics')}
            </Button>
          </div>
        </SettingsActionField>
      </SettingsCard>

      {/* Confirmation Dialog */}
      {confirmAction && (
        <ConfirmDialog
          open={!!confirmAction}
          onOpenChange={() => setConfirmAction(null)}
          title={confirmConfig[confirmAction].title}
          description={confirmConfig[confirmAction].description}
          confirmLabel={confirmConfig[confirmAction].confirmLabel}
          onConfirm={confirmConfig[confirmAction].handler}
          variant="destructive"
        />
      )}
    </div>
  )
}
