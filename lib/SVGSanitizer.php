<?php


/**
 * Class SVGSanitizer
 * 
 * simplified/compact version of svg-sanitizer - https://github.com/darylldoyle/svg-sanitizer by Daryll Doyle
 *
 * @package enshrined\svgSanitize
 */
class SVGSanitizer
{

    /**
     * Regex to catch script and data values in attributes
     */
    const SCRIPT_REGEX = '/(?:\w+script|data):/xi';

    /**
     * @var DOMDocument
     */
    protected $xmlDocument;

    /**
     * @var array
     */
    protected $allowedTags;

    /**
     * @var array
     */
    protected $allowedAttrs;

    /**
     * @var
     */
    protected $xmlLoaderValue;

    /**
     * @var bool
     */
    protected $minifyXML = false;

    /**
     * @var bool
     */
    protected $removeRemoteReferences = false;

    /**
     * @var bool
     */
    protected $removeXMLTag = false;

    /**
     * @var int
     */
    protected $xmlOptions = LIBXML_NOEMPTYTAG;


    /**
     * SVGSanitizer::clean('<svg ...>')
     */
    public static function clean($svgText) {
        
        $sanitizer = new static();

        return $sanitizer->sanitize($svgText);
    }

    /**
     *
     */
    function __construct()
    {
        // Load default tags/attributes
        $this->allowedAttrs = [
            
            // HTML
            'accept', 'action', 'align', 'alt', 'autocomplete',
            'background', 'bgcolor', 'border',
            'cellpadding', 'cellspacing', 'checked', 'cite', 'class', 'clear', 'color', 'cols', 'colspan', 'coords', 'crossorigin',
            'datetime', 'default', 'dir', 'disabled', 'download',
            'enctype',
            'face', 'for',
            'headers', 'height', 'hidden', 'high', 'href', 'hreflang',
            'id', 'integrity', 'ismap',
            'label', 'lang', 'list', 'loop', 'low',
            'max', 'maxlength', 'media', 'method', 'min', 'multiple',
            'name', 'noshade', 'novalidate', 'nowrap',
            'open', 'optimum',
            'pattern', 'placeholder', 'poster', 'preload', 'pubdate',
            'radiogroup', 'readonly', 'rel', 'required', 'rev', 'reversed', 'role', 'rows', 'rowspan',
            'spellcheck', 'scope', 'selected', 'shape', 'size', 'sizes', 'span', 'srclang', 'start', 'src', 'srcset', 'step', 'style', 'summary',
            'tabindex', 'title', 'type',
            'usemap',
            'valign', 'value',
            'width',
            'xmlns',

            // SVG
            'accent-height', 'accumulate', 'additivive', 'alignment-baseline', 'ascent', 'attributename', 'attributetype', 'azimuth',
            'basefrequency', 'baseline-shift', 'begin', 'bias', 'by',
            'class', 'clip', 'clip-path', 'clip-rule', 'color', 'color-interpolation', 'color-interpolation-filters', 'color-profile', 'color-rendering', 'cx', 'cy',
            'd', 'dx', 'dy', 'diffuseconstant', 'direction', 'display', 'divisor', 'dur',
            'edgemode', 'elevation', 'end',
            'fill', 'fill-opacity', 'fill-rule', 'filter', 'flood-color', 'flood-opacity', 'font-family', 'font-size', 'font-size-adjust', 'font-stretch', 'font-style', 'font-variant', 'font-weight', 'fx', 'fy',
            'g1', 'g2', 'glyph-name', 'glyphref', 'gradientunits', 'gradienttransform',
            'height', 'href',
            'id', 'image-rendering', 'in', 'in2',
            'k', 'k1', 'k2', 'k3', 'k4', 'kerning', 'keypoints', 'keysplines', 'keytimes',
            'lang', 'lengthadjust', 'letter-spacing',
            'kernelmatrix', 'kernelunitlength',
            'lighting-color', 'local',
            'marker-end', 'marker-mid', 'marker-start', 'markerheight', 'markerunits', 'markerwidth', 'maskcontentunits', 'maskunits', 'max', 'mask', 'media', 'method', 'mode', 'min',
            'name', 'numoctaves',
            'offset', 'operator', 'opacity', 'order', 'orient', 'orientation', 'origin', 'overflow',
            'paint-order', 'path', 'pathlength', 'patterncontentunits', 'patterntransform', 'patternunits', 'points', 'preservealpha', 'preserveaspectratio',
            'r', 'rx', 'ry', 'radius', 'refx', 'refy', 'repeatcount', 'repeatdur', 'restart', 'result', 'rotate',
            'scale', 'seed', 'shape-rendering', 'specularconstant', 'specularexponent', 'spreadmethod', 'stddeviation', 'stitchtiles', 'stop-color', 'stop-opacity', 'stroke-dasharray', 'stroke-dashoffset', 'stroke-linecap', 'stroke-linejoin', 'stroke-miterlimit', 'stroke-opacity', 'stroke', 'stroke-width', 'style', 'surfacescale',
            'tabindex', 'targetx', 'targety', 'transform', 'text-anchor', 'text-decoration', 'text-rendering', 'textlength', 'type',
            'u1', 'u2', 'unicode',
            'values', 'viewbox', 'visibility', 'vert-adv-y', 'vert-origin-x', 'vert-origin-y',
            'width', 'word-spacing', 'wrap', 'writing-mode',
            'xchannelselector',
            'ychannelselector',
            'x', 'x1', 'x2',
            'xmlns',
            'y', 'y1', 'y2',
            'z', 'zoomandpan',

            // MathML
            'accent', 'accentunder', 'align',
            'bevelled',
            'close', 'columnsalign', 'columnlines', 'columnspan',
            'denomalign', 'depth', 'dir', 'display', 'displaystyle',
            'fence', 'frame',
            'height', 'href',
            'id',
            'largeop', 'length', 'linethickness', 'lspace', 'lquote',
            'mathbackground', 'mathcolor', 'mathsize', 'mathvariant', 'maxsize', 'minsize', 'movablelimits',
            'notation', 'numalign',
            'open',
            'rowalign', 'rowlines', 'rowspacing', 'rowspan', 'rspace', 'rquote',
            'scriptlevel', 'scriptminsize', 'scriptsizemultiplier', 'selection', 'separator', 'separators', 'slope', 'stretchy', 'subscriptshift', 'supscriptshift', 'symmetric',
            'voffset',
            'width',
            'xmlns',

            // XML
            'xlink:href',
            'xml:id',
            'xlink:title',
            'xml:space',
            'xmlns:xlink',
        ];

        $this->allowedTags = [
            // HTML
            'a', 'abbr', 'acronym', 'address', 'area', 'article', 'aside', 'audio',
            'b', 'bdi', 'bdo', 'big', 'blink', 'blockquote', 'body', 'br', 'button',
            'canvas', 'caption', 'center', 'cite', 'code', 'col', 'colgroup', 'content',
            'data', 'datalist', 'dd', 'decorator', 'del', 'details', 'dfn', 'dir', 'div', 'dl', 'dt',
            'element', 'em',
            'fieldset', 'figcaption', 'figure', 'font', 'footer', 'form',
            'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'head', 'header', 'hgroup', 'hr', 'html',
            'i', 'image', 'img', 'input', 'ins',
            'kbd',
            'label', 'legend', 'li',
            'main', 'map', 'mark', 'marquee', 'menu', 'menuitem', 'meter',
            'nav', 'nobr',
            'ol', 'optgroup', 'option', 'output',
            'p', 'pre', 'progress',
            'q',
            'rp', 'rt', 'ruby',
            's', 'samp', 'section', 'select', 'shadow', 'small', 'source', 'spacer', 'span', 'strike', 'strong', 'style', 'sub', 'summary', 'sup',
            'table', 'tbody', 'td', 'template', 'textarea', 'tfoot', 'th', 'thead', 'time', 'tr', 'track', 'tt',
            'u', 'ul',
            'var', 'video',
            'wbr',

            // SVG
            'svg',
            'altglyph', 'altglyphdef', 'altglyphitem', 'animatecolor', 'animatemotion', 'animatetransform',
            'circle', 'clippath',
            'defs', 'desc',
            'ellipse',
            'filter', 'font',
            'g', 'glyph', 'glyphref',
            'hkern',
            'image',
            'line',
            'lineargradient',
            'marker', 'mask', 'metadata', 'mpath',
            'path', 'pattern', 'polygon', 'polyline',
            'radialgradient', 'rect',
            'stop', 'switch', 'symbol',
            'text', 'textpath', 'title', 'tref', 'tspan',
            'use',
            'view', 'vkern',

            // SVG Filters
            'feBlend',
            'feColorMatrix',
            'feComponentTransfer',
            'feComposite',
            'feConvolveMatrix',
            'feDiffuseLighting',
            'feDisplacementMap',
            'feDistantLight',
            'feFlood',
            'feFuncA', 'feFuncB', 'feFuncG', 'feFuncR',
            'feGaussianBlur',
            'feMerge',
            'feMergeNode',
            'feMorphology',
            'feOffset',
            'fePointLight',
            'feSpecularLighting',
            'feSpotLight',
            'feTile',
            'feTurbulence',

            //MathML
            'math',
            'menclose',
            'merror',
            'mfenced',
            'mfrac',
            'mglyph',
            'mi',
            'mlabeledtr',
            'mmuliscripts',
            'mn',
            'mo',
            'mover',
            'mpadded',
            'mphantom',
            'mroot',
            'mrow',
            'ms',
            'mpspace',
            'msqrt',
            'mystyle',
            'msub',
            'msup',
            'msubsup',
            'mtable',
            'mtd',
            'mtext',
            'mtr',
            'munder',
            'munderover',

            //text
            '#text'
        ];
    }

    /**
     * Set up the DOMDocument
     */
    protected function resetInternal()
    {
        $this->xmlDocument = new DOMDocument();
        $this->xmlDocument->preserveWhiteSpace = false;
        $this->xmlDocument->strictErrorChecking = false;
        $this->xmlDocument->formatOutput = !$this->minifyXML;
    }

    /**
     * Set XML options to use when saving XML
     * See: DOMDocument::saveXML
     * 
     * @param int  $xmlOptions
     */
    public function setXMLOptions($xmlOptions)
    {
        $this->xmlOptions = $xmlOptions;
    }

     /**
     * Get XML options to use when saving XML
     * See: DOMDocument::saveXML
     * 
     * @return int
     */
    public function getXMLOptions()
    {
       return $this->xmlOptions;
    }

    /**
     * Get the array of allowed tags
     *
     * @return array
     */
    public function getAllowedTags()
    {
        return $this->allowedTags;
    }

    /**
     * Set custom allowed tags
     *
     * @param array $allowedTags
     */
    public function setAllowedTags($allowedTags)
    {
        $this->allowedTags = array_map('strtolower', $allowedTags);
    }

    /**
     * Get the array of allowed attributes
     *
     * @return array
     */
    public function getAllowedAttrs()
    {
        return $this->allowedAttrs;
    }

    /**
     * Set custom allowed attributes
     *
     * @param array $allowedAttrs
     */
    public function setAllowedAttrs($allowedAttrs)
    {
        $this->allowedAttrs = array_map('strtolower', $allowedAttrs);
    }

    /**
     * Should we remove references to remote files?
     *
     * @param bool $removeRemoteRefs
     */
    public function removeRemoteReferences($removeRemoteRefs = false)
    {
        $this->removeRemoteReferences = $removeRemoteRefs;
    }

    /**
     * Sanitize the passed string
     *
     * @param string $dirty
     * @return string
     */
    public function sanitize($dirty)
    {
        // Don't run on an empty string
        if (empty($dirty)) {
            return '';
        }

        // Strip php tags
        $dirty = preg_replace('/<\?(=|php)(.+?)\?>/i', '', $dirty);

        $this->resetInternal();
        $this->setUpBefore();

        $loaded = $this->xmlDocument->loadXML($dirty);

        // If we couldn't parse the XML then we go no further. Reset and return false
        if (!$loaded) {
            $this->resetAfter();
            return false;
        }

        $this->removeDoctype();

        // Grab all the elements
        $allElements = $this->xmlDocument->getElementsByTagName("*");

        // Start the cleaning proccess
        $this->startClean($allElements);

        // Save cleaned XML to a variable
        if ($this->removeXMLTag) {
            $clean = $this->xmlDocument->saveXML($this->xmlDocument->documentElement, $this->xmlOptions);
        } else {
            $clean = $this->xmlDocument->saveXML($this->xmlDocument, $this->xmlOptions);
        }

        $this->resetAfter();

        // Remove any extra whitespaces when minifying
        if ($this->minifyXML) {
            $clean = preg_replace('/\s+/', ' ', $clean);
        }

        // Return result
        return $clean;
    }

    /**
     * Set up libXML before we start
     */
    protected function setUpBefore()
    {
        // Turn off the entity loader
        $this->xmlLoaderValue = libxml_disable_entity_loader(true);

        // Suppress the errors because we don't really have to worry about formation before cleansing
        libxml_use_internal_errors(true);
    }

    /**
     * Reset the class after use
     */
    protected function resetAfter()
    {
        // Reset the entity loader
        libxml_disable_entity_loader($this->xmlLoaderValue);
    }

    /**
     * Remove the XML Doctype
     * It may be caught later on output but that seems to be buggy, so we need to make sure it's gone
     */
    protected function removeDoctype()
    {
        foreach ($this->xmlDocument->childNodes as $child) {
            if ($child->nodeType === XML_DOCUMENT_TYPE_NODE) {
                $child->parentNode->removeChild($child);
            }
        }
    }

    /**
     * Start the cleaning with tags, then we move onto attributes and hrefs later
     *
     * @param \DOMNodeList $elements
     */
    protected function startClean(\DOMNodeList $elements)
    {
        // loop through all elements
        // we do this backwards so we don't skip anything if we delete a node
        // see comments at: http://php.net/manual/en/class.domnamednodemap.php
        for ($i = $elements->length - 1; $i >= 0; $i--) {
            $currentElement = $elements->item($i);

            // If the tag isn't in the whitelist, remove it and continue with next iteration
            if (!in_array(strtolower($currentElement->tagName), $this->allowedTags)) {
                $currentElement->parentNode->removeChild($currentElement);
                continue;
            }

            $this->cleanAttributesOnWhitelist($currentElement);

            $this->cleanXlinkHrefs($currentElement);

            $this->cleanHrefs($currentElement);

            if (strtolower($currentElement->tagName) === 'use') {
                if ($this->isUseTagDirty($currentElement)) {
                    $currentElement->parentNode->removeChild($currentElement);
                    continue;
                }
            }
        }
    }

    /**
     * Only allow attributes that are on the whitelist
     *
     * @param \DOMElement $element
     */
    protected function cleanAttributesOnWhitelist(\DOMElement $element)
    {
        for ($x = $element->attributes->length - 1; $x >= 0; $x--) {
            // get attribute name
            $attrName = $element->attributes->item($x)->name;

            // Remove attribute if not in whitelist
            if (!in_array(strtolower($attrName), $this->allowedAttrs) && !$this->isAriaAttribute(strtolower($attrName)) && !$this->isDataAttribute(strtolower($attrName))) {
                $element->removeAttribute($attrName);
            }

            // Do we want to strip remote references?
            if($this->removeRemoteReferences) {
                // Remove attribute if it has a remote reference
                if (isset($element->attributes->item($x)->value) && $this->hasRemoteReference($element->attributes->item($x)->value)) {
                    $element->removeAttribute($attrName);
                }
            }
        }
    }

    /**
     * Clean the xlink:hrefs of script and data embeds
     *
     * @param \DOMElement $element
     */
    protected function cleanXlinkHrefs(\DOMElement $element)
    {
        $xlinks = $element->getAttributeNS('http://www.w3.org/1999/xlink', 'href');
        if (preg_match(self::SCRIPT_REGEX, $xlinks) === 1) {
            if (!in_array(substr($xlinks, 0, 14), array(
                'data:image/png', // PNG
                'data:image/gif', // GIF
                'data:image/jpg', // JPG
                'data:image/jpe', // JPEG
                'data:image/pjp', // PJPEG
            ))) {
                $element->removeAttributeNS( 'http://www.w3.org/1999/xlink', 'href' );
            }
        }
    }

    /**
     * Clean the hrefs of script and data embeds
     *
     * @param \DOMElement $element
     */
    protected function cleanHrefs(\DOMElement $element)
    {
        $href = $element->getAttribute('href');
        if (preg_match(self::SCRIPT_REGEX, $href) === 1) {
            $element->removeAttribute('href');
        }
    }

    /**
     * Removes non-printable ASCII characters from string & trims it
     *
     * @param string $value
     * @return bool
     */
    protected function removeNonPrintableCharacters($value)
    {
        return trim(preg_replace('/[^ -~]/xu','',$value));
    }

    /**
     * Does this attribute value have a remote reference?
     *
     * @param $value
     * @return bool
     */
    protected function hasRemoteReference($value)
    {
        $value = $this->removeNonPrintableCharacters($value);

        $wrapped_in_url = preg_match('~^url\(\s*[\'"]\s*(.*)\s*[\'"]\s*\)$~xi', $value, $match);
        if (!$wrapped_in_url){
            return false;
        }

        $value = trim($match[1], '\'"');

        return preg_match('~^((https?|ftp|file):)?//~xi', $value);
    }

    /**
     * Should we minify the output?
     *
     * @param bool $shouldMinify
     */
    public function minify($shouldMinify = false)
    {
        $this->minifyXML = (bool) $shouldMinify;
    }

    /**
     * Should we remove the XML tag in the header?
     *
     * @param bool $removeXMLTag
     */
    public function removeXMLTag($removeXMLTag = false)
    {
        $this->removeXMLTag = (bool) $removeXMLTag;
    }

    /**
     * Check to see if an attribute is an aria attribute or not
     *
     * @param $attributeName
     *
     * @return bool
     */
    protected function isAriaAttribute($attributeName)
    {
        return strpos($attributeName, 'aria-') === 0;
    }

    /**
     * Check to see if an attribute is an data attribute or not
     *
     * @param $attributeName
     *
     * @return bool
     */
    protected function isDataAttribute($attributeName)
    {
        return strpos($attributeName, 'data-') === 0;
    }

    /**
     * Make sure our use tag is only referencing internal resources
     *
     * @param \DOMElement $element
     * @return bool
     */
    protected function isUseTagDirty(\DOMElement $element)
    {
        $xlinks = $element->getAttributeNS('http://www.w3.org/1999/xlink', 'href');
        if ($xlinks && substr($xlinks, 0, 1) !== '#') {
            return true;
        }

        return false;
    }
}
