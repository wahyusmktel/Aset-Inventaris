import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Roboto', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Telkom Schools Signature Palette: Vibrant Red (#E52320 / #E11D48), Crisp White, Subtle Cool Gray
                primary: {
                    DEFAULT: '#E52320', // Signature Telkom Red
                    foreground: '#FFFFFF',
                    container: '#FFE8E9', // Light pastel red tint
                    'on-container': '#660007',
                    hover: '#CC1513',
                    active: '#B30D10',
                },
                secondary: {
                    DEFAULT: '#334155', // Slate Gray
                    foreground: '#FFFFFF',
                    container: '#F1F5F9',
                    'on-container': '#0F172A',
                },
                tertiary: {
                    DEFAULT: '#991B1B', // Crimson
                    foreground: '#FFFFFF',
                    container: '#FEE2E2',
                    'on-container': '#450A0A',
                },
                error: {
                    DEFAULT: '#DC2626',
                    foreground: '#FFFFFF',
                    container: '#FEE2E2',
                    'on-container': '#450A0A',
                },
                surface: {
                    DEFAULT: '#F8FAFC', // Crisp Slate White-Gray background
                    foreground: '#0F172A', // Dark Slate
                    variant: '#E2E8F0',
                    'on-variant': '#64748B',
                    'container-lowest': '#FFFFFF', // Pure Crisp White
                    'container-low': '#F8FAFC',
                    container: '#F1F5F9',
                    'container-high': '#E2E8F0',
                    'container-highest': '#CBD5E1',
                },
                outline: {
                    DEFAULT: '#94A3B8',
                    variant: '#E2E8F0',
                },
                'inverse-surface': {
                    DEFAULT: '#1E293B',
                    foreground: '#F8FAFC',
                },
                'inverse-primary': '#FFB3B7',
            },
            borderRadius: {
                'm3-xs': '4px',
                'm3-sm': '8px',
                'm3-md': '12px',
                'm3-lg': '16px',
                'm3-xl': '28px',
                'm3-full': '9999px',
            },
            boxShadow: {
                'm3-elevation-1': '0px 1px 3px 1px rgba(0, 0, 0, 0.08), 0px 1px 2px 0px rgba(0, 0, 0, 0.15)',
                'm3-elevation-2': '0px 2px 6px 2px rgba(0, 0, 0, 0.08), 0px 1px 2px 0px rgba(0, 0, 0, 0.15)',
                'm3-elevation-3': '0px 4px 8px 3px rgba(0, 0, 0, 0.08), 0px 1px 3px 0px rgba(0, 0, 0, 0.15)',
                'm3-elevation-4': '0px 6px 10px 4px rgba(0, 0, 0, 0.08), 0px 2px 3px 0px rgba(0, 0, 0, 0.15)',
                'm3-elevation-5': '0px 8px 12px 6px rgba(0, 0, 0, 0.08), 0px 4px 4px 0px rgba(0, 0, 0, 0.15)',
            }
        },
    },

    plugins: [forms],
};
