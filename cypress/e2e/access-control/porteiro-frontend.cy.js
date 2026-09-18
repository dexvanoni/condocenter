describe('Controle de Acesso — Porteiro (frontend)', () => {
  let seededPin = '4821';

  beforeEach(() => {
    cy.task('seedAccessAuthorization', {
      pin: seededPin,
      visitorName: 'Porteiro UI Cypress',
    });

    cy.loginAsPorteiro();
    cy.visit('/access-control/porteiro');
    cy.get('#loadingPanel', { timeout: 45000 }).should('have.class', 'd-none');
  });

  it('carrega painel da portaria com liberação rápida', () => {
    cy.contains('Liberação rápida').should('be.visible');
    cy.get('#access-checkin-app').should('exist');
    cy.get('.porteiro-brand__title').should('contain', 'Portaria');
    cy.get('.access-checkin__btn--pin', { timeout: 30000 }).should('be.visible');
  });

  it('abre formulário de senha e rejeita PIN inválido', () => {
    cy.get('.access-checkin__btn--pin', { timeout: 30000 }).click();
    cy.get('.access-checkin__pin').should('be.visible').type('0000');
    cy.contains('button', 'LIBERAR ENTRADA').click();

    cy.get('.access-checkin .alert-danger', { timeout: 15000 }).should('be.visible');
  });

  it('check-in por senha exibe sucesso', () => {
    cy.get('.access-checkin__btn--pin', { timeout: 30000 }).click();
    cy.get('.access-checkin__pin').clear().type(seededPin);
    cy.contains('button', 'LIBERAR ENTRADA').click();

    cy.get('.access-checkin__success', { timeout: 45000 }).should('be.visible');
    cy.contains('Portão liberado').should('be.visible');
    cy.contains('Porteiro UI Cypress').should('be.visible');
  });

  it('grid do painel lista liberações pendentes', () => {
    cy.get('#cardsGrid', { timeout: 20000 }).should('not.have.class', 'd-none');
    cy.get('.access-card', { timeout: 20000 }).should('have.length.at.least', 1);
  });
});
