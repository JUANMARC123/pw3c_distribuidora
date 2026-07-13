<?php

it('arch no usa debuggers en codigo fuente', function () {
    $basePath = dirname(__DIR__, 2);
    $appPath = $basePath . '/app';
    $debugFunctions = ['dd', 'dump', 'ray', 'var_dump', 'print_r', 'exit'];
    $dirs = ['Http', 'Models', 'Services', 'Events', 'Listeners', 'Providers', 'Console', 'Exceptions', 'Mail', 'Notifications', 'Rules'];

    $files = glob("{$appPath}/*.php");
    foreach ($dirs as $dir) {
        $matched = glob("{$appPath}/{$dir}/**/*.php");
        if ($matched !== false) {
            $files = array_merge($files ?: [], $matched);
        }
    }

    $violations = [];
    foreach ($files ?: [] as $file) {
        $content = file_get_contents($file);
        foreach ($debugFunctions as $func) {
            if (preg_match('/\b' . preg_quote($func, '/') . '\s*\(/', $content)) {
                $violations[] = "{$func} encontrado en " . str_replace($basePath, '', $file);
            }
        }
    }

    expect($violations)->toBeEmpty(implode("\n", $violations));
});
