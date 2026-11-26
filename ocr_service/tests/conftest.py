import os

os.environ.setdefault("OCR_SHARED_SECRET", "test-secret")

from io import BytesIO

import pytest
from PIL import Image


@pytest.fixture()
def png_bytes() -> bytes:
    buffer = BytesIO()
    Image.new("RGB", (8, 8), "white").save(buffer, format="PNG")
    return buffer.getvalue()
