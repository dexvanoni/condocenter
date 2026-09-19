"""
Credenciais e autenticacao centralizadas para suites TestSprite/SindCON.

Ordem de carregamento:
  1. Variaveis de ambiente (TEST_BASE_URL, TEST_USER_EMAIL, TEST_USER_PASSWORD)
  2. Arquivo testsprite_tests/.env (gitignored)
  3. testsprite_tests/tmp/config.json (gitignored, gerado pelo TestSprite)

Nunca commitar senhas em arquivos versionados.
"""
from __future__ import annotations

import json
import os
import urllib.parse
from pathlib import Path

import requests

TESTS_DIR = Path(__file__).resolve().parent.parent
PROJECT_ROOT = TESTS_DIR.parent
ENV_FILE = TESTS_DIR / ".env"
CONFIG_FILE = TESTS_DIR / "tmp" / "config.json"
DEFAULT_TIMEOUT = 30


def _load_dotenv(path: Path) -> None:
    if not path.is_file():
        return
    for line in path.read_text(encoding="utf-8").splitlines():
        line = line.strip()
        if not line or line.startswith("#") or "=" not in line:
            continue
        key, value = line.split("=", 1)
        os.environ.setdefault(key.strip(), value.strip().strip('"').strip("'"))


def _load_config_json() -> dict:
    if not CONFIG_FILE.is_file():
        return {}
    try:
        return json.loads(CONFIG_FILE.read_text(encoding="utf-8"))
    except (json.JSONDecodeError, OSError):
        return {}


def load_test_credentials() -> dict[str, str]:
    _load_dotenv(ENV_FILE)
    cfg = _load_config_json()

    base_url = (
        os.environ.get("TEST_BASE_URL")
        or cfg.get("localEndpoint", "").rstrip("/").replace("/login", "")
        or "http://localhost:8000"
    )

    email = (
        os.environ.get("TEST_USER_EMAIL")
        or os.environ.get("TEST_SYNDIC_EMAIL")
        or cfg.get("backendUsername")
        or cfg.get("loginUser")
    )
    password = (
        os.environ.get("TEST_USER_PASSWORD")
        or os.environ.get("TEST_SYNDIC_PASSWORD")
        or cfg.get("backendPassword")
        or cfg.get("loginPassword")
    )

    if not email or not password:
        raise RuntimeError(
            "Credenciais de teste nao configuradas. Defina TEST_USER_EMAIL e TEST_USER_PASSWORD "
            "em testsprite_tests/.env (copie de .env.example) ou use testsprite_tests/tmp/config.json."
        )

    return {
        "base_url": base_url.rstrip("/"),
        "email": email,
        "password": password,
        "morador_email": os.environ.get("TEST_MORADOR_EMAIL", ""),
        "morador_password": os.environ.get("TEST_MORADOR_PASSWORD", ""),
    }


def api_headers(session: requests.Session, base_url: str) -> dict[str, str]:
    token = session.cookies.get("XSRF-TOKEN", "")
    headers = {
        "Accept": "application/json",
        "X-Requested-With": "XMLHttpRequest",
        "Referer": f"{base_url}/dashboard",
    }
    if token:
        headers["X-XSRF-TOKEN"] = urllib.parse.unquote(token)
    return headers


def setup_csrf(session: requests.Session, base_url: str) -> dict[str, str]:
    session.get(f"{base_url}/sanctum/csrf-cookie", timeout=DEFAULT_TIMEOUT)
    return api_headers(session, base_url)


def login(
    session: requests.Session,
    base_url: str,
    email: str,
    password: str,
    profile: str | None = None,
) -> bool:
    session.headers.setdefault("User-Agent", "SindCON-TestSuite/1.0")
    headers = setup_csrf(session, base_url)

    login_resp = session.post(
        f"{base_url}/login",
        json={"email": email, "password": password},
        headers=headers,
        timeout=DEFAULT_TIMEOUT,
    )
    if login_resp.status_code not in (200, 302):
        return False

    if profile:
        headers = api_headers(session, base_url)
        profile_resp = session.post(
            f"{base_url}/profile/set",
            json={"role": profile},
            headers=headers,
            timeout=DEFAULT_TIMEOUT,
        )
        if profile_resp.status_code not in (200, 302):
            return False

    user_resp = session.get(
        f"{base_url}/api/user",
        headers=api_headers(session, base_url),
        timeout=DEFAULT_TIMEOUT,
    )
    return user_resp.status_code == 200


def authenticated_session(profile: str | None = None) -> tuple[requests.Session, dict[str, str]]:
    creds = load_test_credentials()
    session = requests.Session()
    if not login(session, creds["base_url"], creds["email"], creds["password"], profile):
        raise RuntimeError(f"Falha ao autenticar {creds['email']} (perfil: {profile or 'padrao'})")
    return session, creds
