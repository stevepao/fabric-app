"""Deterministic SVG output."""

from fabric_app.fonts import font_family_stack_css
from fabric_app.models import FabricParams
from fabric_app.svg_gen import build_svg


def test_build_svg_stable_twice() -> None:
    params = FabricParams()
    a = build_svg(params)
    b = build_svg(params)
    assert a == b
    stack = font_family_stack_css(params.font_family)
    expected_ff = 'font-family="' + stack.replace("&", "&amp;").replace('"', "&quot;") + '"'
    assert expected_ff in a


def test_build_svg_column_order_columns_tags() -> None:
    params = FabricParams(
        grid_cols=3,
        grid_rows=2,
        cell_size=10.0,
        canvas_width=30.0,
        canvas_height=20.0,
    )
    svg = build_svg(params)
    idx1 = svg.index('data-column="1"')
    idx2 = svg.index('data-column="2"')
    idx3 = svg.index('data-column="3"')
    assert idx1 < idx2 < idx3
