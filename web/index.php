<?php

declare(strict_types=1);

require_once __DIR__ . '/fabric_fonts.php';

session_start();

$errors = $_SESSION['flash_errors'] ?? [];
$old = $_SESSION['old_input'] ?? [];
unset($_SESSION['flash_errors'], $_SESSION['old_input']);

$d = static function (string $key, string $default): string {
    global $old;
    return isset($old[$key]) ? htmlspecialchars((string) $old[$key], ENT_QUOTES, 'UTF-8') : $default;
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fabric Pattern Generator</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800">

<div class="max-w-2xl mx-auto mt-10 bg-white p-6 rounded-2xl shadow-md">

    <h1 class="text-2xl font-semibold mb-4">Fabric Pattern Generator</h1>
    <p class="text-sm text-gray-500 mb-6">Generate a deterministic repeating pattern tile</p>

    <?php if ($errors !== []): ?>
        <div class="bg-red-100 text-red-700 p-3 rounded-lg mb-4" role="alert">
            <ul class="list-disc list-inside text-sm space-y-1">
                <?php foreach ($errors as $err): ?>
                    <li><?= htmlspecialchars((string) $err, ENT_QUOTES, 'UTF-8') ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="generate.php" method="post" class="space-y-0">

        <div class="grid grid-cols-2 gap-3 mb-4">
            <div>
                <label class="block text-sm font-medium mb-1" for="canvas_w">Canvas width</label>
                <input class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                       type="text" inputmode="decimal" name="canvas_w" id="canvas_w"
                       value="<?= $d('canvas_w', '1260') ?>">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1" for="canvas_h">Canvas height</label>
                <input class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                       type="text" inputmode="decimal" name="canvas_h" id="canvas_h"
                       value="<?= $d('canvas_h', '1260') ?>">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-3 mb-4">
            <div>
                <label class="block text-sm font-medium mb-1" for="grid_cols">Grid columns</label>
                <input class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                       type="number" min="1" name="grid_cols" id="grid_cols"
                       value="<?= $d('grid_cols', '18') ?>">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1" for="grid_rows">Grid rows</label>
                <input class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                       type="number" min="1" name="grid_rows" id="grid_rows"
                       value="<?= $d('grid_rows', '18') ?>">
            </div>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium mb-1" for="cell_size">Cell size</label>
            <input class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                   type="text" inputmode="decimal" name="cell_size" id="cell_size"
                   value="<?= $d('cell_size', '70') ?>">
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium mb-1" for="bg_color">Background</label>
            <input class="h-11 w-full max-w-xs border border-gray-300 rounded-lg cursor-pointer bg-white"
                   type="color" name="bg_color" id="bg_color"
                   value="<?= $d('bg_color', '#828282') ?>">
        </div>

        <div class="mb-4">
            <p class="text-sm font-medium mb-2 text-gray-700">Glyph colors</p>
            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs text-gray-500 mb-1" for="color1">Color 1</label>
                    <input class="h-11 w-full border border-gray-300 rounded-lg cursor-pointer bg-white"
                           type="color" name="color1" id="color1"
                           value="<?= $d('color1', '#000000') ?>">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1" for="color2">Color 2</label>
                    <input class="h-11 w-full border border-gray-300 rounded-lg cursor-pointer bg-white"
                           type="color" name="color2" id="color2"
                           value="<?= $d('color2', '#d2d2d2') ?>">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1" for="color3">Color 3</label>
                    <input class="h-11 w-full border border-gray-300 rounded-lg cursor-pointer bg-white"
                           type="color" name="color3" id="color3"
                           value="<?= $d('color3', '#ffffff') ?>">
                </div>
            </div>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium mb-1" for="font_choice">Font</label>
            <select class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                    name="font_choice" id="font_choice">
                <?php
                $picked = trim((string) ($old['font_choice'] ?? 'noto-sans-jp'));
                foreach (fabric_font_catalog() as $slug => $meta):
                    $sel = $picked === $slug ? ' selected' : '';
                    ?>
                    <option value="<?= htmlspecialchars($slug, ENT_QUOTES, 'UTF-8') ?>"<?= $sel ?>>
                        <?= htmlspecialchars($meta['label'], ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <p class="text-xs text-gray-500 mt-1">
                Google Fonts lists the last option as <span class="font-medium">Hina Mincho</span>
                (often confused with “Hana Mincho”).
            </p>
        </div>

        <div class="grid grid-cols-2 gap-3 mb-6">
            <div>
                <label class="block text-sm font-medium mb-1" for="font_size_ratio">Font size ratio</label>
                <input class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                       type="text" inputmode="decimal" name="font_size_ratio" id="font_size_ratio"
                       value="<?= $d('font_size_ratio', '0.70') ?>">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1" for="delta_ratio">Delta ratio</label>
                <input class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                       type="text" inputmode="decimal" name="delta_ratio" id="delta_ratio"
                       value="<?= $d('delta_ratio', '0.16') ?>">
            </div>
        </div>

        <button type="submit"
                class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition font-medium">
            Generate Pattern
        </button>
    </form>

</div>

</body>
</html>
