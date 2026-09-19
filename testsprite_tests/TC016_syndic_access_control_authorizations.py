import requests
import urllib.parse
import sys

BASE_URL = "http://localhost:8000"
EMAIL = "joaosilva@gmail.com"
PASSWORD = "@!T1q2w3e4r"
ROLE = "Síndico"
TIMEOUT = 30

def test_syndic_access_control_authorizations_tc016():
    session = requests.Session()
    session.headers.update({"Accept": "application/json"})
    try:
        # 1) Get CSRF cookie
        csrf_url = f"{BASE_URL}/sanctum/csrf-cookie"
        r = session.get(csrf_url, timeout=TIMEOUT)
        if r.status_code not in (200, 204):
            raise AssertionError(f"CSRF cookie request failed: {r.status_code}, body: {r.text}")

        # Extract XSRF token from cookies and set header
        raw_token = session.cookies.get("XSRF-TOKEN")
        if raw_token:
            token = urllib.parse.unquote(raw_token)
            session.headers.update({"X-XSRF-TOKEN": token})
        else:
            # It's possible the app uses a differently named cookie, but fail if missing
            raise AssertionError("XSRF-TOKEN cookie not found after CSRF request")

        # 2) POST /login
        login_url = f"{BASE_URL}/login"
        login_payload = {"email": EMAIL, "password": PASSWORD}
        r = session.post(login_url, json=login_payload, timeout=TIMEOUT)
        if r.status_code not in (200, 201, 204):
            raise AssertionError(f"Login failed: {r.status_code}, body: {r.text}")

        # After login, refresh token header if cookie updated
        raw_token = session.cookies.get("XSRF-TOKEN")
        if raw_token:
            session.headers.update({"X-XSRF-TOKEN": urllib.parse.unquote(raw_token)})

        # 3) POST /profile/set with role "Síndico"
        profile_set_url = f"{BASE_URL}/profile/set"
        profile_payload = {"role": ROLE}
        r = session.post(profile_set_url, json=profile_payload, timeout=TIMEOUT)
        if r.status_code not in (200, 201, 204):
            raise AssertionError(f"Setting profile failed: {r.status_code}, body: {r.text}")

        # Ensure session cookies persist for /api/* calls
        # 4) GET /api/access-control/authorizations
        authorizations_url = f"{BASE_URL}/api/access-control/authorizations"
        r = session.get(authorizations_url, timeout=TIMEOUT)
        assert r.status_code == 200, f"Expected 200 from {authorizations_url}, got {r.status_code}, body: {r.text}"

        # Validate response body is JSON and represents a list of authorizations (or contains data list)
        try:
            body = r.json()
        except ValueError:
            raise AssertionError("Response is not valid JSON")

        if isinstance(body, list):
            # OK: list of authorizations
            pass
        elif isinstance(body, dict):
            # Accept either a direct 'data' key with list or a dict representing result
            if "data" in body and isinstance(body["data"], list):
                pass
            else:
                # Accept non-list dict as valid response object but assert it's not an error envelope
                if "error" in body or "message" in body and isinstance(body.get("message"), str) and r.status_code != 200:
                    raise AssertionError(f"Unexpected error in response body: {body}")
        else:
            raise AssertionError("Unexpected JSON structure for authorizations response")

        print("TC016 passed: /api/access-control/authorizations returned 200 with expected JSON structure.")

    except requests.exceptions.RequestException as e:
        raise AssertionError(f"Network request failed: {e}") from e

if __name__ == "__main__":
    test_syndic_access_control_authorizations_tc016()