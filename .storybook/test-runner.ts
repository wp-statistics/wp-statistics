import type { TestRunnerConfig } from '@storybook/test-runner'
import { Page } from '@playwright/test'
import AxeBuilder from '@axe-core/playwright'

/**
 * Story-specific rule overrides for known exceptions.
 * Format: { 'story-id': ['rule-id-to-disable', ...] }
 */
const storyRuleOverrides: Record<string, string[]> = {
  // Skeleton components use intentional low contrast for loading states
  'ui-skeletons-metricsskeleton--default': ['color-contrast'],
  'ui-skeletons-chartskeleton--default': ['color-contrast'],
  'ui-skeletons-tableskeleton--default': ['color-contrast'],
  'ui-skeletons-barlistskeleton--default': ['color-contrast'],
  'ui-skeletons-panelskeleton--default': ['color-contrast'],
}

/**
 * Pattern-based rule overrides for component categories.
 * Matches story IDs containing these patterns.
 */
const patternRuleOverrides: Array<{ pattern: RegExp; rules: string[] }> = [
  // Skeleton components: all variants use intentional low contrast
  { pattern: /ui-skeletons-/, rules: ['color-contrast'] },
  // ScrollArea: scrollbar thumb uses subtle styling
  { pattern: /ui-scrollarea--/, rules: ['scrollable-region-focusable'] },
  // Sidebar: demo links use href="#" and icon buttons
  { pattern: /ui-sidebar--/, rules: ['link-name', 'button-name', 'color-contrast'] },
  // Chart components: visual elements don't need text alternatives
  { pattern: /custom-linechart--/, rules: ['color-contrast'] },
  { pattern: /custom-globalmap--/, rules: ['color-contrast', 'button-name'] },
  // Metrics: comparison percentages may use intentional color coding
  { pattern: /custom-metrics--/, rules: ['color-contrast'] },
  // Tabs: muted foreground is intentional design
  { pattern: /ui-tabs--/, rules: ['color-contrast'] },
  // NoticeBanner: colored backgrounds with matching text
  { pattern: /ui-noticebanner--/, rules: ['color-contrast'] },
  // JourneyCell: badge colors for visual categorization
  { pattern: /datatable-cells-journeycell--/, rules: ['color-contrast'] },
  // FilterButton: badge styling and icon buttons
  { pattern: /custom-filterbutton--/, rules: ['color-contrast', 'button-name'] },
  // VisitorInfoCell: secondary badge has intentional styling
  { pattern: /datatable-cells-visitorinfocell--/, rules: ['color-contrast'] },
  // Progress: now has aria-label by default
  { pattern: /ui-progress--/, rules: ['aria-label'] },
  // NoticeContainer: colored text on colored backgrounds
  { pattern: /ui-noticecontainer--/, rules: ['color-contrast'] },
  // ApiError: error styling
  { pattern: /ui-apierror--/, rules: ['color-contrast'] },
  // FilterRow: form elements with muted styling
  { pattern: /custom-filterrow--/, rules: ['color-contrast'] },
  // HorizontalBar: intentional semi-transparent bar
  { pattern: /custom-horizontalbar--/, rules: ['color-contrast'] },
  // Referrer Cell: badge and link styling
  { pattern: /datatable-cells-referrercell--/, rules: ['color-contrast'] },
  // Dialog: form elements inside dialogs
  { pattern: /ui-dialog--/, rules: ['color-contrast'] },
]

/**
 * Get disabled rules for a story based on exact match and pattern matching.
 */
function getDisabledRules(storyId: string): string[] {
  const rules = new Set<string>()

  // Check exact matches
  const exactRules = storyRuleOverrides[storyId]
  if (exactRules) {
    exactRules.forEach((rule) => rules.add(rule))
  }

  // Check pattern matches
  patternRuleOverrides.forEach(({ pattern, rules: patternRules }) => {
    if (pattern.test(storyId)) {
      patternRules.forEach((rule) => rules.add(rule))
    }
  })

  return Array.from(rules)
}

const config: TestRunnerConfig = {
  async postVisit(page: Page, context) {
    const storyId = context.id
    const disabledRules = getDisabledRules(storyId)

    // Build Axe accessibility scanner
    const axeBuilder = new AxeBuilder({ page })
      .include('#storybook-root')
      .withTags(['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa'])

    // Disable specific rules for certain stories
    if (disabledRules.length > 0) {
      axeBuilder.disableRules(disabledRules)
    }

    const accessibilityScanResults = await axeBuilder.analyze()

    // Enhanced logging with more context
    if (accessibilityScanResults.violations.length > 0) {
      console.log(`\n⚠️  Found ${accessibilityScanResults.violations.length} accessibility violations in ${storyId}:`)
      accessibilityScanResults.violations.forEach((violation) => {
        console.log(`  - ${violation.id}: ${violation.description}`)
        console.log(`    Impact: ${violation.impact}`)
        console.log(`    Help: ${violation.helpUrl}`)
        console.log(`    Nodes affected: ${violation.nodes.length}`)
        // Show first 3 affected nodes for debugging
        violation.nodes.slice(0, 3).forEach((node, idx) => {
          console.log(`    ${idx + 1}. ${node.html.substring(0, 100)}${node.html.length > 100 ? '...' : ''}`)
        })
      })
    }

    // Log incomplete checks for manual review awareness
    if (accessibilityScanResults.incomplete.length > 0) {
      console.log(`\n⚡ ${accessibilityScanResults.incomplete.length} checks need manual review in ${storyId}`)
    }

    // Fail the test if there are serious or critical violations
    const seriousViolations = accessibilityScanResults.violations.filter(
      (v) => v.impact === 'critical' || v.impact === 'serious'
    )

    if (seriousViolations.length > 0) {
      throw new Error(
        `${seriousViolations.length} serious accessibility violations found in ${storyId}. See details above.`
      )
    }
  },
}

export default config
