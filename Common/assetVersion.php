<?php
function versionedAssetUrl($assetPath, $baseDir = __DIR__) {
    $normalizedPath = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $assetPath);
    $absolutePath = realpath($baseDir . DIRECTORY_SEPARATOR . $normalizedPath);

    if ($absolutePath === false || !is_file($absolutePath)) {
        return $assetPath;
    }

    $separator = (strpos($assetPath, '?') === false) ? '?' : '&';
    return $assetPath . $separator . 'v=' . filemtime($absolutePath);
}
