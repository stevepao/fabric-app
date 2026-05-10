"""Configuration models for tile generation."""

from __future__ import annotations

from pydantic import BaseModel, Field, field_validator

from fabric_app.fonts import default_font_family


class RGB(BaseModel):
    r: int = Field(ge=0, le=255)
    g: int = Field(ge=0, le=255)
    b: int = Field(ge=0, le=255)

    def css(self) -> str:
        return f"rgb({self.r},{self.g},{self.b})"


class FabricParams(BaseModel):
    canvas_width: float = 1260.0
    canvas_height: float = 1260.0
    grid_cols: int = Field(18, ge=1)
    grid_rows: int = Field(18, ge=1)
    cell_size: float = Field(70.0, gt=0)
    background: RGB = Field(default_factory=lambda: RGB(r=130, g=130, b=130))
    ink_black: RGB = Field(default_factory=lambda: RGB(r=0, g=0, b=0))
    ink_lightgrey: RGB = Field(default_factory=lambda: RGB(r=210, g=210, b=210))
    ink_white: RGB = Field(default_factory=lambda: RGB(r=255, g=255, b=255))
    font_family: str = Field(default_factory=default_font_family)
    font_path: str | None = None
    font_size_ratio: float = Field(0.70, gt=0, le=2.0)
    delta_ratio: float = Field(0.16, ge=0, le=1.0)

    @property
    def inks(self) -> tuple[RGB, RGB, RGB]:
        return (self.ink_black, self.ink_lightgrey, self.ink_white)

    @field_validator("canvas_width", "canvas_height")
    @classmethod
    def positive_canvas(cls, v: float) -> float:
        if v <= 0:
            raise ValueError("canvas dimensions must be positive")
        return v
