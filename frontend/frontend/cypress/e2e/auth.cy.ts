/// <reference types="cypress" />

describe('Authentication Flow', () => {
  beforeEach(() => {
    cy.visit('/play-fake')
  })

  describe('Login', () => {
    it('should display login page', () => {
      cy.url().should('include', '/play-fake')
      cy.get('input[type="email"]').should('be.visible')
      cy.get('input[type="password"]').should('be.visible')
      cy.get('button[type="submit"]').should('be.visible')
    })

    it('should show error for invalid credentials', () => {
      cy.get('input[type="email"]').type('wrong@email.com')
      cy.get('input[type="password"]').type('wrongpassword')
      cy.get('button[type="submit"]').click()
      
      cy.get('.error-message, .alert-error, [role="alert"]').should('be.visible')
      cy.url().should('include', '/play-fake')
    })

    it('should login successfully as admin', () => {
      cy.loginAsAdmin()
      cy.url().should('include', '/dashboard')
      cy.get('.sidebar, .app-shell').should('be.visible')
    })

    it('should login successfully as user', () => {
      cy.loginAsUser()
      cy.url().should('include', '/dashboard')
    })
  })

  describe('Logout', () => {
    beforeEach(() => {
      cy.loginAsAdmin()
    })

    it('should logout successfully', () => {
      cy.get('.sidebar-footer button, [title="Sair"]').click()
      cy.url().should('include', '/play-fake')
      cy.get('input[type="email"]').should('be.visible')
    })
  })
})

describe('Dashboard & Navigation', () => {
  beforeEach(() => {
    cy.loginAsAdmin()
  })

  describe('Sidebar Navigation', () => {
    it('should display sidebar with brand', () => {
      cy.get('.brand-name').should('contain', 'The Vault Panel')
      cy.get('.brand-version').should('contain', 'v2.0')
    })

    it('should display user info in sidebar footer', () => {
      cy.get('.sidebar-footer').should('be.visible')
      cy.get('.user-name').should('contain', 'Admin Vault')
      cy.get('.user-sub').should('contain', 'Admin')
    })

    it('should display navigation groups', () => {
      cy.get('.nav-eyebrow').should('have.length.at.least', 1)
      cy.get('.nav-item').should('have.length.at.least', 5)
    })
  })

  describe('Feature Flags - Admin Access', () => {
    it('should show admin navigation items', () => {
      cy.get('.nav-item').should('contain', 'Usuários')
      cy.get('.nav-item').should('contain', 'Logs')
      cy.get('.nav-item').should('contain', 'Apps')
    })

    it('should navigate to admin dashboard', () => {
      cy.get('.nav-item').contains('Visão geral').click()
      cy.url().should('include', '/admin/dashboard')
    })

    it('should navigate to admin users', () => {
      cy.get('.nav-item').contains('Usuários').click()
      cy.url().should('include', '/admin/usuarios')
    })

    it('should navigate to logs', () => {
      cy.get('.nav-item').contains('Logs').click()
      cy.url().should('include', '/admin/logs')
    })
  })

  describe('Feature Flags - User Access', () => {
    beforeEach(() => {
      cy.loginAsUser()
    })

    it('should NOT show admin navigation items', () => {
      cy.get('.nav-item').should('not.contain', 'Usuários')
      cy.get('.nav-item').should('not.contain', 'Logs')
      cy.get('.nav-item').should('not.contain', 'Apps')
    })

    it('should show user navigation items', () => {
      cy.get('.nav-item').should('contain', 'Play Fake')
      cy.get('.nav-item').should('contain', 'QR Code')
      cy.get('.nav-item').should('contain', 'Consulta')
    })
  })
})

describe('Module: Consulta', () => {
  beforeEach(() => {
    cy.loginAsUser()
  })

  it('should navigate to consulta page', () => {
    cy.get('.nav-item').contains('Consulta').click()
    cy.url().should('include', '/tools/consulta')
  })

  it('should display consulta form', () => {
    cy.visit('/tools/consulta')
    cy.get('input[placeholder*="CPF"]').should('be.visible')
    cy.get('button[type="submit"]').should('be.visible')
  })

  it('should create consulta and show in history', () => {
    cy.visit('/tools/consulta')
    cy.get('input[placeholder*="CPF"]').type('123.456.789-00')
    cy.get('button[type="submit"]').click()
    
    cy.get('table, .data-table').should('be.visible')
    cy.get('td, .documento').should('contain', '123.456.789-00')
  })
})

describe('Module: Ranking', () => {
  beforeEach(() => {
    cy.loginAsUser()
  })

  it('should navigate to ranking page', () => {
    cy.get('.nav-item').contains('Ranking').click()
    cy.url().should('include', '/tools/ranking')
  })

  it('should display podium', () => {
    cy.visit('/tools/ranking')
    cy.get('.podium-grid, .podium-card').should('be.visible')
    cy.get('.rank-badge').should('have.length.at.least', 1)
  })
})

describe('Module: Play Fake', () => {
  beforeEach(() => {
    cy.loginAsAdmin()
  })

  it('should navigate to play-fake', () => {
    cy.get('.nav-item').contains('Play Fake').click()
    cy.url().should('include', '/tools/play-fake')
  })

  it('should display app list', () => {
    cy.visit('/tools/play-fake')
    cy.get('table, .apps-list').should('be.visible')
  })
})

describe('Module: Bypass', () => {
  beforeEach(() => {
    cy.loginAsAdmin()
  })

  it('should navigate to bypass page', () => {
    cy.get('.nav-item').contains('Bypass').click()
    cy.url().should('include', '/tools/bypass')
  })

  it('should display bypass rules list', () => {
    cy.visit('/tools/bypass')
    cy.get('table, .bypass-list').should('be.visible')
  })
})

describe('Responsive Design', () => {
  beforeEach(() => {
    cy.loginAsAdmin()
  })

  it('should work on mobile viewport', () => {
    cy.viewport('iphone-x')
    cy.visit('/dashboard')
    cy.get('.sidebar-toggle').should('be.visible')
    cy.get('.sidebar').should('not.be.visible')
    
    cy.get('.sidebar-toggle').click()
    cy.get('.sidebar').should('be.visible')
  })

  it('should work on tablet viewport', () => {
    cy.viewport('ipad-2')
    cy.visit('/dashboard')
    cy.get('.sidebar').should('be.visible')
  })
})