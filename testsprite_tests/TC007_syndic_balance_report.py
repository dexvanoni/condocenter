import requests
import urllib.parse
import sys

BASE_URL = "http://localhost:8000"
EMAIL = "joaosilva@gmail.com"
PASSWORD = "@!T1q2w3e4r"
TIMEOUT = 30

def test_syndic_balance_report():
    session = requests.Session()
    session.headers.update({
        "Accept": "application/json",
        "Content-Type": "application/json"
    })

    try:
        # 1) Get CSRF cookie
        csrf_resp = session.get(f"{BASE_URL}/sanctum/csrf-cookie", timeout=TIMEOUT)
        if not (200 <= csrf_resp.status_code < 300):
            raise AssertionError(f"CSRF cookie request failed: {csrf_resp.status_code} - {csrf_resp.text}")

        # Extract XSRF token from cookies if present and set header
        xsrf_cookie = session.cookies.get("XSRF-TOKEN")
        if xsrf_cookie:
            xsrf_token = urllib.parse.unquote(xsrf_cookie)
            session.headers.update({"X-XSRF-TOKEN": xsrf_token})

        # 2) Login
        login_payload = {"email": EMAIL, "password": PASSWORD}
        login_resp = session.post(f"{BASE_URL}/login", json=login_payload, timeout=TIMEOUT)
        if login_resp.status_code != 200:
            raise AssertionError(f"Login failed: {login_resp.status_code} - {login_resp.text}")

        # 3) Set active profile to Síndico
        profile_payload = {"role": "Síndico"}
        profile_resp = session.post(f"{BASE_URL}/profile/set", json=profile_payload, timeout=TIMEOUT)
        if not (200 <= profile_resp.status_code < 300):
            raise AssertionError(f"Set profile failed: {profile_resp.status_code} - {profile_resp.text}")

        # 4) GET /api/reports/balance
        balance_resp = session.get(f"{BASE_URL}/api/reports/balance", timeout=TIMEOUT)
        if balance_resp.status_code != 200:
            raise AssertionError(f"GET /api/reports/balance returned {balance_resp.status_code}: {balance_resp.text}")

        try:
            data = balance_resp.json()
        except ValueError:
            raise AssertionError("Response from /api/reports/balance is not valid JSON")

        assert isinstance(data, dict), "Balance report response is not a JSON object"
        # Optionally assert non-empty object
        assert data is not None, "Balance report JSON is None"

        print("TC007 passed: /api/reports/balance returned 200 and a JSON object")

    except requests.RequestException as e:
        raise AssertionError(f"HTTP request failed: {e}")

if __name__ == "__main__":
    try:
        test_syndic_balance_report()
    except AssertionError as e:
        print(f"Test failed: {e}")
        sys.exit(1)
    except Exception as e:
        print(f"Unexpected error: {e}")
        sys.exit(2)
    else:
        sys.exit(0)