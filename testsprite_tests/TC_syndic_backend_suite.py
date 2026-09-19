"""
SindCON - Suite de testes backend para perfil Sindico.
Credenciais via testsprite_tests/.env ou testsprite_tests/tmp/config.json (gitignored).
Execucao: python testsprite_tests/TC_syndic_backend_suite.py
"""
import json
import sys
from dataclasses import dataclass, field
from pathlib import Path

import requests

sys.path.insert(0, str(Path(__file__).resolve().parent))

from lib.auth import DEFAULT_TIMEOUT, api_headers, authenticated_session, load_test_credentials

PROFILE = "Síndico"
TIMEOUT = DEFAULT_TIMEOUT


@dataclass
class TestResult:
    test_id: str
    title: str
    status: str
    detail: str = ""


@dataclass
class SyndicTestRunner:
    session: requests.Session = field(default_factory=requests.Session)
    results: list[TestResult] = field(default_factory=list)
    base_url: str = ""
    headers: dict = field(default_factory=dict)

    def record(self, test_id: str, title: str, passed: bool, detail: str = "") -> None:
        self.results.append(TestResult(test_id, title, "PASSED" if passed else "FAILED", detail))
        tag = "[OK]" if passed else "[FAIL]"
        print(f"{tag} {test_id}: {title}" + (f" - {detail}" if detail else ""))

    def api_get(self, test_id: str, title: str, path: str, expected: int = 200) -> None:
        try:
            resp = self.session.get(f"{self.base_url}{path}", headers=self.headers, timeout=TIMEOUT)
            ok = resp.status_code == expected
            detail = f"HTTP {resp.status_code}"
            if not ok and resp.text:
                try:
                    body = resp.json()
                    detail += f" - {body.get('message', '')}"
                except ValueError:
                    detail += f" - {resp.text[:120]}"
            self.record(test_id, title, ok, detail)
        except requests.RequestException as exc:
            self.record(test_id, title, False, str(exc))

    def run_all(self) -> int:
        creds = load_test_credentials()
        self.base_url = creds["base_url"]
        print(f"\n=== SindCON Backend - Perfil {PROFILE} ===\nBase: {self.base_url}\n")

        try:
            r = requests.get(f"{self.base_url}/api/health", timeout=TIMEOUT)
            ok = r.status_code == 200 and isinstance(r.json(), dict)
            self.record("TC001", "GET /api/health", ok, f"HTTP {r.status_code}")
        except requests.RequestException as exc:
            self.record("TC001", "GET /api/health", False, str(exc))

        try:
            self.session, _ = authenticated_session(PROFILE)
            self.headers = api_headers(self.session, self.base_url)
            user = self.session.get(f"{self.base_url}/api/user", headers=self.headers, timeout=TIMEOUT)
            if user.status_code == 200:
                self.record("TC002", "Login e perfil Sindico", True, f"user_id={user.json().get('id')}")
            else:
                self.record("TC002", "Login e perfil Sindico", False, f"HTTP {user.status_code}")
                return 1
        except RuntimeError as exc:
            self.record("TC002", "Login e perfil Sindico", False, str(exc))
            return 1

        endpoints = [
            ("TC003", "GET /api/charges", "/api/charges"),
            ("TC004", "GET /api/transactions", "/api/transactions"),
            ("TC005", "GET /api/reports/financial", "/api/reports/financial"),
            ("TC006", "GET /api/reports/defaulters", "/api/reports/defaulters"),
            ("TC007", "GET /api/reports/balance", "/api/reports/balance"),
            ("TC008", "GET /api/reservations", "/api/reservations"),
            ("TC009", "GET /api/spaces", "/api/spaces"),
            ("TC010", "GET /api/packages", "/api/packages"),
            ("TC011", "GET /api/packages/summary/units", "/api/packages/summary/units"),
            ("TC012", "GET /api/assemblies", "/api/assemblies"),
            ("TC013", "GET /api/conversations", "/api/conversations"),
            ("TC014", "GET /api/notifications/unread-count", "/api/notifications/unread-count"),
            ("TC015", "GET /api/notifications", "/api/notifications"),
            ("TC016", "GET /api/access-control/authorizations", "/api/access-control/authorizations"),
            ("TC017", "GET /api/access-control/movements", "/api/access-control/movements"),
            ("TC018", "GET /api/users/search", "/api/users/search?q=a"),
            ("TC019", "GET /api/marketplace", "/api/marketplace"),
        ]
        for test_id, title, path in endpoints:
            self.api_get(test_id, title, path)

        try:
            resp = requests.get(f"{self.base_url}/api/charges", timeout=TIMEOUT)
            self.record("TC020", "GET /api/charges sem auth", resp.status_code == 401, f"HTTP {resp.status_code}")
        except requests.RequestException as exc:
            self.record("TC020", "GET /api/charges sem auth", False, str(exc))

        passed = sum(1 for r in self.results if r.status == "PASSED")
        failed = sum(1 for r in self.results if r.status == "FAILED")
        print(f"\n=== {passed}/{len(self.results)} passaram ===\n")

        report_path = Path(__file__).parent / "tmp" / "syndic_backend_results.json"
        report_path.write_text(
            json.dumps([r.__dict__ for r in self.results], indent=2, ensure_ascii=False),
            encoding="utf-8",
        )
        print(f"Relatorio: {report_path}")
        return 0 if failed == 0 else 1


if __name__ == "__main__":
    sys.exit(SyndicTestRunner().run_all())
