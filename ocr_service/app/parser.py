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

TOTAL_LABEL_PATTERNS = (
    (re.compile(r"\bgrand\s*total\b", re.IGNORECASE), 3),
    (re.compile(r"\btotal\s*belanja\b", re.IGNORECASE), 3),
    (re.compile(r"\btotal\s*bayar\b", re.IGNORECASE), 3),
    (re.compile(r"\btotal\b", re.IGNORECASE), 2),
    (re.compile(r"\bjumlah\b", re.IGNORECASE), 1),
    (re.compile(r"\btagihan\b", re.IGNORECASE), 1),
)
TOTAL_LABEL_PATTERN = re.compile(
    r"\b(?:grand\s*total|total\s*belanja|total\s*bayar|total|jumlah|tagihan)\b",
    re.IGNORECASE,
)
NON_TOTAL_LABEL_PATTERN = re.compile(
    r"\b(?:tunai|cash|kembalian|kembali|diskon|discount|disc|potongan|hemat|"
    r"dpp|ppn|pajak|tax|harga\s*jual|subtotal|no\.?|tgl|tanggal|jam|waktu|kasir)\b",
    re.IGNORECASE,
)
AMOUNT_ONLY_PATTERN = re.compile(r"^(?:Rp\.?\s*)?[\d.,]+$")
IDENTIFIER_PATTERN = re.compile(r"^\d{10,}$")
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


def _best_total_label(line: str) -> tuple[re.Match[str], int] | None:
    best: tuple[re.Match[str], int] | None = None
    for pattern, priority in TOTAL_LABEL_PATTERNS:
        match = pattern.search(line)
        if match is not None and (best is None or priority > best[1]):
            best = (match, priority)
    return best


def _amount_after_label(line: str, label_match: re.Match[str]) -> Decimal | None:
    tail = line[label_match.end():]
    cutoff = NON_TOTAL_LABEL_PATTERN.search(tail)
    if cutoff is not None:
        tail = tail[: cutoff.start()]

    for found in AMOUNT_PATTERN.finditer(tail):
        token = found.group(1)
        if IDENTIFIER_PATTERN.fullmatch(token):
            continue
        amount = parse_amount(token)
        if amount is not None:
            return amount

    return None


def _amount_on_following_line(lines: list[str], index: int) -> Decimal | None:
    if index + 1 >= len(lines):
        return None

    next_line = lines[index + 1].strip()
    if TOTAL_LABEL_PATTERN.search(next_line) or NON_TOTAL_LABEL_PATTERN.search(next_line):
        return None
    if not AMOUNT_ONLY_PATTERN.fullmatch(next_line):
        return None

    token = re.sub(r"(?i)^rp\.?\s*", "", next_line)
    if IDENTIFIER_PATTERN.fullmatch(token):
        return None

    return parse_amount(token)


def _extract_total(lines: list[str]) -> tuple[Decimal | None, str | None]:
    candidates: list[tuple[int, int, Decimal]] = []

    for index, line in enumerate(lines):
        label = _best_total_label(line)
        if label is None:
            continue

        match, priority = label
        amount = _amount_after_label(line, match)
        if amount is None:
            amount = _amount_on_following_line(lines, index)
        if amount is not None:
            candidates.append((priority, index, amount))

    if not candidates:
        return None, None

    highest_priority = max(priority for priority, _, _ in candidates)
    best = next(candidate for candidate in reversed(candidates) if candidate[0] == highest_priority)
    return best[2], "label"


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

    total, total_source = _extract_total(lines)
    total_amount = int(total) if total is not None and total == total.to_integral_value() else None

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
    if total_amount is None:
        warnings.append("Total tidak terdeteksi dengan pasti dari label struk. Isi manual sebelum menyimpan.")

    return {
        "merchant": merchant[:255] if merchant else None,
        "transaction_date": transaction_date,
        "items": items,
        "subtotal": f"{items_total:.2f}",
        "tax": f"{tax:.2f}" if tax is not None else None,
        "discount": f"{discount:.2f}" if discount is not None else None,
        "total": f"{total:.2f}" if total is not None else None,
        "total_amount": total_amount,
        "currency": "IDR",
        "suggested_category": _suggest_category(lines),
        "raw_text": text,
        "warnings": warnings,
        "metadata": {
            "parser": "receipt-rules-v2",
            "line_count": len(lines),
            "total_source": total_source,
            "needs_manual_verification": total_amount is None,
        },
    }
