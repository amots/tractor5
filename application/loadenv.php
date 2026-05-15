<?php

/**
 * @author Amots Hetzroni
 * @since 2026-05-14
 */
function loadEnv(string $path): bool
{
    if (!file_exists($path)) {
        return false;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Ignore comments starting with #
        if (strpos(trim($line), '#') === 0) continue;

        // Split by the first '=' found
        list($name, $value) = explode('=', $line, 2);

        $name = trim($name);
        $value = trim($value);
        // Put into environment and superglobals
        if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
            putenv(sprintf('%s=%s', $name, $value));
            $_ENV[$name] = $value; // avoid using - not predictable
            $_SERVER[$name] = $value; // use only this
        }
    }
    return true;
}

// Call the function immediately pointing to your .env file
loadEnv(__SITE_PATH  . '/../.env');
