# Testing

This package provides a consistent set of [Composer](https://getcomposer.org/) scripts for local validation, plus
[npm](https://www.npmjs.com/) scripts for the JavaScript runtime and Cropper.js adapter.

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
