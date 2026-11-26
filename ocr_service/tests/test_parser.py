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


TLOGOMAS_RECEIPT = """TLOGOMAS 44 MALANG 08113336422
JLN RAYA TLOGOMAS NO. 44 RT. 001 RW. 004
21.01.22-18:27/2.2.1/F4UB 354501/SELVI/01

POP MIE AYAM 75G       1  4900   4900
POP MIE PD.DWR AYM75   1  5400   5400
NESTLE PURE LIFE 600   2  3600   7200
LE MINERALE 600ML      2  3500   7000
ULTRA KCG HIJAU 250    1  4900   4900
NUTRIJEL PWD.STRW.15   2  6600  13200
KNZLER SNGL.ES KJU 65  1  8700   8700
KNZLER SNGLES HOT 65   1  8700   8700
KANZLR BAKSO ORI 48G   2  8700  17400

HARGA JUAL : 77,400
VC NUTRIJEL : (2,000)

TOTAL   : 75,400
TUNAI   : 100,500
KEMBALI : 25,100
ANDA HEMAT : 2,000
DPP=70,364 PPN=7,036
"""


KOPI_NAKO_RECEIPT = """KOPI NAKO
Print :1
Summarecon Bekasi
==
Jun 18, 2023 7:32:42 PM
By: Anggi
Computer Name: cashier1
Served By: Anggi
Jun 18, 2023 7:33:47 PM
TAKE
TOTAL 75,400
"""


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


def test_total_comes_from_total_label_not_phone_or_other_amounts():
    parsed = parse_receipt_text(TLOGOMAS_RECEIPT)

    assert parsed["total_amount"] == 75400
    assert parsed["total"] == "75400.00"
    assert parsed["currency"] == "IDR"
    assert parsed["metadata"]["total_source"] == "label"
    assert parsed["total_amount"] not in {8113366422, 77400, 100500, 25100, 70364, 7036}


def test_total_ignores_non_total_labels_on_the_same_line():
    parsed = parse_receipt_text("TOTAL : 75,400 TUNAI : 100,500 KEMBALI : 25,100")

    assert parsed["total_amount"] == 75400


def test_total_prefers_specific_label_and_last_occurrence():
    parsed = parse_receipt_text("TOTAL 75,400\nGRAND TOTAL 75,500\nTUNAI 100,500")

    assert parsed["total_amount"] == 75500


def test_total_accepts_common_nominal_formats():
    for candidate in ("75,400", "75.400", "75400", "Rp75.400"):
        parsed = parse_receipt_text(f"TOTAL {candidate}")

        assert parsed["total_amount"] == 75400


def test_total_can_read_amount_on_following_line():
    parsed = parse_receipt_text("TOTAL\n75,400")

    assert parsed["total_amount"] == 75400


def test_total_requires_label_instead_of_largest_amount():
    parsed = parse_receipt_text("TOKO CONTOH\n08113336422\n100,500\n25,100")

    assert parsed["total_amount"] is None
    assert parsed["total"] is None
    assert parsed["metadata"]["needs_manual_verification"] is True
    assert any("Isi manual" in warning for warning in parsed["warnings"])


def test_total_rejects_identifier_shaped_amount_after_label():
    parsed = parse_receipt_text("TOTAL 08113336422")

    assert parsed["total_amount"] is None


def test_parse_date_supports_all_required_formats():
    candidates = (
        "Jun 18, 2023",
        "Jun 18, 2023 7:32:42 PM",
        "June 18, 2023",
        "June 18, 2023 7:32:42 PM",
        "18/06/2023",
        "18-06-2023",
        "2023-06-18",
    )

    for candidate in candidates:
        assert parse_date(candidate) == "2023-06-18", candidate


def test_parse_date_supports_english_and_indonesian_months():
    candidates = (
        ("18 Jun 2023", "2023-06-18"),
        ("18 June 2023", "2023-06-18"),
        ("18 Juni 2023", "2023-06-18"),
        ("Jan 18 2023", "2023-01-18"),
        ("18 Januari 2023", "2023-01-18"),
        ("Feb 18 2023", "2023-02-18"),
        ("18 Februari 2023", "2023-02-18"),
        ("Mar 18 2023", "2023-03-18"),
        ("18 Maret 2023", "2023-03-18"),
        ("Apr 18 2023", "2023-04-18"),
        ("18 April 2023", "2023-04-18"),
        ("Mei 18 2023", "2023-05-18"),
        ("May 18 2023", "2023-05-18"),
        ("Jul 18 2023", "2023-07-18"),
        ("18 Juli 2023", "2023-07-18"),
        ("Aug 18 2023", "2023-08-18"),
        ("18 Agustus 2023", "2023-08-18"),
        ("18 Ags 2023", "2023-08-18"),
        ("Sep 18 2023", "2023-09-18"),
        ("18 September 2023", "2023-09-18"),
        ("Okt 18 2023", "2023-10-18"),
        ("18 Oktober 2023", "2023-10-18"),
        ("Oct 18 2023", "2023-10-18"),
        ("18 October 2023", "2023-10-18"),
        ("Nov 18 2023", "2023-11-18"),
        ("18 November 2023", "2023-11-18"),
        ("Des 18 2023", "2023-12-18"),
        ("18 Desember 2023", "2023-12-18"),
        ("Dec 18 2023", "2023-12-18"),
        ("18 December 2023", "2023-12-18"),
    )

    for candidate, expected in candidates:
        assert parse_date(candidate) == expected, candidate


def test_parse_date_supports_12_hour_timestamps():
    assert parse_date("Jun 18, 2023 7:32:42 PM") == "2023-06-18"
    assert parse_date("Jun 18, 2023 7:32:42 AM") == "2023-06-18"
    assert parse_date("June 18, 2023 12:01:02 AM") == "2023-06-18"


def test_parse_receipt_text_uses_same_date_timestamps():
    parsed = parse_receipt_text(
        "KOPI NAKO\nJun 18, 2023 7:32:42 PM\nJun 18, 2023 7:33:47 PM\nTOTAL 75,400"
    )

    assert parsed["transaction_date"] == "2023-06-18"
    assert "Tanggal transaksi tidak terdeteksi" not in parsed["warnings"]
    assert parsed["metadata"]["date_status"] == "detected"
    assert parsed["metadata"]["needs_manual_verification"] is False


def test_parse_receipt_text_marks_conflicting_dates_for_manual_entry():
    parsed = parse_receipt_text("Tanggal 17/06/2023\nTanggal 18/06/2023\nTOTAL 75,400")

    assert parsed["transaction_date"] is None
    assert any("Beberapa tanggal berbeda" in warning for warning in parsed["warnings"])
    assert parsed["metadata"]["date_status"] == "ambiguous"
    assert parsed["metadata"]["needs_manual_verification"] is True


def test_parse_receipt_text_uses_labeled_date_when_unlabeled_dates_conflict():
    parsed = parse_receipt_text("17/06/2023\n18/06/2023\nTANGGAL 19/06/2023\nTOTAL 75,400")

    assert parsed["transaction_date"] == "2023-06-19"
    assert parsed["metadata"]["date_status"] == "detected"


def test_parse_receipt_text_reports_missing_date_without_guessing_today():
    parsed = parse_receipt_text("KOPI NAKO\nTOTAL 75,400")

    assert parsed["transaction_date"] is None
    assert "Tanggal transaksi tidak terdeteksi. Isi manual sebelum menyimpan." in parsed["warnings"]
    assert parsed["metadata"]["date_status"] == "missing"
    assert parsed["metadata"]["needs_manual_verification"] is True


def test_parse_receipt_text_extracts_kopi_nako_date():
    parsed = parse_receipt_text(KOPI_NAKO_RECEIPT)

    assert parsed["merchant"] == "KOPI NAKO"
    assert parsed["transaction_date"] == "2023-06-18"
    assert "Tanggal transaksi tidak terdeteksi" not in parsed["warnings"]
    assert parsed["metadata"]["date_status"] == "detected"
