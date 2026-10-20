# Testing

This package provides a consistent set of [Composer](https://getcomposer.org/) scripts for local validation, plus
[npm](https://www.npmjs.com/) scripts for the JavaScript runtime and Cropper.js adapter.

These are package development checks. Keep the repository's Foxy manager set to `npm` and use its Node.js toolchain
for Vitest, Stryker, and esbuild. Applications consuming the published extension can install its ready-to-serve
assets with Foxy's native PHP manager; see [installation](installation.md). Native installation does not replace
this development toolchain.

Tool references:

- [Actionlint](https://github.com/rhysd/actionlint) for GitHub Actions workflow linting.
- [Codespell](https://github.com/codespell-project/codespell) for spelling checks.
- [Composer Require Checker](https://github.com/maglnet/ComposerRequireChecker) for dependency definition checks.
- [Easy Coding Standard (ECS)](https://github.com/easy-coding-standard/easy-coding-standard) for coding standards.
- [EditorConfig Checker](https://github.com/editorconfig-checker/editorconfig-checker) for EditorConfig compliance.
- [esbuild](https://esbuild.github.io/) for minified assets.
- [Gitleaks](https://github.com/gitleaks/gitleaks) for secret scanning.
- [Infection](https://infection.github.io/) for mutation testing.
- [Markdownlint-cli2](https://github.com/DavidAnson/markdownlint-cli2) for Markdown linting.
- [PHPStan](https://phpstan.org/) for static analysis.
- [PHPUnit](https://phpunit.de/) for unit tests.
- [Prettier](https://prettier.io/) for Markdown and YAML formatting checks.
- [Rector](https://github.com/rectorphp/rector) for automated refactoring.
- [StrykerJS](https://stryker-mutator.io/) for JavaScript mutation testing.
- [Vitest](https://vitest.dev/) with [jsdom](https://github.com/jsdom/jsdom) for JavaScript unit tests.
- [Yamllint](https://github.com/adrienverge/yamllint) for YAML linting.
- [Zizmor](https://github.com/zizmorcore/zizmor) for GitHub Actions security audits.

## Automated refactoring (Rector)

Run Rector to apply automated code refactoring. This command modifies files.

```bash
composer rector
```

Preview the refactoring without modifying files.

```bash
composer rector-check
```

## Coding standards (ECS)

Check coding standards without modifying files.

```bash
composer ecs-check
```

Apply coding standard fixes. Review the changes before keeping them.

```bash
composer ecs
```

## Dependency definition check

Verify that runtime dependencies are correctly declared in `composer.json`.

```bash
composer check-dependencies
```

## Mutation testing (Infection)

Run mutation testing.

```bash
composer mutation
```

Run mutation testing with PHPStan static analysis enabled.

```bash
composer mutation-static
```

Thresholds, thread count and memory options live in `infection.json5`. CI enforces the same thresholds.

## Repository quality checks

Make the CLI tools used by the shared quality workflow available on `PATH`:
actionlint with its ShellCheck helper, codespell, EditorConfig Checker, markdownlint-cli2, Prettier and
yamllint. Run all checks:

```bash
composer quality
```

Each tool also has its own script:

```bash
composer actionlint
composer codespell
composer editorconfig-checker
composer markdownlint
composer prettier
composer yamllint
```

The linter configuration lives in `.github/linters`, and the Prettier configuration in `.prettierrc.json` and
`.prettierignore`. CI uses the same files.

## Static analysis (PHPStan)

Run static analysis.

```bash
composer static
```

## Unit tests (PHPUnit)

Run the full test suite.

```bash
composer tests
```

## Workflow security checks

Make zizmor and Gitleaks available on `PATH`. Audit the current workflow files
with the same pedantic persona used by the shared security workflow:

```bash
composer zizmor
```

Scan Git history for secrets with the repository's Gitleaks configuration:

```bash
composer gitleaks
```

Run both checks. Findings make the command fail; inspect them rather than
disabling the checks.

```bash
composer security
```

Zizmor needs GitHub API access for its online audits. If the tool runs offline,
report that limit separately. To request an offline audit explicitly:

```bash
composer zizmor -- --offline
```

Report hosted CI execution and its PHP/OS matrix separately from these local
commands. Keep generated reports in ignored `internal/`.

## Passing extra arguments

Composer scripts support forwarding additional arguments using `--`.

Run PHPUnit with code coverage report generation.

```bash
composer tests -- --coverage-html internal/coverage
```

Run PHPStan with a different memory limit.

```bash
composer static -- --memory-limit=512M
```

## JavaScript tests (Vitest)

Install the Node.js toolchain (see `.nvmrc`) and run the tests in `tests/js` with 100% line, branch, and function
coverage thresholds.

```bash
npm ci
npm run test:js
```

Run mutation testing for the JavaScript sources (minimum score 100%).

```bash
npm run test:mutation
```

The `pretest:mutation` npm hook temporarily adapts Stryker 10.0.0's test-name filtering to Vitest 5's
[`>` separator](https://vitest.dev/guide/migration/#testnamepattern-matches-the-joined-full-name). It runs
automatically with the command above, including after a clean `npm ci`. The versioned patch checks the installed
runner version, Vitest major version, and both helper copies before changing anything; repeated runs are safe.
Unexpected versions or helper content stop the run so dependency upgrades cannot silently keep a stale patch.
Remove the hook and `tools/patch-stryker-vitest5.mjs` after upgrading to an official runner with verified Vitest 5
filtering support. The patch changes only the installed test runner; coverage and mutation thresholds stay at 100%.

## Minified assets

Regenerate the committed `*.min.js` and `*.min.css` files after editing `src/asset/widget` or `src/asset/cropper`.
The `assets` workflow fails when the committed files differ from a fresh build.

```bash
npm run build
```

[Back to documentation](../README.md#documentation)
