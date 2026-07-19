<?php

namespace App\Services;

class ContentImportService
{
    /**
     * Import and parse Markdown content into clean HTML.
     */
    public function importMarkdown(string $content): string
    {
        $lines = explode("\n", str_replace("\r", "", $content));
        $html = '';
        $inList = false;
        $listType = ''; // 'ul' or 'ol'
        $inCodeBlock = false;
        $codeLanguage = '';
        $inTable = false;
        $tableHeaders = [];
        $tableRows = [];

        foreach ($lines as $line) {
            $trimmed = trim($line);

            // 1. Fenced Code Blocks
            if (str_starts_with($trimmed, '```')) {
                if ($inCodeBlock) {
                    $html .= "</code></pre>\n";
                    $inCodeBlock = false;
                } else {
                    $lang = trim(substr($trimmed, 3));
                    $codeLanguage = $lang ? " class=\"language-{$lang}\"" : '';
                    $html .= "<pre><code{$codeLanguage}>";
                    $inCodeBlock = true;
                }
                continue;
            }

            if ($inCodeBlock) {
                $html .= htmlspecialchars($line) . "\n";
                continue;
            }

            // Close table if line is empty or does not contain pipe
            if ($inTable && (!str_contains($line, '|') || $trimmed === '')) {
                $html .= $this->buildTableHtml($tableHeaders, $tableRows);
                $inTable = false;
                $tableHeaders = [];
                $tableRows = [];
            }

            // 2. Tables
            if (str_contains($line, '|')) {
                $cells = array_map('trim', explode('|', $trimmed));
                // Remove empty outer cells (common in markdown tables)
                if (count($cells) > 0 && $cells[0] === '') array_shift($cells);
                if (count($cells) > 0 && $cells[count($cells) - 1] === '') array_pop($cells);

                if (!$inTable) {
                    $inTable = true;
                    $tableHeaders = $cells;
                    continue;
                } else {
                    // Check if it's the separator line e.g. |---|---|
                    $isSeparator = true;
                    foreach ($cells as $c) {
                        if ($c !== '' && !preg_match('/^:?-+:?$/', $c)) {
                            $isSeparator = false;
                            break;
                        }
                    }
                    if ($isSeparator) {
                        continue;
                    }
                    $tableRows[] = $cells;
                    continue;
                }
            }

            // Close lists if we exit them
            if ($inList && !$this->isListLine($trimmed, $listType)) {
                $html .= "</{$listType}>\n";
                $inList = false;
                $listType = '';
            }

            if ($trimmed === '') {
                continue;
            }

            // 3. Headings (# to ######)
            if (preg_match('/^(#{1,6})\s+(.*)$/', $trimmed, $matches)) {
                $level = strlen($matches[1]);
                // Shift H1 to H2 to protect main title hierarchy
                if ($level === 1) $level = 2;
                $text = $this->parseInlineStyles($matches[2]);
                $html .= "<h{$level}>{$text}</h{$level}>\n";
                continue;
            }

            // 4. Blockquotes
            if (str_starts_with($trimmed, '>')) {
                $text = trim(substr($trimmed, 1));
                $text = $this->parseInlineStyles($text);
                $html .= "<blockquote>{$text}</blockquote>\n";
                continue;
            }

            // 5. Unordered Lists (- or *)
            if (preg_match('/^[\*\+-]\s+(.*)$/', $trimmed, $matches)) {
                if (!$inList || $listType !== 'ul') {
                    if ($inList) $html .= "</{$listType}>\n";
                    $html .= "<ul>\n";
                    $inList = true;
                    $listType = 'ul';
                }
                $text = $this->parseInlineStyles($matches[1]);
                $html .= "<li>{$text}</li>\n";
                continue;
            }

            // 6. Ordered Lists (1. )
            if (preg_match('/^\d+\.\s+(.*)$/', $trimmed, $matches)) {
                if (!$inList || $listType !== 'ol') {
                    if ($inList) $html .= "</{$listType}>\n";
                    $html .= "<ol>\n";
                    $inList = true;
                    $listType = 'ol';
                }
                $text = $this->parseInlineStyles($matches[1]);
                $html .= "<li>{$text}</li>\n";
                continue;
            }

            // 7. Paragraph
            $text = $this->parseInlineStyles($trimmed);
            $html .= "<p>{$text}</p>\n";
        }

        // Clean up remaining lists or tables
        if ($inList) {
            $html .= "</{$listType}>\n";
        }
        if ($inTable) {
            $html .= $this->buildTableHtml($tableHeaders, $tableRows);
        }

        return $html;
    }

    private function isListLine(string $trimmed, string $currentType): bool
    {
        if ($currentType === 'ul' && preg_match('/^[\*\+-]\s+/', $trimmed)) return true;
        if ($currentType === 'ol' && preg_match('/^\d+\.\s+/', $trimmed)) return true;
        return false;
    }

    private function buildTableHtml(array $headers, array $rows): string
    {
        $html = "<table class=\"table table-bordered table-striped\">\n<thead>\n<tr>\n";
        foreach ($headers as $h) {
            $html .= "<th>" . $this->parseInlineStyles($h) . "</th>\n";
        }
        $html .= "</tr>\n</thead>\n<tbody>\n";
        foreach ($rows as $row) {
            $html .= "<tr>\n";
            foreach ($row as $cell) {
                $html .= "<td>" . $this->parseInlineStyles($cell) . "</td>\n";
            }
            $html .= "</tr>\n";
        }
        $html .= "</tbody>\n</table>\n";
        return $html;
    }

    private function parseInlineStyles(string $text): string
    {
        $text = htmlspecialchars($text);

        // Images: ![alt](url)
        $text = preg_replace(
            '/\\!\\@amp\\#91\\;(.*?)\\@amp\\#93\\;\\((.*?)\\)/i', 
            '<img src="$2" alt="$1" class="img-fluid my-2" style="border-radius:6px; max-height:400px;">', 
            $text
        );
        // Fallback standard regex
        $text = preg_replace(
            '/\\!\\[(.*?)\\]\\((.*?)\\)/i', 
            '<img src="$2" alt="$1" class="img-fluid my-2" style="border-radius:6px; max-height:400px;">', 
            $text
        );

        // Links: [text](url)
        $text = preg_replace('/\\[(.*?)\\]\\((.*?)\\)/i', '<a href="$2" target="_blank">$1</a>', $text);

        // Bold: **text**
        $text = preg_replace('/\\*\\*(.*?)\\*\\*/', '<strong>$1</strong>', $text);

        // Italic: *text*
        $text = preg_replace('/\\*(.*?)\\*/', '<em>$1</em>', $text);

        // Inline Code: `code`
        $text = preg_replace('/`(.*?)`/', '<code>$1</code>', $text);

        return $text;
    }

    /**
     * Import and parse a Microsoft Word Document (.docx) into clean HTML.
     */
    public function importDocx(string $filePath): string
    {
        if (!class_exists('ZipArchive')) {
            return "<p>PHP ZipArchive extension is required to import Word Documents.</p>";
        }

        $zip = new \ZipArchive();
        if ($zip->open($filePath) !== true) {
            return "<p>Unable to open Word file.</p>";
        }

        // Get document.xml
        $xmlContent = $zip->getFromName('word/document.xml');
        if (!$xmlContent) {
            $zip->close();
            return "<p>Invalid Word document structure.</p>";
        }

        // Parse relationships to resolve hyperlink targets
        $rels = [];
        $relsContent = $zip->getFromName('word/_rels/document.xml.rels');
        if ($relsContent) {
            $relsDom = new \DOMDocument();
            @$relsDom->loadXML($relsContent);
            foreach ($relsDom->getElementsByTagName('Relationship') as $rel) {
                $id = $rel->getAttribute('Id');
                $target = $rel->getAttribute('Target');
                $type = $rel->getAttribute('Type');
                if (str_contains($type, 'relationships/hyperlink')) {
                    $rels[$id] = $target;
                }
            }
        }

        $zip->close();

        // Load document DOM
        $dom = new \DOMDocument();
        // Ignore parsing errors for custom XML namespaces
        @$dom->loadXML($xmlContent);

        $body = $dom->getElementsByTagName('body')->item(0);
        if (!$body) {
            return "<p>No body content found in Word document.</p>";
        }

        $html = '';
        foreach ($body->childNodes as $node) {
            if ($node->nodeName === 'w:p') {
                $html .= $this->parseDocxParagraph($node, $rels);
            } elseif ($node->nodeName === 'w:tbl') {
                $html .= $this->parseDocxTable($node, $rels);
            }
        }

        return $html;
    }

    private function parseDocxParagraph(\DOMNode $pNode, array $rels): string
    {
        $text = '';
        $isHeading = false;
        $headingLevel = 2;
        $isList = false;

        // Check paragraph properties
        $pPr = $this->getChildNode($pNode, 'w:pPr');
        if ($pPr) {
            // Check if heading
            $pStyle = $this->getChildNode($pPr, 'w:pStyle');
            if ($pStyle) {
                $styleVal = $pStyle->getAttribute('w:val');
                if (preg_match('/Heading(\d)/i', $styleVal, $matches)) {
                    $isHeading = true;
                    $headingLevel = min(6, max(2, intval($matches[1]) + 1)); // map Heading 1 -> <h2>, Heading 2 -> <h3>
                }
            }
            // Check if list item
            $numPr = $this->getChildNode($pPr, 'w:numPr');
            if ($numPr || ($pStyle && str_contains(strtolower($pStyle->getAttribute('w:val')), 'list'))) {
                $isList = true;
            }
        }

        // Loop runs and hyperlinks
        foreach ($pNode->childNodes as $child) {
            if ($child->nodeName === 'w:r') {
                $text .= $this->parseDocxRun($child);
            } elseif ($child->nodeName === 'w:hyperlink') {
                $rId = $child->getAttribute('r:id') ?: $child->getAttribute('w:id');
                $url = $rels[$rId] ?? '#';
                $linkText = '';
                foreach ($child->childNodes as $rChild) {
                    if ($rChild->nodeName === 'w:r') {
                        $linkText .= $this->parseDocxRun($rChild);
                    }
                }
                if ($linkText !== '') {
                    $text .= "<a href=\"" . htmlspecialchars($url) . "\" target=\"_blank\">{$linkText}</a>";
                }
            }
        }

        $trimmed = trim(strip_tags($text));
        if ($trimmed === '') return '';

        if ($isHeading) {
            return "<h{$headingLevel}>{$text}</h{$headingLevel}>\n";
        } elseif ($isList) {
            return "<ul><li>{$text}</li></ul>\n"; // cleaned up by browser rendering or custom post-process
        } else {
            return "<p>{$text}</p>\n";
        }
    }

    private function parseDocxRun(\DOMNode $rNode): string
    {
        $text = '';
        $rPr = $this->getChildNode($rNode, 'w:rPr');
        $isBold = $rPr && $this->getChildNode($rPr, 'w:b');
        $isItalic = $rPr && $this->getChildNode($rPr, 'w:i');
        $isUnderline = $rPr && $this->getChildNode($rPr, 'w:u');

        foreach ($rNode->childNodes as $child) {
            if ($child->nodeName === 'w:t') {
                $text .= htmlspecialchars($child->nodeValue);
            } elseif ($child->nodeName === 'w:br') {
                $text .= "<br>";
            }
        }

        if ($text === '') return '';

        if ($isBold) $text = "<strong>{$text}</strong>";
        if ($isItalic) $text = "<em>{$text}</em>";
        if ($isUnderline) $text = "<u>{$text}</u>";

        return $text;
    }

    private function parseDocxTable(\DOMNode $tblNode, array $rels): string
    {
        $html = "<table class=\"table table-bordered table-striped\">\n<tbody>\n";

        foreach ($tblNode->childNodes as $row) {
            if ($row->nodeName !== 'w:tr') continue;
            $html .= "<tr>\n";
            foreach ($row->childNodes as $cell) {
                if ($cell->nodeName !== 'w:tc') continue;
                $html .= "<td>";
                // Get cell paragraph contents
                foreach ($cell->childNodes as $cNode) {
                    if ($cNode->nodeName === 'w:p') {
                        $html .= $this->parseDocxParagraph($cNode, $rels);
                    }
                }
                $html .= "</td>\n";
            }
            $html .= "</tr>\n";
        }

        $html .= "</tbody>\n</table>\n";
        return $html;
    }

    private function getChildNode(\DOMNode $parent, string $name): ?\DOMElement
    {
        foreach ($parent->childNodes as $child) {
            if ($child->nodeName === $name && $child instanceof \DOMElement) {
                return $child;
            }
        }
        return null;
    }

    /**
     * Extract plain layout texts from PDF and wrap into readable HTML paragraphs.
     */
    public function importPdf(string $filePath): string
    {
        $content = file_get_contents($filePath);
        if (!$content) {
            return "<p>Unable to read PDF file.</p>";
        }

        $text = '';
        // Extract streams
        preg_match_all('/stream(.*?)endstream/is', $content, $streams);

        foreach ($streams[1] as $stream) {
            $stream = trim($stream);
            $decompressed = '';

            // Try decompressing FlateDecode streams
            try {
                // PDF streams are compressed via zlib FlateDecode. 
                // We attempt to suppress errors with @ in case of custom filters.
                $decompressed = @gzuncompress($stream);
                if (!$decompressed) {
                    // Fallback to simple sub-string checks
                    if (str_starts_with($stream, 'x')) {
                        $decompressed = @gzuncompress(substr($stream, 2)); // offset PDF header bytes
                    }
                }
            } catch (\Exception $e) {
                continue;
            }

            if ($decompressed) {
                // Extract parenthesized strings Tj / TJ
                // Matches (string) Tj or strings inside [(s1) -5 (s2)] TJ
                preg_match_all('/(?:\((.*?)\)\s*T[j\'])|(?:\[(.*?)\]\s*TJ)/s', $decompressed, $matches);
                
                foreach ($matches[0] as $match) {
                    // Extract all parentheses matches
                    preg_match_all('/\((.*?)\)/s', $match, $strMatches);
                    foreach ($strMatches[1] as $s) {
                        // Decode octal characters e.g. \342
                        $s = preg_replace_callback('/\\\\([0-7]{3})/', function($m) {
                            return chr(octdec($m[1]));
                        }, $s);
                        // Strip escape slashes
                        $s = str_replace(['\\(', '\\)', '\\\\'], ['(', ')', '\\'], $s);
                        $text .= $s;
                    }
                    $text .= ' ';
                }
            }
        }

        // Clean up extracted texts and format as block paragraphs
        $text = trim($text);
        if ($text === '') {
            // Fallback: extract plain text directly from the PDF raw stream strings
            preg_match_all('/\((.*?)\)\s*Tj/s', $content, $fallbackMatches);
            $text = implode(' ', $fallbackMatches[1]);
        }

        if (trim($text) === '') {
            return "<p>No readable text content could be extracted from this PDF.</p>";
        }

        // Break long character strings into block paragraphs
        $paragraphs = explode("\n", str_replace(["\r\n", "\r"], "\n", $text));
        $html = '';
        foreach ($paragraphs as $p) {
            $pTrim = trim($p);
            if ($pTrim !== '') {
                $html .= "<p>" . htmlspecialchars($pTrim) . "</p>\n";
            }
        }

        return $html;
    }

    /**
     * Import, parse, and sanitize HTML document.
     */
    public function importHtml(string $htmlContent): string
    {
        // Remove unsafe headers, script and iframe elements
        $htmlContent = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $htmlContent);
        $htmlContent = preg_replace('/<iframe\b[^>]*>(.*?)<\/iframe>/is', '', $htmlContent);
        $htmlContent = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $htmlContent);

        // Extract body element content if present
        if (preg_match('/<body\b[^>]*>(.*?)<\/body>/is', $htmlContent, $matches)) {
            $htmlContent = $matches[1];
        }

        return trim($htmlContent);
    }
}
