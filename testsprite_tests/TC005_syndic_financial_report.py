import requests
import urllib.parse
import sys

BASE_URL = "http://localhost:8000"
EMAIL = "joaosilva@gmail.com"
PASSWORD = "@!T1q2w3e4r"
TIMEOUT = 30


def test_syndic_financial_report():
    session = requests.Session()
    session.headers.update({"Accept": "application/json"})

    try:
        # 1) Get CSRF cookie
        r = session.get(f"{BASE_URL}/sanctum/csrf-cookie", timeout=TIMEOUT)
        assert r.status_code in (200, 204), f"CSRF cookie request failed: {r.status_code} - {r.text}"

        # Try to set X-XSRF-TOKEN header if cookie present
        xsrf_cookie = session.cookies.get("XSRF-TOKEN")
        if xsrf_cookie:
            session.headers.update({"X-XSRF-TOKEN": urllib.parse.unquote(xsrf_cookie)})

        # 2) POST /login
        login_payload = {"email": EMAIL, "password": PASSWORD}
        r = session.post(f"{BASE_URL}/login", json=login_payload, timeout=TIMEOUT)
        assert r.status_code == 200, f"Login failed: {r.status_code} - {r.text}"

        # After login, ensure we still have XSRF token header up to date (cookie may have been refreshed)
        xsrf_cookie = session.cookies.get("XSRF-TOKEN")
        if xsrf_cookie:
            session.headers.update({"X-XSRF-TOKEN": urllib.parse.unquote(xsrf_cookie)})

        # 3) POST /profile/set with role = "Síndico"
        profile_payload = {"role": "Síndico"}
        r = session.post(f"{BASE_URL}/profile/set", json=profile_payload, timeout=TIMEOUT)
        assert r.status_code in (200, 204), f"Profile set failed: {r.status_code} - {r.text}"

        # 4) GET /api/reports/financial using session cookies
        r = session.get(f"{BASE_URL}/api/reports/financial", timeout=TIMEOUT)
        assert r.status_code == 200, f"Financial report request failed: {r.status_code} - {r.text}"

        # Validate response body is a JSON object (dict) and not empty
        try:
            data = r.json()
        except ValueError:
            raise AssertionError(f"Response is not valid JSON: {r.text}")

        assert isinstance(data, dict), f"Expected JSON object for financial report, got: {type(data)}"
        assert data != {}, "Financial report JSON is empty"

        print("TC005 passed: /api/reports/financial returned 200 and valid JSON object.")

    except AssertionError:
        raise
    except Exception as exc:
        raise AssertionError(f"Unexpected error during test execution: {exc}") from exc
    finally:
        session.close()


if __name__ == "__main__":
    test_syndic_financial_report()