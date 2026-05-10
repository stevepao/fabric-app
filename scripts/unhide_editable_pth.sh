#!/bin/sh
# macOS + Python 3.11+: editable installs may drop a *_editable_impl_*.pth file that is
# marked UF_HIDDEN. site.py then skips it, so `import fabric_app` fails after `pip install -e`.
# Clearing the hidden flag fixes path insertion without reinstalling.
set -e
test -n "${VIRTUAL_ENV}" || {
  echo "Activate your venv first (expected VIRTUAL_ENV to be set)." >&2
  exit 1
}
find "${VIRTUAL_ENV}/lib" -name '_editable_impl_*.pth' -exec chflags nohidden {} \; 2>/dev/null || true
echo "OK — retry: python -c \"import fabric_app\""
