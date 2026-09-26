describe('Consultor Financeiro — settings de IA (admin)', () => {
  it('admin seleciona Gemini e OpenAI no condomínio', function () {
    // Requer usuário Administrador autenticado no ambiente Cypress.
    cy.visit('/condominiums');
    cy.get('a[href*="/condominiums/"]').first().click();
    cy.contains('Consultor Financeiro — Inteligência Artificial').should('be.visible');

    cy.get('#ai_provider').select('gemini');
    cy.get('#ai_model').should('contain', 'gemini');
    cy.contains('button', 'Salvar provedor de IA').click();

    cy.get('#ai_provider').select('openai');
    cy.contains('button', 'Salvar provedor de IA').click();
  });
});
