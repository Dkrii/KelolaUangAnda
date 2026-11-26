from __future__ import annotations

from dataclasses import dataclass
from io import BytesIO

from PIL import Image

from .parser import parse_receipt_text


@dataclass
class OcrEngine:
    language: str = "en"

    def __post_init__(self) -> None:
        self._engine = None

    def _get_engine(self):
        if self._engine is None:
            from paddleocr import PaddleOCR

            self._engine = PaddleOCR(
                use_doc_orientation_classify=False,
                use_doc_unwarping=False,
                use_textline_orientation=False,
                lang=self.language,
            )
        return self._engine

    def extract(self, data: bytes) -> dict:
        try:
            with Image.open(BytesIO(data)) as probe:
                probe.verify()
        except Exception as exc:
            raise ValueError("File bukan gambar yang valid.") from exc

        with Image.open(BytesIO(data)) as source:
            image = source.convert("RGB")

        import numpy as np

        result = self._get_engine().predict(np.array(image))

        lines: list[str] = []
        scores: list[float] = []
        for item in result:
            payload = item.json if hasattr(item, "json") else {}
            texts = payload.get("rec_texts") if isinstance(payload, dict) else None
            item_scores = payload.get("rec_scores") if isinstance(payload, dict) else None
            if texts is None:
                texts = item.get("rec_texts", []) if hasattr(item, "get") else []
            if item_scores is None:
                item_scores = item.get("rec_scores", []) if hasattr(item, "get") else []
            lines.extend(str(text) for text in texts)
            scores.extend(float(score) for score in item_scores)

        if not lines:
            raise ValueError("Teks tidak terdeteksi pada gambar.")

        parsed = parse_receipt_text("\n".join(lines))
        parsed["ocr_confidence"] = round(sum(scores) / len(scores), 4) if scores else None
        return parsed
