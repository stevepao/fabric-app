"""Typer CLI entry point."""

from __future__ import annotations

from pathlib import Path
from typing import Annotated

import typer

from fabric_app.fonts import default_font_family
from fabric_app.models import RGB, FabricParams
from fabric_app.render import svg_to_png
from fabric_app.svg_gen import build_svg
from fabric_app.system_fonts import default_embedded_font_path

app = typer.Typer(no_args_is_help=True, add_completion=False)


@app.callback()
def _cli_root() -> None:
    """Deterministic seamless fabric tile generator."""


def _parse_rgb(spec: str) -> RGB:
    parts = spec.replace(" ", "").split(",")
    if len(parts) != 3:
        raise typer.BadParameter("Expected r,g,b with commas")
    r, g, b = (int(p) for p in parts)
    return RGB(r=r, g=g, b=b)


@app.command("render")
def render_cmd(
    png_out: Annotated[
        Path,
        typer.Option("--png-out", help="Output PNG path."),
    ],
    svg_out: Annotated[
        Path | None,
        typer.Option("--svg-out", help="Optional output SVG path."),
    ] = None,
    canvas_w: Annotated[float, typer.Option("--canvas-w", help="Canvas width.")] = 1260.0,
    canvas_h: Annotated[float, typer.Option("--canvas-h", help="Canvas height.")] = 1260.0,
    grid_cols: Annotated[int, typer.Option("--grid-cols", min=1)] = 18,
    grid_rows: Annotated[int, typer.Option("--grid-rows", min=1)] = 18,
    cell_size: Annotated[float, typer.Option("--cell-size", help="Cell edge length.")] = 70.0,
    background: Annotated[
        str,
        typer.Option("--background", help="Background r,g,b."),
    ] = "130,130,130",
    ink_black: Annotated[
        str,
        typer.Option("--ink-black", help="Ink 0 (black) r,g,b."),
    ] = "0,0,0",
    ink_lightgrey: Annotated[
        str,
        typer.Option("--ink-lightgrey", help="Ink 1 (light grey) r,g,b."),
    ] = "210,210,210",
    ink_white: Annotated[
        str,
        typer.Option("--ink-white", help="Ink 2 (white) r,g,b."),
    ] = "255,255,255",
    font_family: Annotated[
        str,
        typer.Option(
            "--font-family",
            help=(
                "Primary CSS font family. Default: ヒラギノ明朝 ProN on macOS, "
                "Noto Serif CJK JP on Linux; stack adds Noto (if distinct) and serif."
            ),
        ),
    ] = default_font_family(),
    font_path: Annotated[
        Path | None,
        typer.Option("--font-path", help="Optional path for @font-face embedding."),
    ] = None,
    font_size_ratio: Annotated[
        float,
        typer.Option("--font-size-ratio", help="Font size as fraction of cell size."),
    ] = 0.70,
    delta_ratio: Annotated[
        float,
        typer.Option("--delta-ratio", help="Vertical drift magnitude vs cell size."),
    ] = 0.16,
) -> None:
    """Generate a deterministic fabric tile as PNG (and optionally SVG)."""

    resolved_font_path: str | None = str(font_path) if font_path else None
    if resolved_font_path is None:
        embedded = default_embedded_font_path()
        if embedded is not None:
            resolved_font_path = str(embedded)

    params = FabricParams(
        canvas_width=canvas_w,
        canvas_height=canvas_h,
        grid_cols=grid_cols,
        grid_rows=grid_rows,
        cell_size=cell_size,
        background=_parse_rgb(background),
        ink_black=_parse_rgb(ink_black),
        ink_lightgrey=_parse_rgb(ink_lightgrey),
        ink_white=_parse_rgb(ink_white),
        font_family=font_family,
        font_path=resolved_font_path,
        font_size_ratio=font_size_ratio,
        delta_ratio=delta_ratio,
    )

    svg_text = build_svg(params)

    if svg_out is not None:
        svg_out.parent.mkdir(parents=True, exist_ok=True)
        svg_out.write_text(svg_text, encoding="utf-8")

    svg_to_png(svg_text, png_out)


def main() -> None:
    app()


if __name__ == "__main__":
    main()
