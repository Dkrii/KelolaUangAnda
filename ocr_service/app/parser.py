from __future__ import annotations

import re
from datetime import date
from decimal import Decimal, InvalidOperation


AMOUNT_PATTERN = re.compile(
    r"(?<![\d.,])(\d{1,3}(?:[.,]\d{3})+(?:[.,]\d{1,2})?|\d+(?:[.,]\d{1,2})?)(?![\d.,])"
)

DATE_PATTERNS = [
    (re.compile(r"\b(\d{1,2})[/-](\d{1,2})[/-](\d{4})\b"), "dmy"),
    (re.compile(r"\b(\d{4})-(\d{1,2})-(\d{1,2})\b"), "ymd"),
    (re.compile(r"\b(\d{1,2})[/-](\d{1,2})[/-](\d{2})\b"), "dmy"),
]

TOTAL_LABEL_PATTERN = re.compile(r"\b(?:grand\s*total|total\s*bayar|total|jumlah|tagihan)\b", re.IGNORECASE)
ITEM_QTY_UNIT_PATTERN = re.compile(
    r"^(?P<name>.{2,}?)\s+(?P<qty>\d{1,3}(?:[.,]\d+)?)\s*[xX]\s*(?P<unit>\d[\d.,]*)(?:\s+(?P<amount>\d[\d.,]*))?$"
)
ITEM_QTY_AMOUNT_PATTERN = re.compile(
    r"^(?P<name>.{2,}?)\s+(?P<qty>\d{1,2})\s+(?P<amount>\d[\d.,]*)$"
)

NOISE_TOKENS = (
    "STRUK",
    "NOTA",
    "RECEIPT",
    "INVOICE",
    "KWITANSI",
    "TANGGAL",
    "TGL",
    "WAKTU",
    "JAM",
    "KASIR",
    "TRX",
    "TERIMA KASIH",
    "SIMPAN STRUK",
)

CATEGORY_KEYWORDS = (
    (("INDOMIE", "MIE", "MAKAN", "NASI", "AYAM", "KOPI", "TEH", "ROTI", "CAFE", "RESTORAN"), "Makanan & Minuman"),
    (("BENSIN", "PERTAMAX", "PERTADEX", "TOLL", "TOL", "GRAB", "GOJEK", "TAXI", "PARKIR"), "Transportasi"),
    (("ALFAMART", "INDOMARET", "SUPERMARKET", "TOKO", "WARUNG", "BELANJA"), "Belanja"),
    (("APOTEK", "OBAT", "KLINIK", "RS", "HEALTH"), "Kesehatan"),
)


def _is_grouped(value: str, separator: str) -> bool:
    parts = value.split(separator)
    return len(parts) > 1 and bool(parts[0]) and parts[0].isdigit() and all(len(part) == 3 for part in parts[1:])


def parse_amount(value: object) -> Decimal | None:
    if value is None:
        return None

    text = str(value).strip()
    text = re.sub(r"(?i)\bRp\.?\s*", "", text)
    text = text.replace(" ", "")
    text = re.sub(r"[^\d.,-]", "", text)

    if not text or not re.search(r"\d", text):
        return None

    negative = text.startswith("-")
    text = text.lstrip("-")

    if "," in text and "." in text:
        if text.rfind(",") > text.rfind("."):
            text = text.replace(".", "").replace(",", ".")
        else:
            text = text.replace(",", "")
    elif "," in text:
        if _is_grouped(text, ","):
            text = text.replace(",", "")
        else:
            text = text.replace(",", ".")
    elif "." in text and _is_grouped(text, "."):
        text = text.replace(".", "")

    try:
        amount = Decimal(text)
    except InvalidOperation:
        return None

    if negative:
        amount = -amount

    return amount.quantize(Decimal("0.01"))


def parse_date(value: str) -> str | None:
    for pattern, order in DATE_PATTERNS:
        match = pattern.search(value)
        if not match:
            continue

        try:
            if order == "ymd":
                year, month, day = (int(part) for part in match.groups())
            else:
                day, month, year = (int(part) for part in match.groups())
                if year < 100:
                    year += 2000 if year < 70 else 1900

            return date(year, month, day).isoformat()
        except ValueError:
            continue

    return None


def _is_noise(line: str) -> bool:
    upper = line.upper()
    if any(token in upper for token in NOISE_TOKENS):
        return True
    if re.search(r"\b\d{1,2}[/-]\d{1,2}[/-]\d{2,4}\b", line):
        return True
    if re.match(r"^\d{1,2}:\d{2}", line):
        return True
    return False


def _last_amount(line: str) -> Decimal | None:
    amounts = AMOUNT_PATTERN.findall(line)
    if not amounts:
        return None
    return parse_amount(amounts[-1])


def _suggest_category(lines: list[str]) -> str | None:
    joined = " ".join(lines).upper()
    for keywords, category in CATEGORY_KEYWORDS:
        if any(keyword in joined for keyword in keywords):
            return category
    return None


def parse_receipt_text(text: str) -> dict:
    lines = [line.strip() for line in (text or "").splitlines() if line.strip()]
    merchant = next((line for line in lines if not _is_noise(line)), None)
    transaction_date = next((found for line in lines if (found := parse_date(line))), None)

    total = None
    for line in reversed(lines):
        if TOTAL_LABEL_PATTERN.search(line) and "SUBTOTAL" not in line.upper():
            total = _last_amount(line)
            if total is not None:
                break

    if total is None:
        candidates = [amount for line in lines if (amount := _last_amount(line)) is not None]
        if candidates:
            total = max(candidates)

    tax = None
    discount = None
    for line in lines:
        upper = line.upper()
        if any(token in upper for token in ("PPN", "PAJAK", "TAX")):
            tax = _last_amount(line)
        if any(token in upper for token in ("DISKON", "DISCOUNT", "DISC", "POTONGAN")):
            discount = _last_amount(line)

    items = []
    for line in lines:
        if _is_noise(line) or TOTAL_LABEL_PATTERN.search(line):
            continue

        match = ITEM_QTY_UNIT_PATTERN.match(line)
        if match:
            quantity = parse_amount(match.group("qty"))
            unit_price = parse_amount(match.group("unit"))
            subtotal = parse_amount(match.group("amount"))
            if quantity is None or unit_price is None:
                continue
            if subtotal is None:
                subtotal = (quantity * unit_price).quantize(Decimal("0.01"))
        else:
            match = ITEM_QTY_AMOUNT_PATTERN.match(line)
            if not match:
                continue
            quantity = parse_amount(match.group("qty"))
            subtotal = parse_amount(match.group("amount"))
            unit_price = subtotal
            if quantity is None or subtotal is None:
                continue

        items.append(
            {
                "name": match.group("name").strip()[:255],
                "quantity": f"{quantity:.2f}",
                "unit_price": f"{unit_price:.2f}",
                "subtotal": f"{subtotal:.2f}",
            }
        )

    items_total = sum((Decimal(item["subtotal"]) for item in items), Decimal("0.00"))
    expected_total = items_total + (tax or Decimal("0.00")) - (discount or Decimal("0.00"))

    warnings = []
    if total is not None and items and abs(expected_total - total) > Decimal("0.01"):
        warnings.append(
            f"Jumlah item ({expected_total:.2f}) tidak cocok dengan total struk ({total:.2f}). Periksa kembali."
        )
    if transaction_date is None:
        warnings.append("Tanggal transaksi tidak terdeteksi. Isi manual sebelum menyimpan.")
    if total is None:
        warnings.append("Total pembayaran tidak terdeteksi. Isi manual sebelum menyimpan.")

    return {
        "merchant": merchant[:255] if merchant else None,
        "transaction_date": transaction_date,
        "items": items,
        "subtotal": f"{items_total:.2f}",
        "tax": f"{tax:.2f}" if tax is not None else None,
        "discount": f"{discount:.2f}" if discount is not None else None,
        "total": f"{total:.2f}" if total is not None else None,
        "suggested_category": _suggest_category(lines),
        "raw_text": text,
        "warnings": warnings,
        "metadata": {"parser": "receipt-rules-v1", "line_count": len(lines)},
    }
