from decimal import Decimal

from app.parser import parse_amount, parse_date, parse_receipt_text


RECEIPT = """TOKO SUMBER REJEKI
Jl. Merdeka 12
25/11/2026  14:32
Indomie 2 x 3.500 7.000
Aqua 1 x 5.000 5.000
Subtotal 12.000
PPN 1.200
TOTAL 13.200
Terima kasih"""


def test_parse_amount_handles_indonesian_and_us_formats():
    assert parse_amount("Rp 12.500") == Decimal("12500.00")
    assert parse_amount("1.234,56") == Decimal("1234.56")
    assert parse_amount("1,234.56") == Decimal("1234.56")
    assert parse_amount("25000") == Decimal("25000.00")
    assert parse_amount("abc") is None


def test_parse_date_handles_common_formats():
    assert parse_date("25/11/2026 14:32") == "2026-11-25"
    assert parse_date("2026-11-25") == "2026-11-25"
    assert parse_date("not a date") is None


def test_parse_receipt_text_extracts_fields_and_checks_total():
    parsed = parse_receipt_text(RECEIPT)

    assert parsed["merchant"] == "TOKO SUMBER REJEKI"
    assert parsed["transaction_date"] == "2026-11-25"
    assert parsed["total"] == "13200.00"
    assert parsed["tax"] == "1200.00"
    assert len(parsed["items"]) == 2
    assert parsed["items"][0] == {
        "name": "Indomie",
        "quantity": "2.00",
        "unit_price": "3500.00",
        "subtotal": "7000.00",
    }
    assert parsed["warnings"] == []
    assert parsed["suggested_category"] == "Makanan & Minuman"


def test_parse_receipt_text_warns_on_mismatched_total():
    parsed = parse_receipt_text("TOKO ABC\nIndomie 2 x 3.500 7.000\nTOTAL 5.000")

    assert parsed["warnings"]
    assert "tidak cocok" in parsed["warnings"][0]
