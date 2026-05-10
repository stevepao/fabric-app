<?php

declare(strict_types=1);

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
            <p class="text-sm font-medium mb-2 text-gray-700">Colors (r,g,b)</p>
            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs text-gray-500 mb-1" for="background">Background</label>
                    <input class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                           type="text" name="background" id="background"
                           value="<?= $d('background', '130,130,130') ?>">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1" for="ink_black">Black ink</label>
                    <input class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                           type="text" name="ink_black" id="ink_black"
                           value="<?= $d('ink_black', '0,0,0') ?>">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1" for="ink_lightgrey">Light grey</label>
                    <input class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                           type="text" name="ink_lightgrey" id="ink_lightgrey"
                           value="<?= $d('ink_lightgrey', '210,210,210') ?>">
                </div>
            </div>
            <div class="mt-3">
                <label class="block text-xs text-gray-500 mb-1" for="ink_white">White ink</label>
                <input class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 max-w-xs"
                       type="text" name="ink_white" id="ink_white"
                       value="<?= $d('ink_white', '255,255,255') ?>">
            </div>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium mb-1" for="font_family">Font family</label>
            <input class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                   type="text" name="font_family" id="font_family"
                   value="<?= $d('font_family', 'Hiragino Mincho ProN') ?>"
                   maxlength="120">
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
