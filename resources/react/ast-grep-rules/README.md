# ast-grep Rules for WP Statistics React UI

This directory contains ast-grep rules for automated code quality checks.

## Available Rules

| Rule | Severity | Description |
|------|----------|-------------|
| `no-explicit-any.yml` | warning | Detects explicit `any` type usage |
| `no-console-log.yml` | warning | Detects `console.log` statements |
| `require-aria-label-button.yml` | hint | Suggests aria-labels for buttons |
| `prefer-design-tokens.yml` | hint | Suggests using design tokens for colors |

## Running Rules

### Run all rules

```bash
cd resources/react
ast-grep scan --rule ast-grep-rules resources/react/src
```

### Run a specific rule

```bash
ast-grep scan --rule ast-grep-rules/no-explicit-any.yml resources/react/src
```

### Run with JSON output (for CI)

```bash
ast-grep scan --rule ast-grep-rules --json resources/react/src
```

## Adding to CI Pipeline

Add to your CI workflow:

```yaml
- name: Install ast-grep
  run: npm install -g @ast-grep/cli

- name: Run ast-grep checks
  run: |
    cd resources/react
    ast-grep scan --rule ast-grep-rules src --json > ast-grep-results.json
    # Fail if warnings found
    if [ -s ast-grep-results.json ]; then
      cat ast-grep-results.json
      exit 1
    fi
```

## Creating New Rules

See the [ast-grep documentation](https://ast-grep.github.io/) for rule syntax.

Basic rule structure:

```yaml
id: rule-name
language: tsx
severity: error|warning|hint
rule:
  pattern: 'code pattern'
message: |
  Error message explaining the issue
  and how to fix it.
```
