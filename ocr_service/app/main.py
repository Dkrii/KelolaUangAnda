from __future__ import annotations

import hmac
from typing import Annotated

from fastapi import Depends, FastAPI, File, Header, HTTPException, UploadFile

from .config import load_settings
from .ocr import OcrEngine


settings = load_settings()
engine = OcrEngine(language=settings.language)
app = FastAPI(title="OCR Struk", version="1.0.0")

ALLOWED_CONTENT_TYPES = {"image/jpeg", "image/png", "image/webp"}


async def verify_token(x_ocr_token: Annotated[str | None, Header()] = None) -> None:
    if not x_ocr_token or not hmac.compare_digest(x_ocr_token, settings.shared_secret):
        raise HTTPException(status_code=401, detail="Token tidak valid")


@app.get("/health")
def health() -> dict:
    return {"status": "ok"}


@app.post("/ocr")
async def scan(
    file: Annotated[UploadFile, File(...)],
    _: Annotated[None, Depends(verify_token)] = None,
) -> dict:
    data = await file.read()

    if not data:
        raise HTTPException(status_code=400, detail="File kosong.")
    if len(data) > settings.max_upload_bytes:
        raise HTTPException(status_code=413, detail="Ukuran gambar terlalu besar.")
    if (file.content_type or "") not in ALLOWED_CONTENT_TYPES:
        raise HTTPException(status_code=415, detail="Format gambar tidak didukung.")

    try:
        return engine.extract(data)
    except ValueError as exc:
        raise HTTPException(status_code=400, detail=str(exc)) from exc
    except Exception as exc:
        raise HTTPException(status_code=500, detail="OCR gagal diproses.") from exc
