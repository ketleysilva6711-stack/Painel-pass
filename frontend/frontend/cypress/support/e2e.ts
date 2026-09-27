// cypress/support/e2e.ts

/// <reference types="cypress" />

// Import commands
import './commands'

// Global configuration
Cypress.config('defaultCommandTimeout', 10000)
Cypress.config('requestTimeout', 10000)
Cypress.config('responseTimeout', 30000)

// Global beforeEach
beforeEach(() => {
  // Clear localStorage and cookies before each test
  cy.clearLocalStorage()
  cy.clearCookies()
  
  // Set viewport
  cy.viewport(1280, 720)
})

// Handle uncaught exceptions
Cypress.on('uncaught:exception', (err, runnable) => {
  // Ignore specific errors
  if (err.message.includes('ResizeObserver loop limit exceeded')) {
    return false
  }
  if (err.message.includes('Non-Error promise rejection captured')) {
    return false
  }
  return true
})

// Custom commands are defined in commands.ts