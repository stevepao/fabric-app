<?php

declare(strict_types=1);

require_once __DIR__ . '/fabric_fonts.php';

session_start();

/**
 * Path to fabric-app executable (Typer CLI).
 * Override with env FABRIC_APP_BIN if needed.
 */
function fabric_bin(): string
{
    $env = getenv('FABRIC_APP_BIN');
    if ($env !== false && $env !== '') {
        return $env;
    }

    return '/usr/local/bin/fabric-app';
}

function redirect_flash_errors(array $errors, array $post): never
{
    $_SESSION['flash_errors'] = $errors;
    $_SESSION['old_input'] = $post;
    header('Location: index.php', true, 303);
    exit;
}

/** Convert HTML `#RRGGBB` to `r,g,b` for the CLI. */
function parse_hex_color(string $raw): ?string
{
    $raw = trim($raw);
    if (!preg_match('/^#([0-9a-fA-F]{6})$/', $raw, $m)) {
        return null;
    }
    $hex = $m[1];
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));

    return "{$r},{$g},{$b}";
}

function validate_positive_float(string $label, string $raw): array
{
    if ($raw === '') {
        return [false, "{$label} is required."];
    }
    if (!is_numeric($raw)) {
        return [false, "{$label} must be a number."];
    }
    $v = (float) $raw;
    if (!is_finite($v) || $v <= 0) {
        return [false, "{$label} must be a positive finite number."];
    }

    return [true, $v];
}

function validate_int_min(string $label, string $raw, int $min): array
{
    if ($raw === '') {
        return [false, "{$label} is required."];
    }
    if (!preg_match('/^-?\d+$/', $raw)) {
        return [false, "{$label} must be an integer."];
    }
    $v = (int) $raw;
    if ($v < $min) {
        return [false, "{$label} must be at least {$min}."];
    }

    return [true, $v];
}

/** Non-interactive GET: preview image or download (token-bound). */
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $token = isset($_GET['img']) ? (string) $_GET['img'] : (isset($_GET['download']) ? (string) $_GET['download'] : '');
    if ($token !== '' && preg_match('/^[a-f0-9]{32}$/', $token)) {
        $sessToken = $_SESSION['fabric_token'] ?? '';
        $path = $_SESSION['fabric_png_path'] ?? '';
        if (!is_string($sessToken) || !is_string($path) || !hash_equals($sessToken, $token)) {
            http_response_code(403);
            exit('Forbidden');
        }
        if ($path === '' || !is_readable($path)) {
            http_response_code(404);
            exit('Not found');
        }
        $download = isset($_GET['download']);
        header('Content-Type: image/png');
        header('X-Content-Type-Options: nosniff');
        if ($download) {
            header('Content-Disposition: attachment; filename="fabric-pattern.png"');
        }
        readfile($path);
        exit;
    }

    $done = isset($_GET['done']) && $_GET['done'] === '1';
    if ($done) {
        $token = $_SESSION['fabric_token'] ?? '';
        $path = $_SESSION['fabric_png_path'] ?? '';
        if (!is_string($token) || !is_string($path) || $token === '' || $path === '' || !is_readable($path)) {
            header('Location: index.php', true, 302);
            exit;
        }

        $imgUrl = 'generate.php?img=' . rawurlencode($token);
        $dlUrl = 'generate.php?download=' . rawurlencode($token);
        ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pattern ready — Fabric Pattern Generator</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800">

<div class="max-w-2xl mx-auto mt-10 bg-white p-6 rounded-2xl shadow-md">

    <h1 class="text-2xl font-semibold mb-4">Fabric Pattern Generator</h1>
    <p class="text-sm text-gray-500 mb-6">Generate a deterministic repeating pattern tile</p>

    <div class="mt-6">
        <h2 class="text-lg font-medium mb-2">Preview</h2>
        <img src="<?= htmlspecialchars($imgUrl, ENT_QUOTES, 'UTF-8') ?>"
             alt="Generated fabric pattern"
             class="max-w-full rounded-lg border border-gray-200">
        <a href="<?= htmlspecialchars($dlUrl, ENT_QUOTES, 'UTF-8') ?>"
           class="inline-block mt-4 bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition">
            Download PNG
        </a>
    </div>

    <p class="mt-8">
        <a href="index.php"
           class="text-blue-600 hover:text-blue-800 text-sm font-medium">
            ← Generate another
        </a>
    </p>

</div>

</body>
</html>
        <?php
        exit;
    }

    header('Location: index.php', true, 302);
    exit;
}

/** POST: validate and run CLI */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php', true, 302);
    exit;
}

$post = $_POST;
$errors = [];

[$ok, $canvas_w] = validate_positive_float('Canvas width', trim((string) ($post['canvas_w'] ?? '')));
if (!$ok) {
    $errors[] = $canvas_w;
}
[$ok2, $canvas_h] = validate_positive_float('Canvas height', trim((string) ($post['canvas_h'] ?? '')));
if (!$ok2) {
    $errors[] = $canvas_h;
}
[$ok3, $grid_cols] = validate_int_min('Grid columns', trim((string) ($post['grid_cols'] ?? '')), 1);
if (!$ok3) {
    $errors[] = $grid_cols;
}
[$ok4, $grid_rows] = validate_int_min('Grid rows', trim((string) ($post['grid_rows'] ?? '')), 1);
if (!$ok4) {
    $errors[] = $grid_rows;
}
[$ok5, $cell_size] = validate_positive_float('Cell size', trim((string) ($post['cell_size'] ?? '')));
if (!$ok5) {
    $errors[] = $cell_size;
}

$bgRgb = parse_hex_color((string) ($post['bg_color'] ?? ''));
if ($bgRgb === null) {
    $errors[] = 'Background color must be a #RRGGBB value.';
}

$c1Rgb = parse_hex_color((string) ($post['color1'] ?? ''));
if ($c1Rgb === null) {
    $errors[] = 'Color 1 must be a #RRGGBB value.';
}

$c2Rgb = parse_hex_color((string) ($post['color2'] ?? ''));
if ($c2Rgb === null) {
    $errors[] = 'Color 2 must be a #RRGGBB value.';
}

$c3Rgb = parse_hex_color((string) ($post['color3'] ?? ''));
if ($c3Rgb === null) {
    $errors[] = 'Color 3 must be a #RRGGBB value.';
}

$catalog = fabric_font_catalog();
$fontKey = trim((string) ($post['font_choice'] ?? ''));
if (!isset($catalog[$fontKey])) {
    $errors[] = 'Invalid font selection.';
}

[$okFs, $font_size_ratio] = validate_positive_float('Font size ratio', trim((string) ($post['font_size_ratio'] ?? '')));
if (!$okFs) {
    $errors[] = $font_size_ratio;
}
[$okD, $delta_ratio] = validate_positive_float('Delta ratio', trim((string) ($post['delta_ratio'] ?? '')));
if (!$okD) {
    $errors[] = $delta_ratio;
}

if ($errors !== []) {
    redirect_flash_errors($errors, $post);
}

$background = $bgRgb;
$ink_black = $c1Rgb;
$ink_lightgrey = $c2Rgb;
$ink_white = $c3Rgb;

$fontFamily = $catalog[$fontKey]['label'];
$fontPath = fabric_resolve_font_file($catalog[$fontKey]['dir']);

if (!empty($_SESSION['fabric_png_path']) && is_string($_SESSION['fabric_png_path'])) {
    $prev = $_SESSION['fabric_png_path'];
    $tmpRoot = sys_get_temp_dir();
    if ($prev !== '' && str_starts_with($prev, $tmpRoot) && is_file($prev)) {
        @unlink($prev);
    }
}

$tmpDir = sys_get_temp_dir();
$basename = 'fabric_' . bin2hex(random_bytes(8)) . '.png';
$pngOut = $tmpDir . DIRECTORY_SEPARATOR . $basename;

$bin = fabric_bin();
if (!is_file($bin) || !is_executable($bin)) {
    redirect_flash_errors(
        ['Generator executable not found or not executable. Set FABRIC_APP_BIN to the fabric-app path.'],
        $post
    );
}

$cmdParts = [
    escapeshellarg($bin),
    'render',
    '--png-out',
    escapeshellarg($pngOut),
    '--canvas-w',
    escapeshellarg((string) $canvas_w),
    '--canvas-h',
    escapeshellarg((string) $canvas_h),
    '--grid-cols',
    escapeshellarg((string) $grid_cols),
    '--grid-rows',
    escapeshellarg((string) $grid_rows),
    '--cell-size',
    escapeshellarg((string) $cell_size),
    '--background',
    escapeshellarg($background),
    '--ink-black',
    escapeshellarg($ink_black),
    '--ink-lightgrey',
    escapeshellarg($ink_lightgrey),
    '--ink-white',
    escapeshellarg($ink_white),
];

if ($fontPath !== null && $fontPath !== '' && is_readable($fontPath)) {
    $cmdParts[] = '--font-path';
    $cmdParts[] = escapeshellarg($fontPath);
}

$cmdParts[] = '--font-family';
$cmdParts[] = escapeshellarg($fontFamily);
$cmdParts[] = '--font-size-ratio';
$cmdParts[] = escapeshellarg((string) $font_size_ratio);
$cmdParts[] = '--delta-ratio';
$cmdParts[] = escapeshellarg((string) $delta_ratio);

$cmd = implode(' ', $cmdParts);

$descriptorSpec = [
    0 => ['pipe', 'r'],
    1 => ['pipe', 'w'],
    2 => ['pipe', 'w'],
];
$proc = proc_open($cmd . ' 2>&1', $descriptorSpec, $pipes, null, null);
if (!is_resource($proc)) {
    redirect_flash_errors(['Could not start the generator process.'], $post);
}
fclose($pipes[0]);
$stdout = stream_get_contents($pipes[1]);
$stderr = stream_get_contents($pipes[2]);
fclose($pipes[1]);
fclose($pipes[2]);
$exitCode = proc_close($proc);

if ($exitCode !== 0 || !is_readable($pngOut) || filesize($pngOut) === 0) {
    if (is_file($pngOut)) {
        @unlink($pngOut);
    }
    $msg = trim(($stderr !== false ? $stderr : '') . ($stdout !== false ? $stdout : ''));
    if ($msg === '') {
        $msg = 'Generation failed.';
    }
    redirect_flash_errors(
        ['Generation failed: ' . $msg],
        $post
    );
}

$token = bin2hex(random_bytes(16));
$_SESSION['fabric_token'] = $token;
$_SESSION['fabric_png_path'] = $pngOut;

header('Location: generate.php?done=1', true, 303);
exit;
