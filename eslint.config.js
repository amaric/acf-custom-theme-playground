const js = require('@eslint/js');

module.exports = [
  js.configs.recommended,
  {
    files: ['src/js/**/*.js'],
    languageOptions: {
      ecmaVersion: 'latest',
      sourceType: 'script',
      globals: {
        document: 'readonly',
        window: 'readonly',
        console: 'readonly',
        Swiper: 'readonly',
        jQuery: 'readonly',
        wp: 'readonly',
      },
    },
    rules: {
      'no-unused-vars': 'warn',
      'no-console': 'warn',
    },
  },
  {
    files: ['_dev/tools/**/*.mjs'],
    languageOptions: {
      ecmaVersion: 'latest',
      sourceType: 'module',
      globals: {
        process: 'readonly',
        console: 'readonly',
        Buffer: 'readonly',
        URL: 'readonly',
        fetch: 'readonly',
        WebSocket: 'readonly',
        setTimeout: 'readonly',
      },
    },
  },
  {
    ignores: ['dist/**', 'node_modules/**', 'assets/**', '_dev/tools/visual/out/**', '_dev/release/**', '_dev/examples/**'],
  },
];
