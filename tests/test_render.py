"""PNG rasterization via resvg."""

import shutil
from pathlib import Path

import pytest

from fabric_app.render import svg_to_png

MIN_SVG = (
    '<?xml version="1.0" encoding="UTF-8"?>'
    '<svg xmlns="http://www.w3.org/2000/svg" width="10" height="10">'
    '<rect width="10" height="10" fill="red"/></svg>'
)


def test_svg_to_png_requires_resvg(monkeypatch: pytest.MonkeyPatch, tmp_path: Path) -> None:
    monkeypatch.setattr("fabric_app.render._resvg_bin", lambda: None)
    with pytest.raises(RuntimeError, match="resvg"):
        svg_to_png(MIN_SVG, tmp_path / "out.png")


@pytest.mark.skipif(not shutil.which("resvg"), reason="resvg not installed")
def test_svg_to_png_resvg_writes_png(tmp_path: Path) -> None:
    out = tmp_path / "out.png"
    svg_to_png(MIN_SVG, out)
    assert out.is_file() and out.stat().st_size > 50
