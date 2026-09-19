import requests
from urllib.parse import unquote
import sys

BASE_URL = "http://localhost:8000"
TIMEOUT = 30.0

def test_syndic_list_spaces():
    session = requests.Session()
    headers = {
        "Accept": "application/json",
    }

    try:
        # 1) GET CSRF cookie
        csrf_url = f"{BASE_URL}/sanctum/csrf-cookie"
        r = session.get(csrf_url, headers=headers, timeout=TIMEOUT)
        assert r.status_code in (200, 204), f"CSRF cookie endpoint returned unexpected status: {r.status_code}"
        # Extract XSRF token from cookies
        if "XSRF-TOKEN" in session.cookies:
            xsrf_token = unquote(session.cookies.get("XSRF-TOKEN"))
        else:
            raise AssertionError("XSRF-TOKEN cookie not found after /sanctum/csrf-cookie")

        # 2) POST /login
        login_url = f"{BASE_URL}/login"
        login_headers = headers.copy()
        login_headers["X-XSRF-TOKEN"] = xsrf_token
        login_payload = {
            "email": "joaosilva@gmail.com",
            "password": "@!T1q2w3e4r"
        }
        r = session.post(login_url, json=login_payload, headers=login_headers, timeout=TIMEOUT)
        assert r.status_code == 200, f"Login failed with status {r.status_code}, body: {r.text}"

        # 3) POST /profile/set with role = "Síndico"
        profile_set_url = f"{BASE_URL}/profile/set"
        profile_payload = {"role": "Síndico"}
        profile_headers = headers.copy()
        # Refresh XSRF token value from cookies in case it was rotated
        if "XSRF-TOKEN" in session.cookies:
            profile_headers["X-XSRF-TOKEN"] = unquote(session.cookies.get("XSRF-TOKEN"))
        else:
            profile_headers["X-XSRF-TOKEN"] = xsrf_token
        r = session.post(profile_set_url, json=profile_payload, headers=profile_headers, timeout=TIMEOUT)
        assert r.status_code == 200, f"Profile set failed with status {r.status_code}, body: {r.text}"

        # 4) GET /api/spaces using the authenticated session
        spaces_url = f"{BASE_URL}/api/spaces"
        api_headers = {"Accept": "application/json"}
        r = session.get(spaces_url, headers=api_headers, timeout=TIMEOUT)
        assert r.status_code == 200, f"GET /api/spaces returned {r.status_code}, body: {r.text}"

        try:
            data = r.json()
        except ValueError:
            raise AssertionError("Response from /api/spaces is not valid JSON")

        # Accept either a top-level list or a dict with 'data' list
        if isinstance(data, list):
            spaces_list = data
        elif isinstance(data, dict) and "data" in data and isinstance(data["data"], list):
            spaces_list = data["data"]
        else:
            raise AssertionError(f"Unexpected JSON shape for /api/spaces: {data}")

        # Final assertion: spaces_list should be a list (can be empty)
        assert isinstance(spaces_list, list), "Spaces payload is not a list"
        print("TC009 passed: /api/spaces returned 200 and a list payload")

    except requests.RequestException as e:
        raise AssertionError(f"HTTP request failed: {e}") from e


if __name__ == "__main__":
    try:
        test_syndic_list_spaces()
    except AssertionError as e:
        print(f"Test failed: {e}")
        sys.exit(1)
    except Exception as e:
        print(f"Unexpected error: {e}")
        sys.exit(2)
    else:
        sys.exit(0)