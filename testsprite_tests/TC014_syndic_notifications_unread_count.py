import requests
import urllib.parse
import sys

BASE_URL = "http://localhost:8000"
EMAIL = "joaosilva@gmail.com"
PASSWORD = "@!T1q2w3e4r"
TIMEOUT = 30

def test_syndic_notifications_unread_count():
    session = requests.Session()
    headers = {
        "Accept": "application/json",
        "Content-Type": "application/json",
    }

    try:
        # 1) Get CSRF cookie
        csrf_url = f"{BASE_URL}/sanctum/csrf-cookie"
        r = session.get(csrf_url, headers=headers, timeout=TIMEOUT)
        assert r.status_code == 204 or r.status_code == 200, f"CSRF cookie request failed: {r.status_code} {r.text}"

        # Extract XSRF-TOKEN cookie and set header
        xsrf_cookie = session.cookies.get("XSRF-TOKEN")
        if xsrf_cookie:
            # Laravel stores an URL-encoded token in the cookie
            xsrf_token = urllib.parse.unquote(xsrf_cookie)
            headers["X-XSRF-TOKEN"] = xsrf_token

        # 2) POST /login
        login_url = f"{BASE_URL}/login"
        payload = {"email": EMAIL, "password": PASSWORD}
        r = session.post(login_url, json=payload, headers=headers, timeout=TIMEOUT)
        if r.status_code not in (200, 201, 204, 302):
            raise AssertionError(f"Login failed: status {r.status_code}, body: {r.text}")

        # 3) POST /profile/set with role "Síndico"
        profile_set_url = f"{BASE_URL}/profile/set"
        profile_payload = {"role": "Síndico"}
        r = session.post(profile_set_url, json=profile_payload, headers=headers, timeout=TIMEOUT)
        if r.status_code not in (200, 201, 204):
            raise AssertionError(f"Profile set failed: status {r.status_code}, body: {r.text}")

        # 4) GET /api/notifications/unread-count using session (cookies preserved)
        unread_count_url = f"{BASE_URL}/api/notifications/unread-count"
        api_headers = headers.copy()
        # Ensure we accept JSON
        api_headers["Accept"] = "application/json"
        r = session.get(unread_count_url, headers=api_headers, timeout=TIMEOUT)

        assert r.status_code == 200, f"Expected 200 from unread-count, got {r.status_code}, body: {r.text}"

        try:
            data = r.json()
        except ValueError:
            raise AssertionError(f"Response is not valid JSON: {r.text}")

        assert isinstance(data, dict), f"Expected JSON object for unread-count, got {type(data)}: {data}"
        assert len(data) > 0, f"Unread-count response object is empty: {data}"

        # Validate there's at least one numeric value in the object (common pattern: {"count": 3})
        def contains_numeric(obj):
            if isinstance(obj, (int, float)):
                return True
            if isinstance(obj, dict):
                return any(contains_numeric(v) for v in obj.values())
            if isinstance(obj, list):
                return any(contains_numeric(i) for i in obj)
            return False

        assert contains_numeric(data), f"Unread-count JSON does not contain any numeric value: {data}"

        print("TEST PASSED: syndic_notifications_unread_count")

    except requests.RequestException as e:
        raise AssertionError(f"HTTP request failed: {e}") from e


if __name__ == "__main__":
    test_syndic_notifications_unread_count()