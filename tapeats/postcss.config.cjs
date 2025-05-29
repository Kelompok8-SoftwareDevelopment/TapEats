module.exports = {
<<<<<<< Updated upstream
  plugins: {
    tailwindcss: {},
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
