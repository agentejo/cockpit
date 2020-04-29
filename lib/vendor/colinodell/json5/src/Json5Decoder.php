<?php

/*
 * This file is part of the colinodell/json5 package.
 *
 * (c) Colin O'Dell <colinodell@gmail.com>
 *
 * Based on the official JSON5 implementation for JavaScript (https://github.com/json5/json5)
 *  - (c) 2012-2016 Aseem Kishore and others (https://github.com/json5/json5/contributors)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ColinODell\Json5;

final class Json5Decoder
{
    private $json;

    private $length;

    private $at = 0;

    private $currentByte;

    private $lineNumber = 1;

    private $associative = false;

    private $maxDepth = 512;

    private $castBigIntToString = false;

    private $depth = 1;

    private $currentLineStartsAt = 0;

    /**
     * Private constructor.
     *
     * @param string $json
     * @param bool   $associative
     * @param int    $depth
     * @param bool   $castBigIntToString
     */
    private function __construct($json, $associative = false, $depth = 512, $castBigIntToString = false)
    {
        $this->json = $json;
        $this->associative = $associative;
        $this->maxDepth = $depth;
        $this->castBigIntToString = $castBigIntToString;

        $this->length = \strlen($json);
        $this->currentByte = $this->getByte(0);
    }

    /**
     * Takes a JSON encoded string and converts it into a PHP variable.
     *
     * The parameters exactly match PHP's json_decode() function - see
     * http://php.net/manual/en/function.json-decode.php for more information.
     *
     * @param string $source      The JSON string being decoded.
     * @param bool   $associative When TRUE, returned objects will be converted into associative arrays.
     * @param int    $depth       User specified recursion depth.
     * @param int    $options     Bitmask of JSON decode options.
     *
     * @return mixed
     */
    public static function decode($source, $associative = false, $depth = 512, $options = 0)
    {
        // Try parsing with json_decode first, since that's much faster
        // We only attempt this on PHP 7+ because 5.x doesn't parse some edge cases correctly
        if (PHP_VERSION_ID >= 70000) {
            $result = \json_decode($source, $associative, $depth, $options);
            if (\json_last_error() === \JSON_ERROR_NONE) {
                return $result;
            }
        }

        // Fall back to JSON5 if that fails
        $associative = $associative === true || ($associative === null && $options & \JSON_OBJECT_AS_ARRAY);
        $castBigIntToString = $options & \JSON_BIGINT_AS_STRING;

        $decoder = new self((string)$source, $associative, $depth, $castBigIntToString);

        $result = $decoder->value();
        $decoder->white();
        if ($decoder->currentByte) {
            $decoder->throwSyntaxError('Syntax error');
        }

        return $result;
    }

    /**
     * @param int $at
     *
     * @return null
     */
    private function getByte($at)
    {
        if ($at >= $this->length) {
            return null;
        }

        return $this->json[$at];
    }

    /**
     * @return string|null
     */
    private function currentChar()
    {
        if ($this->at >= $this->length) {
            return null;
        }

        return \mb_substr(\substr($this->json, $this->at, 4), 0, 1);
    }

    /**
     * Parse the next character.
     *
     * @return null|string
     */
    private function next()
    {
        // Get the next character. When there are no more characters,
        // return the empty string.
        if ($this->currentByte === "\n" || ($this->currentByte === "\r" && $this->peek() !== "\n")) {
            $this->lineNumber++;
            $this->currentLineStartsAt = $this->at + 1;
        }

        $this->at++;
        
        return $this->currentByte = $this->getByte($this->at);
    }

    /**
     * Parse the next character if it matches $c or fail.
     *
     * @param string $c
     *
     * @return string|null
     */
    private function nextOrFail($c)
    {
        if ($c !== $this->currentByte) {
            $this->throwSyntaxError(\sprintf(
                'Expected %s instead of %s',
                self::renderChar($c),
                self::renderChar($this->currentChar())
            ));
        }

        return $this->next();
    }

    /**
     * Get the next character without consuming it or
     * assigning it to the ch variable.
     *
     * @return mixed
     */
    private function peek()
    {
        return $this->getByte($this->at + 1);
    }

    /**
     * Attempt to match a regular expression at the current position on the current line.
     *
     * This function will not match across multiple lines.
     *
     * @param string $regex
     *
     * @return string|null
     */
    private function match($regex)
    {
        $subject = \substr($this->json, $this->at);
        // Only match on the current line
        if ($pos = \strpos($subject, "\n")) {
            $subject = \substr($subject, 0, $pos);
        }

        if (!\preg_match($regex, $subject, $matches, PREG_OFFSET_CAPTURE)) {
            return null;
        }

        $this->at += $matches[0][1] + \strlen($matches[0][0]);
        $this->currentByte = $this->getByte($this->at);

        return $matches[0][0];
    }

    /**
     * Parse an identifier.
     *
     * Normally, reserved words are disallowed here, but we
     * only use this for unquoted object keys, where reserved words are allowed,
     * so we don't check for those here. References:
     * - http://es5.github.com/#x7.6
     * - https://developer.mozilla.org/en/Core_JavaScript_1.5_Guide/Core_Language_Features#Variables
     * - http://docstore.mik.ua/orelly/webprog/jscript/ch02_07.htm
     */
    private function identifier()
    {
        // @codingStandardsIgnoreStart
        // Be careful when editing this regex, there are a couple Unicode characters in between here -------------vv
        $match = $this->match('/^(?:[\$_\p{L}\p{Nl}]|\\\\u[0-9A-Fa-f]{4})(?:[\$_\p{L}\p{Nl}\p{Mn}\p{Mc}\p{Nd}\p{Pc}‌‍]|\\\\u[0-9A-Fa-f]{4})*/u');
        // @codingStandardsIgnoreEnd

        if ($match === null) {
            $this->throwSyntaxError('Bad identifier as unquoted key');
        }

        // Un-escape escaped Unicode chars
        $unescaped = \preg_replace_callback('/(?:\\\\u[0-9A-Fa-f]{4})+/', function ($m) {
            return \json_decode('"'.$m[0].'"');
        }, $match);

        return $unescaped;
    }

    private function number()
    {
        $number = null;
        $sign = '';
        $string = '';
        $base = 10;

        if ($this->currentByte === '-' || $this->currentByte === '+') {
            $sign = $this->currentByte;
            $this->next();
        }

        // support for Infinity
        if ($this->currentByte === 'I') {
            $this->word();

            return ($sign === '-') ? -INF : INF;
        }

        // support for NaN
        if ($this->currentByte === 'N') {
            $number = $this->word();

            // ignore sign as -NaN also is NaN
            return $number;
        }

        if ($this->currentByte === '0') {
            $string .= $this->currentByte;
            $this->next();
            if ($this->currentByte === 'x' || $this->currentByte === 'X') {
                $string .= $this->currentByte;
                $this->next();
                $base = 16;
            } elseif (\is_numeric($this->currentByte)) {
                $this->throwSyntaxError('Octal literal');
            }
        }

        switch ($base) {
            case 10:
                // @codingStandardsIgnoreStart
                if ((\is_numeric($this->currentByte) || $this->currentByte === '.') && ($match = $this->match('/^\d*\.?\d*/')) !== null) {
                    $string .= $match;
                }
                if (($this->currentByte === 'E' || $this->currentByte === 'e') && ($match = $this->match('/^[Ee][-+]?\d*/')) !== null) {
                    $string .= $match;
                }
                // @codingStandardsIgnoreEnd
                $number = $string;
                break;
            case 16:
                if (($match = $this->match('/^[A-Fa-f0-9]+/')) !== null) {
                    $string .= $match;
                    $number = \hexdec($string);
                    break;
                }
                $this->throwSyntaxError('Bad hex number');
        }

        if ($sign === '-') {
            $number = -1 * $number;
        }

        if (!\is_numeric($number) || !\is_finite($number)) {
            $this->throwSyntaxError('Bad number');
        }

        if ($this->castBigIntToString) {
            return $number;
        }

        // Adding 0 will automatically cast this to an int or float
        return $number + 0;
    }

    private function string()
    {
        $string = '';

        $delim = $this->currentByte;
        $this->next();
        while ($this->currentByte !== null) {
            if ($this->currentByte === $delim) {
                $this->next();

                return $string;
            }

            if ($this->currentByte === '\\') {
                if ($this->peek() === 'u' && $unicodeEscaped = $this->match('/^(?:\\\\u[A-Fa-f0-9]{4})+/')) {
                    $string .= \json_decode('"'.$unicodeEscaped.'"');
                    continue;
                }

                $this->next();
                if ($this->currentByte === "\r") {
                    if ($this->peek() === "\n") {
                        $this->next();
                    }
                } elseif (($escapee = self::getEscapee($this->currentByte)) !== null) {
                    $string .= $escapee;
                } else {
                    break;
                }
            } elseif ($this->currentByte === "\n") {
                // unescaped newlines are invalid; see:
                // https://github.com/json5/json5/issues/24
                // @todo this feels special-cased; are there other invalid unescaped chars?
                break;
            } else {
                $string .= $this->currentByte;
            }

            $this->next();
        }

        $this->throwSyntaxError('Bad string');
    }

    /**
     * Skip an inline comment, assuming this is one.
     *
     * The current character should be the second / character in the // pair that begins this inline comment.
     * To finish the inline comment, we look for a newline or the end of the text.
     */
    private function inlineComment()
    {
        do {
            $this->next();
            if ($this->currentByte === "\n" || $this->currentByte === "\r") {
                $this->next();

                return;
            }
        } while ($this->currentByte !== null);
    }

    /**
     * Skip a block comment, assuming this is one.
     *
     * The current character should be the * character in the /* pair that begins this block comment.
     * To finish the block comment, we look for an ending *​/ pair of characters,
     * but we also watch for the end of text before the comment is terminated.
     */
    private function blockComment()
    {
        do {
            $this->next();
            while ($this->currentByte === '*') {
                $this->nextOrFail('*');
                if ($this->currentByte === '/') {
                    $this->nextOrFail('/');

                    return;
                }
            }
        } while ($this->currentByte !== null);

        $this->throwSyntaxError('Unterminated block comment');
    }

    /**
     * Skip a comment, whether inline or block-level, assuming this is one.
     */
    private function comment()
    {
        // Comments always begin with a / character.
        $this->nextOrFail('/');

        if ($this->currentByte === '/') {
            $this->inlineComment();
        } elseif ($this->currentByte === '*') {
            $this->blockComment();
        } else {
            $this->throwSyntaxError('Unrecognized comment');
        }
    }

    /**
     * Skip whitespace and comments.
     *
     * Note that we're detecting comments by only a single / character.
     * This works since regular expressions are not valid JSON(5), but this will
     * break if there are other valid values that begin with a / character!
     */
    private function white()
    {
        while ($this->currentByte !== null) {
            if ($this->currentByte === '/') {
                $this->comment();
            } elseif (\preg_match('/^[ \t\r\n\v\f\xA0]/', $this->currentByte) === 1) {
                $this->next();
            } elseif (\ord($this->currentByte) === 0xC2 && \ord($this->peek()) === 0xA0) {
                // Non-breaking space in UTF-8
                $this->next();
                $this->next();
            } else {
                return;
            }
        }
    }

    /**
     * Matches true, false, null, etc
     */
    private function word()
    {
        switch ($this->currentByte) {
            case 't':
                $this->nextOrFail('t');
                $this->nextOrFail('r');
                $this->nextOrFail('u');
                $this->nextOrFail('e');
                return true;
            case 'f':
                $this->nextOrFail('f');
                $this->nextOrFail('a');
                $this->nextOrFail('l');
                $this->nextOrFail('s');
                $this->nextOrFail('e');
                return false;
            case 'n':
                $this->nextOrFail('n');
                $this->nextOrFail('u');
                $this->nextOrFail('l');
                $this->nextOrFail('l');
                return null;
            case 'I':
                $this->nextOrFail('I');
                $this->nextOrFail('n');
                $this->nextOrFail('f');
                $this->nextOrFail('i');
                $this->nextOrFail('n');
                $this->nextOrFail('i');
                $this->nextOrFail('t');
                $this->nextOrFail('y');
                return INF;
            case 'N':
                $this->nextOrFail('N');
                $this->nextOrFail('a');
                $this->nextOrFail('N');
                return NAN;
        }

        $this->throwSyntaxError('Unexpected ' . self::renderChar($this->currentChar()));
    }

    private function arr()
    {
        $arr = [];

        if (++$this->depth > $this->maxDepth) {
            $this->throwSyntaxError('Maximum stack depth exceeded');
        }

        $this->nextOrFail('[');
        $this->white();
        while ($this->currentByte !== null) {
            if ($this->currentByte === ']') {
                $this->nextOrFail(']');
                $this->depth--;
                return $arr; // Potentially empty array
            }
            // ES5 allows omitting elements in arrays, e.g. [,] and
            // [,null]. We don't allow this in JSON5.
            if ($this->currentByte === ',') {
                $this->throwSyntaxError('Missing array element');
            }

            $arr[] = $this->value();

            $this->white();
            // If there's no comma after this value, this needs to
            // be the end of the array.
            if ($this->currentByte !== ',') {
                $this->nextOrFail(']');
                $this->depth--;
                return $arr;
            }
            $this->nextOrFail(',');
            $this->white();
        }

        $this->throwSyntaxError('Invalid array');
    }

    /**
     * Parse an object value
     */
    private function obj()
    {
        $object = $this->associative ? [] : new \stdClass;

        if (++$this->depth > $this->maxDepth) {
            $this->throwSyntaxError('Maximum stack depth exceeded');
        }

        $this->nextOrFail('{');
        $this->white();
        while ($this->currentByte !== null) {
            if ($this->currentByte === '}') {
                $this->nextOrFail('}');
                $this->depth--;
                return $object; // Potentially empty object
            }

            // Keys can be unquoted. If they are, they need to be
            // valid JS identifiers.
            if ($this->currentByte === '"' || $this->currentByte === "'") {
                $key = $this->string();
            } else {
                $key = $this->identifier();
            }

            $this->white();
            $this->nextOrFail(':');
            if ($this->associative) {
                $object[$key] = $this->value();
            } else {
                $object->{$key} = $this->value();
            }
            $this->white();
            // If there's no comma after this pair, this needs to be
            // the end of the object.
            if ($this->currentByte !== ',') {
                $this->nextOrFail('}');
                $this->depth--;
                return $object;
            }
            $this->nextOrFail(',');
            $this->white();
        }

        $this->throwSyntaxError('Invalid object');
    }

    /**
     * Parse a JSON value.
     *
     * It could be an object, an array, a string, a number,
     * or a word.
     */
    private function value()
    {
        $this->white();
        switch ($this->currentByte) {
            case '{':
                return $this->obj();
            case '[':
                return $this->arr();
            case '"':
            case "'":
                return $this->string();
            case '-':
            case '+':
            case '.':
                return $this->number();
            default:
                return \is_numeric($this->currentByte) ? $this->number() : $this->word();
        }
    }

    private function throwSyntaxError($message)
    {
        // Calculate the column number
        $str = \substr($this->json, $this->currentLineStartsAt, $this->at - $this->currentLineStartsAt);
        $column = \mb_strlen($str) + 1;

        throw new SyntaxError($message, $this->lineNumber, $column);
    }

    private static function renderChar($chr)
    {
        return $chr === null ? 'EOF' : "'" . $chr . "'";
    }

    /**
     * @param string $ch
     *
     * @return string|null
     */
    private static function getEscapee($ch)
    {
        switch ($ch) {
            // @codingStandardsIgnoreStart
            case "'":  return "'";
            case '"':  return '"';
            case '\\': return '\\';
            case '/':  return '/';
            case "\n": return '';
            case 'b':  return \chr(8);
            case 'f':  return "\f";
            case 'n':  return "\n";
            case 'r':  return "\r";
            case 't':  return "\t";
            default:   return null;
            // @codingStandardsIgnoreEnd
        }
    }
}
