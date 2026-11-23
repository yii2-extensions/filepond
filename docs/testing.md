# Testing

This package provides a consistent set of [Composer](https://getcomposer.org/) scripts for local validation, plus
[npm](https://www.npmjs.com/) scripts for the JavaScript runtime and Cropper.js adapter.

These are package development checks. Keep the repository's Foxy manager set to `npm` and use its Node.js toolchain for
Vitest, Stryker, and esbuild. Applications consuming the published extension can install its ready-to-serve assets with
Foxy's native PHP manager; see [installation](installation.md). Native installation does not replace this development
toolchain.

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

CI runs actionlint, codespell, EditorConfig Checker, markdownlint-cli2, Prettier and yamllint through the shared quality
workflow. It uses the shared configuration with FilePond-specific spelling exceptions and EditorConfig Checker
exclusions for minified assets and the npm lockfile. The repository's formatting rules remain in `.editorconfig`.

To lint workflows locally, make actionlint and ShellCheck available on `PATH`:

```bash
composer actionlint
```

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

CI runs zizmor and Gitleaks through the shared security workflow. Gitleaks uses the default rules plus an allowlist for
the dummy credentials in the test fixture. The scan step is skipped if `GITLEAKS_LICENSE` is unavailable to the
workflow.

To audit workflow files locally with the same pedantic persona, make zizmor available on `PATH`:

```bash
composer zizmor
```

Zizmor needs GitHub API access for its online audits. If the tool runs offline, report that limit separately. To request
an offline audit explicitly:

```bash
composer zizmor -- --offline
```

Report hosted CI execution and its PHP/OS matrix separately from these local commands. Keep generated reports in ignored
`internal/`.

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

The Stryker Vitest runner supports Vitest 5's
[`>` test-name separator](https://vitest.dev/guide/migration/#testnamepattern-matches-the-joined-full-name).

## Minified assets

Regenerate the committed `*.min.js` and `*.min.css` files after editing `src/asset/widget` or `src/asset/cropper`. The
`assets` workflow fails when the committed files differ from a fresh build.

```bash
npm run build
```

[Back to documentation](../README.md#documentation)
