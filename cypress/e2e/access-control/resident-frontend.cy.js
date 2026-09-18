describe('Controle de Acesso — Morador (frontend)', () => {
  beforeEach(() => {
    cy.loginAsMorador();
    cy.visit('/access-control', { failOnStatusCode: false });
    cy.url().should('include', '/access-control');
  });

  it('carrega a página de liberações', () => {
    cy.contains('h2', 'Liberações de Acesso').should('be.visible');
    cy.get('#formAuth').should('be.visible');
    cy.get('#tabAuth').should('have.class', 'active');
  });

  it('exibe campos do visitante conhecido ao selecionar preset', () => {
    cy.get('#preset-outro').check({ force: true });
    cy.get('#authOtherNameWrap').should('be.visible');
    cy.get('#authValidUntilWrap').should('be.visible');
    cy.get('#authOtherInfo').should('be.visible');
    cy.get('#authMoreOptionsWrap').should('have.class', 'd-none');
  });

  it('cria liberação por preset (Uber/iFood)', () => {
    cy.toDatetimeLocal(new Date(Date.now() + 3600000)).then((scheduled) => {
      cy.get('input[name="visitor_preset"]').first().check({ force: true });
      cy.get('#authScheduledAt').clear().type(scheduled);
      cy.get('#btnSubmitAuth').click();

      cy.get('#accessFeedbackAlert', { timeout: 20000 })
        .should('be.visible')
        .and('not.have.class', 'd-none');
    });
  });

  it('cria visitante conhecido e abre modal com PDF', () => {
    cy.toDatetimeLocal(new Date(Date.now() + 3600000)).then((scheduled) => {
      cy.toDatetimeLocal(new Date(Date.now() + 5 * 3600000)).then((validUntil) => {
        cy.get('#preset-outro').check({ force: true });
        cy.get('#authOtherNameWrap input').type('Maria Cypress E2E');
        cy.get('#authScheduledAt').clear().type(scheduled);
        cy.get('#authValidUntil').clear().type(validUntil);
        cy.get('#btnSubmitAuth').click();

        cy.get('#visitorCredentialModal', { timeout: 20000 }).should('be.visible');
        cy.get('#credentialVisitorName').should('contain', 'Maria Cypress E2E');
        cy.get('#credentialPdfDownload').should('have.attr', 'href').and('include', '/pdf');
      });
    });
  });

  it('valida nome obrigatório no visitante conhecido', () => {
    cy.intercept('POST', '/api/access-control/authorizations').as('createAuth');

    cy.toDatetimeLocal(new Date(Date.now() + 3600000)).then((scheduled) => {
      cy.toDatetimeLocal(new Date(Date.now() + 5 * 3600000)).then((validUntil) => {
        cy.get('#preset-outro').check({ force: true });
        cy.get('input[name="visitor_preset"][value="__other__"]').should('be.checked');
        cy.get('#authOtherNameWrap').should('be.visible');
        cy.get('#authScheduledAt').clear().type(scheduled, { force: true });
        cy.get('#authValidUntil').clear().type(validUntil, { force: true });
        cy.get('#btnSubmitAuth').click();

        cy.get('input[name="visitor_name_other"]:invalid').should('exist');
        cy.get('@createAuth.all').should('have.length', 0);
      });
    });
  });

  it('aba histórico carrega liberações', () => {
    cy.contains('button.nav-link', 'Minhas liberações').click();
    cy.get('#tabHistory').should('have.class', 'active');
    cy.get('#historyContainer', { timeout: 15000 })
      .should('not.contain', 'Carregando...');
  });

  it('registra proibição rápida', () => {
    cy.get('#formProhibition input[name="visitor_name"]').type('Bloqueado Cypress');
    cy.get('#prohibitionNeverExpires').check({ force: true });
    cy.get('#btnSubmitProhibition').click();

    cy.get('#accessFeedbackAlert', { timeout: 15000 }).should('be.visible');
  });
});
