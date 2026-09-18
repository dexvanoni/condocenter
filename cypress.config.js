import { defineConfig } from 'cypress';
import { execSync } from 'node:child_process';

export default defineConfig({
  e2e: {
    baseUrl: process.env.CYPRESS_BASE_URL || 'http://127.0.0.1:8011',
    viewportWidth: 1280,
    viewportHeight: 800,
    defaultCommandTimeout: 15000,
    requestTimeout: 20000,
    video: false,
    screenshotOnRunFailure: true,
    setupNodeEvents(on, config) {
      on('task', {
        ensureTestUsers() {
          const php = process.env.CYPRESS_PHP_PATH || 'php';
          const cwd = config.projectRoot;
          const output = execSync(`${php} cypress/scripts/ensure-test-users.php`, {
            cwd,
            encoding: 'utf8',
          }).trim();

          return JSON.parse(output);
        },
        seedAccessAuthorization({ pin = '4821', visitorName = 'Visitante Cypress' }) {
          const php = process.env.CYPRESS_PHP_PATH || 'php';
          const cwd = config.projectRoot;
          const payload = JSON.stringify({ pin, visitorName });

          const output = execSync(
            `${php} cypress/scripts/seed-access-authorization.php ${Buffer.from(payload).toString('base64')}`,
            { cwd, encoding: 'utf8' }
          ).trim();

          return JSON.parse(output);
        },
      });

      return config;
    },
    specPattern: 'cypress/e2e/**/*.cy.{js,jsx,ts,tsx}',
    supportFile: 'cypress/support/e2e.js',
  },
});
