import requests
import urllib.parse
import sys

BASE_URL = "http://localhost:8000"
EMAIL = "joaosilva@gmail.com"
PASSWORD = "@!T1q2w3e4r"
TIMEOUT = 30

def test_syndic_list_packages():
    session = requests.Session()
    session.headers.update({"Accept": "application/json"})
    try:
        # 1) Obtain CSRF cookie
        r = session.get(f"{BASE_URL}/sanctum/csrf-cookie", timeout=TIMEOUT)
        if not (200 <= r.status_code < 300):
            raise AssertionError(f"GET /sanctum/csrf-cookie returned {r.status_code}, body: {r.text}")

        xsrf_token = session.cookies.get("XSRF-TOKEN")
        if not xsrf_token:
            raise AssertionError("XSRF-TOKEN cookie not found after CSRF request")
        xsrf_token = urllib.parse.unquote(xsrf_token)
        session.headers.update({"X-XSRF-TOKEN": xsrf_token, "Content-Type": "application/json"})

        # 2) POST /login
        login_payload = {"email": EMAIL, "password": PASSWORD}
        r = session.post(f"{BASE_URL}/login", json=login_payload, timeout=TIMEOUT)
        # Accept common success codes (200, 204, 302). If login failed, subsequent /api/user will reveal.
        if not (200 <= r.status_code < 400):
            raise AssertionError(f"POST /login returned {r.status_code}, body: {r.text}")

        # 3) POST /profile/set with role "Síndico"
        profile_payload = {"role": "Síndico"}
        r = session.post(f"{BASE_URL}/profile/set", json=profile_payload, timeout=TIMEOUT)
        if not (200 <= r.status_code < 400):
            raise AssertionError(f"POST /profile/set returned {r.status_code}, body: {r.text}")

        # Verify authenticated user context
        r = session.get(f"{BASE_URL}/api/user", timeout=TIMEOUT)
        if r.status_code == 401:
            raise AssertionError("Authenticated request to /api/user returned 401 Unauthorized after login/profile set")
        if not (200 <= r.status_code < 300):
            raise AssertionError(f"GET /api/user returned unexpected status {r.status_code}, body: {r.text}")

        # 4) GET /api/packages - main assertion for this test
        r = session.get(f"{BASE_URL}/api/packages", timeout=TIMEOUT)
        assert r.status_code == 200, f"GET /api/packages expected 200, got {r.status_code}, body: {r.text}"

        try:
            payload = r.json()
        except ValueError:
            raise AssertionError(f"GET /api/packages did not return valid JSON. Status: {r.status_code}, Body: {r.text}")

        # Accept either a JSON array or an object with a 'data' array (common Laravel resource)
        if isinstance(payload, list):
            packages_list = payload
        elif isinstance(payload, dict) and isinstance(payload.get("data"), list):
            packages_list = payload.get("data")
        else:
            raise AssertionError(f"GET /api/packages returned unexpected JSON structure: {payload}")

        assert isinstance(packages_list, list), "Packages payload is not a list"
        # Test passes if we reach here
        print("TC010 passed: /api/packages returned 200 and a list payload")

    except Exception as e:
        print(f"TC010 failed: {e}")
        raise
    finally:
        session.close()

if __name__ == "__main__":
    test_syndic_list_packages()