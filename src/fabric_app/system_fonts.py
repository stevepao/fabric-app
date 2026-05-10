"""Platform font file discovery for SVG ``@font-face`` embedding."""

from __future__ import annotations

import sys
from pathlib import Path


def hiragino_mincho_pro_n_ttc() -> Path | None:
    """
    Return Apple’s Hiragino Mincho ProN `.ttc` when present (macOS).

    Tries common paths first, then scans ``/System/Library/Fonts`` for a matching
    collection name (handles Unicode-normalization differences in filenames).
    """
    if sys.platform != "darwin":
        return None

    candidates = (
        Path("/System/Library/Fonts/ヒラギノ明朝 ProN.ttc"),
        Path("/System/Library/Fonts/Hiragino Mincho ProN.ttc"),
        Path("/Library/Fonts/ヒラギノ明朝 ProN.ttc"),
    )
    for p in candidates:
        if p.is_file():
            return p.resolve()

    fonts = Path("/System/Library/Fonts")
    if not fonts.is_dir():
        return None
    for p in fonts.glob("*.ttc"):
        name = p.name
        if "ProN" not in name:
            continue
        if "Mincho" in name or "\u660e\u671d" in name:
            return p.resolve()
    return None


def linux_noto_serif_cjk_regular() -> Path | None:
    """
    Return a common packaged Noto Serif CJK Regular font file (Linux).

    Distros place files under different directories; we try known layouts then a
    shallow glob under ``/usr/share/fonts``.
    """
    if not sys.platform.startswith("linux"):
        return None

    candidates = (
        Path("/usr/share/fonts/opentype/noto/NotoSerifCJK-Regular.ttc"),
        Path("/usr/share/fonts/noto-cjk/NotoSerifCJK-Regular.ttc"),
        Path("/usr/share/fonts/google-noto-serif-cjk/NotoSerifCJK-Regular.ttc"),
        Path("/usr/share/fonts/noto/NotoSerifCJK-Regular.ttc"),
        Path("/usr/share/fonts/truetype/noto/NotoSerifCJK-Regular.ttc"),
    )
    for p in candidates:
        if p.is_file():
            return p.resolve()

    bases = (
        Path("/usr/share/fonts/opentype/noto"),
        Path("/usr/share/fonts/noto-cjk"),
        Path("/usr/share/fonts/google-noto-serif-cjk"),
        Path("/usr/share/fonts/noto"),
        Path("/usr/share/fonts/truetype/noto"),
    )
    for base in bases:
        if not base.is_dir():
            continue
        for p in sorted(base.glob("NotoSerifCJK*Regular*")):
            if p.is_file():
                return p.resolve()
    return None


def default_embedded_font_path() -> Path | None:
    """Best-effort font file for ``@font-face`` on this platform."""
    if sys.platform == "darwin":
        return hiragino_mincho_pro_n_ttc()
    if sys.platform.startswith("linux"):
        return linux_noto_serif_cjk_regular()
    return None
