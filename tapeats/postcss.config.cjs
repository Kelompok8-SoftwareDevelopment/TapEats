module.exports = {
<<<<<<< Updated upstream
  plugins: {
    '@tailwindcss/postcss': {},
    autoprefixer: {},
  },
};
=======
  plugins: [
    require('@tailwindcss/postcss'),
    require('autoprefixer'),
  ]
}
>>>>>>> Stashed changes
