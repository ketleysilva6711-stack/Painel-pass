// cypress/support/commands.ts

/// <reference types="cypress" />

declare global {
  namespace Cypress {
    interface Chainable {
      login(email: string, password: string): Chainable<void>
      loginAsAdmin(): Chainable<void>
      loginAsUser(): Chainable<void>
      loginAsRevendedor(): Chainable<void>
      getByTestId(dataTestId: string): Chainable<JQuery<HTMLElement>>
      shouldHaveFuncionalidade(flag: string): Chainable<void>
    }
  }
}

// Login command
Cypress.Commands.add('login', (email: string, password: string) => {
  cy.session([email, password], () => {
    cy.visit('/play-fake')
    cy.get('input[type="email"]').type(email)
    cy.get('input[type="password"]').type(password)
    cy.get('button[type="submit"]').click()
    cy.url().should('include', '/dashboard')
    cy.window().its('localStorage.auth_token').should('exist')
  })
})

// Predefined login commands
Cypress.Commands.add('loginAsAdmin', () => {
  cy.login(Cypress.env('testAdmin').email, Cypress.env('testAdmin').password)
})

Cypress.Commands.add('loginAsUser', () => {
  cy.login(Cypress.env('testUser').email, Cypress.env('testUser').password)
})

Cypress.Commands.add('loginAsRevendedor', () => {
  cy.login(Cypress.env('testRevendedor').email, Cypress.env('testRevendedor').password)
})

// Custom selector by data-testid
Cypress.Commands.add('getByTestId', (dataTestId: string) => {
  cy.get(`[data-testid="${dataTestId}"]`)
})

// Check if user has funcionalidade
Cypress.Commands.add('shouldHaveFuncionalidade', { prevSubject: 'optional' }, (subject, flag) => {
  cy.window().then((window) => {
    const store = window.useAuthStore?.()
    if (store && store.hasFuncionalidade) {
      expect(store.hasFuncionalidade(flag)).to.be.true
    }
  })
})

// API helpers
Cypress.Commands.add('apiRequest', (method: string, endpoint: string, body?: any) => {
  return cy.request({
    method,
    url: `${Cypress.env('apiUrl')}${endpoint}`,
    headers: {
      'Authorization': `Bearer ${window.localStorage.getItem('auth_token')}`,
      'Content-Type': 'application/json'
    },
    body,
    failOnStatusCode: false
  })
})

Cypress.Commands.add('apiGet', (endpoint: string) => {
  return cy.apiRequest('GET', endpoint)
})

Cypress.Commands.add('apiPost', (endpoint: string, body: any) => {
  return cy.apiRequest('POST', endpoint, body)
})

Cypress.Commands.add('apiPut', (endpoint: string, body: any) => {
  return cy.apiRequest('PUT', endpoint, body)
})

Cypress.Commands.add('apiDelete', (endpoint: string) => {
  return cy.apiRequest('DELETE', endpoint)
})

// Navigation helpers
Cypress.Commands.add('navigateTo', (path: string) => {
  cy.visit(path)
  cy.url().should('include', path)
})

// Assertion helpers
Cypress.Commands.add('shouldBeVisible', { prevSubject: 'optional' }, (subject, selector) => {
  if (subject) {
    return cy.wrap(subject).find(selector).should('be.visible')
  }
  return cy.get(selector).should('be.visible')
})

Cypress.Commands.add('shouldNotExist', { prevSubject: 'optional' }, (subject, selector) => {
  if (subject) {
    return cy.wrap(subject).find(selector).should('not.exist')
  }
  return cy.get(selector).should('not.exist')
})

// Form helpers
Cypress.Commands.add('fillForm', (formData: Record<string, string>) => {
  Object.entries(formData).forEach(([selector, value]) => {
    cy.get(selector).clear().type(value)
  })
})

Cypress.Commands.add('submitForm', (selector: string = 'form') => {
  cy.get(selector).submit()
})