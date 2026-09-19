import requests
import urllib.parse
import sys

BASE_URL = "http://localhost:8000"
TIMEOUT = 30

def test_syndic_user_search():
    session = requests.Session()
    try:
        # 1) Get CSRF cookie
        csrf_resp = session.get(f"{BASE_URL}/sanctum/csrf-cookie", timeout=TIMEOUT)
        assert csrf_resp.status_code in (200, 204), f"Unexpected CSRF cookie response: {csrf_resp.status_code}"

        # Extract XSRF token from cookies and prepare header
        raw_xsrf = session.cookies.get("XSRF-TOKEN")
        assert raw_xsrf, "XSRF-TOKEN cookie not found after CSRF request"
        xsrf_token = urllib.parse.unquote(raw_xsrf)

        common_headers = {
            "X-XSRF-TOKEN": xsrf_token,
            "Accept": "application/json",
            "Content-Type": "application/json",
        }

        # 2) POST /login
        login_payload = {"email": "joaosilva@gmail.com", "password": "@!T1q2w3e4r"}
        login_resp = session.post(f"{BASE_URL}/login", json=login_payload, headers=common_headers, timeout=TIMEOUT, allow_redirects=True)
        # Accept typical successful login codes (200) and redirects that may occur
        assert login_resp.status_code in (200, 201, 302), f"Login failed: status {login_resp.status_code}, body: {login_resp.text}"

        # 3) POST /profile/set with role = "Síndico"
        profile_payload = {"role": "Síndico"}
        profile_resp = session.post(f"{BASE_URL}/profile/set", json=profile_payload, headers=common_headers, timeout=TIMEOUT)
        assert profile_resp.status_code in (200, 201), f"Setting profile failed: {profile_resp.status_code}, body: {profile_resp.text}"

        # Verify authenticated user and active profile
        user_resp = session.get(f"{BASE_URL}/api/user", headers={"Accept": "application/json"}, timeout=TIMEOUT)
        assert user_resp.status_code == 200, f"Authenticated user check failed: {user_resp.status_code}, body: {user_resp.text}"
        user_json = user_resp.json()
        # Basic sanity checks for user object
        assert isinstance(user_json, (dict,)), "GET /api/user did not return a JSON object"
        # Optionally ensure role or email present
        if "email" in user_json:
            assert user_json.get("email") == "joaosilva@gmail.com", "Authenticated user email mismatch"

        # 4) GET /api/users/search?q=test
        params = {"q": "test"}
        search_resp = session.get(f"{BASE_URL}/api/users/search", params=params, headers={"Accept": "application/json"}, timeout=TIMEOUT)
        assert search_resp.status_code == 200, f"User search failed: {search_resp.status_code}, body: {search_resp.text}"

        # Validate response is JSON and contains results
        content_type = search_resp.headers.get("Content-Type", "")
        assert "application/json" in content_type, f"Expected JSON response, got Content-Type: {content_type}"
        resp_json = search_resp.json()
        assert isinstance(resp_json, (list, dict)), f"Unexpected JSON structure for search results: {type(resp_json)}"

        # If dict, expect either a data/results/users key or non-empty object
        if isinstance(resp_json, dict):
            if not resp_json:
                # empty dict is allowed but warn / fail as test expects results structure
                raise AssertionError("Search returned empty JSON object; expected results structure")
            allowed_keys = {"data", "users", "results", "items"}
            if not (allowed_keys & set(resp_json.keys())) and not any(isinstance(v, list) for v in resp_json.values()):
                # If no known keys and no lists in values, still accept non-empty but warn
                # Consider this acceptable if non-empty; otherwise fail
                # We'll assert that at least one value is list-like (likely results)
                raise AssertionError(f"Search JSON does not contain expected results keys; keys: {list(resp_json.keys())}")

        elif isinstance(resp_json, list):
            # list is acceptable; may be empty or populated
            # Assert at least the response is a list (structure validation)
            pass

        print("TC018 syndic_user_search: PASSED")

    except AssertionError as e:
        print(f"TC018 syndic_user_search: FAILED - {e}")
        raise
    except Exception as e:
        print(f"TC018 syndic_user_search: ERROR - {e}")
        raise
    finally:
        session.close()

if __name__ == "__main__":
    try:
        test_syndic_user_search()
    except Exception:
        sys.exit(1)
    sys.exit(0)