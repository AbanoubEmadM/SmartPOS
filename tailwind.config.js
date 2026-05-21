/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./app/Filament/**/*.php",
        "./app/Livewire/**/*.php",

        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",

        "./vendor/filament/**/*.blade.php",
        "./vendor/filament/**/*.js",
    ],

    theme: {
        extend: {},
    },

    plugins: [],
}
