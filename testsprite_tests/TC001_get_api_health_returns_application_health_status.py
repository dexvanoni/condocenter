import requests
import sys
import urllib.parse

BASE = "http://localhost:8000"
TIMEOUT = 30

def test_get_api_health_returns_application_health_status():
    session = requests.Session()
    # default headers
    session.headers.update({
        "Accept": "application/json",
        "Content-Type": "application/json",
    })

    try:
        # 1) GET /sanctum/csrf-cookie to obtain CSRF cookie
        csrf_url = BASE + "/sanctum/csrf-cookie"
        resp = session.get(csrf_url, timeout=TIMEOUT)
        assert resp.status_code in (200, 204), f"CSRF endpoint returned unexpected status {resp.status_code}: {resp.text}"

        # extract XSRF-TOKEN cookie and set X-XSRF-TOKEN header decoded
        xsrf_cookie = session.cookies.get("XSRF-TOKEN")
        assert xsrf_cookie is not None, "XSRF-TOKEN cookie not found after CSRF request"
        xsrf_token = urllib.parse.unquote(xsrf_cookie)
        session.headers.update({"X-XSRF-TOKEN": xsrf_token})

        # 2) POST /login with provided credentials
        login_url = BASE + "/login"
        login_payload = {"email": "joaosilva@gmail.com", "password": "@!T1q2w3e4r"}
        resp = session.post(login_url, json=login_payload, timeout=TIMEOUT)
        # Accept common successful login response codes (200,201,204,302)
        assert resp.status_code in (200, 201, 204, 302), f"Login failed with status {resp.status_code}: {resp.text}"
        # ensure session cookie present (laravel_session or similar)
        has_session_cookie = any(name.startswith("laravel") or name.lower().startswith("session") for name in session.cookies.keys())
        assert has_session_cookie, f"No session cookie found after login. Cookies: {session.cookies.items()}"

        # 3) POST /profile/set with role = "Síndico" (activate Sindico profile)
        profile_url = BASE + "/profile/set"
        profile_payload = {"role": "Síndico"}  # accent included
        resp = session.post(profile_url, json=profile_payload, timeout=TIMEOUT)
        # Accept 200/204/201 as success for profile set
        assert resp.status_code in (200, 201, 204), f"Profile set failed with status {resp.status_code}: {resp.text}"

        # 4) Reuse session cookies for /api/health call
        health_url = BASE + "/api/health"
        # As per PRD this endpoint is public, but instructions require reusing session cookies
        resp = session.get(health_url, timeout=TIMEOUT)
        assert resp.status_code == 200, f"Expected 200 from /api/health, got {resp.status_code}: {resp.text}"
        # Validate response is JSON object
        try:
            body = resp.json()
        except ValueError:
            raise AssertionError(f"/api/health did not return valid JSON. Content-Type: {resp.headers.get('Content-Type')}, Body: {resp.text}")
        assert isinstance(body, dict), f"/api/health returned JSON but not an object/dict. Got type {type(body)} and value: {body}"

        print("TC001 passed: GET /api/health returned 200 and JSON object.")

    except AssertionError:
        raise
    except Exception as e:
        raise RuntimeError(f"TC001 encountered an unexpected error: {e}") from e

if __name__ == "__main__":
    test_get_api_health_returns_application_health_status()