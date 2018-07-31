<?php

namespace Markdownify;

/**
 * default configuration
 */
define('MDFY_BODYWIDTH', false);
define('MDFY_KEEPHTML', true);

/**
 * HTML to Markdown converter class
 */
class Converter
{
    /**
     * html parser object
     *
     * @var parseHTML
     */
    protected $parser;

    /**
     * markdown output
     *
     * @var string
     */
    protected $output;

    /**
     * stack with tags which where not converted to html
     *
     * @var array<string>
     */
    protected $notConverted = [];

    /**
     * skip conversion to markdown
     *
     * @var bool
     */
    protected $skipConversion = false;

    /* options */

    /**
     * keep html tags which cannot be converted to markdown
     *
     * @var bool
     */
    protected $keepHTML = false;

    /**
     * wrap output, set to 0 to skip wrapping
     *
     * @var int
     */
    protected $bodyWidth = 0;

    /**
     * minimum body width
     *
     * @var int
     */
    protected $minBodyWidth = 25;

    /**
     * position where the link reference will be displayed
     *
     *
     * @var int
     */
    protected $linkPosition;
    const LINK_AFTER_CONTENT = 0;
    const LINK_AFTER_PARAGRAPH = 1;
    const LINK_IN_PARAGRAPH = 2;

    /**
     * stores current buffers
     *
     * @var array<string>
     */
    protected $buffer = [];

    /**
     * stores current buffers
     *
     * @var array<string>
     */
    protected $footnotes = [];

    /**
     * tags with elements which can be handled by markdown
     *
     * @var array<string>
     */
    protected $isMarkdownable = [
        'p' => [],
        'ul' => [],
        'ol' => [],
        'li' => [],
        'br' => [],
        'blockquote' => [],
        'code' => [],
        'pre' => [],
        'a' => [
            'href' => 'required',
            'title' => 'optional',
        ],
        'strong' => [],
        'b' => [],
        'em' => [],
        'i' => [],
        'img' => [
            'src' => 'required',
            'alt' => 'optional',
            'title' => 'optional',
        ],
        'h1' => [],
        'h2' => [],
        'h3' => [],
        'h4' => [],
        'h5' => [],
        'h6' => [],
        'hr' => [],
    ];

    /**
     * html tags to be ignored (contents will be parsed)
     *
     * @var array<string>
     */
    protected $ignore = [
        'html',
        'body',
    ];

    /**
     * html tags to be dropped (contents will not be parsed!)
     *
     * @var array<string>
     */
    protected $drop = [
        'script',
        'head',
        'style',
        'form',
        'area',
        'object',
        'param',
        'iframe',
    ];

    /**
     * html block tags that allow inline & block children
     *
     * @var array<string>
     */
    protected $allowMixedChildren = [
        'li'
    ];

    /**
     * Markdown indents which could be wrapped
     * @note: use strings in regex format
     *
     * @var array<string>
     */
    protected $wrappableIndents = [
        '\*   ', // ul
        '\d.  ', // ol
        '\d\d. ', // ol
        '> ', // blockquote
        '', // p
    ];

    /**
     * list of chars which have to be escaped in normal text
     * @note: use strings in regex format
     *
     * @var array
     *
     * TODO: what's with block chars / sequences at the beginning of a block?
     */
    protected $escapeInText = [
        '\*\*([^*]+)\*\*' => '\*\*$1\*\*', // strong
        '\*([^*]+)\*' => '\*$1\*', // em
        '__(?! |_)(.+)(?!<_| )__' => '\_\_$1\_\_', // strong
        '_(?! |_)(.+)(?!<_| )_' => '\_$1\_', // em
        '([-*_])([ ]{0,2}\1){2,}' => '\\\\$0', // hr
        '`' => '\`', // code
        '\[(.+)\](\s*\()' => '\[$1\]$2', // links: [text] (url) => [text\] (url)
        '\[(.+)\](\s*)\[(.*)\]' => '\[$1\]$2\[$3\]', // links: [text][id] => [text\][id\]
        '^#(#{0,5}) ' => '\#$1 ', // header
    ];

    /**
     * wether last processed node was a block tag or not
     *
     * @var bool
     */
    protected $lastWasBlockTag = false;

    /**
     * name of last closed tag
     *
     * @var string
     */
    protected $lastClosedTag = '';

    /**
     * number of line breaks before next inline output
     */
    protected $lineBreaks = 0;

    /**
     * node stack, e.g. for <a> and <abbr> tags
     *
     * @var array<array>
     */
    protected $stack = [];

    /**
     * current indentation
     *
     * @var string
     */
    protected $indent = '';

    /**
     * constructor, set options, setup parser
     *
     * @param int $linkPosition define the position of links
     * @param int $bodyWidth whether or not to wrap the output to the given width
     *             defaults to false
     * @param bool $keepHTML whether to keep non markdownable HTML or to discard it
     *             defaults to true (HTML will be kept)
     * @return void
     */
    public function __construct($linkPosition = self::LINK_AFTER_CONTENT, $bodyWidth = MDFY_BODYWIDTH, $keepHTML = MDFY_KEEPHTML)
    {
        $this->linkPosition = $linkPosition;
        $this->keepHTML = $keepHTML;

        if ($bodyWidth > $this->minBodyWidth) {
            $this->bodyWidth = intval($bodyWidth);
        } else {
            $this->bodyWidth = false;
        }

        $this->parser = new Parser;
        $this->parser->noTagsInCode = true;

        // we don't have to do this every time
        $search = [];
        $replace = [];
        foreach ($this->escapeInText as $s => $r) {
            array_push($search, '@(?<!\\\)' . $s . '@U');
            array_push($replace, $r);
        }
        $this->escapeInText = [
            'search' => $search,
            'replace' => $replace
        ];
    }

    /**
     * parse a HTML string
     *
     * @param string $html
     * @return string markdown formatted
     */
    public function parseString($html)
    {
        $this->resetState();

        $this->parser->html = $html;
        $this->parse();

        return $this->output;
    }

    /**
     * set the position where the link reference will be displayed
     *
     * @param int $linkPosition
     * @return void
     */
    public function setLinkPosition($linkPosition)
    {
        $this->linkPosition = $linkPosition;
    }

    /**
     * set keep HTML tags which cannot be converted to markdown
     *
     * @param bool $linkPosition
     * @return void
     */
    public function setKeepHTML($keepHTML)
    {
        $this->keepHTML = $keepHTML;
    }

    /**
     * iterate through the nodes and decide what we
     * shall do with the current node
     *
     * @param void
     * @return void
     */
    protected function parse()
    {
        $this->output = '';
        // drop tags
        $this->parser->html = preg_replace('#<(' . implode('|', $this->drop) . ')[^>]*>.*</\\1>#sU', '', $this->parser->html);
        while ($this->parser->nextNode()) {
            switch ($this->parser->nodeType) {
                case 'doctype':
                    break;
                case 'pi':
                case 'comment':
                    if ($this->keepHTML) {
                        $this->flushLinebreaks();
                        $this->out($this->parser->node);
                        $this->setLineBreaks(2);
                    }
                    // else drop
                    break;
                case 'text':
                    $this->handleText();
                    break;
                case 'tag':
                    if (in_array($this->parser->tagName, $this->ignore)) {
                        break;
                    }
                    // If the previous tag was not a block element, we simulate a paragraph tag
                    if ($this->parser->isBlockElement && $this->parser->isNextToInlineContext && !in_array($this->parent(), $this->allowMixedChildren)) {
                        $this->setLineBreaks(2);
                    }
                    if ($this->parser->isStartTag) {
                        $this->flushLinebreaks();
                    }
                    if ($this->skipConversion) {
                        $this->isMarkdownable(); // update notConverted
                        $this->handleTagToText();
                        continue;
                    }

                    // block elements
                    if (!$this->parser->keepWhitespace && $this->parser->isBlockElement) {
                        $this->fixBlockElementSpacing();
                    }

                    // inline elements
                    if (!$this->parser->keepWhitespace && $this->parser->isInlineContext) {
                        $this->fixInlineElementSpacing();
                    }

                    if ($this->isMarkdownable()) {
                        if ($this->parser->isBlockElement && $this->parser->isStartTag && !$this->lastWasBlockTag && !empty($this->output)) {
                            if (!empty($this->buffer)) {
                                $str =& $this->buffer[count($this->buffer) - 1];
                            } else {
                                $str =& $this->output;
                            }
                            if (substr($str, -strlen($this->indent) - 1) != "\n" . $this->indent) {
                                $str .= "\n" . $this->indent;
                            }
                        }
                        $func = 'handleTag_' . $this->parser->tagName;
                        $this->$func();
                        if ($this->linkPosition == self::LINK_AFTER_PARAGRAPH && $this->parser->isBlockElement && !$this->parser->isStartTag && empty($this->parser->openTags)) {
                            $this->flushFootnotes();
                        }
                        if (!$this->parser->isStartTag) {
                            $this->lastClosedTag = $this->parser->tagName;
                        }
                    } else {
                        $this->handleTagToText();
                        $this->lastClosedTag = '';
                    }
                    break;
                default:
                    trigger_error('invalid node type', E_USER_ERROR);
                    break;
            }
            $this->lastWasBlockTag = $this->parser->nodeType == 'tag' && $this->parser->isStartTag && $this->parser->isBlockElement;
        }
        if (!empty($this->buffer)) {
            // trigger_error('buffer was not flushed, this is a bug. please report!', E_USER_WARNING);
            while (!empty($this->buffer)) {
                $this->out($this->unbuffer());
            }
        }
        // cleanup
        $this->output = rtrim(str_replace('&amp;', '&', str_replace('&lt;', '<', str_replace('&gt;', '>', $this->output))));
        // end parsing, flush stacked tags
        $this->flushFootnotes();
        $this->stack = [];
    }

    /**
     * check if current tag can be converted to Markdown
     *
     * @param void
     * @return bool
     */
    protected function isMarkdownable()
    {
        if (!isset($this->isMarkdownable[$this->parser->tagName])) {
            // simply not markdownable

            return false;
        }
        if ($this->parser->isStartTag) {
            $return = true;
            if ($this->keepHTML) {
                $diff = array_diff(array_keys($this->parser->tagAttributes), array_keys($this->isMarkdownable[$this->parser->tagName]));
                if (!empty($diff)) {
                    // non markdownable attributes given
                    $return = false;
                }
            }
            if ($return) {
                foreach ($this->isMarkdownable[$this->parser->tagName] as $attr => $type) {
                    if ($type == 'required' && !isset($this->parser->tagAttributes[$attr])) {
                        // required markdown attribute not given
                        $return = false;
                        break;
                    }
                }
            }
            if (!$return) {
                array_push($this->notConverted, $this->parser->tagName . '::' . implode('/', $this->parser->openTags));
            }

            return $return;
        } else {
            if (!empty($this->notConverted) && end($this->notConverted) === $this->parser->tagName . '::' . implode('/', $this->parser->openTags)) {
                array_pop($this->notConverted);

                return false;
            }

            return true;
        }
    }

    /**
     * output footnotes
     *
     * @param void
     * @return void
     */
    protected function flushFootnotes()
    {
        $out = false;
        foreach ($this->footnotes as $k => $tag) {
            if (!isset($tag['unstacked'])) {
                if (!$out) {
                    $out = true;
                    $this->out("\n\n", true);
                } else {
                    $this->out("\n", true);
                }
                $this->out(' [' . $tag['linkID'] . ']: ' . $this->getLinkReference($tag), true);
                $tag['unstacked'] = true;
                $this->footnotes[$k] = $tag;
            }
        }
    }

    /**
     * return formated link reference
     *
     * @param array $tag
     * @return string link reference
     */
    protected function getLinkReference($tag)
    {
        return $tag['href'] . (isset($tag['title']) ? ' "' . $tag['title'] . '"' : '');
    }

    /**
     * flush enqued linebreaks
     *
     * @param void
     * @return void
     */
    protected function flushLinebreaks()
    {
        if ($this->lineBreaks && !empty($this->output)) {
            $this->out(str_repeat("\n" . $this->indent, $this->lineBreaks), true);
        }
        $this->lineBreaks = 0;
    }

    /**
     * handle non Markdownable tags
     *
     * @param void
     * @return void
     */
    protected function handleTagToText()
    {
        if (!$this->keepHTML) {
            if (!$this->parser->isStartTag && $this->parser->isBlockElement) {
                $this->setLineBreaks(2);
            }
        } else {
            // don't convert to markdown inside this tag
            /** TODO: markdown extra **/
            if (!$this->parser->isEmptyTag) {
                if ($this->parser->isStartTag) {
                    if (!$this->skipConversion) {
                        $this->skipConversion = $this->parser->tagName . '::' . implode('/', $this->parser->openTags);
                    }
                } else {
                    if ($this->skipConversion == $this->parser->tagName . '::' . implode('/', $this->parser->openTags)) {
                        $this->skipConversion = false;
                    }
                }
            }

            if ($this->parser->isBlockElement) {
                if ($this->parser->isStartTag) {
                    // looks like ins or del are block elements now
                    if (in_array($this->parent(), ['ins', 'del'])) {
                        $this->out("\n", true);
                        $this->indent('  ');
                    }
                    // don't indent inside <pre> tags
                    if ($this->parser->tagName == 'pre') {
                        $this->out($this->parser->node);
                        static $indent;
                        $indent = $this->indent;
                        $this->indent = '';
                    } else {
                        $this->out($this->parser->node . "\n" . $this->indent);
                        if (!$this->parser->isEmptyTag) {
                            $this->indent('  ');
                        } else {
                            $this->setLineBreaks(1);
                        }
                        $this->parser->html = ltrim($this->parser->html);
                    }
                } else {
                    if (!$this->parser->keepWhitespace) {
                        $this->output = rtrim($this->output);
                    }
                    if ($this->parser->tagName != 'pre') {
                        $this->indent('  ');
                        $this->out("\n" . $this->indent . $this->parser->node);
                    } else {
                        // reset indentation
                        $this->out($this->parser->node);
                        static $indent;
                        $this->indent = $indent;
                    }

                    if (in_array($this->parent(), ['ins', 'del'])) {
                        // ins or del was block element
                        $this->out("\n");
                        $this->indent('  ');
                    }
                    if ($this->parser->tagName == 'li') {
                        $this->setLineBreaks(1);
                    } else {
                        $this->setLineBreaks(2);
                    }
                }
            } else {
                $this->out($this->parser->node);
            }
            if (in_array($this->parser->tagName, ['code', 'pre'])) {
                if ($this->parser->isStartTag) {
                    $this->buffer();
                } else {
                    // add stuff so cleanup just reverses this
                    $this->out(str_replace('&lt;', '&amp;lt;', str_replace('&gt;', '&amp;gt;', $this->unbuffer())));
                }
            }
        }
    }

    /**
     * handle plain text
     *
     * @param void
     * @return void
     */
    protected function handleText()
    {
        if ($this->hasParent('pre') && strpos($this->parser->node, "\n") !== false) {
            $this->parser->node = str_replace("\n", "\n" . $this->indent, $this->parser->node);
        }
        if (!$this->hasParent('code') && !$this->hasParent('pre')) {
            // entity decode
            $this->parser->node = $this->decode($this->parser->node);
            if (!$this->skipConversion) {
                // escape some chars in normal Text
                $this->parser->node = preg_replace($this->escapeInText['search'], $this->escapeInText['replace'], $this->parser->node);
            }
        } else {
            $this->parser->node = str_replace(['&quot;', '&apos'], ['"', '\''], $this->parser->node);
        }
        $this->out($this->parser->node);
        $this->lastClosedTag = '';
    }

    /**
     * handle <em> and <i> tags
     *
     * @param void
     * @return void
     */
    protected function handleTag_em()
    {
        $this->out('_', true);
    }

    protected function handleTag_i()
    {
        $this->handleTag_em();
    }

    /**
     * handle <strong> and <b> tags
     *
     * @param void
     * @return void
     */
    protected function handleTag_strong()
    {
        $this->out('**', true);
    }

    protected function handleTag_b()
    {
        $this->handleTag_strong();
    }

    /**
     * handle <h1> tags
     *
     * @param void
     * @return void
     */
    protected function handleTag_h1()
    {
        $this->handleHeader(1);
    }

    /**
     * handle <h2> tags
     *
     * @param void
     * @return void
     */
    protected function handleTag_h2()
    {
        $this->handleHeader(2);
    }

    /**
     * handle <h3> tags
     *
     * @param void
     * @return void
     */
    protected function handleTag_h3()
    {
        $this->handleHeader(3);
    }

    /**
     * handle <h4> tags
     *
     * @param void
     * @return void
     */
    protected function handleTag_h4()
    {
        $this->handleHeader(4);
    }

    /**
     * handle <h5> tags
     *
     * @param void
     * @return void
     */
    protected function handleTag_h5()
    {
        $this->handleHeader(5);
    }

    /**
     * handle <h6> tags
     *
     * @param void
     * @return void
     */
    protected function handleTag_h6()
    {
        $this->handleHeader(6);
    }

    /**
     * handle header tags (<h1> - <h6>)
     *
     * @param int $level 1-6
     * @return void
     */
    protected function handleHeader($level)
    {
        if ($this->parser->isStartTag) {
            $this->out(str_repeat('#', $level) . ' ', true);
        } else {
            $this->setLineBreaks(2);
        }
    }

    /**
     * handle <p> tags
     *
     * @param void
     * @return void
     */
    protected function handleTag_p()
    {
        if (!$this->parser->isStartTag) {
            $this->setLineBreaks(2);
        }
    }

    /**
     * handle <a> tags
     *
     * @param void
     * @return void
     */
    protected function handleTag_a()
    {
        if ($this->parser->isStartTag) {
            $this->buffer();
            $this->handleTag_a_parser();
            $this->stack();
        } else {
            $tag = $this->unstack();
            $buffer = $this->unbuffer();
            $this->handleTag_a_converter($tag, $buffer);
            $this->out($this->handleTag_a_converter($tag, $buffer), true);
        }
    }

    /**
     * handle <a> tags parsing
     *
     * @param void
     * @return void
     */
    protected function handleTag_a_parser()
    {
        if (isset($this->parser->tagAttributes['title'])) {
            $this->parser->tagAttributes['title'] = $this->decode($this->parser->tagAttributes['title']);
        } else {
            $this->parser->tagAttributes['title'] = null;
        }
        $this->parser->tagAttributes['href'] = $this->decode(trim($this->parser->tagAttributes['href']));
    }

    /**
     * handle <a> tags conversion
     *
     * @param array $tag
     * @param string $buffer
     * @return string The markdownified link
     */
    protected function handleTag_a_converter($tag, $buffer)
    {
        if (empty($tag['href']) && empty($tag['title'])) {
            // empty links... testcase mania, who would possibly do anything like that?!
            return '[' . $buffer . ']()';
        }

        if ($buffer == $tag['href'] && empty($tag['title'])) {
            // <http://example.com>
            return '<' . $buffer . '>';
        }

        $bufferDecoded = $this->decode(trim($buffer));
        if (substr($tag['href'], 0, 7) == 'mailto:' && 'mailto:' . $bufferDecoded == $tag['href']) {
            if (is_null($tag['title'])) {
                // <mail@example.com>
                return '<' . $bufferDecoded . '>';
            }
            // [mail@example.com][1]
            // ...
            //  [1]: mailto:mail@example.com Title
            $tag['href'] = 'mailto:' . $bufferDecoded;
        }

        if ($this->linkPosition == self::LINK_IN_PARAGRAPH) {
            return '[' . $buffer . '](' . $this->getLinkReference($tag) . ')';
        }

        // [This link][id]
        foreach ($this->footnotes as $tag2) {
            if ($tag2['href'] == $tag['href'] && $tag2['title'] === $tag['title']) {
                $tag['linkID'] = $tag2['linkID'];
                break;
            }
        }
        if (!isset($tag['linkID'])) {
            $tag['linkID'] = count($this->footnotes) + 1;
            array_push($this->footnotes, $tag);
        }

        return '[' . $buffer . '][' . $tag['linkID'] . ']';
    }

    /**
     * handle <img /> tags
     *
     * @param void
     * @return void
     */
    protected function handleTag_img()
    {
        if (!$this->parser->isStartTag) {
            return; // just to be sure this is really an empty tag...
        }

        if (isset($this->parser->tagAttributes['title'])) {
            $this->parser->tagAttributes['title'] = $this->decode($this->parser->tagAttributes['title']);
        } else {
            $this->parser->tagAttributes['title'] = null;
        }
        if (isset($this->parser->tagAttributes['alt'])) {
            $this->parser->tagAttributes['alt'] = $this->decode($this->parser->tagAttributes['alt']);
        } else {
            $this->parser->tagAttributes['alt'] = null;
        }

        if (empty($this->parser->tagAttributes['src'])) {
            // support for "empty" images... dunno if this is really needed
            // but there are some test cases which do that...
            if (!empty($this->parser->tagAttributes['title'])) {
                $this->parser->tagAttributes['title'] = ' ' . $this->parser->tagAttributes['title'] . ' ';
            }
            $this->out('![' . $this->parser->tagAttributes['alt'] . '](' . $this->parser->tagAttributes['title'] . ')', true);

            return;
        } else {
            $this->parser->tagAttributes['src'] = $this->decode($this->parser->tagAttributes['src']);
        }

        $out = '![' . $this->parser->tagAttributes['alt'] . ']';
        if ($this->linkPosition == self::LINK_IN_PARAGRAPH) {
            $out .= '(' . $this->parser->tagAttributes['src'];
            if ($this->parser->tagAttributes['title']) {
                $out .= ' "' . $this->parser->tagAttributes['title'] . '"';
            }
            $out .= ')';
            $this->out($out, true);
            return;
        }

        // ![This image][id]
        $link_id = false;
        if (!empty($this->footnotes)) {
            foreach ($this->footnotes as $tag) {
                if ($tag['href'] == $this->parser->tagAttributes['src']
                    && $tag['title'] === $this->parser->tagAttributes['title']
                ) {
                    $link_id = $tag['linkID'];
                    break;
                }
            }
        }
        if (!$link_id) {
            $link_id = count($this->footnotes) + 1;
            $tag = [
                'href' => $this->parser->tagAttributes['src'],
                'linkID' => $link_id,
                'title' => $this->parser->tagAttributes['title']
            ];
            array_push($this->footnotes, $tag);
        }
        $out .= '[' . $link_id . ']';

        $this->out($out, true);
    }

    /**
     * handle <code> tags
     *
     * @param void
     * @return void
     */
    protected function handleTag_code()
    {
        if ($this->hasParent('pre')) {
            // ignore code blocks inside <pre>

            return;
        }
        if ($this->parser->isStartTag) {
            $this->buffer();
        } else {
            $buffer = $this->unbuffer();
            // use as many backticks as needed
            preg_match_all('#`+#', $buffer, $matches);
            if (!empty($matches[0])) {
                rsort($matches[0]);

                $ticks = '`';
                while (true) {
                    if (!in_array($ticks, $matches[0])) {
                        break;
                    }
                    $ticks .= '`';
                }
            } else {
                $ticks = '`';
            }
            if ($buffer[0] == '`' || substr($buffer, -1) == '`') {
                $buffer = ' ' . $buffer . ' ';
            }
            $this->out($ticks . $buffer . $ticks, true);
        }
    }

    /**
     * handle <pre> tags
     *
     * @param void
     * @return void
     */
    protected function handleTag_pre()
    {
        if ($this->keepHTML && $this->parser->isStartTag) {
            // check if a simple <code> follows
            if (!preg_match('#^\s*<code\s*>#Us', $this->parser->html)) {
                // this is no standard markdown code block
                $this->handleTagToText();

                return;
            }
        }
        $this->indent('    ');
        if (!$this->parser->isStartTag) {
            $this->setLineBreaks(2);
        } else {
            $this->parser->html = ltrim($this->parser->html);
        }
    }

    /**
     * handle <blockquote> tags
     *
     * @param void
     * @return void
     */
    protected function handleTag_blockquote()
    {
        $this->indent('> ');
    }

    /**
     * handle <ul> tags
     *
     * @param void
     * @return void
     */
    protected function handleTag_ul()
    {
        if ($this->parser->isStartTag) {
            $this->stack();
            if (!$this->keepHTML && $this->lastClosedTag == $this->parser->tagName) {
                $this->out("\n" . $this->indent . '<!-- -->' . "\n" . $this->indent . "\n" . $this->indent);
            }
        } else {
            $this->unstack();
            if ($this->parent() != 'li' || preg_match('#^\s*(</li\s*>\s*<li\s*>\s*)?<(p|blockquote)\s*>#sU', $this->parser->html)) {
                // don't make Markdown add unneeded paragraphs
                $this->setLineBreaks(2);
            }
        }
    }

    /**
     * handle <ol> tags
     *
     * @param void
     * @return void
     */
    protected function handleTag_ol()
    {
        // same as above
        $this->parser->tagAttributes['num'] = 0;
        $this->handleTag_ul();
    }

    /**
     * handle <li> tags
     *
     * @param void
     * @return void
     */
    protected function handleTag_li()
    {
        if ($this->parent() == 'ol') {
            $parent =& $this->getStacked('ol');
            if ($this->parser->isStartTag) {
                $parent['num']++;
                $this->out(str_repeat(' ', 3 - strlen($parent['num'])) . $parent['num'] . '. ', true);
            }
        } else {
            if ($this->parser->isStartTag) {
                $this->out('  * ', true);
            }
        }
        $this->indent('    ', false);
        if (!$this->parser->isStartTag) {
            $this->setLineBreaks(1);
        }
    }

    /**
     * handle <hr /> tags
     *
     * @param void
     * @return void
     */
    protected function handleTag_hr()
    {
        if (!$this->parser->isStartTag) {
            return; // just to be sure this really is an empty tag
        }
        $this->out('* * *', true);
        $this->setLineBreaks(2);
    }

    /**
     * handle <br /> tags
     *
     * @param void
     * @return void
     */
    protected function handleTag_br()
    {
        $this->out("  \n" . $this->indent, true);
        $this->parser->html = ltrim($this->parser->html);
    }

    /**
     * add current node to the stack
     * this only stores the attributes
     *
     * @param void
     * @return void
     */
    protected function stack()
    {
        if (!isset($this->stack[$this->parser->tagName])) {
            $this->stack[$this->parser->tagName] = [];
        }
        array_push($this->stack[$this->parser->tagName], $this->parser->tagAttributes);
    }

    /**
     * remove current tag from stack
     *
     * @param void
     * @return array
     */
    protected function unstack()
    {
        if (!isset($this->stack[$this->parser->tagName]) || !is_array($this->stack[$this->parser->tagName])) {
            trigger_error('Trying to unstack from empty stack. This must not happen.', E_USER_ERROR);
        }

        return array_pop($this->stack[$this->parser->tagName]);
    }

    /**
     * get last stacked element of type $tagName
     *
     * @param string $tagName
     * @return array
     */
    protected function &getStacked($tagName)
    {
        // no end() so it can be referenced
        return $this->stack[$tagName][count($this->stack[$tagName]) - 1];
    }

    /**
     * set number of line breaks before next start tag
     *
     * @param int $number
     * @return void
     */
    protected function setLineBreaks($number)
    {
        if ($this->lineBreaks < $number) {
            $this->lineBreaks = $number;
        }
    }

    /**
     * buffer next parser output until unbuffer() is called
     *
     * @param void
     * @return void
     */
    protected function buffer()
    {
        array_push($this->buffer, '');
    }

    /**
     * end current buffer and return buffered output
     *
     * @param void
     * @return string
     */
    protected function unbuffer()
    {
        return array_pop($this->buffer);
    }

    /**
     * append string to the correct var, either
     * directly to $this->output or to the current
     * buffers
     *
     * @param string $put
     * @param boolean $nowrap
     * @return void
     */
    protected function out($put, $nowrap = false)
    {
        if (empty($put)) {
            return;
        }
        if (!empty($this->buffer)) {
            $this->buffer[count($this->buffer) - 1] .= $put;
        } else {
            if ($this->bodyWidth && !$this->parser->keepWhitespace) { // wrap lines
                // get last line
                $pos = strrpos($this->output, "\n");
                if ($pos === false) {
                    $line = $this->output;
                } else {
                    $line = substr($this->output, $pos);
                }

                if ($nowrap) {
                    if ($put[0] != "\n" && $this->strlen($line) + $this->strlen($put) > $this->bodyWidth) {
                        $this->output .= "\n" . $this->indent . $put;
                    } else {
                        $this->output .= $put;
                    }

                    return;
                } else {
                    $put .= "\n"; // make sure we get all lines in the while below
                    $lineLen = $this->strlen($line);
                    while ($pos = strpos($put, "\n")) {
                        $putLine = substr($put, 0, $pos + 1);
                        $put = substr($put, $pos + 1);
                        $putLen = $this->strlen($putLine);
                        if ($lineLen + $putLen < $this->bodyWidth) {
                            $this->output .= $putLine;
                            $lineLen = $putLen;
                        } else {
                            $split = preg_split('#^(.{0,' . ($this->bodyWidth - $lineLen) . '})\b#', $putLine, 2, PREG_SPLIT_OFFSET_CAPTURE | PREG_SPLIT_DELIM_CAPTURE);
                            $this->output .= rtrim($split[1][0]) . "\n" . $this->indent . $this->wordwrap(ltrim($split[2][0]), $this->bodyWidth, "\n" . $this->indent, false);
                        }
                    }
                    $this->output = substr($this->output, 0, -1);

                    return;
                }
            } else {
                $this->output .= $put;
            }
        }
    }

    /**
     * indent next output (start tag) or unindent (end tag)
     *
     * @param string $str indentation
     * @param bool $output add indendation to output
     * @return void
     */
    protected function indent($str, $output = true)
    {
        if ($this->parser->isStartTag) {
            $this->indent .= $str;
            if ($output) {
                $this->out($str, true);
            }
        } else {
            $this->indent = substr($this->indent, 0, -strlen($str));
        }
    }

    /**
     * decode email addresses
     *
     * @author derernst@gmx.ch <http://www.php.net/manual/en/function.html-entity-decode.php#68536>
     * @author Milian Wolff <http://milianw.de>
     */
    protected function decode($text, $quote_style = ENT_QUOTES)
    {
        return htmlspecialchars_decode($text, $quote_style);
    }

    /**
     * callback for decode() which converts a hexadecimal entity to UTF-8
     *
     * @param array $matches
     * @return string UTF-8 encoded
     */
    protected function _decode_hex($matches)
    {
        return $this->unichr(hexdec($matches[1]));
    }

    /**
     * callback for decode() which converts a numerical entity to UTF-8
     *
     * @param array $matches
     * @return string UTF-8 encoded
     */
    protected function _decode_numeric($matches)
    {
        return $this->unichr($matches[1]);
    }

    /**
     * UTF-8 chr() which supports numeric entities
     *
     * @author grey - greywyvern - com <http://www.php.net/manual/en/function.chr.php#55978>
     * @param array $matches
     * @return string UTF-8 encoded
     */
    protected function unichr($dec)
    {
        if ($dec < 128) {
            $utf = chr($dec);
        } elseif ($dec < 2048) {
            $utf = chr(192 + (($dec - ($dec % 64)) / 64));
            $utf .= chr(128 + ($dec % 64));
        } else {
            $utf = chr(224 + (($dec - ($dec % 4096)) / 4096));
            $utf .= chr(128 + ((($dec % 4096) - ($dec % 64)) / 64));
            $utf .= chr(128 + ($dec % 64));
        }

        return $utf;
    }

    /**
     * UTF-8 strlen()
     *
     * @param string $str
     * @return int
     *
     * @author dtorop 932 at hotmail dot com <http://www.php.net/manual/en/function.strlen.php#37975>
     * @author Milian Wolff <http://milianw.de>
     */
    protected function strlen($str)
    {
        if (function_exists('mb_strlen')) {
            return mb_strlen($str, 'UTF-8');
        } else {
            return preg_match_all('/[\x00-\x7F\xC0-\xFD]/', $str, $var_empty);
        }
    }

    /**
     * wordwrap for utf8 encoded strings
     *
     * @param string $str
     * @param integer $len
     * @param string $what
     * @return string
     */
    protected function wordwrap($str, $width, $break, $cut = false)
    {
        if (!$cut) {
            $regexp = '#^(?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){1,' . $width . '}\b#';
        } else {
            $regexp = '#^(?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){' . $width . '}#';
        }
        $return = '';
        while (preg_match($regexp, $str, $matches)) {
            $string = $matches[0];
            $str = ltrim(substr($str, strlen($string)));
            if (!$cut && isset($str[0]) && in_array($str[0], ['.', '!', ';', ':', '?', ','])) {
                $string .= $str[0];
                $str = ltrim(substr($str, 1));
            }
            $return .= $string . $break;
        }

        return $return . ltrim($str);
    }

    /**
     * check if current node has a $tagName as parent (somewhere, not only the direct parent)
     *
     * @param string $tagName
     * @return bool
     */
    protected function hasParent($tagName)
    {
        return in_array($tagName, $this->parser->openTags);
    }

    /**
     * get tagName of direct parent tag
     *
     * @param void
     * @return string $tagName
     */
    protected function parent()
    {
        return end($this->parser->openTags);
    }

    /**
     * Trims whitespace in block-level elements, on the left side.
     */
    protected function fixBlockElementSpacing()
    {
        if ($this->parser->isStartTag) {
            $this->parser->html = ltrim($this->parser->html);
        }
    }

    /**
     * Moves leading/trailing whitespace from inline elements outside of the
     * element. This is to fix cases like `<strong> Text</strong>`, which if
     * converted to `** strong**` would be incorrect Markdown.
     *
     * Examples:
     *
     *   * leading: `<strong> Text</strong>` becomes ` <strong>Text</strong>`
     *   * trailing: `<strong>Text </strong>` becomes `<strong>Text</strong> `
     */
    protected function fixInlineElementSpacing()
    {
        if ($this->parser->isStartTag && !$this->parser->isEmptyTag) {
            // move spaces after the start element to before the element
            if (preg_match('~^(\s+)~', $this->parser->html, $matches)) {
                $this->out($matches[1]);
                $this->parser->html = ltrim($this->parser->html, " \t\0\x0B");
            }
        } else {
            if (!empty($this->buffer)) {
                $str =& $this->buffer[count($this->buffer) - 1];
            } else {
                $str =& $this->output;
            }

            // move spaces before the end element to after the element
            if (preg_match('~(\s+)$~', $str, $matches)) {
                $str = rtrim($str, " \t\0\x0B");
                $this->parser->html = $matches[1] . $this->parser->html;
            }
        }
    }

    /**
     * Resetting the state forces the instance to behave as a fresh instance.
     * Ideal for running within a loop where you want to maintain a single instance.
     */
    protected function resetState()
    {
        $this->notConverted = [];
        $this->skipConversion = false;
        $this->buffer = [];
        $this->indent = '';
        $this->stack = [];
        $this->lineBreaks = 0;
        $this->lastClosedTag = '';
        $this->lastWasBlockTag = false;
        $this->footnotes = [];
    }
}
