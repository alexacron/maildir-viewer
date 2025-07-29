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

ini_set("display_errors", 1);
error_reporting(E_ALL^E_NOTICE^E_DEPRECATED);

$id = basename($_GET['id'] ?? '');
$maildir = getenv("HOME") . '/Maildir/new';
$maildir = dirname(__DIR__) . '/Maildir/new';
$mailfile = "$maildir/$id";

if (!is_file($mailfile)) {
    http_response_code(404);
    exit("Email not found.");
}

$parser = new Parser();
$parser->setPath($mailfile);

$subject = htmlspecialchars($parser->getHeader('subject'));
$from = htmlspecialchars($parser->getHeader('from'));
$date = htmlspecialchars($parser->getHeader('date'));

// Get HTML with embedded images (cid)
$html = $parser->getMessageBody('htmlEmbedded');
if (!$html) {
    $html = nl2br(htmlspecialchars($parser->getMessageBody('text')));
}

$attach_dir = __DIR__ . '/attachments/';

$parser->saveAttachments($attach_dir);
$attachments = $parser->getAttachments(false);

echo "<a href='index.php'>← back to inbox</a><br><hr>";
echo "<h2>$subject</h2>";
echo "<p><b>From:</b> $from<br><b>Date:</b> $date</p>";
echo "<div style='border:1px solid #ccc; padding:10px;'>$html</div>";
foreach ($attachments as $attachment) {
    echo "<img src='attachments/{$attachment->getFilename()}'>";
}