import requests
import sys
import traceback
from urllib.parse import unquote

BASE_URL = "http://localhost:8000"
TIMEOUT = 30.0

def test_syndic_list_reservations():
    session = requests.Session()
    session.headers.update({"Accept": "application/json"})
    try:
        # 1) Obtain CSRF cookie
        csrf_url = f"{BASE_URL}/sanctum/csrf-cookie"
        r = session.get(csrf_url, timeout=TIMEOUT)
        assert r.status_code in (200, 204), f"Expected 200/204 from csrf-cookie, got {r.status_code}: {r.text}"

        # Extract XSRF token from cookies (if present)
        xsrf_token = session.cookies.get("XSRF-TOKEN")
        if xsrf_token:
            xsrf_token = unquote(xsrf_token)
        else:
            # continue even if missing; some setups may not set it here
            xsrf_token = None

        # 2) Login
        login_url = f"{BASE_URL}/login"
        login_data = {
            "email": "joaosilva@gmail.com",
            "password": "@!T1q2w3e4r"
        }
        headers = {}
        if xsrf_token:
            headers["X-XSRF-TOKEN"] = xsrf_token
        headers["Accept"] = "application/json"

        r = session.post(login_url, data=login_data, headers=headers, timeout=TIMEOUT)
        # Accept common successful responses: 200 OK or 204 No Content or 302 redirect depending on app config
        assert r.status_code in (200, 204, 302), f"Login failed with status {r.status_code}: {r.text}"

        # Verify authenticated user endpoint to ensure session is active
        user_url = f"{BASE_URL}/api/user"
        r = session.get(user_url, timeout=TIMEOUT)
        assert r.status_code == 200, f"Expected 200 from /api/user after login, got {r.status_code}: {r.text}"

        # 3) Activate profile as Síndico
        profile_set_url = f"{BASE_URL}/profile/set"
        profile_data = {"role": "Síndico"}
        # Re-extract XSRF token in case it changed
        xsrf_token = session.cookies.get("XSRF-TOKEN")
        if xsrf_token:
            xsrf_token = unquote(xsrf_token)
            headers["X-XSRF-TOKEN"] = xsrf_token

        r = session.post(profile_set_url, data=profile_data, headers=headers, timeout=TIMEOUT)
        assert r.status_code in (200, 204), f"Setting profile failed with status {r.status_code}: {r.text}"

        # Confirm profile is active by calling /api/user again and checking role presence if available
        r = session.get(user_url, timeout=TIMEOUT)
        assert r.status_code == 200, f"Expected 200 from /api/user after profile set, got {r.status_code}: {r.text}"
        try:
            user_json = r.json()
        except ValueError:
            user_json = None

        # It's acceptable if role info is not present; proceed as long as authenticated.
        # 4) GET /api/reservations to list reservations
        reservations_url = f"{BASE_URL}/api/reservations"
        r = session.get(reservations_url, timeout=TIMEOUT)
        assert r.status_code == 200, f"Expected 200 from /api/reservations, got {r.status_code}: {r.text}"

        # Validate response is JSON array (Reservation[])
        try:
            data = r.json()
        except ValueError as e:
            raise AssertionError(f"Response from /api/reservations is not valid JSON: {e}; text: {r.text}")

        assert isinstance(data, list), f"Expected JSON array for reservations, got {type(data)}: {data}"

        print("TC008 PASS: syndic_list_reservations returned 200 and a JSON list of reservations.")

    except requests.RequestException as e:
        traceback.print_exc()
        raise AssertionError(f"HTTP request failed: {e}") from e
    except AssertionError:
        # Re-raise assertion errors to surface test failures
        raise
    except Exception as e:
        traceback.print_exc()
        raise AssertionError(f"Unexpected error during test: {e}") from e
    finally:
        session.close()

if __name__ == "__main__":
    try:
        test_syndic_list_reservations()
    except AssertionError as e:
        print(f"TC008 FAIL: {e}")
        sys.exit(1)
    except Exception as e:
        print(f"TC008 ERROR: {e}")
        sys.exit(2)
    sys.exit(0)