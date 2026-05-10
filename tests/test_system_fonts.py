"""Platform font discovery."""

import sys

import pytest

from fabric_app.system_fonts import (
    default_embedded_font_path,
    hiragino_mincho_pro_n_ttc,
)


def test_hiragino_none_when_not_darwin(monkeypatch: pytest.MonkeyPatch) -> None:
    monkeypatch.setattr(sys, "platform", "linux")
    assert hiragino_mincho_pro_n_ttc() is None


def test_default_embedded_font_path_darwin_delegates(
    monkeypatch: pytest.MonkeyPatch,
) -> None:
    monkeypatch.setattr(sys, "platform", "darwin")
    monkeypatch.setattr(
        "fabric_app.system_fonts.hiragino_mincho_pro_n_ttc",
        lambda: None,
    )
    assert default_embedded_font_path() is None


def test_default_embedded_font_path_linux_delegates(
    monkeypatch: pytest.MonkeyPatch,
) -> None:
    monkeypatch.setattr(sys, "platform", "linux")
    monkeypatch.setattr(
        "fabric_app.system_fonts.linux_noto_serif_cjk_regular",
        lambda: None,
    )
    assert default_embedded_font_path() is None
