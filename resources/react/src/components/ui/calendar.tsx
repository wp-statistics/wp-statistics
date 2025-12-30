import { ChevronDownIcon, ChevronLeftIcon, ChevronRightIcon } from 'lucide-react'
import * as React from 'react'
import { DayButton, DayPicker } from 'react-day-picker'

import { Button, buttonVariants } from '@/components/ui/button'
import { cn } from '@/lib/utils'

const calendarResetStyles = `
  #wps-calendar {
    --rdp-cell-size: var(--cell-size, 2.25rem);
  }
  #wps-calendar table {
    border-collapse: collapse !important;
    border-spacing: 0 !important;
    border: none !important;
    margin: 0 !important;
    padding: 0 !important;
    width: auto !important;
    table-layout: fixed !important;
  }
  #wps-calendar th,
  #wps-calendar td {
    border: none !important;
    padding: 0 !important;
    margin: 0 !important;
    background: transparent !important;
    text-align: center !important;
    vertical-align: middle !important;
    width: var(--rdp-cell-size) !important;
    min-width: var(--rdp-cell-size) !important;
    height: var(--rdp-cell-size) !important;
  }
  #wps-calendar thead,
  #wps-calendar tbody,
  #wps-calendar tr {
    border: none !important;
    background: transparent !important;
  }
  #wps-calendar .rdp-weekday {
    width: var(--rdp-cell-size) !important;
    min-width: var(--rdp-cell-size) !important;
    height: var(--rdp-cell-size) !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    font-size: 0.6875rem !important;
    font-weight: 500 !important;
    color: hsl(var(--muted-foreground)) !important;
  }
  #wps-calendar .rdp-day {
    width: var(--rdp-cell-size) !important;
    min-width: var(--rdp-cell-size) !important;
    height: var(--rdp-cell-size) !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    padding: 0 !important;
  }
  #wps-calendar .rdp-weekdays {
    display: flex !important;
    width: 100% !important;
  }
  #wps-calendar .rdp-week {
    display: flex !important;
    margin-top: 0.375rem !important;
  }
  #wps-calendar .rdp-day_button {
    width: 100% !important;
    height: 100% !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    border-radius: 0.375rem !important;
    font-size: 0.75rem !important;
    font-weight: 500 !important;
  }
  #wps-calendar button {
    all: unset;
    box-sizing: border-box;
    cursor: pointer;
  }
`

function Calendar({
  className,
  classNames,
  showOutsideDays = true,
  captionLayout = 'label',
  buttonVariant = 'ghost',
  formatters,
  components,
  cellSize = '2.25rem',
  ...props
}: React.ComponentProps<typeof DayPicker> & {
  buttonVariant?: React.ComponentProps<typeof Button>['variant']
  cellSize?: string
}) {
  return (
    <div id="wps-calendar" style={{ '--cell-size': cellSize } as React.CSSProperties}>
      <style dangerouslySetInnerHTML={{ __html: calendarResetStyles }} />
      <DayPicker
      showOutsideDays={showOutsideDays}
      className={cn(
        'bg-background group/calendar p-3',
        className
      )}
      captionLayout={captionLayout}
      formatters={{
        formatMonthDropdown: (date) => date.toLocaleString('default', { month: 'short' }),
        ...formatters,
      }}
      classNames={{
        root: 'w-fit',
        months: 'relative flex flex-col gap-3 md:flex-row',
        month: 'flex w-full flex-col gap-3',
        nav: 'absolute inset-x-0 top-0 flex w-full items-center justify-between gap-1',
        button_previous: cn(
          buttonVariants({ variant: buttonVariant }),
          'size-[--cell-size] select-none p-0 aria-disabled:opacity-50'
        ),
        button_next: cn(
          buttonVariants({ variant: buttonVariant }),
          'size-[--cell-size] select-none p-0 aria-disabled:opacity-50'
        ),
        month_caption: 'flex h-[--cell-size] w-full items-center justify-center px-[--cell-size]',
        dropdowns: 'flex h-[--cell-size] w-full items-center justify-center gap-1.5 text-xs font-medium',
        dropdown_root: 'has-focus:border-ring border-input shadow-xs has-focus:ring-ring/50 has-focus:ring-[3px] relative rounded-md border',
        dropdown: 'bg-popover absolute inset-0 opacity-0',
        caption_label: cn(
          'select-none font-medium',
          captionLayout === 'label'
            ? 'text-xs'
            : '[&>svg]:text-muted-foreground flex h-7 items-center gap-1 rounded-md pl-2 pr-1 text-xs [&>svg]:size-3'
        ),
        table: 'w-full border-collapse',
        weekdays: 'flex w-full',
        weekday: 'text-muted-foreground size-[--cell-size] flex items-center justify-center select-none text-[11px] font-medium',
        week: 'mt-1.5 flex w-full',
        week_number_header: 'size-[--cell-size] select-none',
        week_number: 'text-muted-foreground select-none text-[11px]',
        day: 'group/day relative size-[--cell-size] select-none p-0 text-center',
        range_start: 'bg-accent rounded-l-md',
        range_middle: 'rounded-none',
        range_end: 'bg-accent rounded-r-md',
        today: 'bg-accent text-accent-foreground rounded-md data-[selected=true]:rounded-none',
        outside: 'text-muted-foreground aria-selected:text-muted-foreground',
        disabled: 'text-muted-foreground opacity-50',
        hidden: 'invisible',
        ...classNames,
      }}
      components={{
        Root: ({ className, rootRef, ...props }) => {
          return <div data-slot="calendar" ref={rootRef} className={cn(className)} {...props} />
        },
        Chevron: ({ className, orientation, ...props }) => {
          if (orientation === 'left') {
            return <ChevronLeftIcon className={cn('size-3.5', className)} {...props} />
          }

          if (orientation === 'right') {
            return <ChevronRightIcon className={cn('size-3.5', className)} {...props} />
          }

          return <ChevronDownIcon className={cn('size-3.5', className)} {...props} />
        },
        DayButton: CalendarDayButton,
        WeekNumber: ({ children, ...props }) => {
          return (
            <td {...props}>
              <div className="flex size-[--cell-size] items-center justify-center text-center">{children}</div>
            </td>
          )
        },
        ...components,
      }}
      {...props}
    />
    </div>
  )
}

function CalendarDayButton({ className, day, modifiers, ...props }: React.ComponentProps<typeof DayButton>) {
  const ref = React.useRef<HTMLButtonElement>(null)
  React.useEffect(() => {
    if (modifiers.focused) ref.current?.focus()
  }, [modifiers.focused])

  return (
    <Button
      ref={ref}
      variant="ghost"
      size="icon"
      data-day={day.date.toLocaleDateString()}
      data-selected-single={
        modifiers.selected && !modifiers.range_start && !modifiers.range_end && !modifiers.range_middle
      }
      data-range-start={modifiers.range_start}
      data-range-end={modifiers.range_end}
      data-range-middle={modifiers.range_middle}
      className={cn(
        'size-[--cell-size] data-[selected-single=true]:bg-primary data-[selected-single=true]:text-primary-foreground data-[range-middle=true]:bg-accent data-[range-middle=true]:text-accent-foreground data-[range-start=true]:bg-primary data-[range-start=true]:text-primary-foreground data-[range-end=true]:bg-primary data-[range-end=true]:text-primary-foreground flex items-center justify-center font-normal leading-none data-[range-end=true]:rounded-md data-[range-middle=true]:rounded-none data-[range-start=true]:rounded-md',
        className
      )}
      {...props}
    />
  )
}

export { Calendar, CalendarDayButton }
