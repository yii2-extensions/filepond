/**
 * Cropper.js editor adapter for the FilePond Image Edit plugin.
 *
 * Exposes `yii2FilePond.cropper.createEditor(settings)`, which returns an editor implementing the Image Edit plugin
 * contract (`open`, `onconfirm`, `oncancel`, `onclose`). The editor opens a native `<dialog>` with a Cropper.js 2
 * canvas and confirms a FilePond `crop` metadata object computed in source image pixels, so the Image Transform
 * plugin, the Image Preview plugin, and server-side cropping share one coordinate space.
 */
(function (window, document) {
    'use strict';

    const CLASS = 'yii2-filepond-cropper';
    const DEFAULT_ASPECT_RATIOS = ['free', '1:1', '16:9', '4:3', '3:2'];
    const DEFAULT_LABELS = {
        apply: 'Apply',
        cancel: 'Cancel',
        free: 'Free',
        reset: 'Reset',
        title: 'Edit image',
        zoomIn: 'Zoom in',
        zoomOut: 'Zoom out',
    };
    const ICONS = {
        reset:
            '<svg viewBox="0 0 24 24" aria-hidden="true"><polyline points="1 4 1 10 7 10"></polyline>'
            + '<path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"></path></svg>',
        zoomIn:
            '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"></circle>'
            + '<line x1="21" y1="21" x2="16.65" y2="16.65"></line><line x1="11" y1="8" x2="11" y2="14"></line>'
            + '<line x1="8" y1="11" x2="14" y2="11"></line></svg>',
        zoomOut:
            '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"></circle>'
            + '<line x1="21" y1="21" x2="16.65" y2="16.65"></line><line x1="8" y1="11" x2="14" y2="11"></line></svg>',
    };
    const RESIZE_HANDLES = ['n', 'e', 's', 'w', 'ne', 'nw', 'se', 'sw'];
    const ZOOM_STEP = 0.1;

    const clamp = (value, min, max) => Math.min(Math.max(value, min), max);

    /**
     * Parses an aspect ratio given as a positive number, a `width:height` string, or the `free` keyword.
     *
     * @returns {number|null} Width divided by height, or `null` for a free selection.
     */
    function parseAspectRatio(value) {
        if (typeof value === 'number') {
            return value > 0 && Number.isFinite(value) ? value : null;
        }

        if (typeof value !== 'string' || value.trim().toLowerCase() === 'free') {
            return null;
        }

        const parts = value.split(':').map(Number);

        if (parts.length === 2 && parts[0] > 0 && parts[1] > 0) {
            return parts[0] / parts[1];
        }

        const ratio = Number(value);

        return ratio > 0 && Number.isFinite(ratio) ? ratio : null;
    }

    function createTemplate(aspectRatio) {
        const ratio = aspectRatio === null ? '' : ` aspect-ratio="${aspectRatio}"`;
        const handles = RESIZE_HANDLES
            .map((direction) => `<cropper-handle action="${direction}-resize"></cropper-handle>`)
            .join('');

        return '<cropper-canvas background>'
            + '<cropper-image scalable translatable initial-fit="contain"></cropper-image>'
            + '<cropper-shade hidden></cropper-shade>'
            + '<cropper-handle action="select" plain></cropper-handle>'
            + `<cropper-selection initial-coverage="0.8" movable resizable precise${ratio}>`
            + '<cropper-grid role="grid" covered></cropper-grid>'
            + '<cropper-crosshair centered></cropper-crosshair>'
            + '<cropper-handle action="move" theme-color="rgba(255, 255, 255, 0.35)"></cropper-handle>'
            + handles
            + '</cropper-selection>'
            + '</cropper-canvas>';
    }

    function createElement(tag, className, text) {
        const element = document.createElement(tag);

        element.className = className;

        if (text !== undefined) {
            element.textContent = text;
        }

        return element;
    }

    function createButton(className, label, icon) {
        const button = createElement('button', className);

        button.type = 'button';

        if (icon === undefined) {
            button.textContent = label;
        } else {
            button.innerHTML = icon;
            button.title = label;
            button.setAttribute('aria-label', label);
        }

        return button;
    }

    /**
     * Converts a selection expressed in canvas pixels into source image pixels.
     *
     * Cropper.js applies the image transform around the image center, so the inverse maps canvas coordinates back to
     * natural pixels. Rotation and skew are disabled in the template, which keeps the result a rectangle.
     */
    function toSourceRectangle(selection, cropperImage) {
        const image = cropperImage.$image;
        const naturalWidth = image.naturalWidth;
        const naturalHeight = image.naturalHeight;
        const [a, , , d, e, f] = cropperImage.$getTransform();
        const centerX = naturalWidth / 2;
        const centerY = naturalHeight / 2;
        const x = clamp((selection.x - e - centerX) / a + centerX, 0, naturalWidth);
        const y = clamp((selection.y - f - centerY) / d + centerY, 0, naturalHeight);
        const width = clamp(selection.width / a, 0, naturalWidth - x);
        const height = clamp(selection.height / d, 0, naturalHeight - y);

        return {
            naturalHeight,
            naturalWidth,
            x: Math.round(x),
            y: Math.round(y),
            width: Math.round(width),
            height: Math.round(height),
        };
    }

    /**
     * Converts a source image rectangle into canvas pixels for the current image transform.
     */
    function toCanvasRectangle(rect, cropperImage) {
        const image = cropperImage.$image;
        const [a, , , d, e, f] = cropperImage.$getTransform();
        const centerX = image.naturalWidth / 2;
        const centerY = image.naturalHeight / 2;

        return {
            x: a * (rect.x - centerX) + centerX + e,
            y: d * (rect.y - centerY) + centerY + f,
            width: rect.width * a,
            height: rect.height * d,
        };
    }

    /**
     * Builds the FilePond `crop` metadata for a source rectangle.
     *
     * `zoom` is relative to the largest rectangle with the selection aspect ratio that fits in the image, which is
     * how the Image Transform plugin resolves the crop; `scaleToFit` is disabled so the output matches the selection
     * exactly, and `rect` is carried along for server-side cropping.
     */
    function createCropMetadata(rect) {
        const aspectRatio = rect.height / rect.width;
        let baseWidth = rect.naturalWidth;
        let baseHeight = baseWidth * aspectRatio;

        if (baseHeight > rect.naturalHeight) {
            baseHeight = rect.naturalHeight;
            baseWidth = baseHeight / aspectRatio;
        }

        return {
            aspectRatio,
            center: {
                x: (rect.x + rect.width / 2) / rect.naturalWidth,
                y: (rect.y + rect.height / 2) / rect.naturalHeight,
            },
            flip: { horizontal: false, vertical: false },
            rect: { x: rect.x, y: rect.y, width: rect.width, height: rect.height },
            rotation: 0,
            scaleToFit: false,
            zoom: Math.max(baseWidth / rect.width, 1),
        };
    }

    /**
     * Resolves the Cropper.js constructor from the UMD namespace (`window.Cropper.Cropper`) or a direct global.
     */
    function resolveCropper() {
        const library = window.Cropper;

        if (typeof library === 'function') {
            return library;
        }

        if (library !== undefined && library !== null && typeof library.Cropper === 'function') {
            return library.Cropper;
        }

        return null;
    }

    function openDialog(editor, settings, file, instructions) {
        const Cropper = resolveCropper();

        if (Cropper === null) {
            console.error('yii2FilePond: Cropper.js is not loaded; the image editor cannot open.');

            if (typeof editor.oncancel === 'function') {
                editor.oncancel();
            }

            return;
        }

        const labels = settings.labels;
        const initialAspectRatio = parseAspectRatio(settings.aspectRatio ?? editor.cropAspectRatio);
        const previousRect = instructions && instructions.crop ? instructions.crop.rect : null;
        const objectUrl = window.URL.createObjectURL(file);
        const dialog = createElement('dialog', CLASS);
        const header = createElement('header', `${CLASS}__header`);
        const title = createElement('h2', `${CLASS}__title`, labels.title);
        const body = createElement('div', `${CLASS}__body`);
        const image = createElement('img', `${CLASS}__image`);
        const footer = createElement('footer', `${CLASS}__footer`);
        const toolbar = createElement('div', `${CLASS}__toolbar`);
        const ratios = createElement('div', `${CLASS}__ratios`);
        const actions = createElement('div', `${CLASS}__actions`);
        const zoomOut = createButton(`${CLASS}__tool`, labels.zoomOut, ICONS.zoomOut);
        const zoomIn = createButton(`${CLASS}__tool`, labels.zoomIn, ICONS.zoomIn);
        const reset = createButton(`${CLASS}__tool`, labels.reset, ICONS.reset);
        const cancel = createButton(`${CLASS}__button`, labels.cancel);
        const apply = createButton(`${CLASS}__button ${CLASS}__button--primary`, labels.apply);
        const ratioButtons = [];
        let cropper = null;
        let closed = false;

        const getImage = () => (cropper === null ? null : cropper.getCropperImage());
        const getSelection = () => (cropper === null ? null : cropper.getCropperSelection());

        const activateRatio = (active) => {
            ratioButtons.forEach((entry) => {
                entry.button.classList.toggle('is-active', entry === active);
                entry.button.setAttribute('aria-pressed', entry === active ? 'true' : 'false');
            });
        };

        const applyRatio = (ratio) => {
            const selection = getSelection();

            if (selection === null) {
                return;
            }

            const aspectRatio = ratio === null ? NaN : ratio;
            let { width, height } = selection;

            if (ratio !== null) {
                height = width / ratio;

                if (selection.parentElement !== null && height > selection.parentElement.offsetHeight) {
                    height = selection.parentElement.offsetHeight * 0.8;
                    width = height * ratio;
                }
            }

            selection.aspectRatio = aspectRatio;
            selection.$change(selection.x, selection.y, width, height, aspectRatio, true);
            selection.$center();
        };

        const restoreSelection = () => {
            const cropperImage = getImage();
            const selection = getSelection();

            if (previousRect === null || cropperImage === null || selection === null) {
                return;
            }

            const rect = toCanvasRectangle(previousRect, cropperImage);

            selection.$change(rect.x, rect.y, rect.width, rect.height, NaN, true);
        };

        const resetEditor = () => {
            const cropperImage = getImage();
            const selection = getSelection();

            if (cropperImage === null || selection === null) {
                return;
            }

            cropperImage.$resetTransform();
            cropperImage.$center('contain');
            selection.$reset();
            selection.$center();

            const entry = ratioButtons.find((candidate) => candidate.ratio === initialAspectRatio) || null;

            applyRatio(initialAspectRatio);
            activateRatio(entry);
        };

        const close = () => {
            if (closed) {
                return;
            }

            closed = true;

            if (cropper !== null) {
                cropper.destroy();
            }

            window.URL.revokeObjectURL(objectUrl);

            if (dialog.open) {
                dialog.close();
            }

            dialog.remove();

            if (typeof editor.onclose === 'function') {
                editor.onclose();
            }
        };

        const cancelEditing = () => {
            if (typeof editor.oncancel === 'function') {
                editor.oncancel();
            }

            close();
        };

        const confirmEditing = () => {
            const cropperImage = getImage();
            const selection = getSelection();

            if (cropperImage === null || selection === null) {
                return;
            }

            const rect = toSourceRectangle(selection, cropperImage);

            if (rect.width === 0 || rect.height === 0) {
                return;
            }

            if (typeof editor.onconfirm === 'function') {
                editor.onconfirm({ data: { crop: createCropMetadata(rect) } });
            }

            close();
        };

        const initializeCropper = () => {
            if (cropper !== null) {
                return;
            }

            const options = Object.assign({ template: createTemplate(initialAspectRatio) }, settings.options);

            cropper = new Cropper(image, options);

            const cropperImage = getImage();

            if (cropperImage !== null) {
                cropperImage.$ready(() => {
                    restoreSelection();
                });
            }

            activateRatio(ratioButtons.find((entry) => entry.ratio === initialAspectRatio) || null);
        };

        settings.aspectRatios.forEach((value) => {
            const ratio = parseAspectRatio(value);
            const label = ratio === null ? labels.free : String(value);
            const button = createButton(`${CLASS}__ratio`, label);

            button.setAttribute('aria-pressed', 'false');

            const entry = { button, ratio };

            button.addEventListener('click', () => {
                applyRatio(ratio);
                activateRatio(entry);
            });
            ratioButtons.push(entry);
            ratios.appendChild(button);
        });

        image.alt = '';
        image.addEventListener('load', initializeCropper, { once: true });
        image.addEventListener('error', cancelEditing, { once: true });

        zoomOut.addEventListener('click', () => {
            const cropperImage = getImage();

            if (cropperImage !== null) {
                cropperImage.$zoom(-ZOOM_STEP);
            }
        });
        zoomIn.addEventListener('click', () => {
            const cropperImage = getImage();

            if (cropperImage !== null) {
                cropperImage.$zoom(ZOOM_STEP);
            }
        });
        reset.addEventListener('click', resetEditor);
        cancel.addEventListener('click', cancelEditing);
        apply.addEventListener('click', confirmEditing);
        dialog.addEventListener('cancel', (event) => {
            event.preventDefault();
            cancelEditing();
        });

        header.appendChild(title);
        body.appendChild(image);
        toolbar.append(zoomOut, zoomIn, reset);

        if (ratioButtons.length > 0) {
            toolbar.appendChild(ratios);
        }

        actions.append(cancel, apply);
        footer.append(toolbar, actions);
        dialog.append(header, body, footer);
        dialog.setAttribute('aria-labelledby', `${CLASS}-title-${Date.now()}`);
        title.id = dialog.getAttribute('aria-labelledby');
        document.body.appendChild(dialog);
        dialog.showModal();
        image.src = objectUrl;
    }

    /**
     * Creates an Image Edit plugin editor backed by Cropper.js.
     *
     * @param {Object} [options] Editor settings: `aspectRatio`, `aspectRatios`, `labels`, and Cropper.js `options`.
     * @returns {Object} Editor instance for the `imageEditEditor` FilePond option.
     */
    function createEditor(options) {
        const settings = Object.assign(
            { aspectRatio: null, aspectRatios: DEFAULT_ASPECT_RATIOS, labels: {}, options: {} },
            options || {},
        );

        settings.labels = Object.assign({}, DEFAULT_LABELS, settings.labels || {});
        settings.aspectRatios = Array.isArray(settings.aspectRatios) ? settings.aspectRatios : DEFAULT_ASPECT_RATIOS;
        settings.options = settings.options || {};

        const editor = {
            cropAspectRatio: settings.aspectRatio,
            onconfirm: null,
            oncancel: null,
            onclose: null,
            open(file, instructions) {
                openDialog(editor, settings, file, instructions);
            },
        };

        return editor;
    }

    const runtime = window.yii2FilePond || {};

    runtime.cropper = { createEditor, parseAspectRatio };
    window.yii2FilePond = runtime;
})(window, document);
