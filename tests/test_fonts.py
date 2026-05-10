"""Font stack helpers."""

import sys

import pytest

from fabric_app.fonts import FONT_FALLBACK_CJK, default_font_family, font_family_stack_css


def test_default_font_family_darwin(monkeypatch: pytest.MonkeyPatch) -> None:
    monkeypatch.setattr(sys, "platform", "darwin")
    assert default_font_family() == "ヒラギノ明朝 ProN"


def test_default_font_family_linux(monkeypatch: pytest.MonkeyPatch) -> None:
    monkeypatch.setattr(sys, "platform", "linux")
    assert default_font_family() == FONT_FALLBACK_CJK


def test_font_family_stack_css_mac_primary_includes_noto_fallback() -> None:
    assert (
        font_family_stack_css("ヒラギノ明朝 ProN")
        == '"ヒラギノ明朝 ProN", "Noto Serif CJK JP", serif'
    )


def test_font_family_stack_css_linux_primary_skips_duplicate_noto() -> None:
    assert font_family_stack_css("Noto Serif CJK JP") == '"Noto Serif CJK JP", serif'


def test_font_family_stack_css_escapes_quotes() -> None:
    assert (
        font_family_stack_css('Foo"Bar')
        == r'"Foo\"Bar", "Noto Serif CJK JP", serif'
    )
