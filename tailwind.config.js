/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    fontFamily: {
      sans: ['Montserrat', 'sans-serif']
    },
    extend: {},
  },
  plugins: [
    require( 'autoprefixer' ),
    require( 'tailwindcss' ),
  ],
}