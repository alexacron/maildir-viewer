<?php
/**
 * @project: maildir-viewer
 * @author: alexjorj <https://github.com/alexacron>
 * @license: MIT
 * @copyright: Copyright (c) 2025 Alex Iordache
 *
 * MIT License
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

use eXorus\PhpMimeMailParser\Parser;

require_once dirname(__DIR__) . '/vendor/autoload.php';

// Load environment variables from .env file if it exists
if (file_exists(dirname(__DIR__) . '/.env')) {
    $envFile = file(dirname(__DIR__) . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($envFile as $line) {
        if (strpos(trim($line), '#') === 0) continue; // Skip comments
        putenv(trim($line));
    }
}

$appEnv = getenv('APP_ENV') ?: 'dev';
if ('dev' === $appEnv) {
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
    ini_set('display_errors', 1);
    $maildir = dirname(__DIR__) . '/Maildir/new';
}else {
    error_reporting(0);
    ini_set('display_errors', 0);
    $maildir = getenv("HOME") . '/Maildir/new';
}

// Get the request path
$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$base_path = dirname($_SERVER['SCRIPT_NAME']);
$path = substr($request_uri, strlen($base_path) ?: 0);
$config = [
    'app_root' => dirname(__DIR__),
    'path' => trim($path, '/'),
    'maildir' => $maildir,
    'env' => $appEnv,
    'cleanup' => getenv('DELETE_MAILS_DAYS') ?: '30',
];

// Initialize MaildirManager
$maildirManager = new App\MaildirReader\MaildirController($config);

// If no specific path is requested, show the default view
if (empty($path)) {
    $path = 'index';
}

// Check if the method exists in MaildirManager
$method = str_replace('-', '', lcfirst(ucwords($path, '-'))); // Convert kebab-case to camelCase

if (method_exists($maildirManager, $method)) {
    // Call the method
    call_user_func([$maildirManager, $method]);
} else {
    // Return 404 if method doesn't exist
    http_response_code(404);
    echo "404 Not Found";
}

