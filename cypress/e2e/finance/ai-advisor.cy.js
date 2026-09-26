describe('Consultor Financeiro', () => {
  beforeEach(() => {
    cy.intercept('POST', '**/financial/consultor/analisar', {
      statusCode: 200,
      body: {
        ok: true,
        from_cache: false,
        question_key: 'financial_health',
        question_title: 'Como está nossa saúde financeira?',
        analysis: {
          titulo: 'Análise financeira do condomínio',
          resumo: 'Os indicadores sugerem estabilidade com pontos de atenção em despesas.',
          pontos_atencao: ['Concentração em energia'],
          recomendacoes: [
            {
              titulo: 'Revisar energia',
              acao: 'Avaliar consumo das áreas comuns',
              motivo: 'Categoria relevante no período',
              impacto: 'Possível economia operacional',
              prioridade: 'alta',
            },
          ],
          observacoes: ['Dados agregados do SindCON'],
        },
        message: null,
      },
    }).as('analyze');
  });

  it('abre o consultor, seleciona pergunta e exibe análise', function () {
    // Requer usuário síndico autenticado no ambiente Cypress (ver ensure-test-users).
    cy.visit('/financial/consultor');
    cy.contains('Consultor Financeiro SindCON').should('be.visible');
    cy.contains('Como está nossa saúde financeira?').click();
    cy.contains('Analisando os indicadores financeiros').should('be.visible');
    cy.wait('@analyze');
    cy.contains('Análise financeira do condomínio').should('be.visible');
    cy.contains('Revisar energia').should('be.visible');
  });
});
