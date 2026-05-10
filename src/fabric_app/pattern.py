"""Pattern logic: pairing, glyphs, colors, and vertical drift (1-indexed rows/cols)."""

from __future__ import annotations


def glyph_char(row: int, col: int) -> str:
    """Checkerboard: even (row+col) => 西, else 川."""
    return "\u897f" if (row + col) % 2 == 0 else "\u5ddd"


def pair_index(row: int, col: int, grid_rows: int) -> int:
    """
    Vertical pair index down the column; wrap pair is index 0 on even columns.

    Odd columns: (1-2), (3-4), ... → pair_index = (row-1)//2.
    Even columns: (grid_rows, 1) wrap first, then (2-3), (4-5), ...
    """
    if col % 2 == 1:
        return (row - 1) // 2
    if row == 1 or row == grid_rows:
        return 0
    return row // 2


def color_index(row: int, col: int, grid_rows: int) -> int:
    """color_index = (column_offset + pair_index) % 3 with column_offset = (col-1)%3."""
    co = (col - 1) % 3
    pi = pair_index(row, col, grid_rows)
    return (co + pi) % 3


def delta_y_px(row: int, col: int, grid_rows: int, delta_ratio: float, cell_size: float) -> float:
    """
    Vertical drift in pixels (positive = downward).

    Odd columns: upper glyph +delta, lower -delta within each pair.
    Even columns, wrap pair: row 1 → -delta, row grid_rows → +delta.
    Even columns, normal pairs: upper row +delta, lower -delta (pairs (2,3), (4,5), …).
    """
    mag = delta_ratio * cell_size
    if col % 2 == 1:
        return mag if row % 2 == 1 else -mag
    if row == 1:
        return -mag
    if row == grid_rows:
        return mag
    return mag if row % 2 == 0 else -mag
