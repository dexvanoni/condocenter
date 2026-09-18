const roleEmails = {
  Morador: () => Cypress.env('MORADOR_EMAIL') || 'cypress-morador@test.local',
  Porteiro: () => Cypress.env('PORTEIRO_EMAIL') || 'cypress-porteiro@test.local',
};

function statefulHeaders(extra = {}) {
  const baseUrl = Cypress.config('baseUrl');

  return {
    Referer: baseUrl,
    Origin: baseUrl,
    'X-Requested-With': 'XMLHttpRequest',
    ...extra,
  };
}

function extractCsrfToken(html) {
  const match = html.match(/name="_token"\s+value="([^"]+)"/);
  return match ? match[1] : null;
}

function postForm(url, body) {
  return cy.getCookie('XSRF-TOKEN').then((cookie) => {
    const xsrf = cookie ? decodeURIComponent(cookie.value) : '';

    return cy.request({
      method: 'POST',
      url,
      form: true,
      followRedirect: false,
      headers: {
        ...statefulHeaders({
          'X-XSRF-TOKEN': xsrf,
        }),
      },
      body,
    });
  });
}

/**
 * Login via HTTP (Sanctum stateful + sessão web).
 */
Cypress.Commands.add('loginAs', (role = 'Morador') => {
  const email = roleEmails[role]?.() || roleEmails.Morador();
  const password = Cypress.env('PASSWORD') || 'password';

  cy.session(
    [role, email],
    () => {
      cy.request('/sanctum/csrf-cookie');

      cy.request('/login').then((loginPage) => {
        const token = extractCsrfToken(loginPage.body);
        expect(token, 'CSRF do login').to.be.a('string');

        postForm('/login', {
          email,
          password,
          _token: token,
        }).then((loginRes) => {
          expect(loginRes.status).to.be.oneOf([302, 303]);
        });
      });

      cy.request({ url: '/dashboard', failOnStatusCode: false }).then((dash) => {
        if (dash.status === 200 && dash.body.includes('profile/select')) {
          cy.request('/profile/select').then((profilePage) => {
            const token = extractCsrfToken(profilePage.body);
            postForm('/profile/set', {
              _token: token,
              role,
            });
          });
        }
      });

      cy.request({
        url: '/api/access-control/authorizations',
        failOnStatusCode: false,
        headers: statefulHeaders({ Accept: 'application/json' }),
      }).then((probe) => {
        expect(probe.status, `autenticação API (${role})`).to.eq(200);
      });
    },
    {
      validate() {
        cy.request({
          url: '/api/access-control/authorizations',
          failOnStatusCode: false,
          headers: statefulHeaders({ Accept: 'application/json' }),
        }).its('status').should('eq', 200);
      },
    }
  );
});

Cypress.Commands.add('loginAsMorador', () => cy.loginAs('Morador'));
Cypress.Commands.add('loginAsPorteiro', () => cy.loginAs('Porteiro'));

Cypress.Commands.add('ensureCsrfCookie', () => {
  cy.request('/sanctum/csrf-cookie');
});

Cypress.Commands.add('apiRequest', (method, url, body = {}) => {
  cy.ensureCsrfCookie();

  return cy.getCookie('XSRF-TOKEN').then((cookie) => {
    const token = cookie ? decodeURIComponent(cookie.value) : '';

    return cy.request({
      method,
      url,
      body,
      failOnStatusCode: false,
      headers: statefulHeaders({
        Accept: 'application/json',
        'Content-Type': 'application/json',
        'X-XSRF-TOKEN': token,
      }),
    });
  });
});

Cypress.Commands.add('toDatetimeLocal', (date = new Date()) => {
  const pad = (n) => String(n).padStart(2, '0');
  const d = date instanceof Date ? date : new Date(date);

  return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
});
