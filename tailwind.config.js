import defaultTheme from 'tailwindcss/defaultTheme';

/**
 * Surface + text tokens are driven by CSS variables (see resources/css/app.css)
 * so the SAME utility classes (bg-base-bg, text-ink, …) flip between the dark
 * and light themes with zero markup changes. Accents are shared across themes.
 * Channels are space-separated RGB to support Tailwind's `/<alpha>` modifiers.
 */
const withVar = (v) => `rgb(var(${v}) / <alpha-value>)`;

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './app/**/*.php',
    ],
    theme: {
        extend: {
            colors: {
                base: {
                    bg: withVar('--c-bg'),
                    card: withVar('--c-card'),
                    elevated: withVar('--c-elevated'),
                    border: withVar('--c-border'),
                    tag: withVar('--c-tag'),
                },
                ink: {
                    DEFAULT: withVar('--c-text'),
                    muted: withVar('--c-muted'),
                    link: withVar('--c-link'),
                },
                accent: {
                    cyan: withVar('--c-cyan'),
                    purple: withVar('--c-purple'),
                    success: withVar('--c-green'),
                    orange: withVar('--c-orange'),
                },
            },
            fontFamily: {
                // Active family is overridden per <html lang> in app.css.
                sans: ['Cairo', 'Inter', ...defaultTheme.fontFamily.sans],
                display: ['Cairo', '"Space Grotesk"', ...defaultTheme.fontFamily.sans],
                grotesk: ['"Space Grotesk"', ...defaultTheme.fontFamily.sans],
                cairo: ['Cairo', 'sans-serif'],
                mono: ['"JetBrains Mono"', ...defaultTheme.fontFamily.mono],
            },
            boxShadow: {
                glow: '0 0 0 1px rgba(0,217,245,0.25), 0 8px 30px -8px rgba(0,217,245,0.4)',
                card: '0 1px 0 rgba(255,255,255,0.03) inset, 0 8px 24px -12px rgba(0,0,0,0.5)',
            },
            backgroundImage: {
                'grid-faint':
                    'linear-gradient(to right, rgb(var(--c-border) / 0.35) 1px, transparent 1px), linear-gradient(to bottom, rgb(var(--c-border) / 0.35) 1px, transparent 1px)',
                'accent-gradient': 'linear-gradient(135deg, #00d9f5 0%, #a855f7 100%)',
            },
            keyframes: {
                'fade-up': {
                    '0%': { opacity: '0', transform: 'translateY(8px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                },
                'slide-in-end': {
                    '0%': { transform: 'translateX(100%)', opacity: '0' },
                    '100%': { transform: 'translateX(0)', opacity: '1' },
                },
                'pulse-dot': {
                    '0%, 100%': { opacity: '1', transform: 'scale(1)' },
                    '50%': { opacity: '0.5', transform: 'scale(0.85)' },
                },
            },
            animation: {
                'fade-up': 'fade-up 0.5s cubic-bezier(0.16,1,0.3,1) both',
                'slide-in-end': 'slide-in-end 0.35s cubic-bezier(0.16,1,0.3,1) both',
                'pulse-dot': 'pulse-dot 1.8s ease-in-out infinite',
            },
        },
    },
    plugins: [],
};
