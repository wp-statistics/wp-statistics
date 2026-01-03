import * as React from 'react'
import { arrayMove } from '@dnd-kit/sortable'
import { Loader2, Mail, Save, X } from 'lucide-react'

import { Button } from '@/components/ui/button'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs'
import { useToast } from '@/hooks/use-toast'

import { blockDefinitions } from './block-definitions'
import { BlockList } from './block-list'
import { Canvas } from './canvas'
import { Preview } from './preview'
import type { EmailBlock, EmailBlockType, EmailTemplate } from './types'

interface EmailBuilderDialogProps {
  open: boolean
  onOpenChange: (open: boolean) => void
  initialTemplate?: EmailTemplate
  onSave?: (template: EmailTemplate) => void
}

const defaultTemplate: EmailTemplate = {
  blocks: [
    { id: 'default-header', type: 'header', settings: blockDefinitions.header.defaultSettings },
    { id: 'default-metrics', type: 'metrics', settings: blockDefinitions.metrics.defaultSettings },
    { id: 'default-pages', type: 'top-pages', settings: blockDefinitions['top-pages'].defaultSettings },
    { id: 'default-cta', type: 'cta', settings: blockDefinitions.cta.defaultSettings },
  ],
  globalSettings: {
    primaryColor: '#404BF2',
    showLogo: true,
  },
}

export function EmailBuilderDialog({
  open,
  onOpenChange,
  initialTemplate = defaultTemplate,
  onSave,
}: EmailBuilderDialogProps) {
  const [blocks, setBlocks] = React.useState<EmailBlock[]>(initialTemplate.blocks)
  const [globalSettings, setGlobalSettings] = React.useState(initialTemplate.globalSettings)
  const [previewHtml, setPreviewHtml] = React.useState<string | null>(null)
  const [isLoadingPreview, setIsLoadingPreview] = React.useState(false)
  const [isSaving, setIsSaving] = React.useState(false)
  const [isSendingTest, setIsSendingTest] = React.useState(false)
  const { toast } = useToast()

  // Single-instance blocks that can only appear once
  const singleInstanceBlocks: EmailBlockType[] = ['header', 'metrics', 'cta', 'promo']
  const disabledBlocks = singleInstanceBlocks.filter((type) =>
    blocks.some((block) => block.type === type)
  )

  const generateId = () => `block-${Date.now()}-${Math.random().toString(36).substring(2, 9)}`

  const handleAddBlock = (type: EmailBlockType) => {
    const definition = blockDefinitions[type]
    const newBlock: EmailBlock = {
      id: generateId(),
      type,
      settings: { ...definition.defaultSettings },
    }
    setBlocks((prev) => [...prev, newBlock])
  }

  const handleRemoveBlock = (id: string) => {
    setBlocks((prev) => prev.filter((block) => block.id !== id))
  }

  const handleReorder = (activeId: string, overId: string) => {
    setBlocks((prev) => {
      const oldIndex = prev.findIndex((block) => block.id === activeId)
      const newIndex = prev.findIndex((block) => block.id === overId)
      return arrayMove(prev, oldIndex, newIndex)
    })
  }

  const fetchPreview = React.useCallback(async () => {
    setIsLoadingPreview(true)
    try {
      const formData = new FormData()
      formData.append('action', 'wp_statistics_email_preview')
      formData.append('_wpnonce', (window as any).wps_react?.globals?.nonce || '')
      formData.append(
        'template',
        JSON.stringify({ blocks, globalSettings })
      )

      const response = await fetch((window as any).wps_react?.globals?.ajaxUrl || '/wp-admin/admin-ajax.php', {
        method: 'POST',
        body: formData,
      })

      const data = await response.json()
      if (data.success) {
        setPreviewHtml(data.data.html)
      } else {
        console.error('Preview error:', data.data?.message)
      }
    } catch (error) {
      console.error('Preview fetch error:', error)
    } finally {
      setIsLoadingPreview(false)
    }
  }, [blocks, globalSettings])

  const handleSave = async () => {
    setIsSaving(true)
    try {
      const formData = new FormData()
      formData.append('action', 'wp_statistics_email_save_template')
      formData.append('_wpnonce', (window as any).wps_react?.globals?.nonce || '')
      formData.append(
        'template',
        JSON.stringify({ blocks, globalSettings })
      )

      const response = await fetch((window as any).wps_react?.globals?.ajaxUrl || '/wp-admin/admin-ajax.php', {
        method: 'POST',
        body: formData,
      })

      const data = await response.json()
      if (data.success) {
        toast({
          title: 'Template saved',
          description: 'Your email template has been saved successfully.',
        })
        onSave?.({ blocks, globalSettings })
        onOpenChange(false)
      } else {
        toast({
          title: 'Error',
          description: data.data?.message || 'Failed to save template.',
          variant: 'destructive',
        })
      }
    } catch (error) {
      toast({
        title: 'Error',
        description: 'An error occurred while saving the template.',
        variant: 'destructive',
      })
    } finally {
      setIsSaving(false)
    }
  }

  const handleSendTest = async () => {
    setIsSendingTest(true)
    try {
      const formData = new FormData()
      formData.append('action', 'wp_statistics_email_send_test')
      formData.append('_wpnonce', (window as any).wps_react?.globals?.nonce || '')
      formData.append(
        'template',
        JSON.stringify({ blocks, globalSettings })
      )

      const response = await fetch((window as any).wps_react?.globals?.ajaxUrl || '/wp-admin/admin-ajax.php', {
        method: 'POST',
        body: formData,
      })

      const data = await response.json()
      if (data.success) {
        toast({
          title: 'Test email sent',
          description: `A test email has been sent to ${data.data.email}.`,
        })
      } else {
        toast({
          title: 'Error',
          description: data.data?.message || 'Failed to send test email.',
          variant: 'destructive',
        })
      }
    } catch (error) {
      toast({
        title: 'Error',
        description: 'An error occurred while sending the test email.',
        variant: 'destructive',
      })
    } finally {
      setIsSendingTest(false)
    }
  }

  // Load preview when dialog opens
  React.useEffect(() => {
    if (open && blocks.length > 0) {
      fetchPreview()
    }
  }, [open]) // eslint-disable-line react-hooks/exhaustive-deps

  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogContent className="flex h-[90vh] max-h-[900px] max-w-[1200px] flex-col gap-0 p-0">
        <DialogHeader className="border-b px-6 py-4">
          <div className="flex items-center justify-between">
            <div>
              <DialogTitle>Email Template Builder</DialogTitle>
              <DialogDescription>
                Drag and drop blocks to customize your email report.
              </DialogDescription>
            </div>
            <div className="flex items-center gap-2">
              <Button
                variant="outline"
                size="sm"
                onClick={handleSendTest}
                disabled={isSendingTest || blocks.length === 0}
              >
                {isSendingTest ? (
                  <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                ) : (
                  <Mail className="mr-2 h-4 w-4" />
                )}
                Send Test
              </Button>
              <Button size="sm" onClick={handleSave} disabled={isSaving}>
                {isSaving ? (
                  <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                ) : (
                  <Save className="mr-2 h-4 w-4" />
                )}
                Save Template
              </Button>
            </div>
          </div>
        </DialogHeader>

        <div className="flex flex-1 overflow-hidden">
          {/* Left sidebar - Block list */}
          <div className="w-64 border-r bg-muted/30">
            <BlockList onAddBlock={handleAddBlock} disabledBlocks={disabledBlocks} />
          </div>

          {/* Center - Canvas */}
          <div className="flex-1 overflow-auto p-4">
            <div className="mx-auto max-w-lg">
              <h3 className="mb-3 text-sm font-medium">Template Blocks</h3>
              <Canvas
                blocks={blocks}
                onReorder={handleReorder}
                onRemove={handleRemoveBlock}
              />
            </div>
          </div>

          {/* Right - Preview */}
          <div className="w-[400px] border-l bg-muted/30">
            <Tabs defaultValue="preview" className="flex h-full flex-col">
              <TabsList className="m-2 grid w-auto grid-cols-2">
                <TabsTrigger value="preview">Preview</TabsTrigger>
                <TabsTrigger value="settings">Settings</TabsTrigger>
              </TabsList>
              <TabsContent value="preview" className="flex-1 overflow-hidden">
                <Preview
                  html={previewHtml}
                  isLoading={isLoadingPreview}
                  onRefresh={fetchPreview}
                />
              </TabsContent>
              <TabsContent value="settings" className="flex-1 p-4">
                <div className="space-y-4">
                  <div>
                    <label className="text-sm font-medium">Primary Color</label>
                    <div className="mt-1.5 flex items-center gap-2">
                      <input
                        type="color"
                        value={globalSettings.primaryColor}
                        onChange={(e) =>
                          setGlobalSettings((prev) => ({
                            ...prev,
                            primaryColor: e.target.value,
                          }))
                        }
                        className="h-8 w-8 cursor-pointer rounded border"
                      />
                      <span className="text-sm text-muted-foreground">
                        {globalSettings.primaryColor}
                      </span>
                    </div>
                  </div>
                  <div className="flex items-center justify-between">
                    <label className="text-sm font-medium">Show Logo</label>
                    <input
                      type="checkbox"
                      checked={globalSettings.showLogo}
                      onChange={(e) =>
                        setGlobalSettings((prev) => ({
                          ...prev,
                          showLogo: e.target.checked,
                        }))
                      }
                      className="h-4 w-4"
                    />
                  </div>
                </div>
              </TabsContent>
            </Tabs>
          </div>
        </div>
      </DialogContent>
    </Dialog>
  )
}
