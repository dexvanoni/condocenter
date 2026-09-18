describe('Controle de Acesso — API (backend)', () => {
  const scheduledAt = () => {
    const d = new Date();
    d.setHours(d.getHours() + 1);
    return d.toISOString().slice(0, 19).replace('T', ' ');
  };

  const validUntil = () => {
    const d = new Date();
    d.setHours(d.getHours() + 5);
    return d.toISOString().slice(0, 19).replace('T', ' ');
  };

  beforeEach(() => {
    cy.loginAsMorador();
  });

  it('morador lista suas liberações', () => {
    cy.apiRequest('GET', '/api/access-control/authorizations').then((res) => {
      expect(res.status).to.eq(200);
      expect(res.body).to.have.property('data');
    });
  });

  it('morador cria liberação por preset (sem credencial digital)', () => {
    cy.fixture('access-control').then((fx) => {
      cy.apiRequest('POST', '/api/access-control/authorizations', {
        ...fx.presetVisitor,
        scheduled_at: scheduledAt(),
        valid_until: validUntil(),
      }).then((res) => {
        expect(res.status).to.eq(201);
        expect(res.body.has_digital_pass).to.eq(false);
        expect(res.body.authorization.visitor_preset_key).to.eq('ifood');
      });
    });
  });

  it('morador cria visitante "Outro" com credencial digital', () => {
    cy.fixture('access-control').then((fx) => {
      cy.apiRequest('POST', '/api/access-control/authorizations', {
        ...fx.namedVisitor,
        scheduled_at: scheduledAt(),
        valid_until: validUntil(),
      }).then((res) => {
        expect(res.status).to.eq(201);
        expect(res.body.has_digital_pass).to.eq(true);
        expect(res.body.pdf_url).to.include('/api/access-control/authorizations/');
        expect(res.body.authorization.visitor_preset_key).to.eq('other');
      });
    });
  });

  it('visitante "Outro" exige valid_until', () => {
    cy.fixture('access-control').then((fx) => {
      cy.apiRequest('POST', '/api/access-control/authorizations', {
        ...fx.namedVisitor,
        scheduled_at: scheduledAt(),
      }).then((res) => {
        expect(res.status).to.eq(422);
      });
    });
  });

  it('morador registra proibição de visitante', () => {
    cy.fixture('access-control').then((fx) => {
      cy.apiRequest('POST', '/api/access-control/prohibitions', fx.prohibition).then((res) => {
        expect(res.status).to.be.oneOf([200, 201]);
      });
    });
  });

  it('morador cria lista de evento', () => {
    cy.fixture('access-control').then((fx) => {
      cy.apiRequest('POST', '/api/access-control/lists', {
        ...fx.eventList,
        scheduled_at: scheduledAt(),
        valid_until: validUntil(),
      }).then((res) => {
        expect(res.status).to.be.oneOf([200, 201]);
        expect(res.body).to.have.property('list');
      });
    });
  });

  describe('check-in porteiro', () => {
    let seededPin;

    beforeEach(() => {
      cy.loginAsPorteiro();
      cy.task('seedAccessAuthorization', {
        pin: '4821',
        visitorName: 'Check-in Cypress',
      }).then((data) => {
        seededPin = data.pin;
      });
    });

    it('porteiro consulta painel', () => {
      cy.apiRequest('GET', '/api/access-control/porteiro/panel').then((res) => {
        expect(res.status).to.eq(200);
        expect(res.body).to.have.property('authorizations');
      });
    });

    it('check-in por senha libera e mantém credencial ativa', () => {
      cy.apiRequest('POST', '/api/access-control/check-in/pin', {
        access_pin: seededPin,
      }).then((res) => {
        expect(res.status).to.eq(200);
        expect(res.body.authorization.status).to.eq('pending');
        expect(res.body.authorization.visitor_name).to.eq('Check-in Cypress');
      });

      cy.apiRequest('POST', '/api/access-control/check-in/pin', {
        access_pin: seededPin,
      }).then((res) => {
        expect(res.status).to.eq(200);
        expect(res.body.authorization.status).to.eq('pending');
      });
    });

    it('senha inválida retorna erro', () => {
      cy.apiRequest('POST', '/api/access-control/check-in/pin', {
        access_pin: '0000',
      }).then((res) => {
        expect(res.status).to.be.oneOf([404, 422]);
      });
    });
  });
});
