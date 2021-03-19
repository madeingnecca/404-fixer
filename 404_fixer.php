<?php

/**
 * Outputs a http 404 response.
 **/
function show_404($message) {
  header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
  print '404_fixer: ' . $message;
  die();
}

/**
 * Gets a configuration parameter.
 **/
function config($key, $default = NULL) {
  // Support environment variables "forwarded" by Apache's ModRewrite, which are prefixed by "REDIRECT_".
  $candidates = array($key, 'REDIRECT_' . $key);

  foreach ($candidates as $candidate) {
    if (isset($_SERVER[$candidate])) {
      return $_SERVER[$candidate];
    }
  }

  return $default;
}

/**
 * Performs a http request.
 **/
function http_request($location) {
  if (!extension_loaded('curl')) {
    show_404('curl extension must be installed and enabled to perform http requests.');
  }

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_HEADER, 0);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
  curl_setopt($ch, CURLOPT_URL, $location);
  $data = curl_exec($ch);
  $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);

  $result = array();
  $result['data'] = $data;
  $result['code'] = $code;

  return $result;
}

// Production base url must be set!
if (!$production = config('404_FIXER_PRODUCTION')) {
  show_404('no production set');
}

$uri = $_SERVER['REQUEST_URI'];

// Is development version in sub-path? Remove this sub-path when resolving the name of the file in production.
// Note: if production version is in sub-path you just need to append it to 404_FIXER_PRODUCTION.
if ($subpath = config('404_FIXER_DEV_SUBPATH')) {
  $uri = preg_replace('~^' . preg_quote(rtrim($subpath, '/'), '~') . '~', '', $uri);
  //var_dump($uri);die();
}

$location = rtrim($production, '/') . $uri;

// Download locally, if asked.
if ($download = config('404_FIXER_DOWNLOAD_FILES', TRUE)) {
  $http_result = http_request($location);

  if ($http_result['code'] != 200) {
    show_404('got ' . $http_result['code'] . ' trying to download ' . $location);
  }

  $pwd = config('404_FIXER_DEV_ROOT', dirname(__FILE__));
  $path = str_replace('/', DIRECTORY_SEPARATOR, $uri);
  $path = urldecode($path);
  $file = $pwd . $path;
  $dir = dirname($file);

  // Permissions to new directories and downloaded files.
  $perms = config('404_FIXER_DOWNLOAD_FILES_PERMS', 0777);
  $perms = intval($perms, 8);

  if (!is_dir($dir)) {
    if (!mkdir($dir, $perms, TRUE)) {
      show_404('unable to create directory ' . $dir);
    }
  }

  if (!file_put_contents($file, $http_result['data'])) {
    show_404('unable to save file in ' . $file);
  }

  chmod($file, $perms);

  // Final location is local now.
  $location = $_SERVER['REQUEST_URI'];
}

header('Location: ' . $location);
