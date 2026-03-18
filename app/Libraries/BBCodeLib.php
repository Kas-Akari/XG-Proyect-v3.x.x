<?php

namespace App\Libraries;

use App\Helpers\UrlHelper;

class BBCodeLib
{
    public function bbCode(?string $string = ''): string
    {
        $bbcodes = [
            '/\\n/' => '$this->setLineJump()',
            '/\\r/' => '$this->setReturn()',
            '/\[list\](.*?)\[\/list\]/is' => '$this->setList(\'\\1\')',
            '/\[b\](.*?)\[\/b\]/is' => '$this->setBold(\'\\1\')',
            '/\[strong\](.*?)\[\/strong\]/is' => '$this->setBold(\'\\1\')',
            '/\[i\](.*?)\[\/i\]/is' => '$this->setItalic(\'\\1\')',
            '/\[u\](.*?)\[\/u\]/is' => '$this->setUnderline(\'\\1\')',
            '/\[s\](.*?)\[\/s\]/is' => '$this->setStrike(\'\\1\')',
            '/\[del\](.*?)\[\/del\]/is' => '$this->setStrike(\'\\1\')',
            '/\[url=(.*?)\](.*?)\[\/url\]/is' => '$this->setUrl(\'\\1\',\'\\2\')',
            '/\[email=(.*?)\](.*?)\[\/email\]/is' => '$this->setEmail(\'\\1\',\'\\2\')',
            '/\[img](.*?)\[\/img\]/is' => '$this->setImage(\'\\1\')',
            '/\[color=(.*?)\](.*?)\[\/color\]/is' => '$this->setFontColor(\'\\1\',\'\\2\')',
            '/\[font=(.*?)\](.*?)\[\/font\]/is' => '$this->setFontFamiliy(\'\\1\',\'\\2\')',
            '/\[bg=(.*?)\](.*?)\[\/bg\]/is' => '$this->setBackgroundColor(\'\\1\',\'\\2\')',
            '/\[size=(.*?)\](.*?)\[\/size\]/is' => '$this->setFontSize(\'\\1\',\'\\2\')',
            '/\[coordinates\](.*?):(.*?):(.*?)\[\/coordinates]/is' => '$this->setCoordinates(\'\\1\',\'\\2\',\'\\3\')',
        ];

        $string = stripslashes($string ?? '');

        foreach ($bbcodes as $bbcode => $html) {
            $string = preg_replace_callback(
                $bbcode,
                function ($matches) use ($html) {
                    return $this->getBbCode($matches, $html);
                },
                $string
            );
        }

        return $string;
    }

    private function getBbCode(array $matches, string $replace): string
    {
        if (isset($matches[1])) {
            $replacements = [
                '\1' => isset($matches[1]) ? $matches[1] : '',
                '\2' => isset($matches[2]) ? $matches[2] : '',
                '\3' => isset($matches[3]) ? $matches[3] : '',
            ];

            return eval('return ' . strtr($replace, $replacements) . ';');
        } else {
            return eval('return ' . $replace . ';');
        }
    }

    /**
     * 1st removes the backslashes \ to avoid scaping
     * 2nd convert " and ' to HTML entities
     * @param string $string
     * @return string
     */
    private function escapeContent(string $string): string
    {
        return htmlspecialchars(stripslashes($string), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Replaces everything except letters, numbers, #, -, _, and \s
     * @param string $value
     * @return array|string|null
     */
    private function sanitizeStyleValue(string $value): string
    {
        return preg_replace('/[^a-zA-Z0-9#\-_\s]/', '', $value);
    }

    private function setLineJump(): string
    {
        return '<br/>';
    }

    private function setReturn(): string
    {
        return '';
    }

    private function setList(mixed $string): string
    {
        $tmp = explode('[*]', stripslashes($string));
        $out = null;

        foreach ($tmp as $list) {
            if (strlen(str_replace('', '', $list)) > 0) {
                $out .= '<li>' . htmlspecialchars(trim($list), ENT_QUOTES, 'UTF-8') . '</li>';
            }
        }

        return '<ul>' . $out . '</ul>';
    }

    private function setBold(string $string): string
    {
        return '<span style="font-weight: bold;">' . $this->escapeContent($string) . '</span>';
    }

    private function setItalic(string $string): string
    {
        return '<span style="font-style: italic;">' . $this->escapeContent($string) . '</span>';
    }

    private function setUnderline(string $string): string
    {
        return '<span style="text-decoration: underline;">' . $this->escapeContent($string) . '</span>';
    }

    private function setStrike(string $string): string
    {
        return '<span style="text-decoration: line-through;">' . $this->escapeContent($string) . '</span>';
    }

    private function setUrl(string $url, string $title): ?string
    {
        $title = $this->escapeContent($title);
        $url = trim($url);
        $exclude = [
            'data', 'file', 'javascript', 'jar', '#',
        ];

        if (in_array(strstr($url, ':', true), $exclude) == false) {
            $safeUrl = $this->escapeContent($url);
            return UrlHelper::setUrl($safeUrl, $title, $title);
        }

        return $this->escapeContent($url);
    }

    private function setEmail(string $mail, string $title): string
    {
        $safeMail = $this->escapeContent($mail);
        $safeTitle = $this->escapeContent($title);
        return '<a href="mailto:' . $safeMail . '" title="' . $safeMail . '">' . $safeTitle . '</a>';
    }

    private function setImage(string $img): string
    {
        $img = stripslashes($img);

        if ((substr($img, 0, 7) != 'http://') && (substr($img, 0, 8) != 'https://')) {
            $img = XGP_ROOT . IMG_PATH . $img;
        }

        $safeImg = $this->escapeContent($img);
        return '<img src="' . $safeImg . '" alt="' . $safeImg . '" title="' . $safeImg . '" />';
    }

    private function setFontColor(string $color, string $title): string
    {
        $safeColor = $this->sanitizeStyleValue($color);
        return '<span style="color:' . $safeColor . '">' . $this->escapeContent($title) . '</span>';
    }

    private function setFontFamiliy(string $font, string $title): string
    {
        $safeFont = $this->sanitizeStyleValue($font);
        return '<span style="font-family:' . $safeFont . '">' . $this->escapeContent($title) . '</span>';
    }

    private function setBackgroundColor(string $bg, string $title): string
    {
        $safeBg = $this->sanitizeStyleValue($bg);
        return '<span style="background-color:' . $safeBg . '">' . $this->escapeContent($title) . '</span>';
    }

    private function setFontSize(string $size, string $text): string
    {
        $safeSize = preg_replace('/[^0-9]/', '', $size);
        return '<span style="font-size:' . $safeSize . 'px">' . $this->escapeContent($text) . '</span>';
    }

    private function setCoordinates($galaxy, $system, $planet)
    {
        return FormatLib::prettyCoords($galaxy, $system, $planet);
    }
}
