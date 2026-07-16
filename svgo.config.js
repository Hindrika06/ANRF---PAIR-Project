module.exports = {
  js2svg: {
    pretty: false,
  },
  plugins: [
    {
      name: 'preset-default',
      params: {
        overrides: {
          cleanupIds: false,
          removeViewBox: false,
          convertPathData: false,
          convertShapeToPath: false,
          mergePaths: false,
          convertTransform: false,
          removeUselessDefs: false,
          convertColors: false,
          removeUnknownsAndDefaults: false,
        },
      },
    },
  ],
};
