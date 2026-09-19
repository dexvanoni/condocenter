import requests
from urllib.parse import unquote
import sys

BASE_URL = "http://localhost:8000"
TIMEOUT = 30.0

def test_TC011_syndic_package_summary():
    session = requests.Session()
    headers = {
        "Accept": "application/json",
    }

    try:
        # 1) GET /sanctum/csrf-cookie to obtain CSRF cookie
        url_csrf = f"{BASE_URL}/sanctum/csrf-cookie"
        resp = session.get(url_csrf, headers=headers, timeout=TIMEOUT)
        if resp.status_code not in (200, 204):
            raise AssertionError(f"CSRF cookie request returned unexpected status {resp.status_code}: {resp.text}")

        # Extract XSRF-TOKEN cookie and set X-XSRF-TOKEN header if present
        xsrf_token = session.cookies.get("XSRF-TOKEN")
        if xsrf_token:
            headers["X-XSRF-TOKEN"] = unquote(xsrf_token)

        # 2) POST /login with credentials
        url_login = f"{BASE_URL}/login"
        login_payload = {"email": "joaosilva@gmail.com", "password": "@!T1q2w3e4r"}
        resp = session.post(url_login, json=login_payload, headers=headers, timeout=TIMEOUT)
        if resp.status_code not in (200, 201, 204):
            raise AssertionError(f"Login failed with status {resp.status_code}: {resp.text}")

        # Confirm authenticated by calling GET /api/user
        url_user = f"{BASE_URL}/api/user"
        resp = session.get(url_user, headers=headers, timeout=TIMEOUT)
        if resp.status_code != 200:
            raise AssertionError(f"GET /api/user expected 200 after login, got {resp.status_code}: {resp.text}")

        # 3) POST /profile/set with role=Síndico to activate syndic profile
        url_profile_set = f"{BASE_URL}/profile/set"
        profile_payload = {"role": "Síndico"}
        resp = session.post(url_profile_set, json=profile_payload, headers=headers, timeout=TIMEOUT)
        if resp.status_code not in (200, 201, 204):
            raise AssertionError(f"Profile set failed with status {resp.status_code}: {resp.text}")

        # Optionally confirm profile is active via GET /api/user again (best-effort)
        resp = session.get(url_user, headers=headers, timeout=TIMEOUT)
        if resp.status_code != 200:
            raise AssertionError(f"GET /api/user after profile set expected 200, got {resp.status_code}: {resp.text}")

        # 4) As authenticated Síndico, GET /api/packages/summary/units
        url_summary = f"{BASE_URL}/api/packages/summary/units"
        resp = session.get(url_summary, headers=headers, timeout=TIMEOUT)

        # Validate response
        assert resp.status_code == 200, f"Expected 200 from {url_summary}, got {resp.status_code}: {resp.text}"
        content_type = resp.headers.get("Content-Type", "")
        assert "application/json" in content_type.lower(), f"Expected JSON response, got Content-Type: {content_type}"

        data = resp.json()
        assert isinstance(data, (dict, list)), f"Expected response JSON to be an object or list, got {type(data)}"
        # Additional light validation: summary should be present (cannot enforce exact schema)
        assert data is not None, "Response JSON is None"

        print("TC011 passed: /api/packages/summary/units returned 200 with JSON summary.")

    except requests.exceptions.RequestException as e:
        raise AssertionError(f"HTTP request failed: {e}") from e


if __name__ == "__main__":
    try:
        test_TC011_syndic_package_summary()
    except AssertionError as e:
        print(f"Test failed: {e}")
        sys.exit(1)
    except Exception as e:
        print(f"Unexpected error: {e}")
        sys.exit(2)
    sys.exit(0)