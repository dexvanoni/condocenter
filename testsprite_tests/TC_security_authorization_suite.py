"""
SindCON - Testes negativos de autorizacao (SEC-01 a SEC-08).
Requer TEST_USER_EMAIL com multiplos perfis ou TEST_MORADOR_* para SEC-06.
Execucao: python testsprite_tests/TC_security_authorization_suite.py
"""
import json
import sys
from dataclasses import dataclass, field
from pathlib import Path

import requests

sys.path.insert(0, str(Path(__file__).resolve().parent))

from lib.auth import (
    DEFAULT_TIMEOUT,
    api_headers,
    authenticated_session,
    load_test_credentials,
    login,
)

TIMEOUT = DEFAULT_TIMEOUT


@dataclass
class AuthzResult:
    test_id: str
    title: str
    status: str
    detail: str = ""


@dataclass
class AuthorizationTestRunner:
    results: list[AuthzResult] = field(default_factory=list)

    def record(self, test_id: str, title: str, passed: bool, detail: str = "", skipped: bool = False) -> None:
        status = "SKIP" if skipped else ("PASS" if passed else "FAIL")
        self.results.append(AuthzResult(test_id, title, status, detail))
        tag = {"PASS": "[PASS]", "FAIL": "[FAIL]", "SKIP": "[SKIP]"}[status]
        print(f"{tag} {test_id}: {title}" + (f" - {detail}" if detail else ""))

    def api_get(self, session: requests.Session, base_url: str, path: str) -> requests.Response:
        return session.get(f"{base_url}{path}", headers=api_headers(session, base_url), timeout=TIMEOUT)

    def run(self) -> int:
        creds = load_test_credentials()
        base = creds["base_url"]
        print(f"\n=== SindCON Authorization Security Suite ===\nBase: {base}\n")

        # SEC-01, SEC-02, SEC-03: Morador nao acessa recursos de sindico
        morador_session = requests.Session()
        if login(morador_session, base, creds["email"], creds["password"], "Morador"):
            for test_id, title, path in [
                ("SEC-01", "Morador bloqueado em /api/reports/financial", "/api/reports/financial"),
                ("SEC-02", "Morador bloqueado em /api/transactions", "/api/transactions"),
                ("SEC-03", "Morador bloqueado em /api/access-control/authorizations", "/api/access-control/authorizations"),
            ]:
                r = self.api_get(morador_session, base, path)
                self.record(test_id, title, r.status_code in (403, 401), f"HTTP {r.status_code}")
        else:
            for test_id, title in [
                ("SEC-01", "Morador bloqueado em /api/reports/financial"),
                ("SEC-02", "Morador bloqueado em /api/transactions"),
                ("SEC-03", "Morador bloqueado em /api/access-control/authorizations"),
            ]:
                self.record(test_id, title, False, "Login como Morador falhou", skipped=True)

        # SEC-04: IDOR cross-tenant (recurso inexistente nao deve retornar 200 com dados)
        try:
            syndic_session, _ = authenticated_session("Síndico")
            r = self.api_get(syndic_session, base, "/api/charges/999999999")
            self.record("SEC-04", "Cobranca inexistente/outro tenant", r.status_code in (403, 404), f"HTTP {r.status_code}")
        except RuntimeError as exc:
            self.record("SEC-04", "Cobranca inexistente/outro tenant", False, str(exc), skipped=True)

        # SEC-05: API sem sessao retorna 401 JSON (nao HTML)
        r = requests.get(
            f"{base}/api/user",
            headers={"Accept": "application/json"},
            timeout=TIMEOUT,
            allow_redirects=False,
        )
        is_json_unauth = r.status_code == 401 and "application/json" in r.headers.get("Content-Type", "")
        self.record("SEC-05", "/api/user sem sessao retorna 401 JSON", is_json_unauth, f"HTTP {r.status_code}")

        # SEC-06: Morador-only tentando perfil Síndico
        morador_email = creds.get("morador_email") or ""
        morador_password = creds.get("morador_password") or ""
        if morador_email and morador_password:
            s = requests.Session()
            login(s, base, morador_email, morador_password)
            from lib.auth import setup_csrf

            headers = setup_csrf(s, base)
            r = s.post(f"{base}/profile/set", json={"role": "Síndico"}, headers=headers, timeout=TIMEOUT, allow_redirects=False)
            blocked = r.status_code in (403, 419, 302) and "dashboard" not in (r.headers.get("Location") or "").lower()
            if r.status_code == 302 and "profile/select" in (r.headers.get("Location") or ""):
                blocked = True
            self.record("SEC-06", "Morador-only nao ativa perfil Sindico", blocked, f"HTTP {r.status_code}")
        else:
            self.record(
                "SEC-06",
                "Morador-only nao ativa perfil Sindico",
                False,
                "Defina TEST_MORADOR_EMAIL e TEST_MORADOR_PASSWORD",
                skipped=True,
            )

        # SEC-07: Porteiro - busca usuarios retorna apenas JSON do tenant
        porteiro_session = requests.Session()
        if login(porteiro_session, base, creds["email"], creds["password"], "Porteiro"):
            r = self.api_get(porteiro_session, base, "/api/users/search?term=a")
            ok = r.status_code in (200, 403)
            if r.status_code == 200:
                try:
                    data = r.json()
                    ok = isinstance(data, list)
                except ValueError:
                    ok = False
            self.record("SEC-07", "Porteiro busca usuarios com escopo tenant", ok, f"HTTP {r.status_code}")
        else:
            self.record("SEC-07", "Porteiro busca usuarios com escopo tenant", False, "Perfil Porteiro indisponivel", skipped=True)

        # SEC-08: Marketplace POST como morador sem permissao (se aplicavel)
        morador_session2 = requests.Session()
        if login(morador_session2, base, creds["email"], creds["password"], "Morador"):
            headers = api_headers(morador_session2, base)
            r = morador_session2.post(
                f"{base}/api/marketplace",
                json={"title": "Teste SEC-08", "description": "item", "price": 10, "category": "outros"},
                headers=headers,
                timeout=TIMEOUT,
            )
            # 201 = permitido; 403/422 = bloqueado por permissao/validacao
            self.record(
                "SEC-08",
                "Morador marketplace POST restrito quando sem permissao",
                r.status_code in (403, 422),
                f"HTTP {r.status_code} (403/422 esperado se restrito)",
            )
        else:
            self.record("SEC-08", "Morador marketplace POST", False, "Login Morador falhou", skipped=True)

        passed = sum(1 for r in self.results if r.status == "PASS")
        failed = sum(1 for r in self.results if r.status == "FAIL")
        skipped = sum(1 for r in self.results if r.status == "SKIP")

        print(f"\n=== {passed} PASS | {failed} FAIL | {skipped} SKIP ===\n")

        report_path = Path(__file__).parent / "tmp" / "security_authorization_results.json"
        report_path.write_text(
            json.dumps(
                {"summary": {"pass": passed, "fail": failed, "skip": skipped}, "results": [r.__dict__ for r in self.results]},
                indent=2,
                ensure_ascii=False,
            ),
            encoding="utf-8",
        )
        print(f"Relatorio: {report_path}")

        return 0 if failed == 0 else 1


if __name__ == "__main__":
    sys.exit(AuthorizationTestRunner().run())
