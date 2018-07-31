<?php

namespace Markdownify;

class Parser
{
    public static $skipWhitespace = true;
    public static $a_ord;
    public static $z_ord;
    public static $special_ords;

    /**
     * tags which are always empty (<br /> etc.)
     *
     * @var array<string>
     */
    public $emptyTags = [
        'br',
        'hr',
        'input',
        'img',
        'area',
        'link',
        'meta',
        'param',
    ];

    /**
     * tags with preformatted text
     * whitespaces wont be touched in them
     *
     * @var array<string>
     */
    public $preformattedTags = [
        'script',
        'style',
        'pre',
        'code',
    ];

    /**
     * supress HTML tags inside preformatted tags (see above)
     *
     * @var bool
     */
    public $noTagsInCode = false;

    /**
     * html to be parsed
     *
     * @var string
     */
    public $html = '';

    /**
     * node type:
     *
     * - tag (see isStartTag)
     * - text (includes cdata)
     * - comment
     * - doctype
     * - pi (processing instruction)
     *
     * @var string
     */
    public $nodeType = '';

    /**
     * current node content, i.e. either a
     * simple string (text node), or something like
     * <tag attrib="value"...>
     *
     * @var string
     */
    public $node = '';

    /**
     * wether current node is an opening tag (<a>) or not (</a>)
     * set to NULL if current node is not a tag
     * NOTE: empty tags (<br />) set this to true as well!
     *
     * @var bool | null
     */
    public $isStartTag = null;

    /**
     * wether current node is an empty tag (<br />) or not (<a></a>)
     *
     * @var bool | null
     */
    public $isEmptyTag = null;

    /**
     * tag name
     *
     * @var string | null
     */
    public $tagName = '';

    /**
     * attributes of current tag
     *
     * @var array (attribName=>value) | null
     */
    public $tagAttributes = null;

    /**
     * whether or not the actual context is a inline context
     *
     * @var bool | null
     */
    public $isInlineContext = null;

    /**
     * whether the current tag is a block element
     *
     * @var bool | null
     */
    public $isBlockElement = null;

    /**
     * whether the previous tag (browser) is a block element
     *
     * @var bool | null
     */
    public $isNextToInlineContext = null;

    /**
     * keep whitespace
     *
     * @var int
     */
    public $keepWhitespace = 0;

    /**
     * list of open tags
     * count this to get current depth
     *
     * @var array
     */
    public $openTags = [];

    /**
     * list of block elements
     *
     * @var array
     * TODO: what shall we do with <del> and <ins> ?!
     */
    public $blockElements = [
        // tag name => <bool> is block
        // block elements
        'address' => true,
        'aside' => true,
        'blockquote' => true,
        'center' => true,
        'del' => true,
        'dir' => true,
        'div' => true,
        'dl' => true,
        'fieldset' => true,
        'form' => true,
        'h1' => true,
        'h2' => true,
        'h3' => true,
        'h4' => true,
        'h5' => true,
        'h6' => true,
        'hr' => true,
        'ins' => true,
        'isindex' => true,
        'menu' => true,
        'noframes' => true,
        'noscript' => true,
        'ol' => true,
        'p' => true,
        'pre' => true,
        'table' => true,
        'ul' => true,
        // set table elements and list items to block as well
        'thead' => true,
        'tbody' => true,
        'tfoot' => true,
        'td' => true,
        'tr' => true,
        'th' => true,
        'li' => true,
        'dd' => true,
        'dt' => true,
        // header items and html / body as well
        'html' => true,
        'body' => true,
        'head' => true,
        'meta' => true,
        'link' => true,
        'style' => true,
        'title' => true,
        // unfancy media tags, when indented should be rendered as block
        'map' => true,
        'object' => true,
        'param' => true,
        'embed' => true,
        'area' => true,
        // inline elements
        'a' => false,
        'abbr' => false,
        'acronym' => false,
        'applet' => false,
        'b' => false,
        'basefont' => false,
        'bdo' => false,
        'big' => false,
        'br' => false,
        'button' => false,
        'cite' => false,
        'code' => false,
        'del' => false,
        'dfn' => false,
        'em' => false,
        'font' => false,
        'i' => false,
        'img' => false,
        'ins' => false,
        'input' => false,
        'iframe' => false,
        'kbd' => false,
        'label' => false,
        'q' => false,
        'samp' => false,
        'script' => false,
        'select' => false,
        'small' => false,
        'span' => false,
        'strong' => false,
        'sub' => false,
        'sup' => false,
        'textarea' => false,
        'tt' => false,
        'u' => false,
        'var' => false,
    ];

    /**
     * get next node, set $this->html prior!
     *
     * @param void
     * @return bool
     */
    public function nextNode()
    {
        if (empty($this->html)) {
            // we are done with parsing the html string

            return false;
        }

        if ($this->isStartTag && !$this->isEmptyTag) {
            array_push($this->openTags, $this->tagName);
            if (in_array($this->tagName, $this->preformattedTags)) {
                // don't truncate whitespaces for <code> or <pre> contents
                $this->keepWhitespace++;
            }
        }

        if ($this->html[0] == '<') {
            $token = substr($this->html, 0, 9);
            if (substr($token, 0, 2) == '<?') {
                // xml prolog or other pi's
                /** TODO **/
                // trigger_error('this might need some work', E_USER_NOTICE);
                $pos = strpos($this->html, '>');
                $this->setNode('pi', $pos + 1);

                return true;
            }
            if (substr($token, 0, 4) == '<!--') {
                // comment
                $pos = strpos($this->html, '-->');
                if ($pos === false) {
                    // could not find a closing -->, use next gt instead
                    // this is firefox' behaviour
                    $pos = strpos($this->html, '>') + 1;
                } else {
                    $pos += 3;
                }
                $this->setNode('comment', $pos);

                static::$skipWhitespace = true;

                return true;
            }
            if ($token == '<!DOCTYPE') {
                // doctype
                $this->setNode('doctype', strpos($this->html, '>') + 1);

                static::$skipWhitespace = true;

                return true;
            }
            if ($token == '<![CDATA[') {
                // cdata, use text node

                // remove leading <![CDATA[
                $this->html = substr($this->html, 9);

                $this->setNode('text', strpos($this->html, ']]>') + 3);

                // remove trailing ]]> and trim
                $this->node = substr($this->node, 0, -3);
                $this->handleWhitespaces();

                static::$skipWhitespace = true;

                return true;
            }
            if ($this->parseTag()) {
                // seems to be a tag
                // handle whitespaces
                if ($this->isBlockElement) {
                    static::$skipWhitespace = true;
                } else {
                    static::$skipWhitespace = false;
                }

                return true;
            }
        }
        if ($this->keepWhitespace) {
            static::$skipWhitespace = false;
        }
        // when we get here it seems to be a text node
        $pos = strpos($this->html, '<');
        if ($pos === false) {
            $pos = strlen($this->html);
        }
        $this->setNode('text', $pos);
        $this->handleWhitespaces();
        if (static::$skipWhitespace && $this->node == ' ') {
            return $this->nextNode();
        }
        $this->isInlineContext = true;
        static::$skipWhitespace = false;

        return true;
    }

    /**
     * parse tag, set tag name and attributes, see if it's a closing tag and so forth...
     *
     * @param void
     * @return bool
     */
    protected function parseTag()
    {
        if (!isset(static::$a_ord)) {
            static::$a_ord = ord('a');
            static::$z_ord = ord('z');
            static::$special_ords = [
                ord(':'), // for xml:lang
                ord('-'), // for http-equiv
            ];
        }

        $tagName = '';

        $pos = 1;
        $isStartTag = $this->html[$pos] != '/';
        if (!$isStartTag) {
            $pos++;
        }
        // get tagName
        while (isset($this->html[$pos])) {
            $pos_ord = ord(strtolower($this->html[$pos]));
            if (($pos_ord >= static::$a_ord && $pos_ord <= static::$z_ord) || (!empty($tagName) && is_numeric($this->html[$pos]))) {
                $tagName .= $this->html[$pos];
                $pos++;
            } else {
                $pos--;
                break;
            }
        }

        $tagName = strtolower($tagName);
        if (empty($tagName) || !isset($this->blockElements[$tagName])) {
            // something went wrong => invalid tag
            $this->invalidTag();

            return false;
        }
        if ($this->noTagsInCode && end($this->openTags) == 'code' && !($tagName == 'code' && !$isStartTag)) {
            // we supress all HTML tags inside code tags
            $this->invalidTag();

            return false;
        }

        // get tag attributes
        /** TODO: in html 4 attributes do not need to be quoted **/
        $isEmptyTag = false;
        $attributes = [];
        $currAttrib = '';
        while (isset($this->html[$pos + 1])) {
            $pos++;
            // close tag
            if ($this->html[$pos] == '>' || $this->html[$pos] . $this->html[$pos + 1] == '/>') {
                if ($this->html[$pos] == '/') {
                    $isEmptyTag = true;
                    $pos++;
                }
                break;
            }

            $pos_ord = ord(strtolower($this->html[$pos]));
            if (($pos_ord >= static::$a_ord && $pos_ord <= static::$z_ord) || in_array($pos_ord, static::$special_ords)) {
                // attribute name
                $currAttrib .= $this->html[$pos];
            } elseif (in_array($this->html[$pos], [' ', "\t", "\n"])) {
                // drop whitespace
            } elseif (in_array($this->html[$pos] . $this->html[$pos + 1], ['="', "='"])) {
                // get attribute value
                $pos++;
                $await = $this->html[$pos]; // single or double quote
                $pos++;
                $value = '';
                while (isset($this->html[$pos]) && $this->html[$pos] != $await) {
                    $value .= $this->html[$pos];
                    $pos++;
                }
                $attributes[$currAttrib] = $value;
                $currAttrib = '';
            } else {
                $this->invalidTag();

                return false;
            }
        }
        if ($this->html[$pos] != '>') {
            $this->invalidTag();

            return false;
        }

        if (!empty($currAttrib)) {
            // html 4 allows something like <option selected> instead of <option selected="selected">
            $attributes[$currAttrib] = $currAttrib;
        }
        if (!$isStartTag) {
            if (!empty($attributes) || $tagName != end($this->openTags)) {
                // end tags must not contain any attributes
                // or maybe we did not expect a different tag to be closed
                $this->invalidTag();

                return false;
            }
            array_pop($this->openTags);
            if (in_array($tagName, $this->preformattedTags)) {
                $this->keepWhitespace--;
            }
        }
        $pos++;
        $this->node = substr($this->html, 0, $pos);
        $this->html = substr($this->html, $pos);
        $this->tagName = $tagName;
        $this->tagAttributes = $attributes;
        $this->isStartTag = $isStartTag;
        $this->isEmptyTag = $isEmptyTag || in_array($tagName, $this->emptyTags);
        if ($this->isEmptyTag) {
            // might be not well formed
            $this->node = preg_replace('# */? *>$#', ' />', $this->node);
        }
        $this->nodeType = 'tag';
        $this->isBlockElement = $this->blockElements[$tagName];
        $this->isNextToInlineContext = $isStartTag && $this->isInlineContext;
        $this->isInlineContext = !$this->isBlockElement;
        return true;
    }

    /**
     * handle invalid tags
     *
     * @param void
     * @return void
     */
    protected function invalidTag()
    {
        $this->html = substr_replace($this->html, '&lt;', 0, 1);
    }

    /**
     * update all vars and make $this->html shorter
     *
     * @param string $type see description for $this->nodeType
     * @param int $pos to which position shall we cut?
     * @return void
     */
    protected function setNode($type, $pos)
    {
        if ($this->nodeType == 'tag') {
            // set tag specific vars to null
            // $type == tag should not be called here
            // see this::parseTag() for more
            $this->tagName = null;
            $this->tagAttributes = null;
            $this->isStartTag = null;
            $this->isEmptyTag = null;
            $this->isBlockElement = null;

        }
        $this->nodeType = $type;
        $this->node = substr($this->html, 0, $pos);
        $this->html = substr($this->html, $pos);
    }

    /**
     * check if $this->html begins with $str
     *
     * @param string $str
     * @return bool
     */
    protected function match($str)
    {
        return substr($this->html, 0, strlen($str)) == $str;
    }

    /**
     * truncate whitespaces
     *
     * @param void
     * @return void
     */
    protected function handleWhitespaces()
    {
        if ($this->keepWhitespace) {
            // <pre> or <code> before...

            return;
        }
        // truncate multiple whitespaces to a single one
        $this->node = preg_replace('#\s+#s', ' ', $this->node);
    }

    /**
     * normalize self::node
     *
     * @param void
     * @return void
     */
    protected function normalizeNode()
    {
        $this->node = '<';
        if (!$this->isStartTag) {
            $this->node .= '/' . $this->tagName . '>';

            return;
        }
        $this->node .= $this->tagName;
        foreach ($this->tagAttributes as $name => $value) {
            $this->node .= ' ' . $name . '="' . str_replace('"', '&quot;', $value) . '"';
        }
        if ($this->isEmptyTag) {
            $this->node .= ' /';
        }
        $this->node .= '>';
    }
}
