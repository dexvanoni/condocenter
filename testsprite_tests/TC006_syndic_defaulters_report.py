import requests
from urllib.parse import unquote

BASE_URL = "http://localhost:8000"
EMAIL = "joaosilva@gmail.com"
PASSWORD = "@!T1q2w3e4r"
TIMEOUT = 30

def test_syndic_defaulters_report():
    session = requests.Session()
    try:
        # 1) Obtain CSRF cookie
        resp = session.get(f"{BASE_URL}/sanctum/csrf-cookie", timeout=TIMEOUT)
        resp.raise_for_status()

        # Extract XSRF token from cookies (URL-decoded)
        xsrf_cookie = session.cookies.get("XSRF-TOKEN")
        xsrf_token = unquote(xsrf_cookie) if xsrf_cookie else None

        headers = {"Accept": "application/json"}
        if xsrf_token:
            headers["X-XSRF-TOKEN"] = xsrf_token

        # 2) Login
        login_payload = {"email": EMAIL, "password": PASSWORD}
        resp = session.post(f"{BASE_URL}/login", data=login_payload, headers=headers, timeout=TIMEOUT)
        # Accept 200 or 204 commonly returned by Laravel on successful login
        assert resp.status_code in (200, 204), f"Login failed: {resp.status_code} {resp.text}"

        # After login, XSRF token may be refreshed -> update header
        xsrf_cookie = session.cookies.get("XSRF-TOKEN")
        xsrf_token = unquote(xsrf_cookie) if xsrf_cookie else None
        if xsrf_token:
            headers["X-XSRF-TOKEN"] = xsrf_token

        # 3) Set active profile to Síndico
        profile_payload = {"role": "Síndico"}
        resp = session.post(f"{BASE_URL}/profile/set", json=profile_payload, headers=headers, timeout=TIMEOUT)
        assert resp.status_code in (200, 204), f"Setting profile failed: {resp.status_code} {resp.text}"

        # Update XSRF token again before API call (if rotated)
        xsrf_cookie = session.cookies.get("XSRF-TOKEN")
        xsrf_token = unquote(xsrf_cookie) if xsrf_cookie else None
        if xsrf_token:
            headers["X-XSRF-TOKEN"] = xsrf_token

        # 4) GET defaulters report
        resp = session.get(f"{BASE_URL}/api/reports/defaulters", headers=headers, timeout=TIMEOUT)
        # Validate response
        assert resp.status_code == 200, f"Expected 200 for defaulters report, got {resp.status_code}: {resp.text}"

        try:
            body = resp.json()
        except ValueError:
            assert False, "Response is not valid JSON"

        assert isinstance(body, dict), f"Expected JSON object for defaulters report, got {type(body)}"

    except requests.exceptions.RequestException as e:
        assert False, f"HTTP request failed: {e}"

if __name__ == "__main__":
    test_syndic_defaulters_report()