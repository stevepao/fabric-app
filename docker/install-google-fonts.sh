#!/usr/bin/env bash
# Download selected Google Fonts families as .ttf/.otf into /usr/share/fonts/google-webfonts/<slug>/
set -euo pipefail

DEST=/usr/share/fonts/google-webfonts

dl() {
  local slug="$1"
  local family="$2"
  local tmp="/tmp/gf-${slug}"
  rm -rf "${tmp}.zip" "${tmp}.work"
  mkdir -p "${tmp}.work"

  local enc
  enc="$(python3 -c "import urllib.parse,sys; print(urllib.parse.quote(sys.argv[1]))" "${family}")"

  curl -fsSL \
    -A "Mozilla/5.0 (compatible; fabric-app-docker-font-fetch)" \
    -L \
    "https://fonts.google.com/download?family=${enc}" \
    -o "${tmp}.zip"

  unzip -qo "${tmp}.zip" -d "${tmp}.work"
  mkdir -p "${DEST}/${slug}"
  find "${tmp}.work" -type f \( -name '*.ttf' -o -name '*.otf' \) -exec install -m644 {} "${DEST}/${slug}/" \;
  rm -rf "${tmp}.zip" "${tmp}.work"
}

mkdir -p "${DEST}"

dl noto-sans-jp "Noto Sans JP"
dl noto-serif-jp "Noto Serif JP"
dl murecho "Murecho"
dl yomogi "Yomogi"
# Google Fonts catalog name (often confused with “Hana Mincho”).
dl hina-mincho "Hina Mincho"

fc-cache -f
