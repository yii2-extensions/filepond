---
name: yii2-filepond-uploads
description: Use when adding or changing FilePond upload inputs, image editing, or encoded upload validation and storage in a Yii2 application using yii2-extensions/filepond.
license: BSD-3-Clause
metadata:
  guide: https://yii2-framework.github.io/docs/extensions/filepond/
---

# Compose FilePond uploads in Yii2

Use `yii2\extensions\filepond` for the widget and its server helpers. Match the application's installed package revision
and form conventions. The package supports Yii2 `2.0.55` and `22.x`, and requires PHP 8.3 with `fileinfo`. Adding this
skill does not install the PHP or frontend dependencies.

## Read the relevant guide

In a root package checkout, use `composer show --self --format=json` for metadata. For installed dependencies, resolve
the real path and read the configured vendor directory's `composer/installed.php` (`versions[<package>]`):
`install_path`, `pretty_version` and Composer's selected `reference`. Do not blindly prefer `source.reference` over
`dist.reference`. For any live package checkout, including a symlinked path install, inspect its current `HEAD` and
files even when clean; report metadata mismatches and let the actual files govern. Uncommitted changes have no matching
SHA. Otherwise use the selected reference or a verified matching tag only after establishing its source provenance.
Compare `composer.lock`; it may differ from the inspected installation. Identify a lock-only state when no package files
are installed, and resolve dependencies separately. Use hosted pages, catalogs or snapshots only when their source
revision matches.

Prefer matching supplied Markdown or checkout `docs/`. Otherwise fetch the pages below after checking their revision, or
the corresponding files at the resolved reference in <https://github.com/yii2-extensions/filepond>. If guides are
unavailable or offline, inspect local source/PHPDoc and the changelog, and report the gap.

- Setup and asset resolution: <https://yii2-framework.github.io/docs/extensions/filepond/installation/index.md>.
- Option precedence, plugins, and editor:
  <https://yii2-framework.github.io/docs/extensions/filepond/configuration/index.md>.
- Request loading, validation, cropping, and storage:
  <https://yii2-framework.github.io/docs/extensions/filepond/uploads/index.md>.
- Widget recipes and individual helper demos:
  <https://yii2-framework.github.io/docs/extensions/filepond/examples/index.md>.

Read only the pages relevant to the requested change. For an exact property, enum mapping, helper signature, or
exception, inspect the relevant type/member and its PHPDoc in the resolved package files. Use
<https://yii2-framework.github.io/docs/llms.txt> only when the needed page is unknown. Do not infer a signature from
another FilePond wrapper or copy a full API catalogue into the task.

## Install consumer assets

Use the [installation guide](https://yii2-framework.github.io/docs/extensions/filepond/installation/index.md) for Foxy's
native PHP setup, requirements, and lockfiles. The application must require and authorize Foxy and select its own
manager; the extension's manager setting does not propagate to consumers.

Native mode serves this extension's prebuilt assets without Node.js, but cannot run lifecycle scripts or provide
executable links. Preserve a JavaScript manager when the application's build needs those features. Native mode owns its
install directory; do not share it with another installer. Verify `@npm` against the actual install path in both Yii2
versions; `@app/node_modules` assumes `basePath` is the Composer root and default frontend paths. Keep npm for FilePond
package development with Vitest, Stryker, and esbuild.

## Choose the widget and effective options

- Use `FilePond::widget(['name' => ...])` for a plain input, or the application's active field with `FilePond::class`.
  Let the active field derive the model input name; multiple inputs add the `[]` suffix.
- Effective options merge translated labels, typed properties, then `config`. A `config` override also controls plugin
  registration. JavaScript callbacks use `yii\web\JsExpression`.
- `asset\Plugin::option()` maps the supported plugin flags; `allowMultiple` is a core option, not a plugin.
  `allowImageEdit` loads Cropper.js and its adapter; configure `cropper` only while that option is effectively enabled.
- For an editor that opens when adding an image, use `config['imageEditInstantEdit']`. Decide whether the saved image
  uses client `allowImageTransform` or server `file\ImageCropper`; a preview alone does not transform uploaded bytes.
- Preserve application bundle overrides; setting widget `cdn` does not replace an already configured delivery mode. CDN
  delivery still reads the installed package manifests under `@npm` to pin versions.

## Receive, validate, and save

The normal form workflow keeps File Encode enabled and submits base64 JSON in the model attribute. Use `Model::load()`
on POST, then `validator\EncodedFileValidator`, then `file\EncodedFile::fromInput()` and `file\FileSaver`. Save only
after successful server validation. These encoded fields are not PHP `UploadedFile` instances. Match client size/count
feedback with server limits and reject malformed input.

MIME validation detects decoded bytes with `finfo`; client `type` and client extension are not proof of content. Use
explicit allowed MIME types, especially raster types for publicly served images. Account for base64 overhead in the
whole request limit; retain decoded size/count limits in the model.

When cropping on the server, disable client Image Transform, validate the original, then use the editor's
`metadata.crop.rect`. `ImageCropper` requires `gd` and may return the original when no rectangle exists. Do not reapply
an original crop rectangle to bytes already transformed by the browser. Validate any application requirement on the
resulting dimensions, type, or size before saving.

`FileSaver` returns a filesystem path and derives its extension from detected content. Choose a server-controlled base
name and handle write failures. Keep private files outside the web root; associate the generated identifier with the
authorized application record before reporting success. An existing `type: 'local'` item submits its `source` identifier
rather than new encoded bytes. For optional replacement, resolve the current identifier from the authorized record and
normalize only its exact match to an empty upload before encoded validation. Validate all other values normally; do not
ignore arbitrary non-JSON input. Missing upload input is not a deletion instruction.

## Verify the requested behavior

Check effective options and plugin selection, not just the rendered preview. Verify an empty/invalid submission writes
nothing and a valid request reaches the expected storage path. For crop changes, inspect saved image dimensions
separately for client and server modes. Run the application's applicable checks and report skipped checks or missing
dependencies. Preserve the project's access rules and CSRF handling while integrating the form.
