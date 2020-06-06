<?php

/**
 * Marsble Favicons Proxy
 *
 * @author    Marsble Team
 * @copyright Marsble. MIT.
 */


/**
 * Settings
 */

define('MARSBLE_DEBUG', false);

$parts = explode('&', $_SERVER['QUERY_STRING'], 2);
$path = array_shift($parts);
$query = array_shift($parts);

define('MARSBLE_URL_PATH', $path);
define('MARSBLE_URL_QUERY', $query ? '?' . $query : null);


/**
 * Check Home Page
 */

function is_home() {
    return !MARSBLE_URL_PATH;
}


/**
 * Aliases
 */

foreach (['base64', 'html', 'json', 'raw', 'xhtml'] as $output) {
    if (isset($_GET[$output])) {
        $_GET['output'] = $output;
        break;
    }
}

error_reporting(MARSBLE_DEBUG ? E_ALL | E_STRICT : 0);

if (is_home()) {
    require __DIR__ . '/home.php';
} else {
    require __DIR__ . '/class-favicons.php';
    $favicon = new Favicons(MARSBLE_URL_PATH);
    $favicon->debugMode = MARSBLE_DEBUG;
    // `http://127.0.0.1/example.com?cache=0`
    if (isset($_GET['cache'])) {
        $favicon->expires = (int) $_GET['cache'];
    }
    if (isset($_GET['output']) && method_exists($favicon, $draw = 'drawAs' . ucfirst($_GET['output']))) {
        $favicon->{$draw}();
    } else {
        $favicon->draw();
    }
}
