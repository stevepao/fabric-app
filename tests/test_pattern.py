"""Tests for pairing, glyphs, colors, and drift rules."""

from fabric_app.pattern import color_index, delta_y_px, glyph_char, pair_index


def test_glyph_checkerboard() -> None:
    assert glyph_char(1, 1) == "\u897f"  # 西 even sum
    assert glyph_char(1, 2) == "\u5ddd"  # 川 odd sum


def test_pair_index_odd_column() -> None:
    n = 18
    assert pair_index(1, 1, n) == 0
    assert pair_index(2, 1, n) == 0
    assert pair_index(3, 1, n) == 1
    assert pair_index(17, 1, n) == 8
    assert pair_index(18, 1, n) == 8


def test_pair_index_even_column_wrap() -> None:
    n = 18
    assert pair_index(1, 2, n) == 0
    assert pair_index(18, 2, n) == 0


def test_pair_index_even_column_interior() -> None:
    n = 18
    assert pair_index(2, 2, n) == 1
    assert pair_index(3, 2, n) == 1
    assert pair_index(16, 2, n) == 8
    assert pair_index(17, 2, n) == 8


def test_color_index_first_column() -> None:
    n = 18
    assert color_index(1, 1, n) == 0
    assert color_index(2, 1, n) == 0
    assert color_index(3, 1, n) == 1


def test_delta_odd_column() -> None:
    n = 18
    cell = 70.0
    d = 0.16
    m = d * cell
    assert delta_y_px(1, 1, n, d, cell) == m
    assert delta_y_px(2, 1, n, d, cell) == -m


def test_delta_even_wrap() -> None:
    n = 18
    cell = 50.0
    d = 0.2
    m = d * cell
    assert delta_y_px(1, 2, n, d, cell) == -m
    assert delta_y_px(18, 2, n, d, cell) == m


def test_delta_even_interior_pair() -> None:
    n = 18
    cell = 40.0
    d = 0.1
    m = d * cell
    assert delta_y_px(2, 2, n, d, cell) == m
    assert delta_y_px(3, 2, n, d, cell) == -m


def test_pair_index_general_rows() -> None:
    n = 6
    assert pair_index(1, 2, n) == 0
    assert pair_index(6, 2, n) == 0
    assert pair_index(2, 2, n) == 1
    assert pair_index(5, 2, n) == 2
