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

namespace App\MaildirReader;

use eXorus\PhpMimeMailParser\Parser;

class MaildirService
{
    private $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function getMail($id)
    {
        $parser = new Parser();
        $parser->setPath($this->config['maildir'].'/'.$id);

        $subject = htmlspecialchars($parser->getHeader('subject'));
        $from = htmlspecialchars($parser->getHeader('from'));
        $date = htmlspecialchars($parser->getHeader('date'));

// Get HTML with embedded images (cid)
        $html = $parser->getMessageBody('htmlEmbedded');
        if (!$html) {
            $html = nl2br(htmlspecialchars($parser->getMessageBody('text')));
        }

        $attach_dir = $this->config['app_root'].'/public_html/attachments/'.$id.'/';
        if (!is_dir($attach_dir)) {
            mkdir($attach_dir, 0777, true);
        }

        $parser->saveAttachments($attach_dir);
        $attachments = $parser->getAttachments(false);

        foreach ($attachments as $attachment) {
            $html .= "<img src='attachments/{$id}/{$attachment->getFilename()}'>";
        }

        return [
            'id' => $id,
            'subject' => $subject,
            'from' => $from,
            'date' => $date,
            'html' => $html,
        ];
    }

    public function getTotalMails()
    {
        return count(glob($this->config['maildir'].'/*'));
    }

    public function getAllMailFiles()
    {
        return glob($this->config['maildir'].'/*');
    }

    public function getMails($page, $limit = 20)
    {
        $ret = [];
        $files = glob($this->config['maildir'].'/*');
        // newest files first
        rsort($files);

        $parser = new Parser();
        foreach ($files as $index => $file) {
            if ($index < $limit * $page && $index >= $limit * ($page - 1) && is_file($file)) {
                $ret[] = $this->parseEmailFile($parser, $file);
            }
        }

        return $ret;
    }

    /**
     * @param Parser $parser
     * @param $file
     * @return array
     * @throws \Exception
     */
    protected
    function parseEmailFile(
        Parser $parser,
        $file
    ): array {
        $parser->setPath($file);

        $subject = htmlspecialchars($parser->getHeader('subject') ?: '(no subject)');
        $from = htmlspecialchars($parser->getHeader('from'));
        $date = htmlspecialchars($parser->getHeader('date'));

        $id = urlencode(basename($file));
        $ret = [
            'id' => $id,
            'subject' => $subject,
            'from' => $from,
            'date' => $date,
        ];

        return $ret;
    }

    public function getNextPrevId($id)
    {
        $files = glob($this->config['maildir'].'/*');
        rsort($files);

        $index = array_search($this->config['maildir'].'/'.$id, $files);
        if ($index === false) {
            return null;
        }

        return [
            'next' => $index > 1 && $files[$index - 1] ? urlencode(basename($files[$index - 1])) : null,
            'prev' => $files[$index + 1] ? urlencode(basename($files[$index + 1])) : null,
        ];
    }

    public function isMailFile($id)
    {
        $files = glob($this->config['maildir'].'/*');

        return in_array($this->config['maildir'].'/'.$id, $files);
    }

    public function cleanup()
    {
        $patterns = [
            $this->config['app_root'].'/Maildir/new/*',
            $this->config['app_root'].'/public_html/attachments/*',
        ];
        foreach ($patterns as $pattern) {
            $files = glob($pattern);
            $days = $this->config['cleanup'];
            foreach ($files as $file) {
                if (time() - filemtime($file) > $days * 24 * 60 * 60) {
                    if (is_dir($file)) {
                        // Remove directory and its contents recursively
                        $this->removeDirectory($file);
                    } else {
                        unlink($file);
                    }
                }
            }
        }
    }

    /**
     * Remove directory and its contents recursively
     *
     * @param string $dir Directory path
     * @return bool True on success, false on failure
     */
    private function removeDirectory($dir)
    {
        if (!file_exists($dir)) {
            return true;
        }

        if (!is_dir($dir)) {
            return unlink($dir);
        }

        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }

            if (!$this->removeDirectory($dir.DIRECTORY_SEPARATOR.$item)) {
                return false;
            }
        }

        return rmdir($dir);
    }

    public function getAllMailDates()
    {
        $ret = [];
        $files = glob($this->config['maildir'].'/*');

        foreach ($files as $file) {
            $fileDate = date('Y-m-d', filemtime($file));
            $ret[$fileDate] = true;
        }

        return array_keys($ret);
    }

    public function getMailsByDate($date)
    {
        $ret = [];
        $files = glob($this->config['maildir'].'/*');
        foreach ($files as $file) {
            $fileDate = date('Y-m-d', filemtime($file));
            if ($fileDate == $date) {
                $ret[] = $this->getMail(basename($file));
            }
        }
        return $ret;
    }
}