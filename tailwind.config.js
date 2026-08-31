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
                // Material Design 3 (M3) Baseline Dynamic Color Tokens
                primary: {
                    DEFAULT: '#6750A4',
                    foreground: '#FFFFFF',
                    container: '#EADDFF',
                    'on-container': '#21005D',
                    hover: '#5A4397',
                    active: '#4F378B',
                },
                secondary: {
                    DEFAULT: '#625B71',
                    foreground: '#FFFFFF',
                    container: '#E8DEF8',
                    'on-container': '#1D192B',
                },
                tertiary: {
                    DEFAULT: '#7D5260',
                    foreground: '#FFFFFF',
                    container: '#FFD8E4',
                    'on-container': '#31111D',
                },
                error: {
                    DEFAULT: '#B3261E',
                    foreground: '#FFFFFF',
                    container: '#F9DEDC',
                    'on-container': '#410E0B',
                },
                surface: {
                    DEFAULT: '#FEF7FF',
                    foreground: '#1D1B20',
                    variant: '#E7E0EC',
                    'on-variant': '#49454F',
                    'container-lowest': '#FFFFFF',
                    'container-low': '#F7F2FA',
                    container: '#F3EDF7',
                    'container-high': '#ECE6F0',
                    'container-highest': '#E6E0E9',
                },
                outline: {
                    DEFAULT: '#79747E',
                    variant: '#CAC4D0',
                },
                'inverse-surface': {
                    DEFAULT: '#322F35',
                    foreground: '#F5EFF7',
                },
                'inverse-primary': '#D0BCFF',
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
                'm3-elevation-1': '0px 1px 3px 1px rgba(0, 0, 0, 0.15), 0px 1px 2px 0px rgba(0, 0, 0, 0.30)',
                'm3-elevation-2': '0px 2px 6px 2px rgba(0, 0, 0, 0.15), 0px 1px 2px 0px rgba(0, 0, 0, 0.30)',
                'm3-elevation-3': '0px 4px 8px 3px rgba(0, 0, 0, 0.15), 0px 1px 3px 0px rgba(0, 0, 0, 0.30)',
                'm3-elevation-4': '0px 6px 10px 4px rgba(0, 0, 0, 0.15), 0px 2px 3px 0px rgba(0, 0, 0, 0.30)',
                'm3-elevation-5': '0px 8px 12px 6px rgba(0, 0, 0, 0.15), 0px 4px 4px 0px rgba(0, 0, 0, 0.30)',
            }
        },
    },

    plugins: [forms],
};
