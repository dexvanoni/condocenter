import requests
import urllib.parse
import sys

BASE_URL = "http://localhost:8000"
TIMEOUT = 30.0

def test_syndic_list_transactions():
    session = requests.Session()
    session.headers.update({
        "Accept": "application/json",
        "Content-Type": "application/json",
    })

    try:
        # 1) Get CSRF cookie
        csrf_url = f"{BASE_URL}/sanctum/csrf-cookie"
        r = session.get(csrf_url, timeout=TIMEOUT)
        if r.status_code not in (200, 204):
            raise AssertionError(f"Failed to obtain CSRF cookie: {r.status_code} {r.text}")

        # Extract XSRF-TOKEN cookie and set X-XSRF-TOKEN header
        xsrf_cookie = session.cookies.get("XSRF-TOKEN")
        if xsrf_cookie:
            xsrf_token = urllib.parse.unquote(xsrf_cookie)
            session.headers.update({"X-XSRF-TOKEN": xsrf_token})
        else:
            # Some deployments may not set the cookie but still work; continue but warn
            print("Warning: XSRF-TOKEN cookie not found; proceeding without X-XSRF-TOKEN header", file=sys.stderr)

        # 2) Login
        login_url = f"{BASE_URL}/login"
        login_payload = {"email": "joaosilva@gmail.com", "password": "@!T1q2w3e4r"}
        r = session.post(login_url, json=login_payload, timeout=TIMEOUT)
        if r.status_code not in (200, 201, 204, 302):
            raise AssertionError(f"Login failed: {r.status_code} {r.text}")

        # 3) Set profile to Síndico
        profile_url = f"{BASE_URL}/profile/set"
        profile_payload = {"role": "Síndico"}
        r = session.post(profile_url, json=profile_payload, timeout=TIMEOUT)
        if r.status_code not in (200, 201, 204):
            raise AssertionError(f"Setting profile failed: {r.status_code} {r.text}")

        # 4) Access protected endpoint: GET /api/transactions
        tx_url = f"{BASE_URL}/api/transactions"
        r = session.get(tx_url, timeout=TIMEOUT)
        # Handle expected outcomes
        if r.status_code == 200:
            # Attempt to parse JSON and validate structure (Transaction[] expected)
            try:
                payload = r.json()
            except ValueError:
                raise AssertionError("Response from /api/transactions is not valid JSON")

            # Transactions may be returned as a list or wrapped in an object with a 'data' key
            if isinstance(payload, dict) and "data" in payload:
                transactions = payload["data"]
            else:
                transactions = payload

            assert isinstance(transactions, list), f"Expected transactions list, got {type(transactions)}"
            # Optionally check that elements look like transaction objects when present
            if len(transactions) > 0:
                first = transactions[0]
                assert isinstance(first, dict), "Transaction item is not an object/dict"
                # Common expected keys (best-effort)
                expected_keys = ("id", "amount", "date")
                assert any(k in first for k in expected_keys), f"Transaction object missing expected keys; sample keys: {list(first.keys())}"
            print("Test passed: /api/transactions returned 200 and a transactions list")
            return

        elif r.status_code == 403:
            raise AssertionError(f"/api/transactions returned 403 Forbidden - full financial mode or permissions may be disabled: {r.text}")
        elif r.status_code == 401:
            raise AssertionError(f"/api/transactions returned 401 Unauthorized - authentication/session failed: {r.text}")
        else:
            raise AssertionError(f"Unexpected status code from /api/transactions: {r.status_code} {r.text}")

    except requests.RequestException as e:
        raise AssertionError(f"HTTP request failed: {e}")

if __name__ == "__main__":
    test_syndic_list_transactions()