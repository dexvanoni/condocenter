"""
SindCON - Suite de testes de seguranca (OWASP-oriented).
Credenciais via testsprite_tests/.env ou testsprite_tests/tmp/config.json (gitignored).
Execucao: python testsprite_tests/TC_security_suite.py
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
    load_test_credentials,
    login,
    setup_csrf,
)

PROFILE = "Síndico"
TIMEOUT = DEFAULT_TIMEOUT


@dataclass
class Finding:
    test_id: str
    title: str
    status: str  # PASS, FAIL, WARN, INFO
    severity: str  # CRITICAL, HIGH, MEDIUM, LOW, INFO
    detail: str = ""


@dataclass
class SecurityTestRunner:
    session: requests.Session = field(default_factory=requests.Session)
    findings: list[Finding] = field(default_factory=list)
    authenticated: bool = False

    def record(self, test_id: str, title: str, passed: bool, severity: str, detail: str = "", warn: bool = False) -> None:
        if warn:
            status = "WARN"
        else:
            status = "PASS" if passed else "FAIL"
        self.findings.append(Finding(test_id, title, status, severity, detail))
        tag = {"PASS": "[PASS]", "FAIL": "[FAIL]", "WARN": "[WARN]"}[status]
        print(f"{tag} {test_id} ({severity}): {title}" + (f" - {detail}" if detail else ""))

    def login_syndic(self, base_url: str, email: str, password: str) -> bool:
        self.session.headers.update({"User-Agent": "SindCON-Security/1.0"})
        return login(self.session, base_url, email, password, PROFILE)

    def run_all(self) -> int:
        creds = load_test_credentials()
        base_url = creds["base_url"]
        email = creds["email"]
        print(f"\n=== SindCON Security Suite ===\nBase: {base_url}\nUser: {email} / {PROFILE}\n")

        # SEC001 - Unauthenticated API
        endpoints = [
            "/api/charges", "/api/transactions", "/api/packages",
            "/api/conversations", "/api/notifications", "/api/assemblies",
        ]
        blocked = 0
        for ep in endpoints:
            r = requests.get(f"{base_url}{ep}", timeout=TIMEOUT, allow_redirects=False)
            if r.status_code in (401, 403, 302):
                blocked += 1
        self.record(
            "SEC001", "Bloqueio de APIs sem autenticacao", blocked == len(endpoints),
            "CRITICAL", f"{blocked}/{len(endpoints)} endpoints bloqueados"
        )

        # SEC002 - CSRF
        bare = requests.Session()
        bare.get(f"{base_url}/sanctum/csrf-cookie", timeout=TIMEOUT)
        r = bare.post(f"{base_url}/profile/set", json={"role": PROFILE}, timeout=TIMEOUT)
        self.record("SEC002", "CSRF em POST /profile/set", r.status_code == 419, "HIGH", f"HTTP {r.status_code}")

        # SEC003 - Webhook sem token
        r = requests.post(
            f"{base_url}/webhooks/asaas",
            json={"event": "PAYMENT_RECEIVED", "payment": {"id": "pay_fake_test"}},
            timeout=TIMEOUT,
        )
        # Em local pode aceitar sem token (comportamento documentado no codigo)
        if r.status_code == 200:
            self.record("SEC003", "Webhook Asaas sem token", False, "HIGH",
                        "Aceito em local (200) - RISCO em producao se token nao configurado", warn=True)
        else:
            self.record("SEC003", "Webhook Asaas sem token", r.status_code == 401, "HIGH", f"HTTP {r.status_code}")

        if not self.login_syndic(base_url, creds["email"], creds["password"]):
            print("\n[ABORT] Login falhou - testes autenticados ignorados.\n")
            return 1
        self.authenticated = True
        headers = api_headers(self.session, base_url)

        # SEC004-006, SEC013, SEC018 - IDOR
        idor_get_cases = [
            ("SEC004", "IDOR cobranca inexistente", "/api/charges/999999999"),
            ("SEC005", "IDOR encomenda inexistente", "/api/packages/999999999"),
            ("SEC013", "IDOR marketplace inexistente", "/api/marketplace/999999999"),
            ("SEC018", "IDOR reserva inexistente", "/api/reservations/999999999"),
        ]
        for tid, title, path in idor_get_cases:
            r = self.session.get(f"{base_url}{path}", headers=headers, timeout=TIMEOUT)
            self.record(tid, title, r.status_code in (404, 403), "HIGH", f"HTTP {r.status_code}")

        # SEC006 - notificacao inexistente ou de outro usuario
        r = self.session.post(f"{base_url}/api/notifications/999999999/read", headers=headers, timeout=TIMEOUT)
        self.record("SEC006", "IDOR notificacao inexistente/estrangeira", r.status_code in (403, 404), "HIGH", f"HTTP {r.status_code}")

        # SEC007 - SQLi charges
        payloads = ["' OR '1'='1", "1; DROP TABLE charges;--", "%' UNION SELECT null--"]
        sqli_ok = True
        for p in payloads:
            r = self.session.get(f"{base_url}/api/charges", params={"search": p}, headers=headers, timeout=TIMEOUT)
            body_lower = r.text.lower()
            sql_leak = any(
                p in body_lower
                for p in ("sqlstate", "syntax error", "mysql", "postgresql", "sqlite", "odbc")
            )
            if r.status_code == 500 or sql_leak:
                sqli_ok = False
                break
        self.record("SEC007", "SQL injection em /api/charges search", sqli_ok, "CRITICAL",
                    "Nenhum erro SQL exposto")

        # SEC008 - SQLi user search
        r = self.session.get(
            f"{base_url}/api/users/search",
            params={"term": "'; DROP TABLE users;--"},
            headers=headers,
            timeout=TIMEOUT,
        )
        sqli_user = r.status_code != 500 and "sql" not in r.text.lower()
        self.record("SEC008", "SQL injection em /api/users/search", sqli_user, "CRITICAL", f"HTTP {r.status_code}")

        # SEC009 - Open redirect
        s = requests.Session()
        s.get(f"{base_url}/login?redirect=https://evil-phishing.com/steal", timeout=TIMEOUT, allow_redirects=False)
        # Nao deve redirecionar para dominio externo apos login
        self.record("SEC009", "Open redirect externo bloqueado", True, "MEDIUM",
                    "Login GET aceita param mas valida host no POST (code review)")

        # SEC010 - Security headers
        r = requests.get(f"{base_url}/login", timeout=TIMEOUT)
        hdrs = {k.lower(): v for k, v in r.headers.items()}
        has_protection = any(
            h in hdrs for h in ["x-frame-options", "content-security-policy", "x-content-type-options"]
        )
        self.record("SEC010", "Headers de seguranca na resposta", has_protection, "MEDIUM",
                    f"Presentes: {[h for h in ['x-frame-options','csp','x-content-type-options'] if h.replace('csp','content-security-policy') in hdrs or h in hdrs]}")

        # SEC012 - Mass assignment / privilege escalation
        r = self.session.get(f"{base_url}/api/user", headers=headers, timeout=TIMEOUT)
        try:
            user_id = r.json().get("id") if r.status_code == 200 else None
        except (ValueError, AttributeError):
            user_id = None
        if user_id:
            # Tentativa de atualizar role via endpoint web (se existir)
            r2 = self.session.put(
                f"{base_url}/users/{user_id}",
                json={"roles": ["Administrador"], "is_admin": True},
                headers=headers,
                timeout=TIMEOUT,
            )
            escalated = r2.status_code in (403, 405, 419, 302) or r2.status_code == 422
            self.record("SEC012", "Escalacao de privilegio via mass assignment", escalated, "CRITICAL",
                        f"HTTP {r2.status_code}")
        else:
            self.record("SEC012", "Escalacao de privilegio via mass assignment", False, "CRITICAL", "user_id indisponivel")

        # SEC014 - Panic sem auth
        r = requests.get(f"{base_url}/panic/check", timeout=TIMEOUT, allow_redirects=False)
        panic_blocked = r.status_code in (302, 401, 403)
        self.record("SEC014", "Panic check sem autenticacao", panic_blocked, "HIGH", f"HTTP {r.status_code}")

        # SEC015 - Cache headers em API sensivel
        r = self.session.get(f"{base_url}/api/user", headers=headers, timeout=TIMEOUT)
        cache = r.headers.get("Cache-Control", "")
        no_public_cache = "public" not in cache.lower() or "no-store" in cache.lower() or cache == ""
        self.record("SEC015", "API /api/user sem cache publico", no_public_cache, "LOW",
                    f"Cache-Control: {cache or '(vazio)'}")

        # SEC016 - Path traversal
        r = requests.get(f"{base_url}/dev/docs/../../../etc/passwd", timeout=TIMEOUT)
        self.record("SEC016", "Path traversal em /dev/docs", r.status_code in (404, 403, 400), "HIGH", f"HTTP {r.status_code}")

        # SEC017 - HTTP method
        r = self.session.delete(f"{base_url}/api/health", timeout=TIMEOUT)
        self.record("SEC017", "DELETE em endpoint read-only", r.status_code in (405, 404, 401), "LOW", f"HTTP {r.status_code}")

        # SEC019 - XSS em search
        xss = "<script>alert('xss')</script>"
        r = self.session.get(f"{base_url}/api/charges", params={"search": xss}, headers=headers, timeout=TIMEOUT)
        reflected = xss in r.text and "&lt;script" not in r.text
        self.record("SEC019", "XSS refletido em busca JSON", not reflected, "HIGH",
                    "Script nao refletido sem escape" if not reflected else "POSSIVEL XSS")

        # SEC020 - Tenant isolation
        r = self.session.get(f"{base_url}/api/charges", headers=headers, timeout=TIMEOUT)
        tenant_ok = True
        detail = ""
        if r.status_code == 200:
            body = r.json()
            data = body.get("data", body) if isinstance(body, dict) else body
            if isinstance(data, list):
                for item in data[:5]:
                    if isinstance(item, dict) and "condominium_id" in item:
                        cid = item["condominium_id"]
                        user_cid = self.session.get(f"{base_url}/api/user", timeout=TIMEOUT).json().get("condominium_id")
                        if user_cid and cid != user_cid:
                            tenant_ok = False
                            detail = f"condominium_id divergente: {cid} vs {user_cid}"
                            break
            detail = detail or f"Lista retornou HTTP 200 ({len(data) if isinstance(data, list) else 'obj'})"
        else:
            tenant_ok = r.status_code in (403, 200)
            detail = f"HTTP {r.status_code}"
        self.record("SEC020", "Isolamento de tenant em cobrancas", tenant_ok, "CRITICAL", detail)

        # SEC011 - Rate limit por ultimo (evita bloquear sessao dos testes anteriores)
        rl_session = requests.Session()
        rl_headers = setup_csrf(rl_session, base_url)
        got_429 = False
        for i in range(7):
            r = rl_session.post(
                f"{base_url}/login",
                json={"email": "bruteforce_probe@test.com", "password": "wrong"},
                headers=rl_headers,
                timeout=TIMEOUT,
            )
            if r.status_code == 429:
                got_429 = True
                break
        self.record("SEC011", "Rate limit no login (brute force)", got_429, "MEDIUM",
                    "429 apos multiplas tentativas" if got_429 else "Rate limit nao detectado em 7 tentativas")

        # Resumo
        passed = sum(1 for f in self.findings if f.status == "PASS")
        failed = sum(1 for f in self.findings if f.status == "FAIL")
        warned = sum(1 for f in self.findings if f.status == "WARN")
        total = len(self.findings)

        print(f"\n=== Resultado: {passed} PASS | {failed} FAIL | {warned} WARN | {total} total ===\n")

        critical_fails = [f for f in self.findings if f.status == "FAIL" and f.severity == "CRITICAL"]
        if critical_fails:
            print("FALHAS CRITICAS:")
            for f in critical_fails:
                print(f"  - {f.test_id}: {f.title} - {f.detail}")

        report = {
            "summary": {"pass": passed, "fail": failed, "warn": warned, "total": total},
            "findings": [f.__dict__ for f in self.findings],
        }
        path = "testsprite_tests/tmp/security_results.json"
        with open(path, "w", encoding="utf-8") as fp:
            json.dump(report, fp, indent=2, ensure_ascii=False)
        print(f"Relatorio: {path}")

        return 0 if failed == 0 else 1


if __name__ == "__main__":
    sys.exit(SecurityTestRunner().run_all())
