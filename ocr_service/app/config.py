from __future__ import annotations

import os
from dataclasses import dataclass


@dataclass(frozen=True)
class Settings:
    shared_secret: str
    language: str
    max_upload_bytes: int


def load_settings() -> Settings:
    secret = os.getenv("OCR_SHARED_SECRET")
    if not secret:
        raise RuntimeError("OCR_SHARED_SECRET environment variable is required")

    return Settings(
        shared_secret=secret,
        language=os.getenv("OCR_LANG", "en"),
        max_upload_bytes=int(os.getenv("OCR_MAX_UPLOAD_BYTES", str(10 * 1024 * 1024))),
    )
