#!/usr/bin/env bash
# Install selected Google Fonts from the upstream google/fonts repo (stable raw URLs).
# The fonts.google.com ZIP endpoint often returns HTML for automated clients (Docker builds).
set -euo pipefail

DEST=/usr/share/fonts/google-webfonts
BASE_URL="${GFONTS_RAW_BASE:-https://raw.githubusercontent.com/google/fonts/main}"

mkdir -p "${DEST}"

verify_font_binary() {
  python3 -c 'import pathlib, sys

p = pathlib.Path(sys.argv[1])
b = p.read_bytes()
if len(b) < 4096:
    raise SystemExit(f"Too small to be a font: {p}")
head = b.lstrip()[:64].lower()
if head.startswith(b"<!doctype") or head.startswith(b"<html"):
    raise SystemExit(f"Download looks like HTML, not a font: {p}")
sig = b[:4]
if sig not in (bytes.fromhex("00010000"), b"OTTO", b"true", b"ttcf"):
    raise SystemExit(f"Unrecognized font signature {sig!r}: {p}")
' "$1"
}

fetch_ttf() {
  local slug="$1"
  local url_path="$2"
  local filename="$3"

  mkdir -p "${DEST}/${slug}"
  local out="${DEST}/${slug}/${filename}"
  curl -fsSL "${BASE_URL}/${url_path}" -o "${out}"
  chmod 644 "${out}"
  verify_font_binary "${out}"
}

fetch_ttf noto-sans-jp "ofl/notosansjp/NotoSansJP%5Bwght%5D.ttf" "NotoSansJP-wght.ttf"
fetch_ttf noto-serif-jp "ofl/notoserifjp/NotoSerifJP%5Bwght%5D.ttf" "NotoSerifJP-wght.ttf"
fetch_ttf murecho "ofl/murecho/Murecho%5Bwght%5D.ttf" "Murecho-wght.ttf"
fetch_ttf yomogi "ofl/yomogi/Yomogi-Regular.ttf" "Yomogi-Regular.ttf"
fetch_ttf hina-mincho "ofl/hinamincho/HinaMincho-Regular.ttf" "HinaMincho-Regular.ttf"

fc-cache -f
