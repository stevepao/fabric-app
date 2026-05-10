"""Font defaults and deterministic CSS font-family stacks."""

from __future__ import annotations

import sys

# Secondary face when primary is not Noto (macOS Hiragino path).
FONT_FALLBACK_CJK = "Noto Serif CJK JP"


def default_font_family() -> str:
    """Primary CSS font family for this OS (single tree; tuned defaults)."""
    if sys.platform == "darwin":
        # Localized name — resolves well with Core Text / typical macOS stacks.
        return "ヒラギノ明朝 ProN"
    if sys.platform.startswith("linux"):
        return FONT_FALLBACK_CJK
    # Other platforms: prefer widely packaged Noto naming.
    return FONT_FALLBACK_CJK


def _escape_css_font_family_name(name: str) -> str:
    return name.replace("\\", "\\\\").replace('"', '\\"')


def font_family_stack_css(primary: str) -> str:
    """
    CSS `font-family` value: primary, optional Noto fallback if distinct, then serif.

    If ``primary`` is already ``Noto Serif CJK JP``, the Noto fallback is not duplicated.
    """
    primary_stripped = primary.strip()
    p = _escape_css_font_family_name(primary_stripped)
    parts: list[str] = [f'"{p}"']
    if primary_stripped != FONT_FALLBACK_CJK:
        parts.append(f'"{FONT_FALLBACK_CJK}"')
    parts.append("serif")
    return ", ".join(parts)
