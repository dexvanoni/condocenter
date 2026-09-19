import requests
import urllib.parse
import sys

BASE_URL = "http://localhost:8000"
EMAIL = "joaosilva@gmail.com"
PASSWORD = "@!T1q2w3e4r"
TIMEOUT = 30.0

def test_syndic_list_notifications():
    session = requests.Session()
    session.headers.update({"Accept": "application/json"})
    try:
        # 1) Get CSRF cookie
        resp = session.get(f"{BASE_URL}/sanctum/csrf-cookie", timeout=TIMEOUT)
        assert resp.status_code == 204 or resp.status_code == 200, f"CSRF cookie endpoint returned unexpected status {resp.status_code}"
        xsrf_raw = session.cookies.get("XSRF-TOKEN", "")
        xsrf_token = urllib.parse.unquote_plus(xsrf_raw) if xsrf_raw else ""
        if xsrf_token:
            session.headers.update({"X-XSRF-TOKEN": xsrf_token})

        # 2) Login
        login_payload = {"email": EMAIL, "password": PASSWORD}
        resp = session.post(f"{BASE_URL}/login", json=login_payload, timeout=TIMEOUT)
        assert resp.status_code in (200, 201, 204), f"Login failed with status {resp.status_code}, body: {resp.text}"

        # 3) Set profile to Síndico (with accent)
        profile_payload = {"role": "Síndico"}
        resp = session.post(f"{BASE_URL}/profile/set", json=profile_payload, timeout=TIMEOUT)
        assert resp.status_code in (200, 201, 204), f"Setting profile failed with status {resp.status_code}, body: {resp.text}"

        # 4) Reuse session cookies to call protected API
        resp = session.get(f"{BASE_URL}/api/notifications", timeout=TIMEOUT)
        assert resp.status_code == 200, f"GET /api/notifications returned {resp.status_code}, body: {resp.text}"

        data = resp.json()
        assert isinstance(data, list), f"Expected notifications list (array), got {type(data)}"

        # Optional: basic shape check if there are items
        if len(data) > 0:
            first = data[0]
            assert isinstance(first, dict), "Notification items should be objects"
        print("test_syndic_list_notifications: PASSED")

    except requests.RequestException as e:
        raise AssertionError(f"HTTP request failed: {e}") from e
    except AssertionError:
        raise
    except Exception as e:
        raise AssertionError(f"Unexpected error: {e}") from e
    finally:
        session.close()

if __name__ == "__main__":
    try:
        test_syndic_list_notifications()
    except AssertionError as e:
        print(f"test_syndic_list_notifications: FAILED - {e}")
        sys.exit(1)
    except Exception as e:
        print(f"test_syndic_list_notifications: ERROR - {e}")
        sys.exit(2)
    sys.exit(0)