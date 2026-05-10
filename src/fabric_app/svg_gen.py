"""Deterministic SVG generation with stable element ordering."""

from __future__ import annotations

import html
import xml.etree.ElementTree as ET
from pathlib import Path

from fabric_app.fonts import font_family_stack_css
from fabric_app.models import FabricParams
from fabric_app.pattern import color_index, delta_y_px, glyph_char


def _fmt_coord(x: float) -> str:
    """Fixed-width style coords for stable diffs."""
    s = f"{x:.4f}"
    if "." in s:
        s = s.rstrip("0").rstrip(".")
    return s if s else "0"


def build_svg(params: FabricParams) -> str:
    """
    Build SVG document. Elements are emitted in column-major order (col 1→cols),
    rows 1→grid_rows within each column, for deterministic diffs.
    """
    w = params.canvas_width
    h = params.canvas_height
    cols = params.grid_cols
    rows = params.grid_rows
    cell = params.cell_size
    inks = params.inks

    font_px = params.font_size_ratio * cell

    ns = "http://www.w3.org/2000/svg"
    root = ET.Element(f"{{{ns}}}svg")
    root.set("xmlns", ns)
    root.set("width", _fmt_coord(w))
    root.set("height", _fmt_coord(h))
    root.set("viewBox", f"0 0 {_fmt_coord(w)} {_fmt_coord(h)}")
    root.set("version", "1.1")

    defs = ET.SubElement(root, f"{{{ns}}}defs")
    style_el = ET.SubElement(defs, f"{{{ns}}}style")
    style_el.set("type", "text/css")

    face_rules: list[str] = []
    if params.font_path:
        uri = Path(params.font_path).expanduser().resolve().as_uri()
        fam_esc = html.escape(params.font_family, quote=True)
        face_rules.append(
            f"@font-face {{ font-family: '{fam_esc}'; src: url('{uri}'); }}",
        )

    ink_css = [ink.css() for ink in inks]
    face_rules.append(
        f".fabric-bg {{ fill: {params.background.css()}; }}",
    )
    font_sz = _fmt_coord(font_px)
    face_rules.append(
        f".fabric-glyph {{ font-size: {font_sz}px; "
        "dominant-baseline: central; text-anchor: middle; }",
    )
    face_rules.append(f".fabric-ink0 {{ fill: {ink_css[0]}; }}")
    face_rules.append(f".fabric-ink1 {{ fill: {ink_css[1]}; }}")
    face_rules.append(f".fabric-ink2 {{ fill: {ink_css[2]}; }}")

    style_el.text = "\n".join(face_rules) + "\n"

    bg = ET.SubElement(root, f"{{{ns}}}rect")
    bg.set("class", "fabric-bg")
    bg.set("x", "0")
    bg.set("y", "0")
    bg.set("width", _fmt_coord(w))
    bg.set("height", _fmt_coord(h))

    grid_height_px = rows * cell
    cy_rot_base = grid_height_px / 2.0

    for col in range(1, cols + 1):
        cx_col = (col - 0.5) * cell

        col_group = ET.SubElement(root, f"{{{ns}}}g")
        col_group.set("data-column", str(col))

        if col % 2 == 0:
            tf = (
                f"translate({_fmt_coord(cx_col)},{_fmt_coord(cy_rot_base)}) "
                f"rotate(180) "
                f"translate({_fmt_coord(-cx_col)},{_fmt_coord(-cy_rot_base)})"
            )
            col_group.set("transform", tf)

        for row in range(1, rows + 1):
            ch = glyph_char(row, col)
            ci = color_index(row, col, rows)
            dy = delta_y_px(row, col, rows, params.delta_ratio, cell)

            x = cx_col
            y = (row - 0.5) * cell + dy

            text = ET.SubElement(col_group, f"{{{ns}}}text")
            text.set("class", f"fabric-glyph fabric-ink{ci}")
            text.set("font-family", font_family_stack_css(params.font_family))
            text.set("x", _fmt_coord(x))
            text.set("y", _fmt_coord(y))
            text.text = ch

    return _pretty_xml(root)


def _pretty_xml(elem: ET.Element) -> str:
    """Serialize with stable attribute order and XML declaration."""
    ET.indent(elem, space="  ")
    body = ET.tostring(elem, encoding="unicode", xml_declaration=False)
    return '<?xml version="1.0" encoding="UTF-8"?>\n' + body + "\n"
