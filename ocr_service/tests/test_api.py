import pytest
from fastapi.testclient import TestClient

from app.main import app, engine, settings


class FakePredictor:
    def predict(self, image):
        return [
            {
                "rec_texts": [
                    "TOKO SUMBER REJEKI",
                    "25/11/2026 14:32",
                    "Indomie 2 x 3.500 7.000",
                    "TOTAL 7.000",
                ],
                "rec_scores": [0.99, 0.98, 0.97, 0.96],
            }
        ]


@pytest.fixture()
def client():
    return TestClient(app)


@pytest.fixture()
def fake_engine(monkeypatch):
    monkeypatch.setattr(engine, "_get_engine", lambda: FakePredictor())


def test_health(client):
    assert client.get("/health").json() == {"status": "ok"}


def test_ocr_requires_valid_token(client, png_bytes, fake_engine):
    files = {"file": ("struk.png", png_bytes, "image/png")}

    assert client.post("/ocr", files=files).status_code == 401
    assert client.post("/ocr", files=files, headers={"X-OCR-Token": "salah"}).status_code == 401

    response = client.post("/ocr", files=files, headers={"X-OCR-Token": settings.shared_secret})
    assert response.status_code == 200
    body = response.json()
    assert body["merchant"] == "TOKO SUMBER REJEKI"
    assert body["total"] == "7000.00"
    assert body["total_amount"] == 7000
    assert body["currency"] == "IDR"
    assert body["items"][0]["name"] == "Indomie"
    assert body["ocr_confidence"] == 0.975


def test_ocr_rejects_bad_payloads(client, png_bytes, fake_engine):
    headers = {"X-OCR-Token": settings.shared_secret}

    empty = client.post("/ocr", files={"file": ("struk.png", b"", "image/png")}, headers=headers)
    assert empty.status_code == 400

    wrong_type = client.post(
        "/ocr",
        files={"file": ("struk.txt", b"bukan gambar", "text/plain")},
        headers=headers,
    )
    assert wrong_type.status_code == 415

    broken_image = client.post(
        "/ocr",
        files={"file": ("struk.png", b"bukan gambar", "image/png")},
        headers=headers,
    )
    assert broken_image.status_code == 400
