/** @type {import('tailwindcss').Config} */
import defaultTheme from 'tailwindcss/defaultTheme';
import primeui from 'tailwindcss-primeui';
export default {
  // darkMode: 'class',
  content: [
    './resources/**/*.blade.php',
    './resources/**/*.js',
    './resources/**/*.vue',
    './resources/presets/**/*.{js,vue,ts}',
  ],
  theme: {
    extend: {
      fontFamily: {
        sans: ['Rubik', ...defaultTheme.fontFamily.sans],
      },
      colors: {
        'primary-50': 'rgb(var(--p-primary-50) / <alpha-value>)',
        'primary-100': 'rgb(var(--p-primary-100) / <alpha-value>)',
        'primary-200': 'rgb(var(--p-primary-200) / <alpha-value>)',
        'primary-300': 'rgb(var(--p-primary-300) / <alpha-value>)',
        'primary-400': 'rgb(var(--p-primary-400) / <alpha-value>)',
        'primary-500': 'rgb(var(--p-primary-500) / <alpha-value>)',
        'primary-600': 'rgb(var(--p-primary-600) / <alpha-value>)',
        'primary-700': 'rgb(var(--p-primary-700) / <alpha-value>)',
        'primary-800': 'rgb(var(--p-primary-800) / <alpha-value>)',
        'primary-900': 'rgb(var(--p-primary-900) / <alpha-value>)',
        'primary-950': 'rgb(var(--p-primary-950) / <alpha-value>)',
        'surface-0': 'rgb(var(--p-surface-0))',
        'surface-50': 'rgb(var(--p-surface-50))',
        'surface-100': 'rgb(var(--p-surface-100))',
        'surface-200': 'rgb(var(--p-surface-200))',
        'surface-300': 'rgb(var(--p-surface-300))',
        'surface-400': 'rgb(var(--p-surface-400))',
        'surface-500': 'rgb(var(--p-surface-500))',
        'surface-600': 'rgb(var(--p-surface-600))',
        'surface-700': 'rgb(var(--p-surface-700))',
        'surface-800': 'rgb(var(--p-surface-800))',
        'surface-900': 'rgb(var(--p-surface-900))',
        'surface-950': 'rgb(var(--p-surface-950))',
      },
    },
  },

  plugins: [primeui],
};
