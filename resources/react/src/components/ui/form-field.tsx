import * as React from 'react'

import { Label } from '@/components/ui/label'
import { cn } from '@/lib/utils'

interface FormFieldProps {
  /** Field label text */
  label: string
  /** HTML id for the input — connects label via htmlFor */
  htmlFor?: string
  /** Description text shown below the input */
  description?: string
  /** Error message — replaces description when present */
  error?: string
  /** Whether the field is required (shows red asterisk) */
  required?: boolean
  /** Extra content after the label (e.g. "(optional)" badge) */
  labelSuffix?: React.ReactNode
  /** Additional className on the outer wrapper */
  className?: string
  children: React.ReactNode
}

export function FormField({
  label,
  htmlFor,
  description,
  error,
  required,
  labelSuffix,
  className,
  children,
}: FormFieldProps) {
  return (
    <div className={cn('flex flex-col', className)}>
      <Label htmlFor={htmlFor} className="mb-1.5">
        {label}
        {required && <span className="text-destructive"> *</span>}
        {labelSuffix}
      </Label>
      {children}
      {error ? (
        <p className="mt-1 text-xs text-destructive">{error}</p>
      ) : description ? (
        <p className="mt-1 text-[11px] leading-tight text-neutral-400">{description}</p>
      ) : null}
    </div>
  )
}
