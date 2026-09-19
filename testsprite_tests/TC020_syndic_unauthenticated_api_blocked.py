import requests
import sys

BASE_URL = "http://localhost:8000"
TIMEOUT = 30

def test_syndic_unauthenticated_api_blocked():
    """
    TC020: Without session, GET /api/charges should return 401 Unauthorized.
    """
    session = requests.Session()
    # Ensure no cookies or auth are present
    session.cookies.clear()

    url = f"{BASE_URL}/api/charges"
    headers = {
        "Accept": "application/json"
    }

    try:
        resp = session.get(url, headers=headers, timeout=TIMEOUT, allow_redirects=False)
    except requests.exceptions.RequestException as e:
        raise AssertionError(f"Request to {url} failed with exception: {e}") from e

    # Validate response status code is 401 Unauthorized
    if resp.status_code != 401:
        # Provide diagnostic info in the assertion message
        body_preview = resp.text[:1000] if resp.text else "<empty body>"
        raise AssertionError(
            f"Expected 401 Unauthorized for unauthenticated request to {url}, "
            f"but got {resp.status_code}. Response headers: {dict(resp.headers)}. Body preview: {body_preview}"
        )

    # Optionally validate standard unauthorized response structure (if JSON)
    content_type = resp.headers.get("Content-Type", "")
    if "application/json" in content_type:
        try:
            payload = resp.json()
        except ValueError:
            raise AssertionError("Response Content-Type is JSON but body is not valid JSON")
        # Common Laravel unauthorized response may include 'message' or 'error'
        assert isinstance(payload, dict), "Expected JSON object in 401 response"
    print("TC020 passed: Unauthenticated GET /api/charges returned 401 as expected.")

if __name__ == "__main__":
    try:
        test_syndic_unauthenticated_api_blocked()
    except AssertionError as e:
        print(f"TC020 failed: {e}")
        sys.exit(1)
    except Exception as e:
        print(f"TC020 encountered an unexpected error: {e}")
        sys.exit(2)
    sys.exit(0)