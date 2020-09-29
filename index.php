<?php
/**
 * Settings
 */

define('ICON_DEBUG', false);

$parts = explode('&', $_SERVER['QUERY_STRING'], 2);
$path = array_shift($parts);
$query = array_shift($parts);

define('ICON_URL_PATH', $path);
define('ICON_URL_QUERY', $query ? '?' . $query : null);


/**
 * Check Home Page
 */

function is_home() {
    return !ICON_URL_PATH;
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

error_reporting(ICON_DEBUG ? E_ALL | E_STRICT : 0);

if (is_home()) {
    header('Location: https://statically.io/favicons/');
} else {
    require __DIR__ . '/class-icon.php';
    $icon = new Icon(ICON_URL_PATH);
    $icon->debugMode = ICON_DEBUG;
    // `http://127.0.0.1/example.com?cache=0`
    if (isset($_GET['cache'])) {
        $icon->expires = (int) $_GET['cache'];
    }
    if (isset($_GET['output']) && method_exists($icon, $draw = 'drawAs' . ucfirst($_GET['output']))) {
        $icon->{$draw}();
    } else {
        $icon->draw();
    }
}
