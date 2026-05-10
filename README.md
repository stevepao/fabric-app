# fabric-app

CLI that generates **deterministic** seamless **fabric-style** tiles: an **18×18** grid (by default) of **西 / 川** glyphs, three ink colors, vertical drift, and **180° rotation of whole even columns**—same output every run, stable SVG ordering for diffs.

## Benefits

- **Spec-driven pattern** — checkerboard glyphs, column pairing, shared pair colors, delta drift, and column rotation match the documented rules below.
- **SVG + PNG** — vector output plus PNG rasterization through **`resvg`** (high-quality SVG → PNG, good CJK support when fonts are embedded or discoverable).
- **Sane defaults** — **1260×1260**, **18×18**, cell **70**, grey background, black / light grey / white inks; **platform-aware typography** (macOS: **ヒラギノ明朝 ProN** + Noto/Serif stack; Linux: **Noto Serif CJK JP** + serif); **`@font-face`** auto-embeds Hiragino on macOS or **Noto Serif CJK Regular** on Linux when common paths exist; **delta 0.16**, **font size ratio 0.70**.
- **Deterministic** — no randomness; fixed float formatting and predictable SVG element order.
- **Tunable** — canvas, grid, cell size, colors, font family/path, and drift/font-size ratios.

## Usage

PNG export shells out to the **`resvg`** binary (see setup below). Minimal render:

```bash
fabric-app render --png-out tile.png
```

Same via module:

```bash
python -m fabric_app render --png-out tile.png
```

Optional SVG:

```bash
fabric-app render --png-out tile.png --svg-out tile.svg
```

**Typography** — Defaults are chosen by OS: **macOS** → primary **ヒラギノ明朝 ProN** and auto **`@font-face`** to Apple’s Hiragino Mincho ProN **`.ttc`** when found; **Linux** → primary **Noto Serif CJK JP** and auto-embed **Noto Serif CJK Regular** when found under typical **`/usr/share/fonts/...`** layouts. Override anytime with **`--font-family`** and/or **`--font-path`** (paired names for `@font-face`).

Full override example:

```bash
fabric-app render \
  --png-out out.png \
  --canvas-w 1400 --canvas-h 1400 \
  --grid-cols 18 --grid-rows 18 \
  --cell-size 70 \
  --background "130,130,130" \
  --ink-black "0,0,0" \
  --ink-lightgrey "210,210,210" \
  --ink-white "255,255,255" \
  --font-family "Hiragino Sans GB" \
  --font-size-ratio 0.65 \
  --delta-ratio 0.14
```

Embedded font:

```bash
fabric-app render --png-out tile.png --font-path /Library/Fonts/Arial.ttf --font-family "Arial"
```

Help:

```bash
fabric-app --help
fabric-app render --help
```

### Calling from PHP (LAMP) or other backends

Yes: invoking **`fabric-app render`** (or **`python -m fabric_app render`**) from **`shell_exec`**, **`proc_open`**, or a job queue is a normal pattern. Use absolute paths for outputs, validate/whitelist arguments, and avoid passing raw user input into the shell string.

**Environment:** the web server user (e.g. **`www-data`**) needs **`resvg`** on **`PATH`** (often set **`PATH`** explicitly in PHP or the service unit), plus fonts if you rely on discovery rather than **`--font-path`**.

## Pattern rules

Logic uses **1-indexed** `row` and `col`.

1. **Checkerboard glyph** — If `(row + col)` is **even**, **西**; otherwise **川**.
2. **Vertical pairing** — **Odd columns:** `(1–2), (3–4), …` **Even columns:** wrap **`(grid_rows, 1)`** first (pair index **0**), then `(2–3), (4–5), …`.
3. **Shared color per pair** — Both glyphs in a pair use the same color.
4. **Color cycling** — `column_offset = (col - 1) % 3`, `pair_index` increases down the column, `color_index = (column_offset + pair_index) % 3` → inks **[black, light grey, white]**.
5. **Spacing delta** (`delta_ratio × cell_size`) — Odd columns: upper row **+delta**, lower **−delta**. Even wrap pair: row **1** **−delta**, row **`grid_rows`** **+delta**. Other even-column pairs: same upper/lower rule as odd.
6. **Orientation** — Odd columns upright; even columns **180°** around the **column strip center** (one group transform).
7. **Determinism** — Fixed numeric formatting; traversal **columns left → right**, **rows top → bottom** within each column.

## Tile sizing

By default the canvas matches **`grid_cols × cell_size`** × **`grid_rows × cell_size`**. If canvas dimensions differ, glyphs still start from the origin on that cell grid; align **`--canvas-w`** / **`--canvas-h`** with your repeat unit for seamless tiling.

## Development

```bash
ruff check src tests
pytest
```

Install **`resvg`** first so the integration PNG test runs (`pytest` skips it when `resvg` is missing).

---

## Setup & installation

Follow these in order.

### 1. Install `resvg` (required for PNG)

The **`resvg`** CLI must be on your **`PATH`**.

macOS (Homebrew):

```bash
brew install resvg
```

Linux: install **`resvg`** from your distro or [resvg releases](https://github.com/RazrFalcon/resvg/releases).

Verify:

```bash
which resvg && resvg --version
```

### 2. Fonts on Linux (recommended)

Install Noto CJK so defaults resolve and auto-embedded paths exist, e.g. Debian/Ubuntu:

```bash
sudo apt-get update && sudo apt-get install -y fonts-noto-cjk
```

Fedora/RHEL package names differ slightly (`google-noto-serif-cjk-fonts`, etc.). Without these, pass **`--font-path`** to a `.ttc`/`.otf` your **`resvg`** can read.

### 3. Python virtual environment

```bash
cd fabric-app
python3 -m venv .venv
source .venv/bin/activate
python -m pip install -U pip
```

### 4. Install fabric-app

Regular install (recommended on macOS; avoids hidden `.pth` issues):

```bash
python -m pip install ".[dev]"
```

Always use **`python -m pip`** so packages install into **this** venv.

After pulling new changes, reinstall:

```bash
python -m pip install .
```

### 5. Optional Pillow extra

```bash
python -m pip install ".[pillow]"
```

### 6. Editable install caveat (`pip install -e`)

Some editable setups create a **hidden** `_editable_impl_*.pth` under **`site-packages`**. Python **3.11+** skips hidden `.pth` files, so **`import fabric_app`** can fail even though pip succeeded.

Choose one:

1. Stick with **non-editable** **`pip install ".[dev]"`** (simplest), or  
2. After **`pip install -e ".[dev]"`**, run:

   ```bash
   sh scripts/unhide_editable_pth.sh
   ```

   or: `find .venv/lib -name '_editable_impl_*.pth' -exec chflags nohidden {} \;`

## License

MIT — see [LICENSE](LICENSE).
