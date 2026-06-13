import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

document.addEventListener('alpine:init', () => {
    Alpine.store('app', {
        theme: localStorage.getItem('kh_theme') || 'dark',
        lang: document.documentElement.getAttribute('lang') || 'ar',

        init() {
            this.applyTheme();
        },

        toggleTheme() {
            this.theme = this.theme === 'dark' ? 'light' : 'dark';
            localStorage.setItem('kh_theme', this.theme);
            this.applyTheme();
        },

        applyTheme() {
            const el = document.documentElement;
            el.classList.toggle('dark', this.theme === 'dark');
            el.classList.toggle('light', this.theme === 'light');
        },

        toggleLang() {
            // Switch the server locale and reload so EVERYTHING translates
            // consistently — both client t()/x-t parts and server __()/lf()
            // content. (A pure no-reload switch left server-rendered sections
            // untranslated, which is worse UX than a quick reload.)
            const next = this.lang === 'ar' ? 'en' : 'ar';
            localStorage.setItem('kh_lang', next);
            window.location.href = '/lang/' + next;
        },

        applyLang() {
            const el = document.documentElement;
            el.setAttribute('lang', this.lang);
            el.setAttribute('dir', this.lang === 'ar' ? 'rtl' : 'ltr');
        },

        // Live (no-reload) translation lookup; dictionaries injected per page.
        t(key) {
            const dict = (window.kh_i18n && window.kh_i18n[this.lang]) || {};
            return dict[key] ?? key;
        },
    });

    /* ---- Premium Chunk D: special features ---- */

    // Thin scroll-progress bar (top of the viewport).
    Alpine.data('readingProgress', () => ({
        width: 0,
        update() {
            const el = document.documentElement;
            const scrollable = el.scrollHeight - el.clientHeight;
            this.width = scrollable > 0 ? (el.scrollTop / scrollable) * 100 : 0;
        },
    }));

    // "Online now" counter — polls the public stats endpoint.
    Alpine.data('onlineCounter', () => ({
        count: null,
        async fetchCount() {
            try {
                const res = await fetch('/api/online', { headers: { Accept: 'application/json' } });
                if (res.ok) {
                    this.count = (await res.json()).online;
                }
            } catch (_) { /* offline — keep last value */ }
        },
        init() {
            this.fetchCount();
            setInterval(() => this.fetchCount(), 30000);
        },
    }));

    // Hidden terminal easter egg. Open with the backtick (`) key, Ctrl+`,
    // or by typing "sudo". Reads a small data blob injected per page.
    Alpine.data('terminalEgg', () => ({
        open: false,
        input: '',
        lines: [],
        typed: '',
        data() { return window.kh_terminal || {}; },

        boot() {
            this.lines = [
                { t: 'out', v: `${this.data().name || 'guest'} portfolio — interactive shell` },
                { t: 'out', v: "type 'help' for commands · 'exit' to close" },
            ];
        },

        toggle() {
            this.open = !this.open;
            if (this.open) {
                if (!this.lines.length) this.boot();
                this.$nextTick(() => this.$refs.cmd && this.$refs.cmd.focus());
            }
        },

        onKey(e) {
            const tag = (e.target.tagName || '').toLowerCase();
            const typing = tag === 'input' || tag === 'textarea' || e.target.isContentEditable;
            if (typing && !this.open) {
                // Track a typed "sudo" trigger only outside form fields handled above.
                return;
            }
            if (!typing && (e.key === '`' || (e.ctrlKey && e.key === '`'))) {
                e.preventDefault();
                this.toggle();
                return;
            }
            if (!typing) {
                this.typed = (this.typed + e.key).slice(-4);
                if (this.typed.toLowerCase() === 'sudo') {
                    this.typed = '';
                    if (!this.open) this.toggle();
                }
            }
            if (e.key === 'Escape' && this.open) this.toggle();
        },

        run() {
            const raw = this.input.trim();
            this.input = '';
            if (!raw) return;
            this.lines.push({ t: 'cmd', v: raw });
            const [cmd, ...args] = raw.toLowerCase().split(/\s+/);
            const d = this.data();
            const out = (v) => this.lines.push({ t: 'out', v });

            switch (cmd) {
                case 'help':
                    out('available: about · skills · projects · blog · contact · social · theme · lang · clear · exit');
                    break;
                case 'about': case 'whoami':
                    out(`${d.name || ''} — ${d.role || ''}`);
                    if (d.bio) out(d.bio);
                    break;
                case 'skills':
                    out((d.skills && d.skills.length) ? d.skills.join(', ') : 'no skills listed');
                    break;
                case 'projects':
                    out('opening /projects …'); window.location.href = '/projects';
                    break;
                case 'blog':
                    out('opening /blog …'); window.location.href = '/blog';
                    break;
                case 'contact':
                    out('jumping to contact …'); this.toggle();
                    document.getElementById('contact')?.scrollIntoView({ behavior: 'smooth' });
                    break;
                case 'social':
                    (d.socials && d.socials.length) ? d.socials.forEach((s) => out(`${s.label}: ${s.url}`)) : out('no links');
                    break;
                case 'theme':
                    if (args[0] === 'dark' || args[0] === 'light') {
                        if (this.$store.app.theme !== args[0]) this.$store.app.toggleTheme();
                        out(`theme → ${args[0]}`);
                    } else { this.$store.app.toggleTheme(); out(`theme → ${this.$store.app.theme}`); }
                    break;
                case 'lang':
                    out('switching language …'); this.$store.app.toggleLang();
                    break;
                case 'clear': case 'cls':
                    this.lines = [];
                    break;
                case 'sudo':
                    out('nice try. 😏 you already have root here.');
                    break;
                case 'ls':
                    out('about  skills  projects  blog  contact  social');
                    break;
                case 'exit': case 'quit': case 'close':
                    this.toggle();
                    break;
                default:
                    out(`command not found: ${cmd} — try 'help'`);
            }
            this.$nextTick(() => { const b = this.$refs.body; if (b) b.scrollTop = b.scrollHeight; });
        },
    }));
});

Alpine.start();
