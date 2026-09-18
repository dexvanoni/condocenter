import { spawn, execSync } from 'node:child_process';
import http from 'node:http';

const php = process.env.CYPRESS_PHP_PATH || 'php';
const host = '127.0.0.1';
const startPort = Number(process.env.CYPRESS_SERVER_PORT || 8011);

function sleep(ms) {
  return new Promise((resolve) => setTimeout(resolve, ms));
}

function ping(baseUrl) {
  return new Promise((resolve) => {
    http
      .get(baseUrl, (response) => {
        response.resume();
        resolve(response.statusCode && response.statusCode < 500);
      })
      .on('error', () => resolve(false));
  });
}

async function findOrStartServer() {
  for (let port = startPort; port < startPort + 10; port += 1) {
    const baseUrl = `http://${host}:${port}`;

    if (await ping(baseUrl)) {
      console.warn(`Porta ${port} já em uso; tentando próxima...`);
      continue;
    }

    const env = {
      ...process.env,
      APP_URL: baseUrl,
      CYPRESS_BASE_URL: baseUrl,
      CYPRESS_PHP_PATH: php,
    };

    console.log(`Iniciando servidor Laravel em ${baseUrl}...`);
    const server = spawn(php, ['artisan', 'serve', `--host=${host}`, `--port=${port}`], {
      env,
      stdio: 'inherit',
      shell: true,
    });

    for (let attempt = 0; attempt < 30; attempt += 1) {
      if (await ping(baseUrl)) {
        return { baseUrl, server };
      }
      await sleep(1000);
    }

    server.kill();
    throw new Error(`Servidor não ficou pronto em ${baseUrl}.`);
  }

  throw new Error('Não foi possível encontrar porta livre para o Cypress.');
}

let server;

function shutdown() {
  if (server && !server.killed) {
    server.kill();
  }
}

process.on('SIGINT', () => {
  shutdown();
  process.exit(1);
});

try {
  const { baseUrl, server: startedServer } = await findOrStartServer();
  server = startedServer;

  const env = {
    ...process.env,
    APP_URL: baseUrl,
    CYPRESS_BASE_URL: baseUrl,
    CYPRESS_PHP_PATH: php,
  };

  execSync('npx cypress run --spec "cypress/e2e/access-control/**/*.cy.js"', {
    stdio: 'inherit',
    env,
  });
} finally {
  shutdown();
}
