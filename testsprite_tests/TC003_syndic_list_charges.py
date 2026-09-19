import requests
import urllib.parse
import sys

BASE_URL = "http://localhost:8000"
TIMEOUT = 30.0

def test_syndic_list_charges():
    session = requests.Session()
    session.headers.update({
        "Accept": "application/json"
    })

    try:
        # 1) Get CSRF cookie
        url_csrf = f"{BASE_URL}/sanctum/csrf-cookie"
        r = session.get(url_csrf, timeout=TIMEOUT)
        assert r.status_code in (200, 204), f"CSRF cookie request failed: {r.status_code}, body: {r.text}"

        # Attach X-XSRF-TOKEN header if cookie present
        xsrf_cookie = session.cookies.get("XSRF-TOKEN")
        if xsrf_cookie:
            session.headers.update({"X-XSRF-TOKEN": urllib.parse.unquote(xsrf_cookie)})

        # 2) POST /login
        url_login = f"{BASE_URL}/login"
        login_payload = {
            "email": "joaosilva@gmail.com",
            "password": "@!T1q2w3e4r"
        }
        r = session.post(url_login, json=login_payload, timeout=TIMEOUT)
        assert 200 <= r.status_code < 400, f"Login failed: {r.status_code}, body: {r.text}"

        # 3) POST /profile/set with role=Síndico
        url_profile = f"{BASE_URL}/profile/set"
        profile_payload = {"role": "Síndico"}
        r = session.post(url_profile, json=profile_payload, timeout=TIMEOUT)
        assert 200 <= r.status_code < 300, f"Set profile failed: {r.status_code}, body: {r.text}"

        # 4) GET /api/charges using same session (cookies preserved)
        url_charges = f"{BASE_URL}/api/charges"
        r = session.get(url_charges, timeout=TIMEOUT)
        assert r.status_code == 200, f"Unexpected status for GET /api/charges: {r.status_code}, body: {r.text}"

        # Validate response body is a list of charges or wrapped in data
        try:
            payload = r.json()
        except ValueError:
            raise AssertionError(f"Response is not valid JSON: {r.text}")

        if isinstance(payload, list):
            assert True  # valid list response
        elif isinstance(payload, dict) and "data" in payload and isinstance(payload["data"], list):
            assert True  # valid paginated/wrapped response
        else:
            raise AssertionError(f"Unexpected JSON structure for charges: {payload}")

        print("test_syndic_list_charges: PASSED")

    except AssertionError:
        raise
    except Exception as e:
        raise AssertionError(f"test_syndic_list_charges: ERROR: {e}") from e
    finally:
        session.close()

if __name__ == "__main__":
    try:
        test_syndic_list_charges()
    except AssertionError as e:
        print(str(e))
        sys.exit(1)
    except Exception as e:
        print(f"Unhandled exception: {e}")
        sys.exit(2)
    sys.exit(0)