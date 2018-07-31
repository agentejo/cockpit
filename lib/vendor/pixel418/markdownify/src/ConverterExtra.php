<?php

namespace Markdownify;

class ConverterExtra extends Converter
{

    /**
     * table data, including rows with content and the maximum width of each col
     *
     * @var array
     */
    protected $table = [];

    /**
     * current col
     *
     * @var int
     */
    protected $col = -1;

    /**
     * current row
     *
     * @var int
     */
    protected $row = 0;

    /**
     * constructor, see Markdownify::Markdownify() for more information
     */
    public function __construct($linksAfterEachParagraph = self::LINK_AFTER_CONTENT, $bodyWidth = MDFY_BODYWIDTH, $keepHTML = MDFY_KEEPHTML)
    {
        parent::__construct($linksAfterEachParagraph, $bodyWidth, $keepHTML);

        // new markdownable tags & attributes
        // header ids: # foo {bar}
        $this->isMarkdownable['h1']['id'] = 'optional';
        $this->isMarkdownable['h1']['class'] = 'optional';
        $this->isMarkdownable['h2']['id'] = 'optional';
        $this->isMarkdownable['h2']['class'] = 'optional';
        $this->isMarkdownable['h3']['id'] = 'optional';
        $this->isMarkdownable['h3']['class'] = 'optional';
        $this->isMarkdownable['h4']['id'] = 'optional';
        $this->isMarkdownable['h4']['class'] = 'optional';
        $this->isMarkdownable['h5']['id'] = 'optional';
        $this->isMarkdownable['h5']['class'] = 'optional';
        $this->isMarkdownable['h6']['id'] = 'optional';
        $this->isMarkdownable['h6']['class'] = 'optional';
        // tables
        $this->isMarkdownable['table'] = [];
        $this->isMarkdownable['th'] = [
            'align' => 'optional',
        ];
        $this->isMarkdownable['td'] = [
            'align' => 'optional',
        ];
        $this->isMarkdownable['tr'] = [];
        array_push($this->ignore, 'thead');
        array_push($this->ignore, 'tbody');
        array_push($this->ignore, 'tfoot');
        // definition lists
        $this->isMarkdownable['dl'] = [];
        $this->isMarkdownable['dd'] = [];
        $this->isMarkdownable['dt'] = [];
        // link class
        $this->isMarkdownable['a']['id'] = 'optional';
        $this->isMarkdownable['a']['class'] = 'optional';
        // footnotes
        $this->isMarkdownable['fnref'] = [
            'target' => 'required',
        ];
        $this->isMarkdownable['footnotes'] = [];
        $this->isMarkdownable['fn'] = [
            'name' => 'required',
        ];
        $this->parser->blockElements['fnref'] = false;
        $this->parser->blockElements['fn'] = true;
        $this->parser->blockElements['footnotes'] = true;
        // abbr
        $this->isMarkdownable['abbr'] = [
            'title' => 'required',
        ];
        // build RegEx lookahead to decide wether table can pe parsed or not
        $inlineTags = array_keys($this->parser->blockElements, false);
        $colContents = '(?:[^<]|<(?:' . implode('|', $inlineTags) . '|[^a-z]))*';
        $this->tableLookaheadHeader = '{
    ^\s*(?:<thead\s*>)?\s*                                  # open optional thead
      <tr\s*>\s*(?:                                         # start required row with headers
        <th(?:\s+align=("|\')(?:left|center|right)\1)?\s*>  # header with optional align
        \s*' . $colContents . '\s*                              # contents
        </th>\s*                                            # close header
      )+</tr>                                               # close row with headers
    \s*(?:</thead>)?                                        # close optional thead
    }sxi';
        $this->tdSubstitute = '\s*' . $colContents . '\s*           # contents
          </td>\s*';
        $this->tableLookaheadBody = '{
      \s*(?:<tbody\s*>)?\s*                                 # open optional tbody
        (?:<tr\s*>\s*                                       # start row
          %s                                                # cols to be substituted
        </tr>)+                                             # close row
      \s*(?:</tbody>)?                                      # close optional tbody
    \s*</table>                                             # close table
    }sxi';
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
            $this->parser->tagAttributes['cssSelector'] = $this->getCurrentCssSelector();
            $this->stack();
        } else {
            $tag = $this->unstack();
            if (!empty($tag['cssSelector'])) {
                // {#id.class}
                $this->out(' {' . $tag['cssSelector'] . '}');
            }
        }
        parent::handleHeader($level);
    }

    /**
     * handle <a> tags parsing
     *
     * @param void
     * @return void
     */
    protected function handleTag_a_parser()
    {
        parent::handleTag_a_parser();
        $this->parser->tagAttributes['cssSelector'] = $this->getCurrentCssSelector();
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
        $output = parent::handleTag_a_converter($tag, $buffer);
        if (!empty($tag['cssSelector'])) {
            // [This link][id]{#id.class}
            $output .= '{' . $tag['cssSelector'] . '}';
        }

        return $output;
    }

    /**
     * handle <abbr> tags
     *
     * @param void
     * @return void
     */
    protected function handleTag_abbr()
    {
        if ($this->parser->isStartTag) {
            $this->stack();
            $this->buffer();
        } else {
            $tag = $this->unstack();
            $tag['text'] = $this->unbuffer();
            $add = true;
            foreach ($this->stack['abbr'] as $stacked) {
                if ($stacked['text'] == $tag['text']) {
                    /** TODO: differing abbr definitions, i.e. different titles for same text **/
                    $add = false;
                    break;
                }
            }
            $this->out($tag['text']);
            if ($add) {
                array_push($this->stack['abbr'], $tag);
            }
        }
    }

    /**
     * flush stacked abbr tags
     *
     * @param void
     * @return void
     */
    protected function flushStacked_abbr()
    {
        $out = [];
        foreach ($this->stack['abbr'] as $k => $tag) {
            if (!isset($tag['unstacked'])) {
                array_push($out, ' *[' . $tag['text'] . ']: ' . $tag['title']);
                $tag['unstacked'] = true;
                $this->stack['abbr'][$k] = $tag;
            }
        }
        if (!empty($out)) {
            $this->out("\n\n" . implode("\n", $out));
        }
    }

    /**
     * handle <table> tags
     *
     * @param void
     * @return void
     */
    protected function handleTag_table()
    {
        if ($this->parser->isStartTag) {
            // check if upcoming table can be converted
            if ($this->keepHTML) {
                if (preg_match($this->tableLookaheadHeader, $this->parser->html, $matches)) {
                    // header seems good, now check body
                    // get align & number of cols
                    preg_match_all('#<th(?:\s+align=("|\')(left|right|center)\1)?\s*>#si', $matches[0], $cols);
                    $regEx = '';
                    $i = 1;
                    $aligns = [];
                    foreach ($cols[2] as $align) {
                        $align = strtolower($align);
                        array_push($aligns, $align);
                        if (empty($align)) {
                            $align = 'left'; // default value
                        }
                        $td = '\s+align=("|\')' . $align . '\\' . $i;
                        $i++;
                        if ($align == 'left') {
                            // look for empty align or left
                            $td = '(?:' . $td . ')?';
                        }
                        $td = '<td' . $td . '\s*>';
                        $regEx .= $td . $this->tdSubstitute;
                    }
                    $regEx = sprintf($this->tableLookaheadBody, $regEx);
                    if (preg_match($regEx, $this->parser->html, $matches, null, strlen($matches[0]))) {
                        // this is a markdownable table tag!
                        $this->table = [
                            'rows' => [],
                            'col_widths' => [],
                            'aligns' => $aligns,
                        ];
                        $this->row = 0;
                    } else {
                        // non markdownable table
                        $this->handleTagToText();
                    }
                } else {
                    // non markdownable table
                    $this->handleTagToText();
                }
            } else {
                $this->table = [
                    'rows' => [],
                    'col_widths' => [],
                    'aligns' => [],
                ];
                $this->row = 0;
            }
        } else {
            // finally build the table in Markdown Extra syntax
            $separator = [];
            if (!isset($this->table['aligns'])) {
                $this->table['aligns'] = [];
            }
            // seperator with correct align identifiers
            foreach ($this->table['aligns'] as $col => $align) {
                if (!$this->keepHTML && !isset($this->table['col_widths'][$col])) {
                    break;
                }
                $left = ' ';
                $right = ' ';
                switch ($align) {
                    case 'left':
                        $left = ':';
                        break;
                    case 'center':
                        $right = ':';
                        $left = ':';
                    case 'right':
                        $right = ':';
                        break;
                }
                array_push($separator, $left . str_repeat('-', $this->table['col_widths'][$col]) . $right);
            }
            $separator = '|' . implode('|', $separator) . '|';

            $rows = [];
            // add padding
            array_walk_recursive($this->table['rows'], [&$this, 'alignTdContent']);
            $header = array_shift($this->table['rows']);
            array_push($rows, '| ' . implode(' | ', $header) . ' |');
            array_push($rows, $separator);
            foreach ($this->table['rows'] as $row) {
                array_push($rows, '| ' . implode(' | ', $row) . ' |');
            }
            $this->out(implode("\n" . $this->indent, $rows));
            $this->table = [];
            $this->setLineBreaks(2);
        }
    }

    /**
     * properly pad content so it is aligned as whished
     * should be used with array_walk_recursive on $this->table['rows']
     *
     * @param string &$content
     * @param int $col
     * @return void
     */
    protected function alignTdContent(&$content, $col)
    {
        if (!isset($this->table['aligns'][$col])) {
            $this->table['aligns'][$col] = 'left';
        }
        switch ($this->table['aligns'][$col]) {
            default:
            case 'left':
                $content .= str_repeat(' ', $this->table['col_widths'][$col] - $this->strlen($content));
                break;
            case 'right':
                $content = str_repeat(' ', $this->table['col_widths'][$col] - $this->strlen($content)) . $content;
                break;
            case 'center':
                $paddingNeeded = $this->table['col_widths'][$col] - $this->strlen($content);
                $left = floor($paddingNeeded / 2);
                $right = $paddingNeeded - $left;
                $content = str_repeat(' ', $left) . $content . str_repeat(' ', $right);
                break;
        }
    }

    /**
     * handle <tr> tags
     *
     * @param void
     * @return void
     */
    protected function handleTag_tr()
    {
        if ($this->parser->isStartTag) {
            $this->col = -1;
        } else {
            $this->row++;
        }
    }

    /**
     * handle <td> tags
     *
     * @param void
     * @return void
     */
    protected function handleTag_td()
    {
        if ($this->parser->isStartTag) {
            $this->col++;
            if (!isset($this->table['col_widths'][$this->col])) {
                $this->table['col_widths'][$this->col] = 0;
            }
            $this->buffer();
        } else {
            $buffer = trim($this->unbuffer());
            if (!isset($this->table['col_widths'][$this->col])) {
                $this->table['col_widths'][$this->col] = 0;
            }
            $this->table['col_widths'][$this->col] = max($this->table['col_widths'][$this->col], $this->strlen($buffer));
            $this->table['rows'][$this->row][$this->col] = $buffer;
        }
    }

    /**
     * handle <th> tags
     *
     * @param void
     * @return void
     */
    protected function handleTag_th()
    {
        if (!$this->keepHTML && !isset($this->table['rows'][1]) && !isset($this->table['aligns'][$this->col + 1])) {
            if (isset($this->parser->tagAttributes['align'])) {
                $this->table['aligns'][$this->col + 1] = $this->parser->tagAttributes['align'];
            } else {
                $this->table['aligns'][$this->col + 1] = '';
            }
        }
        $this->handleTag_td();
    }

    /**
     * handle <dl> tags
     *
     * @param void
     * @return void
     */
    protected function handleTag_dl()
    {
        if (!$this->parser->isStartTag) {
            $this->setLineBreaks(2);
        }
    }

    /**
     * handle <dt> tags
     *
     * @param void
     * @return void
     **/
    protected function handleTag_dt()
    {
        if (!$this->parser->isStartTag) {
            $this->setLineBreaks(1);
        }
    }

    /**
     * handle <dd> tags
     *
     * @param void
     * @return void
     */
    protected function handleTag_dd()
    {
        if ($this->parser->isStartTag) {
            if (substr(ltrim($this->parser->html), 0, 3) == '<p>') {
                // next comes a paragraph, so we'll need an extra line
                $this->out("\n" . $this->indent);
            } elseif (substr($this->output, -2) == "\n\n") {
                $this->output = substr($this->output, 0, -1);
            }
            $this->out(':   ');
            $this->indent('    ', false);
        } else {
            // lookahead for next dt
            if (substr(ltrim($this->parser->html), 0, 4) == '<dt>') {
                $this->setLineBreaks(2);
            } else {
                $this->setLineBreaks(1);
            }
            $this->indent('    ');
        }
    }

    /**
     * handle <fnref /> tags (custom footnote references, see markdownify_extra::parseString())
     *
     * @param void
     * @return void
     */
    protected function handleTag_fnref()
    {
        $this->out('[^' . $this->parser->tagAttributes['target'] . ']');
    }

    /**
     * handle <fn> tags (custom footnotes, see markdownify_extra::parseString()
     * and markdownify_extra::_makeFootnotes())
     *
     * @param void
     * @return void
     */
    protected function handleTag_fn()
    {
        if ($this->parser->isStartTag) {
            $this->out('[^' . $this->parser->tagAttributes['name'] . ']:');
            $this->setLineBreaks(1);
        } else {
            $this->setLineBreaks(2);
        }
        $this->indent('    ');
    }

    /**
     * handle <footnotes> tag (custom footnotes, see markdownify_extra::parseString()
     *  and markdownify_extra::_makeFootnotes())
     *
     * @param void
     * @return void
     */
    protected function handleTag_footnotes()
    {
        if (!$this->parser->isStartTag) {
            $this->setLineBreaks(2);
        }
    }

    /**
     * parse a HTML string, clean up footnotes prior
     *
     * @param string $HTML input
     * @return string Markdown formatted output
     */
    public function parseString($html)
    {
        /** TODO: custom markdown-extra options, e.g. titles & classes **/
        // <sup id="fnref:..."><a href"#fn..." rel="footnote">...</a></sup>
        // => <fnref target="..." />
        $html = preg_replace('@<sup id="fnref:([^"]+)">\s*<a href="#fn:\1" rel="footnote">\s*\d+\s*</a>\s*</sup>@Us', '<fnref target="$1" />', $html);
        // <div class="footnotes">
        // <hr />
        // <ol>
        //
        // <li id="fn:...">...</li>
        // ...
        //
        // </ol>
        // </div>
        // =>
        // <footnotes>
        //   <fn name="...">...</fn>
        //   ...
        // </footnotes>
        $html = preg_replace_callback('#<div class="footnotes">\s*<hr />\s*<ol>\s*(.+)\s*</ol>\s*</div>#Us', [&$this, '_makeFootnotes'], $html);

        return parent::parseString($html);
    }

    /**
     * replace HTML representation of footnotes with something more easily parsable
     *
     * @note this is a callback to be used in parseString()
     *
     * @param array $matches
     * @return string
     */
    protected function _makeFootnotes($matches)
    {
        // <li id="fn:1">
        //   ...
        //   <a href="#fnref:block" rev="footnote">&#8617;</a></p>
        // </li>
        // => <fn name="1">...</fn>
        // remove footnote link
        $fns = preg_replace('@\s*(&#160;\s*)?<a href="#fnref:[^"]+" rev="footnote"[^>]*>&#8617;</a>\s*@s', '', $matches[1]);
        // remove empty paragraph
        $fns = preg_replace('@<p>\s*</p>@s', '', $fns);
        // <li id="fn:1">...</li> -> <footnote nr="1">...</footnote>
        $fns = str_replace('<li id="fn:', '<fn name="', $fns);

        $fns = '<footnotes>' . $fns . '</footnotes>';

        return preg_replace('#</li>\s*(?=(?:<fn|</footnotes>))#s', '</fn>$1', $fns);
    }

    /**
     * handle <a> tags parsing
     *
     * @param void
     * @return void
     */
    protected function getCurrentCssSelector()
    {
        $cssSelector = '';
        if (isset($this->parser->tagAttributes['id'])) {
            $cssSelector .= '#' . $this->decode($this->parser->tagAttributes['id']);
        }
        if (isset($this->parser->tagAttributes['class'])) {
            $classes = explode(' ', $this->decode($this->parser->tagAttributes['class']));
            $classes = array_filter($classes);
            $cssSelector .= '.' . join('.', $classes);
        }
        return $cssSelector;
    }
}
