<?php

declare(strict_types=1);

/**
 * Allowlisted UI fonts: slug => human label + install directory (Dockerfile installer).
 *
 * @return array<string, array{label: string, dir: string}>
 */
function fabric_font_catalog(): array
{
    return [
        'noto-sans-jp' => [
            'label' => 'Noto Sans JP',
            'dir' => '/usr/share/fonts/google-webfonts/noto-sans-jp',
        ],
        'noto-serif-jp' => [
            'label' => 'Noto Serif JP',
            'dir' => '/usr/share/fonts/google-webfonts/noto-serif-jp',
        ],
        'murecho' => [
            'label' => 'Murecho',
            'dir' => '/usr/share/fonts/google-webfonts/murecho',
        ],
        'yomogi' => [
            'label' => 'Yomogi',
            'dir' => '/usr/share/fonts/google-webfonts/yomogi',
        ],
        'hina-mincho' => [
            'label' => 'Hina Mincho',
            'dir' => '/usr/share/fonts/google-webfonts/hina-mincho',
        ],
    ];
}

/** Pick a single font file under $dir (prefers variable / axis fonts). */
function fabric_resolve_font_file(string $dir): ?string
{
    if (!is_dir($dir)) {
        return null;
    }

    $ttf = glob($dir . '/*.ttf') ?: [];
    sort($ttf);
    foreach ($ttf as $path) {
        $base = basename($path);
        if (str_contains($base, 'VariableFont') || str_contains($base, 'wght')) {
            return $path;
        }
    }
    if ($ttf !== []) {
        return $ttf[0];
    }

    $otf = glob($dir . '/*.otf') ?: [];
    sort($otf);
    if ($otf !== []) {
        return $otf[0];
    }

    return null;
}
