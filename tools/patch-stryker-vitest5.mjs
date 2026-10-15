/**
 * Temporary Stryker 10.0.0 compatibility for Vitest 5's ` > ` test-name separator.
 * Remove this script and its npm hook when the official runner supports Vitest 5 filtering.
 */
import { readFile, writeFile } from 'node:fs/promises';
import { createRequire } from 'node:module';
import { dirname, join } from 'node:path';

const require = createRequire(import.meta.url);
const runnerPackageFile =
    require.resolve('@stryker-mutator/vitest-runner/package.json');
const runner = JSON.parse(await readFile(runnerPackageFile, 'utf8'));
const vitest = JSON.parse(
    await readFile(require.resolve('vitest/package.json'), 'utf8'),
);

if (runner.version !== '10.0.0' || !/^5\./.test(vitest.version)) {
    throw new Error(
        `Review the temporary Stryker patch: expected runner 10.0.0 and Vitest 5.x, got ${runner.version} and ${vitest.version}.`,
    );
}

const original = "return nameParts.join(' ').trim();";
const replacement = "return nameParts.join(' > ').trim();";
const patches = await Promise.all(
    ['dist/src/test-helpers.js', 'dist/src/stryker-setup.js'].map(
        async (relativePath) => {
            const path = join(dirname(runnerPackageFile), relativePath);
            const source = await readFile(path, 'utf8');
            const originalCount = source.split(original).length - 1;
            const replacementCount = source.split(replacement).length - 1;

            if (originalCount === 1 && replacementCount === 0) {
                return {
                    path,
                    source,
                    content: source.replace(original, replacement),
                };
            }

            if (originalCount === 0 && replacementCount === 1) {
                return { path, source, content: source };
            }

            throw new Error(
                `Review the temporary Stryker patch: unexpected separator content in ${relativePath}.`,
            );
        },
    ),
);

let changed = 0;

for (const { path, source, content } of patches) {
    if (source !== content) {
        await writeFile(path, content);
        changed++;
    }
}

console.log(
    `Stryker 10 / Vitest 5 compatibility: patched ${changed} helper file(s).`,
);
