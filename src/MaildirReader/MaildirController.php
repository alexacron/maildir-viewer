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

class MaildirController
{
    private $config;
    public function __construct($config)
    {
        $this->config = $config;
    }

    public function index()
    {
        $maildirService = new MaildirService($this->config);

        $page = $_GET['page'] ?? 1;
        $perPage = $_GET['per_page'] ?? 20;
        $allMails = $maildirService->getMails($page, $perPage);

        echo $this->renderView('index', [
            'allMails' => $allMails,
            'pagination' => [
                'page' => $page,
                'limit' => $perPage,
                'total' => $maildirService->getTotalMails(),
            ]
        ]);
    }

    public function view()
    {
        $maildirService = new MaildirService($this->config);
        $id = $_GET['id'] ?? '';

        if (!$id || !$maildirService->isMailFile($id)) {
            http_response_code(404);
            exit("Email not found.");
        }

        $mail = $maildirService->getMail($id);
        $nextPrevId = $maildirService->getNextPrevId($id);

        echo $this->renderView('view', [
            'mail' => $mail,
            'next' => $nextPrevId['next'],
            'prev' => $nextPrevId['prev'],
        ]);
    }

    private function renderView(string $string, array $array)
    {
        $template = file_get_contents(__DIR__ . '/views/' . $string . '.html');

        ob_start();
        extract($array);
        eval('?>' . $template);
        return ob_get_clean();
    }

    public function cleanup()
    {
        $maildirService = new MaildirService($this->config);
        $maildirService->cleanup();
        echo "Cleanup done.";
    }
}