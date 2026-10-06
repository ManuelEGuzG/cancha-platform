import js from '@eslint/js'
import vue from 'eslint-plugin-vue'
import { withVueTs, vueTsConfigs } from '@vue/eslint-config-typescript'
import globals from 'globals'

const sourceFiles = ['**/*.{js,cjs,mjs,ts,tsx,vue}']

export default withVueTs(
  {
    ignores: [
      '**/.DS_Store',
      '**/node_modules/**',
      '**/coverage/**',
      '**/dist/**',
      '**/ios/**',
      '**/android/**',
      '**/.env.local',
      '**/.env.*.local',
      '**/npm-debug.log*',
      '**/yarn-debug.log*',
      '**/yarn-error.log*',
      '**/.idea/**',
      '**/.vscode/**',
      '**/*.suo',
      '**/*.ntvs*',
      '**/*.njsproj',
      '**/*.sln',
      '**/*.sw?',
    ],
  },
  {
    files: sourceFiles,
    languageOptions: {
      globals: {
        ...globals.browser,
        ...globals.node,
      },
    },
  },
  js.configs.recommended,
  vue.configs['flat/essential'],
  vueTsConfigs.recommended,
  {
    files: sourceFiles,
    rules: {
      'no-console': process.env.NODE_ENV === 'production' ? 'warn' : 'off',
      'no-debugger': process.env.NODE_ENV === 'production' ? 'warn' : 'off',
      'vue/no-deprecated-slot-attribute': 'off',
      '@typescript-eslint/no-explicit-any': 'off',
    },
  },
  {
    files: ['tests/e2e/**/*.{ts,tsx}'],
    languageOptions: {
      globals: {
        cy: 'readonly',
        Cypress: 'readonly',
      },
    },
  },
)