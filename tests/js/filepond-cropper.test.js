import { afterEach, beforeEach, describe, expect, test, vi } from 'vitest';

/**
 * Unit tests for the Cropper.js editor adapter (`src/asset/cropper/filepond-cropper.js`).
 *
 * Cropper.js is replaced by a fake that exposes the element API the adapter uses; jsdom lacks `<dialog>` modality and
 * object URLs, so both are stubbed.
 */
const CLASS = 'yii2-filepond-cropper';
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

/**
 * Image of 1200x800 natural pixels shown at half size with its top-left corner at the canvas origin.
 */
const TRANSFORM = [0.5, 0, 0, 0.5, -300, -200];

function expectedTemplate(aspectRatio) {
    const ratio = aspectRatio === undefined ? '' : ` aspect-ratio="${aspectRatio}"`;

    return '<cropper-canvas background>'
        + '<cropper-image scalable translatable initial-fit="contain"></cropper-image>'
        + '<cropper-shade hidden></cropper-shade>'
        + '<cropper-handle action="select" plain></cropper-handle>'
        + `<cropper-selection initial-coverage="0.8" movable resizable precise${ratio}>`
        + '<cropper-grid role="grid" covered></cropper-grid>'
        + '<cropper-crosshair centered></cropper-crosshair>'
        + '<cropper-handle action="move" theme-color="rgba(255, 255, 255, 0.35)"></cropper-handle>'
        + '<cropper-handle action="n-resize"></cropper-handle>'
        + '<cropper-handle action="e-resize"></cropper-handle>'
        + '<cropper-handle action="s-resize"></cropper-handle>'
        + '<cropper-handle action="w-resize"></cropper-handle>'
        + '<cropper-handle action="ne-resize"></cropper-handle>'
        + '<cropper-handle action="nw-resize"></cropper-handle>'
        + '<cropper-handle action="se-resize"></cropper-handle>'
        + '<cropper-handle action="sw-resize"></cropper-handle>'
        + '</cropper-selection>'
        + '</cropper-canvas>';
}

class FakeCropperImage {
    constructor(transform = TRANSFORM) {
        this.$image = { naturalWidth: 1200, naturalHeight: 800 };
        this.transform = transform;
        this.readyCallbacks = [];
        this.$center = vi.fn();
        this.$resetTransform = vi.fn();
        this.$zoom = vi.fn();
    }

    $getTransform() {
        return this.transform;
    }

    $ready(callback) {
        this.readyCallbacks.push(callback);
    }

    ready() {
        this.readyCallbacks.forEach((callback) => callback());
    }
}

class FakeSelection {
    constructor({ x = 150, y = 50, width = 300, height = 300, parentHeight = 400 } = {}) {
        this.x = x;
        this.y = y;
        this.width = width;
        this.height = height;
        this.aspectRatio = undefined;
        this.parentElement = parentHeight === null ? null : { offsetHeight: parentHeight };
        this.$center = vi.fn();
        this.$reset = vi.fn();
        this.$change = vi.fn((nextX, nextY, nextWidth, nextHeight) => {
            this.x = nextX;
            this.y = nextY;
            this.width = nextWidth;
            this.height = nextHeight;
        });
    }
}

/**
 * Installs a fake Cropper.js constructor and returns the state it records.
 */
function installCropper({ image = new FakeCropperImage(), selection = new FakeSelection(), umd = true } = {}) {
    const state = { image, instances: [], selection };

    class FakeCropper {
        constructor(element, options) {
            this.element = element;
            this.options = options;
            this.destroy = vi.fn();
            state.instances.push(this);
        }

        getCropperImage() {
            return state.image;
        }

        getCropperSelection() {
            return state.selection;
        }
    }

    window.Cropper = umd ? { Cropper: FakeCropper } : FakeCropper;

    return state;
}

async function loadAdapter() {
    vi.resetModules();

    await import('../../src/asset/cropper/filepond-cropper.js');

    return window.yii2FilePond.cropper;
}

async function openEditor(options, instructions) {
    const adapter = await loadAdapter();
    const editor = adapter.createEditor(options);

    editor.onconfirm = vi.fn();
    editor.oncancel = vi.fn();
    editor.onclose = vi.fn();
    editor.open(new Blob(['image']), instructions);

    return { editor, ...queryDialog() };
}

function queryDialog() {
    const dialog = document.querySelector(`dialog.${CLASS}`);
    const find = (suffix) => dialog?.querySelector(`.${CLASS}__${suffix}`) ?? null;

    return {
        apply: find('button--primary'),
        cancel: dialog?.querySelectorAll(`.${CLASS}__button`)[0] ?? null,
        dialog,
        image: find('image'),
        ratios: dialog === null ? [] : Array.from(dialog.querySelectorAll(`.${CLASS}__ratio`)),
        tools: dialog === null ? [] : Array.from(dialog.querySelectorAll(`.${CLASS}__tool`)),
    };
}

function load(image) {
    image.dispatchEvent(new Event('load'));
}

function pressed(buttons) {
    return buttons.map((button) => [
        button.textContent,
        button.classList.contains('is-active'),
        button.getAttribute('aria-pressed'),
    ]);
}

describe('filepond-cropper', () => {
    beforeEach(() => {
        HTMLDialogElement.prototype.showModal = vi.fn(function () {
            this.setAttribute('open', '');
        });
        HTMLDialogElement.prototype.close = vi.fn(function () {
            this.removeAttribute('open');
        });
        window.URL.createObjectURL = vi.fn(() => 'blob:filepond');
        window.URL.revokeObjectURL = vi.fn();
    });

    afterEach(() => {
        delete window.Cropper;
        delete window.yii2FilePond;
        delete HTMLDialogElement.prototype.showModal;
        delete HTMLDialogElement.prototype.close;
        delete window.URL.createObjectURL;
        delete window.URL.revokeObjectURL;
        document.body.innerHTML = '';
        vi.restoreAllMocks();
    });

    describe('runtime registration', () => {
        test('preserves members registered before the adapter loads', async () => {
            const create = vi.fn();

            window.yii2FilePond = { create };

            const adapter = await loadAdapter();

            expect(window.yii2FilePond.create, 'Existing members must survive.').toBe(create);
            expect(Object.keys(adapter), 'Public API surface.').toEqual(['createEditor', 'parseAspectRatio']);
        });
    });

    describe('parseAspectRatio', () => {
        test.each([
            [1.5, 1.5],
            [0, null],
            [-1, null],
            [Infinity, null],
            [NaN, null],
            ['free', null],
            [' FREE ', null],
            ['16:9', 16 / 9],
            ['1:2', 0.5],
            ['0:9', null],
            ['16:0', null],
            ['1:2:3', null],
            ['1.5', 1.5],
            ['0', null],
            ['-1', null],
            ['Infinity', null],
            ['abc', null],
            [null, null],
            [undefined, null],
            [{}, null],
        ])('parses %j as %j', async (value, expected) => {
            const adapter = await loadAdapter();

            expect(adapter.parseAspectRatio(value)).toBe(expected);
        });
    });

    describe('createEditor', () => {
        test('returns an editor implementing the Image Edit contract', async () => {
            const adapter = await loadAdapter();
            const editor = adapter.createEditor({ aspectRatio: '1:1' });

            expect(editor, 'Callbacks must start unset.').toMatchObject({
                cropAspectRatio: '1:1',
                oncancel: null,
                onclose: null,
                onconfirm: null,
            });
            expect(typeof editor.open, 'Editor must expose `open`.').toBe('function');
        });

        test('defaults the aspect ratio to `null`', async () => {
            const adapter = await loadAdapter();

            expect(adapter.createEditor().cropAspectRatio, 'Default ratio must be free.').toBeNull();
        });

        test('renders default labels and presets without options', async () => {
            installCropper();

            const adapter = await loadAdapter();

            adapter.createEditor().open(new Blob(['image']));

            const { apply, cancel, dialog, ratios, tools } = queryDialog();

            expect(dialog.querySelector(`.${CLASS}__title`).textContent, 'Default title.').toBe('Edit image');
            expect(cancel.textContent, 'Default cancel label.').toBe('Cancel');
            expect(apply.textContent, 'Default apply label.').toBe('Apply');
            expect(tools.map((tool) => tool.title), 'Default tool labels.').toEqual(['Zoom out', 'Zoom in', 'Reset']);
            expect(ratios.map((ratio) => ratio.textContent), 'Default presets.').toEqual([
                'Free',
                '1:1',
                '16:9',
                '4:3',
                '3:2',
            ]);
        });

        test('merges custom labels over the defaults', async () => {
            installCropper();

            const { apply, cancel, dialog, ratios } = await openEditor({
                aspectRatios: ['free'],
                labels: { apply: 'Aplicar', free: 'Libre' },
            });

            expect(apply.textContent, 'Custom label must win.').toBe('Aplicar');
            expect(cancel.textContent, 'Missing labels keep defaults.').toBe('Cancel');
            expect(dialog.querySelector(`.${CLASS}__title`).textContent, 'Default title.').toBe('Edit image');
            expect(ratios[0].textContent, 'Free preset uses its label.').toBe('Libre');
        });

        test('falls back to defaults for invalid settings', async () => {
            const state = installCropper();
            const { image, ratios } = await openEditor({ aspectRatios: 'free', labels: null, options: null });

            expect(ratios.length, 'Invalid presets fall back to defaults.').toBe(5);
            expect(ratios[0].textContent, 'Null labels fall back to defaults.').toBe('Free');

            load(image);

            expect(state.instances[0].options, 'Null options keep the template only.').toEqual({
                template: expectedTemplate(),
            });
        });
    });

    describe('dialog', () => {
        test('renders the dialog structure and opens it modally', async () => {
            vi.spyOn(Date, 'now').mockReturnValue(123);
            installCropper();

            const blob = new Blob(['image']);
            const adapter = await loadAdapter();

            adapter.createEditor({ aspectRatios: ['free', '16:9'] }).open(blob);

            const { apply, cancel, dialog, image, ratios, tools } = queryDialog();
            const header = dialog.children[0];
            const body = dialog.children[1];
            const footer = dialog.children[2];
            const toolbar = footer.children[0];
            const actions = footer.children[1];

            expect(dialog.parentElement, 'Dialog must be appended to the body.').toBe(document.body);
            expect(dialog.className, 'Dialog class.').toBe(CLASS);
            expect(HTMLDialogElement.prototype.showModal, 'Dialog must open modally.').toHaveBeenCalledOnce();
            expect(dialog.getAttribute('aria-labelledby'), 'Dialog label id.').toBe(`${CLASS}-title-123`);
            expect(Array.from(dialog.children).map((child) => child.tagName), 'Sections.').toEqual([
                'HEADER',
                'DIV',
                'FOOTER',
            ]);
            expect(header.className, 'Header class.').toBe(`${CLASS}__header`);
            expect(header.firstChild.tagName, 'Title tag.').toBe('H2');
            expect(header.firstChild.className, 'Title class.').toBe(`${CLASS}__title`);
            expect(header.firstChild.id, 'Title id must label the dialog.').toBe(`${CLASS}-title-123`);
            expect(body.className, 'Body class.').toBe(`${CLASS}__body`);
            expect(body.firstChild, 'Body must hold the image.').toBe(image);
            expect(image.tagName, 'Image tag.').toBe('IMG');
            expect(image.alt, 'Image must be decorative.').toBe('');
            expect(image.hasAttribute('alt'), 'Image must carry an `alt` attribute.').toBe(true);
            expect(image.getAttribute('src'), 'Image must load the object URL.').toBe('blob:filepond');
            expect(window.URL.createObjectURL, 'Object URL must wrap the file.').toHaveBeenCalledExactlyOnceWith(blob);
            expect(footer.className, 'Footer class.').toBe(`${CLASS}__footer`);
            expect(toolbar.className, 'Toolbar class.').toBe(`${CLASS}__toolbar`);
            expect(Array.from(toolbar.children), 'Toolbar order.').toEqual([
                ...tools,
                dialog.querySelector(`.${CLASS}__ratios`),
            ]);
            expect(dialog.querySelector(`.${CLASS}__ratios`).tagName, 'Ratios container tag.').toBe('DIV');
            expect(Array.from(dialog.querySelector(`.${CLASS}__ratios`).children), 'Ratio buttons.').toEqual(ratios);
            expect(actions.className, 'Actions class.').toBe(`${CLASS}__actions`);
            expect(Array.from(actions.children), 'Actions order.').toEqual([cancel, apply]);
            expect(cancel.className, 'Cancel class.').toBe(`${CLASS}__button`);
            expect(apply.className, 'Apply class.').toBe(`${CLASS}__button ${CLASS}__button--primary`);
        });

        test('renders icon tools with accessible labels', async () => {
            installCropper();

            const { tools } = await openEditor({ labels: { reset: 'R', zoomIn: 'In', zoomOut: 'Out' } });

            expect(
                tools.map((tool) => [tool.tagName, tool.type, tool.title, tool.getAttribute('aria-label'), tool.innerHTML]),
            ).toEqual([
                ['BUTTON', 'button', 'Out', 'Out', ICONS.zoomOut],
                ['BUTTON', 'button', 'In', 'In', ICONS.zoomIn],
                ['BUTTON', 'button', 'R', 'R', ICONS.reset],
            ]);
        });

        test('renders text buttons without icon attributes', async () => {
            installCropper();

            const { apply, cancel, ratios } = await openEditor({ aspectRatios: ['1:1'] });

            [apply, cancel, ratios[0]].forEach((button) => {
                expect(button.type, 'Buttons must not submit forms.').toBe('button');
                expect(button.hasAttribute('title'), 'Text buttons have no title.').toBe(false);
                expect(button.hasAttribute('aria-label'), 'Text buttons have no aria-label.').toBe(false);
                expect(button.children.length, 'Text buttons have no icon.').toBe(0);
            });
            expect(ratios[0].className, 'Ratio class.').toBe(`${CLASS}__ratio`);
            expect(ratios[0].getAttribute('aria-pressed'), 'Ratios start unpressed.').toBe('false');
        });

        test('omits the ratios group without presets', async () => {
            installCropper();

            const { dialog, tools } = await openEditor({ aspectRatios: [] });

            expect(dialog.querySelector(`.${CLASS}__ratios`), 'No preset group.').toBeNull();
            expect(Array.from(dialog.querySelector(`.${CLASS}__toolbar`).children), 'Only tools.').toEqual(tools);
        });

        test('labels presets that do not parse as free', async () => {
            installCropper();

            const { ratios } = await openEditor({ aspectRatios: ['abc', 1.5] });

            expect(ratios.map((ratio) => ratio.textContent), 'Preset labels.').toEqual(['Free', '1.5']);
        });
    });

    describe('Cropper.js resolution', () => {
        test('constructs the UMD namespace constructor on image load', async () => {
            const state = installCropper();
            const { image } = await openEditor({ aspectRatios: [], options: { background: false } });

            expect(state.instances.length, 'Cropper must wait for the image.').toBe(0);

            load(image);

            expect(state.instances.length, 'Cropper must be constructed once.').toBe(1);
            expect(state.instances[0].element, 'Cropper must target the image.').toBe(image);
            expect(state.instances[0].options, 'Options must extend the template.').toEqual({
                background: false,
                template: expectedTemplate(),
            });
        });

        test('constructs a direct global constructor', async () => {
            const state = installCropper({ umd: false });
            const { image } = await openEditor({ aspectRatios: [] });

            load(image);

            expect(state.instances.length, 'Direct constructor must be used.').toBe(1);
        });

        test('lets Cropper.js options override the template', async () => {
            const state = installCropper();
            const { image } = await openEditor({ options: { template: '<custom>' } });

            load(image);

            expect(state.instances[0].options.template, 'Custom template must win.').toBe('<custom>');
        });

        test('constructs the cropper only once per image', async () => {
            const state = installCropper();
            const { image } = await openEditor();

            load(image);
            load(image);

            expect(state.instances.length, 'Load must be handled once.').toBe(1);
        });

        test.each([
            ['an object without constructor', {}],
            ['`null`', null],
            ['`undefined`', undefined],
            ['a non-function member', { Cropper: {} }],
        ])('cancels when Cropper.js is %s', async (_label, library) => {
            const error = vi.spyOn(console, 'error').mockImplementation(() => {});

            window.Cropper = library;

            const { editor, dialog } = await openEditor();

            expect(error, 'Missing library must be reported.').toHaveBeenCalledExactlyOnceWith(
                'yii2FilePond: Cropper.js is not loaded; the image editor cannot open.',
            );
            expect(editor.oncancel, 'Editing must be cancelled.').toHaveBeenCalledOnce();
            expect(editor.onclose, 'Nothing was opened.').not.toHaveBeenCalled();
            expect(dialog, 'No dialog must be rendered.').toBeNull();
            expect(window.URL.createObjectURL, 'No object URL must be created.').not.toHaveBeenCalled();
        });

        test('tolerates a missing cancel callback when Cropper.js is absent', async () => {
            vi.spyOn(console, 'error').mockImplementation(() => {});

            const adapter = await loadAdapter();

            expect(() => adapter.createEditor().open(new Blob(['image']))).not.toThrow();
            expect(queryDialog().dialog, 'No dialog must be rendered.').toBeNull();
        });
    });

    describe('aspect ratio presets', () => {
        test('uses the configured aspect ratio in the template and active preset', async () => {
            const state = installCropper();
            const { image, ratios } = await openEditor({ aspectRatio: '16:9', aspectRatios: ['free', '16:9'] });

            expect(pressed(ratios), 'Nothing is active before load.').toEqual([
                ['Free', false, 'false'],
                ['16:9', false, 'false'],
            ]);

            load(image);

            expect(state.instances[0].options.template, 'Template ratio.').toBe(expectedTemplate(16 / 9));
            expect(pressed(ratios), 'Configured preset is active.').toEqual([
                ['Free', false, 'false'],
                ['16:9', true, 'true'],
            ]);
        });

        test('falls back to the editor aspect ratio', async () => {
            const state = installCropper();
            const adapter = await loadAdapter();
            const editor = adapter.createEditor({ aspectRatios: ['4:3'] });

            editor.cropAspectRatio = '4:3';
            editor.open(new Blob(['image']));

            const { image, ratios } = queryDialog();

            load(image);

            expect(state.instances[0].options.template, 'Editor ratio.').toBe(expectedTemplate(4 / 3));
            expect(pressed(ratios), 'Editor preset is active.').toEqual([['4:3', true, 'true']]);
        });

        test('activates no preset when the initial ratio is not listed', async () => {
            installCropper();

            const { image, ratios } = await openEditor({ aspectRatio: '2:1', aspectRatios: ['free', '1:1'] });

            load(image);

            expect(pressed(ratios), 'No preset matches.').toEqual([
                ['Free', false, 'false'],
                ['1:1', false, 'false'],
            ]);
        });

        test('applies a fixed preset to the selection', async () => {
            const state = installCropper();
            const { image, ratios } = await openEditor({ aspectRatios: ['free', '16:9'] });

            load(image);
            ratios[1].click();

            expect(state.selection.aspectRatio, 'Selection ratio.').toBe(16 / 9);
            expect(state.selection.$change).toHaveBeenCalledExactlyOnceWith(150, 50, 300, 168.75, 16 / 9, true);
            expect(state.selection.$center, 'Selection must be centered.').toHaveBeenCalledOnce();
            expect(pressed(ratios), 'Clicked preset is active.').toEqual([
                ['Free', false, 'false'],
                ['16:9', true, 'true'],
            ]);
        });

        test('shrinks a preset that would overflow the canvas', async () => {
            const state = installCropper();
            const { image, ratios } = await openEditor({ aspectRatios: ['1:2'] });

            load(image);
            ratios[0].click();

            expect(state.selection.$change).toHaveBeenCalledExactlyOnceWith(150, 50, 160, 320, 0.5, true);
        });

        test('keeps a preset that exactly fills the canvas height', async () => {
            const state = installCropper({ selection: new FakeSelection({ parentHeight: 300 }) });
            const { image, ratios } = await openEditor({ aspectRatios: ['1:1'] });

            load(image);
            ratios[0].click();

            expect(state.selection.$change).toHaveBeenCalledExactlyOnceWith(150, 50, 300, 300, 1, true);
        });

        test('skips the overflow check for a detached selection', async () => {
            const state = installCropper({ selection: new FakeSelection({ parentHeight: null }) });
            const { image, ratios } = await openEditor({ aspectRatios: ['1:2'] });

            load(image);
            ratios[0].click();

            expect(state.selection.$change).toHaveBeenCalledExactlyOnceWith(150, 50, 300, 600, 0.5, true);
        });

        test('releases the ratio for a free preset', async () => {
            const state = installCropper();
            const { image, ratios } = await openEditor({ aspectRatio: '1:1', aspectRatios: ['free', '1:1'] });

            load(image);
            ratios[0].click();

            expect(state.selection.aspectRatio, 'Free selection ratio.').toBeNaN();
            expect(state.selection.$change).toHaveBeenCalledExactlyOnceWith(150, 50, 300, 300, NaN, true);
            expect(pressed(ratios), 'Free preset is active.').toEqual([
                ['Free', true, 'true'],
                ['1:1', false, 'false'],
            ]);
        });

        test('remembers a preset chosen before the image loads', async () => {
            const state = installCropper();
            const { image, ratios } = await openEditor({ aspectRatios: ['free', '3:2'] });

            ratios[1].click();

            expect(state.selection.$change, 'No selection exists yet.').not.toHaveBeenCalled();
            expect(pressed(ratios), 'Clicked preset is active.').toEqual([
                ['Free', false, 'false'],
                ['3:2', true, 'true'],
            ]);

            load(image);

            expect(state.instances[0].options.template, 'Template uses the chosen ratio.').toBe(
                expectedTemplate(1.5),
            );
        });

        test('ignores presets when Cropper.js exposes no selection', async () => {
            installCropper({ selection: null });

            const { image, ratios } = await openEditor({ aspectRatios: ['1:1'] });

            load(image);

            expect(() => ratios[0].click(), 'Missing selection must be ignored.').not.toThrow();
        });
    });

    describe('tools', () => {
        test('zooms the image in and out', async () => {
            const state = installCropper();
            const { image, tools } = await openEditor();

            load(image);
            tools[0].click();
            tools[1].click();

            expect(state.image.$zoom.mock.calls, 'Zoom steps.').toEqual([[-0.1], [0.1]]);
        });

        test('ignores tools before the cropper exists', async () => {
            const state = installCropper();
            const { tools } = await openEditor();

            tools.forEach((tool) => tool.click());

            expect(state.image.$zoom, 'Zoom must wait for the cropper.').not.toHaveBeenCalled();
            expect(state.image.$resetTransform, 'Reset must wait for the cropper.').not.toHaveBeenCalled();
        });

        test('ignores tools when Cropper.js exposes no image', async () => {
            const state = installCropper({ image: null });
            const { image, tools } = await openEditor();

            load(image);
            tools.forEach((tool) => tool.click());

            expect(state.selection.$reset, 'Reset needs the image.').not.toHaveBeenCalled();
        });

        test('ignores reset when Cropper.js exposes no selection', async () => {
            const state = installCropper({ selection: null });
            const { image, tools } = await openEditor();

            load(image);
            tools[2].click();

            expect(state.image.$resetTransform, 'Reset needs the selection.').not.toHaveBeenCalled();
        });

        test('resets the transform, selection, and initial preset', async () => {
            const state = installCropper();
            const { image, ratios, tools } = await openEditor({ aspectRatio: '16:9', aspectRatios: ['free', '16:9'] });

            load(image);
            ratios[0].click();
            state.selection.$change.mockClear();
            state.selection.$center.mockClear();
            tools[2].click();

            expect(state.image.$resetTransform, 'Transform must be reset.').toHaveBeenCalledOnce();
            expect(state.image.$center, 'Image must be contained.').toHaveBeenCalledExactlyOnceWith('contain');
            expect(state.selection.$reset, 'Selection must be reset.').toHaveBeenCalledOnce();
            expect(state.selection.$center, 'Selection must be centered after reset and ratio.').toHaveBeenCalledTimes(
                2,
            );
            expect(state.selection.$change).toHaveBeenCalledExactlyOnceWith(150, 50, 300, 168.75, 16 / 9, true);
            expect(pressed(ratios), 'Initial preset is active again.').toEqual([
                ['Free', false, 'false'],
                ['16:9', true, 'true'],
            ]);
        });

        test('clears the active preset when the initial ratio is not listed', async () => {
            installCropper();

            const { image, ratios, tools } = await openEditor({ aspectRatio: '2:1', aspectRatios: ['1:1'] });

            load(image);
            ratios[0].click();
            tools[2].click();

            expect(pressed(ratios), 'No preset matches the initial ratio.').toEqual([['1:1', false, 'false']]);
        });
    });

    describe('selection restore', () => {
        test('restores a previous crop rectangle and ratio', async () => {
            const state = installCropper();
            const crop = { rect: { x: 300, y: 100, width: 600, height: 600 }, selectionRatio: 1 };
            const { image, ratios } = await openEditor({ aspectRatios: ['free', '1:1'] }, { crop });

            load(image);

            expect(state.instances[0].options.template, 'Template uses the stored ratio.').toBe(expectedTemplate(1));
            expect(state.selection.$change, 'Restore waits for the image.').not.toHaveBeenCalled();

            state.image.ready();

            expect(state.selection.$change).toHaveBeenCalledExactlyOnceWith(150, 50, 300, 300, 1, true);
            expect(pressed(ratios), 'Stored preset is active.').toEqual([
                ['Free', false, 'false'],
                ['1:1', true, 'true'],
            ]);
        });

        test('restores a free stored ratio over the configured one', async () => {
            const state = installCropper();
            const crop = { rect: { x: 300, y: 100, width: 600, height: 600 }, selectionRatio: null };
            const { image, ratios } = await openEditor({ aspectRatio: '1:1', aspectRatios: ['free', '1:1'] }, { crop });

            load(image);
            state.image.ready();

            expect(state.instances[0].options.template, 'Template has no ratio.').toBe(expectedTemplate());
            expect(state.selection.$change).toHaveBeenCalledExactlyOnceWith(150, 50, 300, 300, NaN, true);
            expect(pressed(ratios)[0], 'Free preset is active.').toEqual(['Free', true, 'true']);
        });

        test('keeps the configured ratio for a crop without a stored preset', async () => {
            const state = installCropper();
            const crop = { rect: { x: 300, y: 100, width: 600, height: 300 } };
            const { image } = await openEditor({ aspectRatio: '2:1' }, { crop });

            load(image);
            state.image.ready();

            expect(state.instances[0].options.template, 'Configured ratio.').toBe(expectedTemplate(2));
            expect(state.selection.$change).toHaveBeenCalledExactlyOnceWith(150, 50, 300, 150, 2, true);
        });

        test.each([
            ['no instructions', undefined],
            ['instructions without crop', {}],
            ['a crop without rectangle', { crop: { center: { x: 0.5, y: 0.5 } } }],
        ])('leaves the default selection for %s', async (_label, instructions) => {
            const state = installCropper();
            const { image } = await openEditor({}, instructions);

            load(image);
            state.image.ready();

            expect(state.selection.$change, 'Default selection is kept.').not.toHaveBeenCalled();
        });

        test('skips the restore when Cropper.js exposes no selection', async () => {
            const state = installCropper({ selection: null });
            const crop = { rect: { x: 300, y: 100, width: 600, height: 600 } };
            const { image } = await openEditor({}, { crop });

            load(image);

            expect(() => state.image.ready(), 'Missing selection must be ignored.').not.toThrow();
        });

        test('skips the ready hook when Cropper.js exposes no image', async () => {
            const state = installCropper({ image: null });
            const { image } = await openEditor({}, { crop: { rect: { x: 0, y: 0, width: 1, height: 1 } } });

            expect(() => load(image), 'Missing image must be ignored.').not.toThrow();
            expect(state.selection.$change, 'Nothing to restore against.').not.toHaveBeenCalled();
        });
    });

    describe('confirm', () => {
        test('confirms crop metadata in source pixels and closes', async () => {
            const state = installCropper();
            const { apply, dialog, editor, image, ratios } = await openEditor({ aspectRatios: ['free', '1:1'] });

            load(image);
            ratios[1].click();
            apply.click();

            expect(editor.onconfirm).toHaveBeenCalledExactlyOnceWith({
                data: {
                    crop: {
                        aspectRatio: 1,
                        center: { x: 0.5, y: 0.5 },
                        flip: { horizontal: false, vertical: false },
                        rect: { x: 300, y: 100, width: 600, height: 600 },
                        rotation: 0,
                        scaleToFit: false,
                        selectionRatio: 1,
                        zoom: 800 / 600,
                    },
                },
            });
            expect(state.instances[0].destroy, 'Cropper must be destroyed.').toHaveBeenCalledOnce();
            expect(editor.oncancel, 'Confirm is not a cancel.').not.toHaveBeenCalled();
            expect(editor.onclose, 'Editor must close.').toHaveBeenCalledOnce();
            expect(dialog.isConnected, 'Dialog must be removed.').toBe(false);
        });

        test('zooms relative to the widest fitting rectangle', async () => {
            installCropper({ selection: new FakeSelection({ x: 0, y: 0, width: 300, height: 50 }) });

            const { apply, editor, image } = await openEditor();

            load(image);
            apply.click();

            expect(editor.onconfirm.mock.calls[0][0].data.crop).toMatchObject({
                aspectRatio: 100 / 600,
                center: { x: 0.25, y: 0.0625 },
                rect: { x: 0, y: 0, width: 600, height: 100 },
                selectionRatio: null,
                zoom: 2,
            });
        });

        test('zooms relative to the tallest fitting rectangle', async () => {
            installCropper({ selection: new FakeSelection({ x: 150, y: 50, width: 200, height: 300 }) });

            const { apply, editor, image } = await openEditor();

            load(image);
            apply.click();

            expect(editor.onconfirm.mock.calls[0][0].data.crop).toMatchObject({
                aspectRatio: 1.5,
                rect: { x: 300, y: 100, width: 400, height: 600 },
                zoom: 800 / 1.5 / 400,
            });
        });

        test('rounds fractional source coordinates', async () => {
            installCropper({ selection: new FakeSelection({ x: 150.3, y: 50.2, width: 300.4, height: 299.6 }) });

            const { apply, editor, image } = await openEditor();

            load(image);
            apply.click();

            expect(editor.onconfirm.mock.calls[0][0].data.crop.rect, 'Rounded rectangle.').toEqual({
                x: 301,
                y: 100,
                width: 601,
                height: 599,
            });
        });

        test('accounts for zoom and translation in the transform', async () => {
            installCropper({
                image: new FakeCropperImage([2, 0, 0, 2, 100, 50]),
                selection: new FakeSelection({ x: 0, y: 0, width: 200, height: 100 }),
            });

            const { apply, editor, image } = await openEditor();

            load(image);
            apply.click();

            expect(editor.onconfirm.mock.calls[0][0].data.crop.rect, 'Inverse transform.').toEqual({
                x: 250,
                y: 175,
                width: 100,
                height: 50,
            });
        });

        test('clips a selection that extends past the image', async () => {
            installCropper({ selection: new FakeSelection({ x: -50, y: -50, width: 800, height: 600 }) });

            const { apply, editor, image } = await openEditor();

            load(image);
            apply.click();

            expect(editor.onconfirm.mock.calls[0][0].data.crop.rect, 'Overlapping area only.').toEqual({
                x: 0,
                y: 0,
                width: 1200,
                height: 800,
            });
        });

        test.each([
            ['left', { x: -400, y: 50, width: 100, height: 100 }],
            ['above', { x: 150, y: -400, width: 100, height: 100 }],
        ])('keeps the dialog open for a selection %s the image', async (_label, rectangle) => {
            const state = installCropper({ selection: new FakeSelection(rectangle) });
            const { apply, dialog, editor, image } = await openEditor();

            load(image);
            apply.click();

            expect(editor.onconfirm, 'Empty crop must not confirm.').not.toHaveBeenCalled();
            expect(state.instances[0].destroy, 'Cropper must stay alive.').not.toHaveBeenCalled();
            expect(dialog.isConnected, 'Dialog must stay open.').toBe(true);
        });

        test('ignores confirm before the cropper exists', async () => {
            installCropper();

            const { apply, dialog, editor } = await openEditor();

            apply.click();

            expect(editor.onconfirm, 'Nothing to confirm yet.').not.toHaveBeenCalled();
            expect(dialog.isConnected, 'Dialog must stay open.').toBe(true);
        });

        test.each([
            ['image', { image: null }],
            ['selection', { selection: null }],
        ])('ignores confirm when Cropper.js exposes no %s', async (_label, parts) => {
            installCropper(parts);

            const { apply, dialog, editor, image } = await openEditor();

            load(image);
            apply.click();

            expect(editor.onconfirm, 'Nothing to confirm.').not.toHaveBeenCalled();
            expect(dialog.isConnected, 'Dialog must stay open.').toBe(true);
        });

        test('closes without callbacks', async () => {
            installCropper();

            const adapter = await loadAdapter();

            adapter.createEditor().open(new Blob(['image']));

            const { apply, dialog, image } = queryDialog();

            load(image);
            apply.click();

            expect(dialog.isConnected, 'Dialog must be removed.').toBe(false);
        });
    });

    describe('cancel and close', () => {
        test('cancels from the cancel button', async () => {
            const state = installCropper();
            const { cancel, dialog, editor, image } = await openEditor();

            load(image);
            cancel.click();

            expect(editor.oncancel, 'Cancel callback.').toHaveBeenCalledOnce();
            expect(editor.onclose, 'Close callback.').toHaveBeenCalledOnce();
            expect(editor.onconfirm, 'Cancel is not a confirm.').not.toHaveBeenCalled();
            expect(state.instances[0].destroy, 'Cropper must be destroyed.').toHaveBeenCalledOnce();
            expect(window.URL.revokeObjectURL, 'Object URL must be released.').toHaveBeenCalledExactlyOnceWith(
                'blob:filepond',
            );
            expect(HTMLDialogElement.prototype.close, 'Dialog must be closed.').toHaveBeenCalledOnce();
            expect(dialog.isConnected, 'Dialog must be removed.').toBe(false);
        });

        test('cancels from the dialog cancel event', async () => {
            installCropper();

            const { dialog, editor } = await openEditor();
            const event = new Event('cancel', { cancelable: true });

            dialog.dispatchEvent(event);

            expect(event.defaultPrevented, 'Native close must be prevented.').toBe(true);
            expect(editor.oncancel, 'Cancel callback.').toHaveBeenCalledOnce();
            expect(editor.onclose, 'Close callback.').toHaveBeenCalledOnce();
            expect(dialog.isConnected, 'Dialog must be removed.').toBe(false);
        });

        test('cancels when the image fails to load', async () => {
            const state = installCropper();
            const { dialog, editor, image } = await openEditor();

            image.dispatchEvent(new Event('error'));

            expect(editor.oncancel, 'Cancel callback.').toHaveBeenCalledOnce();
            expect(editor.onclose, 'Close callback.').toHaveBeenCalledOnce();
            expect(dialog.isConnected, 'Dialog must be removed.').toBe(false);
            expect(state.instances.length, 'Cropper must not be created.').toBe(0);
        });

        test('handles the image error only once', async () => {
            installCropper();

            const { editor, image } = await openEditor();

            image.dispatchEvent(new Event('error'));
            image.dispatchEvent(new Event('error'));

            expect(editor.oncancel, 'Error must be handled once.').toHaveBeenCalledOnce();
        });

        test('closes only once', async () => {
            installCropper();

            const { cancel, editor } = await openEditor();

            cancel.click();
            cancel.click();

            expect(editor.oncancel, 'Each cancel reports.').toHaveBeenCalledTimes(2);
            expect(editor.onclose, 'Close must run once.').toHaveBeenCalledOnce();
            expect(window.URL.revokeObjectURL, 'Object URL must be released once.').toHaveBeenCalledOnce();
        });

        test('skips closing a dialog that is no longer open', async () => {
            installCropper();

            const { cancel, dialog } = await openEditor();

            dialog.removeAttribute('open');
            cancel.click();

            expect(HTMLDialogElement.prototype.close, 'Closed dialog must not close again.').not.toHaveBeenCalled();
            expect(dialog.isConnected, 'Dialog must be removed.').toBe(false);
        });

        test('cancels without callbacks', async () => {
            installCropper();

            const adapter = await loadAdapter();

            adapter.createEditor().open(new Blob(['image']));

            const { cancel, dialog } = queryDialog();

            expect(() => cancel.click(), 'Missing callbacks must be ignored.').not.toThrow();
            expect(dialog.isConnected, 'Dialog must be removed.').toBe(false);
        });
    });
});
