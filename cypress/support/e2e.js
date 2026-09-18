import './commands';

before(() => {
  cy.task('ensureTestUsers').then((users) => {
    Cypress.env('MORADOR_EMAIL', users.morador_email);
    Cypress.env('PORTEIRO_EMAIL', users.porteiro_email);
    Cypress.env('PASSWORD', users.password);
  });

});
