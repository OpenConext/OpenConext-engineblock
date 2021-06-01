import cssnano from 'cssnano';

module.exports = {
  plugins: [
    cssnano({
      preset: 'default',
    }),
  ],
};
