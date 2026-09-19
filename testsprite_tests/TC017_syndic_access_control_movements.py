import requests
import urllib.parse
import sys

BASE_URL = "http://localhost:8000"
TIMEOUT = 30.0
EMAIL = "joaosilva@gmail.com"
PASSWORD = "@!T1q2w3e4r"
ROLE = "Síndico"

def test_syndic_access_control_movements():
    session = requests.Session()
    try:
        # 1) Get CSRF cookie
        csrf_resp = session.get(f"{BASE_URL}/sanctum/csrf-cookie", timeout=TIMEOUT)
        assert csrf_resp.status_code in (200, 204), f"Unexpected CSRF cookie response: {csrf_resp.status_code} {csrf_resp.text}"
        xsrf_cookie = session.cookies.get("XSRF-TOKEN")
        assert xsrf_cookie, "XSRF-TOKEN cookie not set after /sanctum/csrf-cookie"

        xsrf_value = urllib.parse.unquote(xsrf_cookie)
        common_headers = {
            "Accept": "application/json",
            "X-XSRF-TOKEN": xsrf_value
        }

        # 2) POST /login
        login_payload = {"email": EMAIL, "password": PASSWORD}
        login_resp = session.post(f"{BASE_URL}/login", json=login_payload, headers=common_headers, timeout=TIMEOUT)
        assert login_resp.status_code in (200, 204), f"Login failed: {login_resp.status_code} {login_resp.text}"

        # Verify we are authenticated by calling /api/user
        user_resp = session.get(f"{BASE_URL}/api/user", headers={"Accept": "application/json"}, timeout=TIMEOUT)
        assert user_resp.status_code == 200, f"/api/user did not return 200 after login: {user_resp.status_code} {user_resp.text}"

        # 3) POST /profile/set to activate Síndico role
        profile_payload = {"role": ROLE}
        profile_resp = session.post(f"{BASE_URL}/profile/set", json=profile_payload, headers=common_headers, timeout=TIMEOUT)
        assert profile_resp.status_code in (200, 204), f"Setting profile failed: {profile_resp.status_code} {profile_resp.text}"

        # Confirm profile activated (optional check against /api/user)
        user_after_profile = session.get(f"{BASE_URL}/api/user", headers={"Accept": "application/json"}, timeout=TIMEOUT)
        assert user_after_profile.status_code == 200, f"/api/user after profile set returned {user_after_profile.status_code}"
        # It's okay if response body doesn't explicitly show role; presence of 200 indicates active session/profile

        # 4) GET /api/access-control/movements
        movements_resp = session.get(f"{BASE_URL}/api/access-control/movements", headers={"Accept": "application/json"}, timeout=TIMEOUT)
        assert movements_resp.status_code == 200, f"/api/access-control/movements expected 200, got {movements_resp.status_code}: {movements_resp.text}"

        # Validate response body is JSON and contains movements log (list or paginated data)
        try:
            body = movements_resp.json()
        except ValueError:
            raise AssertionError("Response from /api/access-control/movements is not valid JSON")

        if isinstance(body, list):
            # ok: direct list of movements
            assert True
        elif isinstance(body, dict):
            # common patterns: data key with list or items key
            if "data" in body:
                assert isinstance(body["data"], list), "Expected 'data' key to be a list of movements"
            elif "items" in body:
                assert isinstance(body["items"], list), "Expected 'items' key to be a list of movements"
            else:
                # Accept other dict shapes but ensure it's not empty
                assert body, "Expected non-empty JSON object for movements"
        else:
            raise AssertionError("Unexpected JSON type for movements response")

        print("TC017 passed: /api/access-control/movements returned 200 with valid movements data.")

    except AssertionError:
        raise
    except Exception as e:
        raise
    finally:
        # Attempt to logout to clean session (best-effort)
        try:
            # logout may require CSRF header
            session.post(f"{BASE_URL}/logout", headers=common_headers, timeout=5)
        except Exception:
            pass
        session.close()

if __name__ == "__main__":
    test_syndic_access_control_movements()