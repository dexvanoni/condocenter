import requests
from urllib.parse import unquote
from requests.exceptions import RequestException

BASE = "http://localhost:8000"
EMAIL = "joaosilva@gmail.com"
PASSWORD = "@!T1q2w3e4r"
TIMEOUT = 30

def test_syndic_list_conversations():
    session = requests.Session()
    session.headers.update({"Accept": "application/json"})

    try:
        # 1) GET CSRF cookie
        r = session.get(f"{BASE}/sanctum/csrf-cookie", timeout=TIMEOUT)
        assert r.status_code in (200, 204), f"GET /sanctum/csrf-cookie returned {r.status_code}: {r.text}"

        # Extract XSRF token from cookie and set header if present
        xsrf_cookie = session.cookies.get("XSRF-TOKEN")
        if xsrf_cookie:
            xsrf_token = unquote(xsrf_cookie)
            session.headers.update({"X-XSRF-TOKEN": xsrf_token})

        # 2) POST /login
        login_payload = {"email": EMAIL, "password": PASSWORD}
        r_login = session.post(f"{BASE}/login", json=login_payload, timeout=TIMEOUT)
        assert r_login.status_code in (200, 201, 302), f"POST /login failed: {r_login.status_code} - {r_login.text}"

        # After login, ensure we still carry cookies (session does this)
        # Update XSRF header in case it was refreshed
        xsrf_cookie = session.cookies.get("XSRF-TOKEN")
        if xsrf_cookie:
            session.headers.update({"X-XSRF-TOKEN": unquote(xsrf_cookie)})

        # 3) POST /profile/set with role = "Síndico"
        profile_payload = {"role": "Síndico"}
        r_profile = session.post(f"{BASE}/profile/set", json=profile_payload, timeout=TIMEOUT)
        assert r_profile.status_code in (200, 201, 204), f"POST /profile/set failed: {r_profile.status_code} - {r_profile.text}"

        # 4) GET /api/conversations using the authenticated session
        r_conv = session.get(f"{BASE}/api/conversations", timeout=TIMEOUT)
        assert r_conv.status_code == 200, f"GET /api/conversations returned {r_conv.status_code}: {r_conv.text}"

        # Validate body: expect a list of conversations or an object with 'data' list
        try:
            body = r_conv.json()
        except ValueError:
            raise AssertionError("Response from /api/conversations is not valid JSON")

        is_list = isinstance(body, list)
        is_data_list = isinstance(body, dict) and isinstance(body.get("data"), list)
        assert is_list or is_data_list, f"Unexpected response shape for /api/conversations: {body}"

        print("TC013 passed: syndic_list_conversations returned 200 and valid conversations list")

    except RequestException as e:
        raise AssertionError(f"HTTP request failed: {e}")

if __name__ == "__main__":
    test_syndic_list_conversations()