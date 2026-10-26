/**
 * Judiciary chatbot: shows bot messages one at a time with a "typing…" indicator and streams
 * their words in, so the scripted conversation feels like a live assistant. Sending is disabled
 * until the bot has finished "speaking".
 */
document.addEventListener('alpine:init', () => {
    const reducedMotion = window.matchMedia?.('(prefers-reduced-motion: reduce)').matches;
    // No typing theatre when nobody is watching (reduced motion, or the tab is in the background).
    const sleep = (ms) => new Promise((resolve) => setTimeout(resolve, reducedMotion || document.hidden ? 0 : ms));

    window.Alpine.store('chatbot', {
        typing: false,
        pending: 0,
        chain: Promise.resolve(),
        scroller: null,

        scroll() {
            requestAnimationFrame(() => this.scroller?.scrollTo({ top: this.scroller.scrollHeight, behavior: reducedMotion ? 'auto' : 'smooth' }));
        },

        /** Queue a bot message; `reveal` shows it once the typing pause is over. */
        enqueue(length, reveal) {
            this.pending++;
            this.chain = this.chain.then(async () => {
                this.typing = true;
                this.scroll();
                await sleep(350 + Math.min(length * 6, 700));
                this.typing = false;
                await reveal();
                this.pending--;
                this.scroll();
            });
        },
    });

    window.Alpine.data('chatbot', () => ({
        /** What the citizen is typing; narrows the quick-reply options. */
        filter: '',

        init() {
            const store = window.Alpine.store('chatbot');
            store.scroller = this.$refs.scroller;
            store.scroll();

            window.Livewire.hook('commit', ({ component, succeed }) => {
                if (component.id === this.$wire.$id) succeed(() => this.$nextTick(() => store.scroll()));
            });
        },
    }));

    /** Floating chat button: remembers (per browser tab) whether the chat was open or already started. */
    window.Alpine.data('chatLauncher', () => {
        const read = (key) => { try { return sessionStorage.getItem(key); } catch (e) { return null; } };
        const write = (key, value) => { try { sessionStorage.setItem(key, value); } catch (e) {} };
        const desktop = window.matchMedia('(min-width: 640px)').matches;

        return {
            open: desktop && read('chat-open') === '1',
            started: read('chat-started') === '1',
            teaser: false,

            init() {
                this.$watch('open', (open) => {
                    write('chat-open', open ? '1' : '0');
                    if (open) {
                        this.started = true;
                        write('chat-started', '1');
                        this.dismissTeaser();
                    }
                });

                if (!this.started && !read('chat-teaser-seen')) setTimeout(() => (this.teaser = !this.open), 3000);
            },

            dismissTeaser() {
                this.teaser = false;
                write('chat-teaser-seen', '1');
            },
        };
    });

    window.Alpine.data('botMessage', (text, animate = true) => ({
        shown: !animate,
        out: animate ? '' : text,
        done: !animate,

        init() {
            if (!animate) return; // carried over from an earlier page

            window.Alpine.store('chatbot').enqueue(text.length, async () => {
                this.shown = true;
                const words = text.split(/(\s+)/);
                const step = Math.max(1, Math.ceil(words.length / 40)); // long messages stream in bigger chunks
                for (let i = 0; i < words.length; i += step) {
                    this.out += words.slice(i, i + step).join('');
                    await sleep(18);
                }
                this.out = text;
                this.done = true;
            });
        },
    }));
});
