import requests
import urllib.parse
import sys

BASE_URL = "http://localhost:8000"
TIMEOUT = 30.0

def test_syndic_login_and_select_profile():
    session = requests.Session()
    session.headers.update({
        "Accept": "application/json",
        "User-Agent": "SindCON-Test/1.0"
    })

    try:
        # 1) Obtain CSRF cookie
        csrf_url = f"{BASE_URL}/sanctum/csrf-cookie"
        resp = session.get(csrf_url, timeout=TIMEOUT)
        try:
            resp.raise_for_status()
        except requests.RequestException:
            raise AssertionError(f"Failed to get CSRF cookie: status={resp.status_code}, body={resp.text}")

        # Extract XSRF token from cookies
        xsrf_token = session.cookies.get("XSRF-TOKEN") or resp.cookies.get("XSRF-TOKEN")
        assert xsrf_token, "XSRF-TOKEN cookie not found after GET /sanctum/csrf-cookie"
        # URL-decode token if needed
        xsrf_token = urllib.parse.unquote(xsrf_token)
        session.headers.update({"X-XSRF-TOKEN": xsrf_token})

        # 2) POST /login with credentials
        login_url = f"{BASE_URL}/login"
        login_payload = {"email": "joaosilva@gmail.com", "password": "@!T1q2w3e4r"}
        resp = session.post(login_url, json=login_payload, timeout=TIMEOUT)
        # Accept common success statuses (200 OK or 302 redirect)
        if resp.status_code not in (200, 201, 202, 204, 302):
            raise AssertionError(f"Login failed: status={resp.status_code}, body={resp.text}")

        # Optionally refresh XSRF token if changed
        xsrf_token = session.cookies.get("XSRF-TOKEN")
        if xsrf_token:
            session.headers.update({"X-XSRF-TOKEN": urllib.parse.unquote(xsrf_token)})

        # 3) POST /profile/set with role = "Síndico"
        profile_set_url = f"{BASE_URL}/profile/set"
        profile_payload = {"role": "Síndico"}
        resp = session.post(profile_set_url, json=profile_payload, timeout=TIMEOUT)
        if resp.status_code not in (200, 201, 202, 204):
            raise AssertionError(f"Setting profile failed: status={resp.status_code}, body={resp.text}")

        # 4) GET /api/user to verify authenticated syndic user
        api_user_url = f"{BASE_URL}/api/user"
        resp = session.get(api_user_url, timeout=TIMEOUT)
        try:
            resp.raise_for_status()
        except requests.RequestException:
            raise AssertionError(f"GET /api/user failed: status={resp.status_code}, body={resp.text}")

        data = None
        try:
            data = resp.json()
        except ValueError:
            raise AssertionError("GET /api/user did not return valid JSON")

        # Validate email
        email = data.get("email") if isinstance(data, dict) else None
        assert email == "joaosilva@gmail.com", f"Authenticated user email mismatch: expected 'joaosilva@gmail.com', got '{email}'"

        # Validate active profile contains 'Síndico' somewhere in the response
        import json
        serialized = json.dumps(data, ensure_ascii=False)
        assert "Síndico" in serialized, f"'Síndico' not found in /api/user response: {serialized}"

        print("TC002 passed: syndic login, profile set, and /api/user verified.")
    except requests.RequestException as e:
        raise AssertionError(f"Network error during test: {e}") from e


if __name__ == "__main__":
    try:
        test_syndic_login_and_select_profile()
    except AssertionError as e:
        print(f"TEST FAILED: {e}")
        sys.exit(1)
    except Exception as e:
        print(f"UNEXPECTED ERROR: {e}")
        sys.exit(2)
    else:
        print("TEST SUCCEEDED")
        sys.exit(0)