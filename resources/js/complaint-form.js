/**
 * Alpine components for the complaint form. Livewire ships Alpine, so we register on
 * `alpine:init` rather than importing Alpine ourselves.
 */
document.addEventListener('alpine:init', () => {
    /**
     * Drag-and-drop / click-to-pick multi-file upload into a Livewire property.
     */
    window.Alpine.data('dropzone', ({ property = 'pendingAttachments' } = {}) => ({
        dragging: false,
        uploading: false,
        progress: 0,

        pick() {
            this.$refs.input.click();
        },

        handle(fileList) {
            const files = Array.from(fileList || []);
            if (!files.length) return;

            this.uploading = true;
            this.progress = 0;
            this.$wire.uploadMultiple(
                property,
                files,
                () => { this.uploading = false; this.$refs.input.value = ''; },
                () => { this.uploading = false; this.$refs.input.value = ''; },
                (event) => { this.progress = event.detail.progress; },
            );
        },

        drop(event) {
            this.dragging = false;
            this.handle(event.dataTransfer.files);
        },
    }));

    /** Copy text to the clipboard with a short "Copied" confirmation. */
    window.Alpine.data('copyText', (text) => ({
        copied: false,
        async copy() {
            try {
                await navigator.clipboard.writeText(text);
            } catch (e) {
                const area = Object.assign(document.createElement('textarea'), { value: text });
                document.body.appendChild(area);
                area.select();
                document.execCommand('copy');
                area.remove();
            }
            this.copied = true;
            setTimeout(() => (this.copied = false), 2000);
        },
    }));
});
