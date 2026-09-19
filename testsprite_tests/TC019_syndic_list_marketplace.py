import requests
import urllib.parse
import sys

BASE_URL = "http://localhost:8000"
TIMEOUT = 30

def test_syndic_list_marketplace():
    session = requests.Session()
    created_item_id = None

    try:
        # 1) Get CSRF cookie
        csrf_url = f"{BASE_URL}/sanctum/csrf-cookie"
        r = session.get(csrf_url, timeout=TIMEOUT)
        assert r.status_code == 204 or r.status_code == 200, f"Failed to get CSRF cookie, status: {r.status_code}"
        xsrf_cookie = session.cookies.get("XSRF-TOKEN")
        if xsrf_cookie:
            xsrf_value = urllib.parse.unquote(xsrf_cookie)
            session.headers.update({"X-XSRF-TOKEN": xsrf_value})
        session.headers.update({"Accept": "application/json", "Content-Type": "application/json"})

        # 2) Login
        login_url = f"{BASE_URL}/login"
        login_payload = {"email": "joaosilva@gmail.com", "password": "@!T1q2w3e4r"}
        r = session.post(login_url, json=login_payload, timeout=TIMEOUT)
        # Accept common successful login responses (200, 201, 204, 302)
        assert r.status_code in (200,201,204,302), f"Login failed, status: {r.status_code}, body: {r.text}"

        # 3) Set profile to Síndico (with accent)
        profile_set_url = f"{BASE_URL}/profile/set"
        profile_payload = {"role": "Síndico"}
        r = session.post(profile_set_url, json=profile_payload, timeout=TIMEOUT)
        assert r.status_code in (200,201,204), f"Setting profile failed, status: {r.status_code}, body: {r.text}"

        # 4) Create a marketplace item so the GET /api/marketplace has at least one item to verify.
        create_url = f"{BASE_URL}/api/marketplace"
        new_item = {
            "title": "Test Item from TC019",
            "description": "Temporary marketplace item created by automated test",
            "price": 9.99
        }
        r = session.post(create_url, json=new_item, timeout=TIMEOUT)
        assert r.status_code == 201, f"Failed to create marketplace item, status: {r.status_code}, body: {r.text}"
        created = r.json()
        assert isinstance(created, dict), f"Create response not JSON object: {created}"
        created_item_id = created.get("id")
        assert created_item_id is not None, f"Created item response missing id: {created}"

        # 5) GET /api/marketplace and validate list contains the created item
        list_url = f"{BASE_URL}/api/marketplace"
        r = session.get(list_url, timeout=TIMEOUT)
        assert r.status_code == 200, f"Marketplace list failed, status: {r.status_code}, body: {r.text}"
        data = r.json()
        assert isinstance(data, list), f"Marketplace list response is not a list: {data}"

        # Check that the created item is present in the list (by id or title)
        found = False
        for item in data:
            if not isinstance(item, dict):
                continue
            if created_item_id is not None and item.get("id") == created_item_id:
                found = True
                break
            if item.get("title") == new_item["title"]:
                found = True
                break
        assert found, f"Created marketplace item not found in list. Created id: {created_item_id}, list size: {len(data)}"

        print("TC019 passed: syndic_list_marketplace - marketplace listed and contains created item.")

    except requests.RequestException as e:
        raise AssertionError(f"HTTP request failed: {e}") from e
    finally:
        # Cleanup: attempt to delete created marketplace item if possible
        if created_item_id is not None:
            delete_url = f"{BASE_URL}/api/marketplace/{created_item_id}"
            try:
                r = session.delete(delete_url, timeout=TIMEOUT)
                # Accept common successful delete responses
                if r.status_code in (200,204,202,201):
                    print(f"Cleanup: deleted marketplace item {created_item_id}.")
                else:
                    # If delete not supported or forbidden, just warn but do not fail the test
                    print(f"Cleanup: could not delete marketplace item {created_item_id}, status: {r.status_code}, body: {r.text}", file=sys.stderr)
            except requests.RequestException as e:
                print(f"Cleanup: HTTP error while deleting marketplace item {created_item_id}: {e}", file=sys.stderr)


if __name__ == "__main__":
    test_syndic_list_marketplace()