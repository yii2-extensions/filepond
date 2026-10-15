# Testing

This package provides a consistent set of [Composer](https://getcomposer.org/) scripts for local validation, plus
[npm](https://www.npmjs.com/) scripts for the JavaScript runtime and Cropper.js adapter.

These are package development checks. Keep the repository's Foxy manager set to `npm` and use its Node.js toolchain
for Vitest, Stryker, and esbuild. Applications consuming the published extension can install its ready-to-serve
assets with Foxy's native PHP manager; see [installation](installation.md). Native installation does not replace
this development toolchain.

Tool references:

- [Composer Require Checker](https://github.com/maglnet/ComposerRequireChecker) for dependency definition checks.
- [Easy Coding Standard (ECS)](https://github.com/easy-coding-standard/easy-coding-standard) for coding standards.
- [Infection](https://infection.github.io/) for mutation testing.
- [PHPStan](https://phpstan.org/) for static analysis.
- [PHPUnit](https://phpunit.de/) for unit tests.
- [Rector](https://github.com/rectorphp/rector) for automated refactoring.
- [Vitest](https://vitest.dev/) with [jsdom](https://github.com/jsdom/jsdom) for JavaScript unit tests.
- [StrykerJS](https://stryker-mutator.io/) for JavaScript mutation testing.
- [esbuild](https://esbuild.github.io/) for minified assets.

## Automated refactoring (Rector)

Run Rector to apply automated code refactoring.

```bash
composer rector
```

## Coding standards (ECS)

Run Easy Coding Standard (ECS) and apply fixes.

```bash
composer ecs
```

## Dependency definition check

Verify that runtime dependencies are correctly declared in `composer.json`.

```bash
composer check-dependencies
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

## Mutation testing (Infection)

Run mutation testing.

```bash
composer mutation
```

Run mutation testing with static analysis enabled.

```bash
composer mutation-static
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

## Passing extra arguments

Composer scripts support forwarding additional arguments using `--`.

Run PHPUnit with code coverage report generation.

```bash
composer tests -- --coverage-html code_coverage
```

Run PHPStan with a different memory limit.

```bash
composer static -- --memory-limit=512M
```
