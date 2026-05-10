"""PNG rendering from SVG via the ``resvg`` CLI."""

from __future__ import annotations

import os
import shutil
import subprocess
import tempfile
from pathlib import Path


def _resvg_bin() -> str | None:
    return shutil.which("resvg")


def svg_to_png(svg_text: str, png_path: Path, *, scale: float = 1.0) -> None:
    """Rasterize SVG to PNG using ``resvg`` (must be on ``PATH``)."""
    bin_resvg = _resvg_bin()
    if not bin_resvg:
        raise RuntimeError(
            "PNG export requires the `resvg` binary on PATH. "
            "Install it (e.g. `brew install resvg`) and retry.",
        )

    out = png_path.expanduser().resolve()
    out.parent.mkdir(parents=True, exist_ok=True)

    fd, tmp_name = tempfile.mkstemp(suffix=".svg")
    try:
        os.write(fd, svg_text.encode("utf-8"))
        os.close(fd)
        fd = -1
        cmd = [bin_resvg]
        if scale != 1.0:
            cmd.extend(["-z", f"{scale:.6g}"])
        cmd.extend([tmp_name, str(out)])
        proc = subprocess.run(cmd, capture_output=True, text=True)
        if proc.returncode != 0:
            msg = proc.stderr.strip() or proc.stdout.strip() or f"exit {proc.returncode}"
            raise RuntimeError(f"resvg failed: {msg}")
        if not out.is_file() or out.stat().st_size < 50:
            raise RuntimeError("resvg produced an empty or missing PNG.")
    finally:
        if fd >= 0:
            os.close(fd)
        Path(tmp_name).unlink(missing_ok=True)
