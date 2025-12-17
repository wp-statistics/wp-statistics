import type { TestRunnerConfig } from '@storybook/test-runner'
import { Page } from '@playwright/test'
import AxeBuilder from '@axe-core/playwright'

const config: TestRunnerConfig = {
  async postVisit(page: Page) {
    // Run accessibility checks after each story renders
    const accessibilityScanResults = await new AxeBuilder({ page })
      .include('#storybook-root')
      .withTags(['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa'])
      .analyze()

    // Log violations if any are found
    if (accessibilityScanResults.violations.length > 0) {
      console.log(`\n⚠️  Found ${accessibilityScanResults.violations.length} accessibility violations:`)
      accessibilityScanResults.violations.forEach((violation) => {
        console.log(`  - ${violation.id}: ${violation.description}`)
        console.log(`    Impact: ${violation.impact}`)
        console.log(`    Nodes: ${violation.nodes.length}`)
      })
    }

    // Fail the test if there are serious or critical violations
    const seriousViolations = accessibilityScanResults.violations.filter(
      (v) => v.impact === 'critical' || v.impact === 'serious'
    )

    if (seriousViolations.length > 0) {
      throw new Error(
        `${seriousViolations.length} serious accessibility violations found. See details above.`
      )
    }
  },
}

export default config
