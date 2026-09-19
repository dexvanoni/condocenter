import requests
import urllib.parse
import sys

BASE_URL = "http://localhost:8000"
TIMEOUT = 30


def test_syndic_list_assemblies():
    session = requests.Session()
    try:
        # 1) Get CSRF cookie
        csrf_url = f"{BASE_URL}/sanctum/csrf-cookie"
        r = session.get(csrf_url, timeout=TIMEOUT)
        assert r.status_code == 204 or r.status_code == 200, f"Unexpected status for csrf-cookie: {r.status_code}, body: {r.text}"

        # Extract XSRF token from cookies
        xsrf_cookie = session.cookies.get("XSRF-TOKEN")
        assert xsrf_cookie, "XSRF-TOKEN cookie not found after csrf-cookie request"
        xsrf_value = urllib.parse.unquote(xsrf_cookie)

        # Common headers for JSON requests with CSRF
        headers = {
            "X-XSRF-TOKEN": xsrf_value,
            "Accept": "application/json",
        }

        # 2) POST /login
        login_url = f"{BASE_URL}/login"
        login_payload = {
            "email": "joaosilva@gmail.com",
            "password": "@!T1q2w3e4r"
        }
        r = session.post(login_url, json=login_payload, headers=headers, timeout=TIMEOUT)
        # Accept either 200 or 204 or 302 depending on implementation; require authenticated session cookie present
        if r.status_code not in (200, 204, 302):
            raise AssertionError(f"Login failed: status {r.status_code}, body: {r.text}")
        # ensure session cookie present
        laravel_session = session.cookies.get("laravel_session") or session.cookies.get("laravel-session")
        assert laravel_session or any(k for k in session.cookies.keys()), "No session cookie set after login"

        # 3) POST /profile/set with role = "Síndico"
        profile_set_url = f"{BASE_URL}/profile/set"
        profile_payload = {"role": "Síndico"}
        # Update XSRF token header in case it's refreshed
        xsrf_cookie = session.cookies.get("XSRF-TOKEN")
        if xsrf_cookie:
            headers["X-XSRF-TOKEN"] = urllib.parse.unquote(xsrf_cookie)
        r = session.post(profile_set_url, json=profile_payload, headers=headers, timeout=TIMEOUT)
        if r.status_code not in (200, 204):
            raise AssertionError(f"Setting profile failed: status {r.status_code}, body: {r.text}")

        # Optional: verify authenticated user endpoint returns 200
        user_url = f"{BASE_URL}/api/user"
        r = session.get(user_url, headers={"Accept": "application/json"}, timeout=TIMEOUT)
        assert r.status_code == 200, f"GET /api/user failed with {r.status_code}, body: {r.text}"

        # 4) GET /api/assemblies
        assemblies_url = f"{BASE_URL}/api/assemblies"
        r = session.get(assemblies_url, headers={"Accept": "application/json"}, timeout=TIMEOUT)
        assert r.status_code == 200, f"GET /api/assemblies returned {r.status_code}, body: {r.text}"

        # Validate response JSON structure: either a list or an object with 'data' list
        try:
            payload = r.json()
        except ValueError:
            raise AssertionError("Response from /api/assemblies is not valid JSON")

        if isinstance(payload, dict) and "data" in payload:
            assert isinstance(payload["data"], list), f"Expected 'data' to be a list, got {type(payload['data'])}"
        else:
            assert isinstance(payload, list), f"Expected response to be a list, got {type(payload)}"

        # If we reach here, test passed
        print("TC012 syndic_list_assemblies: PASSED")
    except AssertionError as e:
        print(f"TC012 syndic_list_assemblies: FAILED - {e}")
        raise
    except requests.RequestException as e:
        print(f"TC012 syndic_list_assemblies: ERROR - request exception: {e}")
        raise
    finally:
        session.close()


if __name__ == "__main__":
    test_syndic_list_assemblies()