<?php

/**
 * Favicons
 * Proxying favicon from a domain name
 *
 * This is Class for Favicons, this script is allowing you
 * to proxying favicon from a domain name and set default
 * favicon if no one exist.
 *
 * @author    Marsble Team
 * @license   MIT
 * @copyright 2018 Marsble
 */

class Favicons {

    const version = '1.3.1';

    public $domain = null;
    public $favicon = 'favicon_default.ico'; // Set default favicon <http://transparent-favicon.info>
    public $expires = 86400; // 1 Day as seconds
    public $userAgent = 'MarsbleFavicons';
    public $debugMode = false;
    public $attempt = 2; // Maximum `curl` access to try

    protected $results = [
        'blob' => null,
        'href' => null,
        'rel' => null,
        'type' => 'image/x-icon'
    ];

    public function __construct(string $domain) {
        $this->domain = $this->removeProtocols($domain);
    }

    /**
     * Proxy with `curl`
     */
    protected function cURL(string $url, string $userAgent = null, int $try = 1) {
        if ($try > $this->attempt) {
            return false;
        }
        $c = curl_init($url);
        curl_setopt_array($c, [
            CURLOPT_FAILONERROR => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 2,
            CURLOPT_USERAGENT => $userAgent ?: $this->userAgent . '/' . self::version,// Custom user agent
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPGET => true,
            CURLOPT_TIMEOUT => 15
        ]);
        $result = curl_exec($c);
        curl_close($c);
        // Return the result if success, or try again with the default user agent
        return $result ?: $this->cURL($url, $_SERVER['HTTP_USER_AGENT'], $try + 1);
    }

    /**
     * Remove protocol prefix from URL
     */
    protected function removeProtocols($domain) {
        if (strpos($domain, '://') !== false) {
            return preg_replace('#^(?:f|ht)tps?://#', "", $domain);
        }
        return $domain;
    }

    /**
     * Use `https://` instead of `http://` ?
     */
    protected function useSSL() {
        return !empty($_GET['ssl']);
    }

    /**
     * Fetch results
     */
    protected function fetch() {

        $domain = $this->domain;

        if ($this->useSSL()) {
            $prefix = 'https://';
            $suffix = '?ssl=1';
        } else {
            $prefix = 'http://';
            $suffix = "";
        }

        // Get favicon from URL
        if ($result = $this->cURL($href = $prefix . $domain . '/favicon.ico' . $suffix)) {
            $this->results['blob'] = $result;
            $this->results['href'] = $href;
            $this->results['rel'] = 'icon';
            $this->results['type'] = 'image/x-icon';
        // Get favicon from HTML `<link>`
        } else if ($result = $this->cURL($prefix . $domain)) {
            if (
                stripos($result, '<link') !== false &&
                stripos($result, 'href=') !== false &&
                stripos($result, 'rel=') !== false &&
                preg_match_all('#<link(?:\s[^>]+?)\/?>#i', $result, $m)
            ) {
                foreach ($m[0] as $html) {
                    // Check for `rel` attribute
                    $value = 'apple-touch-icon(?:-precomposed)?|msapplication-TileImage|(?:shortcut\s+)?icon';
                    if (stripos($html, 'rel=') !== false && preg_match('#\srel=([\'"]?)(' . $value . ')\1#i', $html, $m)) {
                        $this->results['rel'] = $m[2];
                        // Check for `href` attribute
                        if (stripos($html, 'href=') !== false && preg_match('#\shref=([\'"]?)([^\s>]+)\1#i', $html, $m)) {
                            $href = $m[2];
                            // Maybe relative protocol
                            if (strpos($href, '//') === 0) {
                                $href = $prefix . substr($href, 2);
                            // Maybe relative path
                            } else if (strpos($href, '/') === 0) {
                                $href = $prefix . $domain . $href;
                            // Maybe relative path without leading `/`
                            } else if (strpos($href, '://') === false) {
                                $href = $prefix . $domain . '/' . $href;
                            }
                            $this->results['href'] = $href;
                            if ($result = $this->cURL($href)) {
                                // Check for `type` attribute
                                if (stripos($html, 'type=') !== false && preg_match('#\stype=([\'"]?)([^\s>]+)\1#i', $html, $m)) {
                                    // Set custom favicon type
                                    $this->results['type'] = $m[2];
                                // Else ...
                                } else {
                                    $type = (string) pathinfo($href, PATHINFO_EXTENSION);
                                    switch ($type) {
                                        case "": // Default to `x-icon`
                                        case 'ico':
                                            $type = 'x-icon';
                                        break;
                                        case 'jpg':
                                            // <https://stackoverflow.com/a/37266399/1163000>
                                            $type = 'jpeg';
                                        break;
                                    }
                                    // ... guess it from the file extension
                                    $this->results['type'] = 'image/' . $type;
                                }
                                $this->results['blob'] = $result;
                            }
                            break;
                        }
                    }
                }
            }
        }

        // Last check, return the default favicon!
        if (!$this->results['blob']) {
            $this->results['blob'] = file_get_contents(__DIR__ . '/' . $this->favicon);
            $this->results['href'] = null;
            $this->results['rel'] = 'icon';
            $this->results['type'] = 'image/x-icon';
        }

    }

    /**
     * Set correct HTTP headers for favicon image
     */
    protected function setHeaders(string $type = null) {
        $this->fetch();
        if (!$this->debugMode) {
            if ($this->expires) {
                header('Cache-Control: public, max-age=' . $this->expires);
            } else {
                header('Cache-Control: no-cache');
            }
            header('Content-Type: ' . $type);
        } else {
            header('Content-Type: text/plain');
            header('Cache-Control: no-cache');
        }
    }

    /**
     * Default
     */
    public function draw() {
        $this->setHeaders($this->results['type']);
        echo $this->results['blob'];
        exit;
    }

    /**
     * Draw as raw favicon Blob
     */
    public function drawAsRaw() {
        $this->setHeaders('text/plain');
        echo $this->results['blob'];
        exit;
    }

    /**
     * Draw as Base64
     */
    public function drawAsBase64() {
        $this->setHeaders('text/plain');
        echo 'data:' . $this->results['type'] . ';base64,' . base64_encode($this->results['blob']);
        exit;
    }

    /**
     * Draw as JSON
     */
    public function drawAsJson() {
        $this->setHeaders('application/json');
        unset($this->results['blob']);
        ksort($this->results);
        echo json_encode($this->results);
        exit;
    }

    /**
     * Draw as HTML
     */
    public function drawAsHtml(string $x = "") {
        $this->setHeaders('text/html');
        unset($this->results['blob']);
        if (!$this->results['href']) {
            echo '<!-- error loading favicon from `http' . ($this->useSSL() ? 's' : "") . '://' . $this->domain . '` -->';
            exit;
        }
        ksort($this->results);
        $html = '<link';
        foreach ($this->results as $attr => $value) {
            $html .= ' ' . $attr . '="' . $value . '"';
        }
        echo $html . $x . '>';
        exit;
    }

    /**
     * Draw as XHTML
     */
    public function drawAsXhtml() {
        return $this->drawAsHtml(' /');
    }

}
