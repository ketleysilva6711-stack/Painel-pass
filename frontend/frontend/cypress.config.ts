import { defineConfig } from 'cypress'

export default defineConfig({
  e2e: {
    baseUrl: 'http://localhost:5173',
    viewportWidth: 1280,
    viewportHeight: 720,
    video: true,
    screenshotOnRunFailure: true,
    defaultCommandTimeout: 10000,
    requestTimeout: 10000,
    responseTimeout: 30000,
    setupNodeEvents(on, config) {
      // Implement custom tasks if needed
      on('task', {
        log(message) {
          console.log(message)
          return null
        },
        clearDatabase() {
          // Implement database cleanup if needed
          return null
        }
      })
    },
    specPattern: 'cypress/e2e/**/*.cy.{js,ts}',
    supportFile: 'cypress/support/e2e.ts',
  },
  component: {
    devServer: {
      framework: 'vue',
      bundler: 'vite'
    },
    specPattern: 'src/**/*.cy.{js,ts}',
  },
  retries: {
    runMode: 2,
    openMode: 0
  },
  env: {
    apiUrl: 'http://localhost/api',
    testUser: {
      email: 'user@thevault.com',
      password: 'secret123'
    },
    testAdmin: {
      email: 'admin@thevault.com',
      password: 'secret123'
    }
  }
})