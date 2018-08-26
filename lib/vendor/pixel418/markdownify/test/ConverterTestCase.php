<?php

namespace Test\Markdownify;

use Markdownify\Converter;

require_once(__DIR__ . '/../vendor/autoload.php');

class ConverterTestCase extends \PHPUnit_Framework_TestCase
{


    /* ATTRIBUTES
     *************************************************************************/
    protected $converter;


    /* HEADING TEST METHODS
     *************************************************************************/
    /**
     * @dataProvider providerHeadingConversion
     */
    public function testHeadingConversion_withAttribute($level, $attributesHTML, $attributesMD = null)
    {
        $innerHTML = 'Heading ' . $level;
        if (empty($attributesHTML)) {
            $md = str_pad('', $level, '#') . ' ' . $innerHTML;
        } else {
            $md = '<h' . $level . $attributesHTML . '>' . "\n"
                . '  ' . $innerHTML . "\n"
                . '</h' . $level . '>';
        }
        $html = '<h' . $level . $attributesHTML . '>' . $innerHTML . '</h' . $level . '>';
        $this->assertEquals($md, $this->converter->parseString($html));
    }

    public function providerHeadingConversion()
    {
        $attributes = [' id="idAttribute"', ' class=" class1  class2 "'];
        $data = [];
        for ($i = 1; $i <= 6; $i++) {
            $data[] = [$i, ''];
            $data[] = [$i, $attributes[0]];
            $data[] = [$i, $attributes[1]];
            $data[] = [$i, $attributes[0] . $attributes[1]];
        }
        return $data;
    }

    /**
     * @dataProvider providerHeadingConversionEscape
     */
    public function testHeadingConversionEscape($html, $md)
    {
        $this->assertEquals($md, $this->converter->parseString($html));
    }

    public function providerHeadingConversionEscape()
    {
        $data = [];
        $data['level1']['html'] = '# Heading 1';
        $data['level1']['md'] = '\# Heading 1';
        $data['level2']['html'] = '## Heading 2';
        $data['level2']['md'] = '\## Heading 2';
        return $data;
    }


    /* ESCAPE TEST METHODS
     *************************************************************************/

    /**
     * @dataProvider providerAutoescapeConversion
     */
    public function testAutoescapeConversion($html, $md)
    {
        $this->assertEquals($md, $this->converter->parseString($html));
    }

    public function providerAutoescapeConversion()
    {
        return [
            ['AT&amp;T', 'AT&T'],
            ['4 &lt; 5', '4 < 5'],
            ['&copy;', '&copy;']
        ];
    }


    /* STRIP TAGS OPTION
     *************************************************************************/

    /**
     * @dataProvider providerKeepHTMLOption
     */
    public function testKeepHTMLOption($html, $mdWithTag, $mdWithoutTag)
    {
        $this->converter->setKeepHTML(false);
        $this->assertEquals($mdWithoutTag, $this->converter->parseString($html));
        $this->converter->setKeepHTML(true);
        $this->assertEquals($mdWithTag, $this->converter->parseString($html));
    }

    public function providerKeepHTMLOption()
    {
        $data = [];

        // Issue #16
        $data['image']['html'] = '<img title="a012.gif" src="http://images/problems/a012.gif" alt="a012.gif" width="374" height="204" />';
        $data['image']['mdWithTag'] = '<img title="a012.gif" src="http://images/problems/a012.gif" alt="a012.gif" width="374" height="204" />';
        $data['image']['mdWithoutTag'] = '![a012.gif][1]

 [1]: http://images/problems/a012.gif "a012.gif"';

        // Issue #23
        $data['target']['html'] = '<p>See <a href="https://github.com/quilljs/quill/issues/81" target="_blank">https://github.com/quilljs/quill/issues/81</a></p>';
        $data['target']['mdWithTag'] = 'See <a href="https://github.com/quilljs/quill/issues/81" target="_blank">https://github.com/quilljs/quill/issues/81</a>';
        $data['target']['mdWithoutTag'] = 'See <https://github.com/quilljs/quill/issues/81>';

        // Issue #25
        $data['u']['html'] = '<span><u>Some text</u></span>';
        $data['u']['mdWithTag'] = '<span><u>Some text</u></span>';
        $data['u']['mdWithoutTag'] = 'Some text';

        return $data;
    }


    /* BLOCKQUOTE TEST METHODS
     *************************************************************************/

    /**
     * @dataProvider providerBlockquoteConversion
     */
    public function testBlockquoteConversion($html, $md)
    {
        $this->assertEquals($md, $this->converter->parseString($html));
    }

    public function providerBlockquoteConversion()
    {
        $data = [];
        $data['simple']['html'] = '<blockquote>blockquoted text goes here</blockquote>';
        $data['simple']['md'] = '> blockquoted text goes here';
        $data['paragraphs']['html'] = '<blockquote><p>paragraph1</p><p>paragraph2</p></blockquote>';
        $data['paragraphs']['md'] = '> paragraph1' . PHP_EOL
            . '> ' . PHP_EOL
            . '> paragraph2';
        $data['cascade']['html'] = '<blockquote><blockquote>cascading blockquote</blockquote></blockquote>';
        $data['cascade']['md'] = '> > cascading blockquote';
        $data['container']['html'] = '<blockquote><h2>This is a header.</h2></blockquote>';
        $data['container']['md'] = '> ## This is a header.';
        return $data;
    }


    /* LISTS TEST METHODS
     *************************************************************************/

    /**
     * @dataProvider providerListConversion
     */
    public function testListConversion($html, $md)
    {
        $this->assertEquals($md, $this->converter->parseString($html));
    }

    public function providerListConversion()
    {
        $data = [];
        $data['ordered']['html'] = '<ol><li>Bird</li><li>McHale</li><li>Parish</li></ol>';
        $data['ordered']['md'] = '  1. Bird' . PHP_EOL
            . '  2. McHale' . PHP_EOL
            . '  3. Parish';
        $data['unordered']['html'] = '<ul><li>Red</li><li>Green</li><li>Blue</li></ul>';
        $data['unordered']['md'] = '  * Red' . PHP_EOL
            . '  * Green' . PHP_EOL
            . '  * Blue';
        $data['paragraph']['html'] = '<ul><li><p>Bird</p></li><li><p>Magic</p></li></ul>';
        $data['paragraph']['md'] = '  * Bird' . PHP_EOL
            . PHP_EOL
            . '  * Magic';
        $data['next-to-text']['html'] = 'McHale<ol><li>Bird</li><li>Magic</li></ol>';
        $data['next-to-text']['md'] = 'McHale' . PHP_EOL
            . '' . PHP_EOL
            . '  1. Bird' . PHP_EOL
            . '  2. Magic';
        $data['next-to-text-in-block-context']['html'] = '<blockquote>McHale<ol><li>Bird</li><li>Magic</li></ol></blockquote>';
        $data['next-to-text-in-block-context']['md'] = '> McHale' . PHP_EOL
            . '> ' . PHP_EOL
            . '>   1. Bird' . PHP_EOL
            . '>   2. Magic';
        $data['next-to-bold']['html'] = '<b>McHale</b><ol><li>Bird</li><li>Magic</li></ol>';
        $data['next-to-bold']['md'] = '**McHale**' . PHP_EOL
            . PHP_EOL
            . '  1. Bird' . PHP_EOL
            . '  2. Magic';
        $data['next-to-bold-and-br']['html'] = '<b>McHale</b><br><ol><li>Bird</li><li>Magic</li></ol>';
        $data['next-to-bold-and-br']['md'] = '**McHale**  ' . PHP_EOL
            . PHP_EOL
            . PHP_EOL
            . '  1. Bird' . PHP_EOL
            . '  2. Magic';
        $data['next-to-paragraph']['html'] = '<p>McHale</p><ol><li>Bird</li><li>Magic</li></ol>';
        $data['next-to-paragraph']['md'] = 'McHale' . PHP_EOL
            . PHP_EOL
            . '  1. Bird' . PHP_EOL
            . '  2. Magic';
        $data['nested-ordered']['html'] = '<ol><li>Bird</li><li>Colors<ol><li>Red</li><li>Green<ol><li>Light</li><li>Dark</li></ol></li><li>Blue</li></ol></li></ol>';
        $data['nested-ordered']['md'] = '  1. Bird' . PHP_EOL
            . '  2. Colors' . PHP_EOL
            . '      1. Red' . PHP_EOL
            . '      2. Green' . PHP_EOL
            . '          1. Light' . PHP_EOL
            . '          2. Dark' . PHP_EOL
            . '      3. Blue';
        $data['nested-unordered']['html'] = '<ul><li>Bird</li><li>Colors<ul><li>Red</li><li>Green<ul><li>Light</li><li>Dark</li></ul></li><li>Blue</li></ul></li></ul>';
        $data['nested-unordered']['md'] = '  * Bird' . PHP_EOL
            . '  * Colors' . PHP_EOL
            . '      * Red' . PHP_EOL
            . '      * Green' . PHP_EOL
            . '          * Light' . PHP_EOL
            . '          * Dark' . PHP_EOL
            . '      * Blue';

        return $data;
    }


    /* CODE TEST METHODS
     *************************************************************************/

    /**
     * @dataProvider providerCodeConversion
     */
    public function testCodeConversion($html, $md)
    {
        $this->assertEquals($md, $this->converter->parseString($html));
    }

    public function providerCodeConversion()
    {
        $data = [];
        $data['inline']['html'] = '<p>Use the <code>printf()</code> function.</p>';
        $data['inline']['md'] = 'Use the `printf()` function.';
        $data['inline-backtick']['html'] = '<p>A single backtick in a code span: <code>`</code></p>';
        $data['inline-backtick']['md'] = 'A single backtick in a code span: `` ` ``';
        $data['inline-doubleBacktick']['html'] = '<p>A backtick-delimited string in a code span: <code>`foo`</code></p>';
        $data['inline-doubleBacktick']['md'] = 'A backtick-delimited string in a code span: `` `foo` ``';
        $data['inline-escape']['html'] = 'Use the `printf()` function.';
        $data['inline-escape']['md'] = 'Use the \`printf()\` function.';
        $data['inline-backtick-escape']['html'] = 'A single backtick in a code span: `` ` ``';
        $data['inline-backtick-escape']['md'] = 'A single backtick in a code span: \`\` \` \`\`';
        $data['inline-doubleBacktick-escape']['html'] = 'A backtick-delimited string in a code span: `` `foo` ``';
        $data['inline-doubleBacktick-escape']['md'] = 'A backtick-delimited string in a code span: \`\` \`foo\` \`\`';
        $data['inline']['md'] = 'Use the `printf()` function.';
        $data['inline-html']['html'] = '<p>Please don\'t use any <code>&lt;blink&gt;</code> tags.</p>';
        $data['inline-html']['md'] = 'Please don\'t use any `<blink>` tags.';
        $data['pre']['html'] = '<p>This is a normal paragraph:</p><pre><code>This is a code block.</code></pre>';
        $data['pre']['md'] = 'This is a normal paragraph:' . PHP_EOL
            . PHP_EOL
            . '    This is a code block.';
        $data['pre-indentation']['html'] = '<p>Here is an example of AppleScript:</p><pre><code>tell application "Foo"
    beep
end tell
</code></pre>';
        $data['pre-indentation']['md'] = 'Here is an example of AppleScript:' . PHP_EOL
            . PHP_EOL
            . '    tell application "Foo"' . PHP_EOL
            . '        beep' . PHP_EOL
            . '    end tell';
        $data['pre-html']['html'] = '<pre><code>&lt;div class="footer"&gt;
    &amp;copy; 2004 Foo Corporation
&lt;/div&gt;
</code></pre>';
        $data['pre-html']['md'] = '    <div class="footer">
        &copy; 2004 Foo Corporation
    </div>';

        return $data;
    }


    /* LINK TEST METHODS
     *************************************************************************/

    /**
     * @dataProvider providerLinkConversion
     */
    public function testLinkConversion($html, $md, $linkPosition = null)
    {
        if ($linkPosition !== null) {
            $this->converter->setLinkPosition($linkPosition);
        }
        $this->assertEquals($md, $this->converter->parseString($html));
    }

    public function providerLinkConversion()
    {
        $data = [];

        // Link with href attribute
        $data['url']['html'] = '<p><a href="http://example.net/">This link</a> has no title attribute.</p>';
        $data['url']['md'] = '[This link][1] has no title attribute.' . PHP_EOL
            . PHP_EOL
            . ' [1]: http://example.net/';

        // Empty link
        $data['url-empty']['html'] = '<p><a href="">This link</a>.</p>';
        $data['url-empty']['md'] = '[This link]().';

        // Multiple paragraph link
        $data['url-multiple-1']['html'] =
            '<p>This is <a href="http://example1.com/" title="Title">an example</a> inline link.</p>
            <p>This is <a href="http://example2.com/" title="Title">another example</a> inline link.</p>';
        $data['url-multiple-1']['md'] = 'This is [an example][1] inline link.' . PHP_EOL
            . PHP_EOL
            . 'This is [another example][2] inline link.' . PHP_EOL
            . PHP_EOL
            . ' [1]: http://example1.com/ "Title"' . PHP_EOL
            . ' [2]: http://example2.com/ "Title"';
        $data['url-multiple-1']['linkPosition'] = Converter::LINK_AFTER_CONTENT;

        // Multiple paragraph link
        $data['url-multiple-2']['html'] =
            '<p>This is <a href="http://example1.com/" title="Title">an example</a> inline link.</p>
            <p>This is <a href="http://example2.com/" title="Title">another example</a> inline link.</p>';
        $data['url-multiple-2']['md'] = 'This is [an example][1] inline link.' . PHP_EOL
            . PHP_EOL
            . ' [1]: http://example1.com/ "Title"' . PHP_EOL
            . PHP_EOL
            . 'This is [another example][2] inline link.' . PHP_EOL
            . PHP_EOL
            . ' [2]: http://example2.com/ "Title"';
        $data['url-multiple-2']['linkPosition'] = Converter::LINK_AFTER_PARAGRAPH;

        // Multiple paragraph link
        $data['url-multiple-2']['html'] =
            '<p>This is <a href="http://example1.com/" title="Title">an example</a> inline link.</p>
            <p>This is <a href="http://example2.com/" title="Title">another example</a> inline link.</p>';
        $data['url-multiple-2']['md'] = 'This is [an example](http://example1.com/ "Title") inline link.' . PHP_EOL
            . PHP_EOL
            . 'This is [another example](http://example2.com/ "Title") inline link.';
        $data['url-multiple-2']['linkPosition'] = Converter::LINK_IN_PARAGRAPH;

        // Direct link
        $data['url-direct']['html'] = '<p><a href="http://example.com">http://example.com</a>.</p>';
        $data['url-direct']['md'] = '<http://example.com>.';

        // Link with href + title attributes
        $data['url-title']['html'] = '<p>This is <a href="http://example.com/" title="Title">an example</a> inline link.</p>';
        $data['url-title']['md'] = 'This is [an example][1] inline link.' . PHP_EOL
            . PHP_EOL
            . ' [1]: http://example.com/ "Title"';

        // Link with href + title + id attributes
        $data['url-title-id']['html'] = '<p>This is <a href="http://example.com/" title="Title" id="myLink">an example</a> inline link.</p>';
        $data['url-title-id']['md'] = 'This is <a href="http://example.com/" title="Title" id="myLink">an example</a> inline link.';

        // Link with href + title + class attributes
        $data['url-title-class']['html'] = '<p>This is <a href="http://example.com/" title="Title" class="external">an example</a> inline link.</p>';
        $data['url-title-class']['md'] = 'This is <a href="http://example.com/" title="Title" class="external">an example</a> inline link.';

        // Link with href + title + multiple classes attributes
        $data['url-title-multiple-class']['html'] = '<p>This is <a href="http://example.com/" title="Title" class=" class1  class2 ">an example</a> inline link.</p>';
        $data['url-title-multiple-class']['md'] = 'This is <a href="http://example.com/" title="Title" class=" class1  class2 ">an example</a> inline link.';

        // Link with href + title + multiple classes + id attributes
        $data['url-title-multiple-class-id']['html'] = '<p>This is <a href="http://example.com/" title="Title" class=" class1  class2 " id="myLink">an example</a> inline link.</p>';
        $data['url-title-multiple-class-id']['md'] = 'This is <a href="http://example.com/" title="Title" class=" class1  class2 " id="myLink">an example</a> inline link.';

        // Escaped link
        $data['url-escape']['html'] = '[This link](/path)';
        $data['url-escape']['md'] = '\[This link\](/path)';

        // Image with src + alt attributes
        $data['image']['html'] = '<img src="/path/to/img.jpg" alt="Alt text" />';
        $data['image']['md'] = '![Alt text][1]' . PHP_EOL
            . PHP_EOL
            . ' [1]: /path/to/img.jpg';

        // Image with src + alt attributes in content
        $data['image--in']['html'] = '<img src="/path/to/img.jpg" alt="Alt text" />';
        $data['image--in']['md'] = '![Alt text](/path/to/img.jpg)';
        $data['image--in']['linkPosition'] = Converter::LINK_IN_PARAGRAPH;

        // Image with src + alt + title attributes
        $data['image-title']['html'] = '<img src="/path/to/img.jpg" alt="Alt text" title="Optional title attribute" />';
        $data['image-title']['md'] = '![Alt text][1]' . PHP_EOL
            . PHP_EOL
            . ' [1]: /path/to/img.jpg "Optional title attribute"';

        // Image with src + alt + title attributes in content
        $data['image-title--in']['html'] = '<img src="/path/to/img.jpg" alt="Alt text" title="Optional title attribute" />';
        $data['image-title--in']['md'] = '![Alt text](/path/to/img.jpg "Optional title attribute")';
        $data['image-title--in']['linkPosition'] = Converter::LINK_IN_PARAGRAPH;

        // Escaped image
        $data['image-escape']['html'] = '![This link](/path)';
        $data['image-escape']['md'] = '!\[This link\](/path)';

        // Image & Link
        $data['image-url']['html'] = '<p><a href="http://google.com"><img src="http://www.fillmurray.com/g/200/300"></a></p>';
        $data['image-url']['md'] = '[![][1]][2]' . PHP_EOL
            . PHP_EOL
            . ' [1]: http://www.fillmurray.com/g/200/300' . PHP_EOL
            . ' [2]: http://google.com';

        return $data;
    }


    /* EMPHASIS TEST METHODS
     *************************************************************************/

    /**
     * @dataProvider providerEmphasisConversion
     */
    public function testEmphasisConversion($html, $md)
    {
        $this->assertEquals($md, $this->converter->parseString($html));
    }

    public function providerEmphasisConversion()
    {
        $data = [];
        $data['strong']['html'] = '<strong>double asterisks</strong>';
        $data['strong']['md'] = '**double asterisks**';
        $data['strong-escape']['html'] = '**double asterisks**';
        $data['strong-escape']['md'] = '\*\*double asterisks\*\*';
        $data['strong-escape2']['html'] = '__double asterisks__';
        $data['strong-escape2']['md'] = '\_\_double asterisks\_\_';
        $data['em']['html'] = '<em>single asterisks</em>';
        $data['em']['md'] = '_single asterisks_';
        $data['em-escape']['html'] = '*single asterisks*';
        $data['em-escape']['md'] = '\*single asterisks\*';
        $data['em-escape2']['html'] = '_single asterisks_';
        $data['em-escape2']['md'] = '\_single asterisks\_';

        return $data;
    }


    /* RULES TEST METHODS
     *************************************************************************/

    /**
     * @dataProvider providerRulesConversion
     */
    public function testRulesConversion($html, $md)
    {
        $this->assertEquals($md, $this->converter->parseString($html));
    }

    public function providerRulesConversion()
    {
        $data = [];
        $data['hr']['html'] = '<hr>';
        $data['hr']['md'] = '* * *';
        $data['escape-']['html'] = '-----------------------------------';
        $data['escape-']['md'] = '\---\---\---\---\---\---\---\---\---\---\-----';
        $data['escape-']['html'] = '*****************';
        $data['escape-']['md'] = '\***\***\***\***\*****';

        return $data;
    }


    /* FIX BREAKS TESTS
     *************************************************************************/

    /**
     * @dataProvider providerFixBreaks
     */
    public function testFixBreaks($html, $md)
    {
        $this->assertEquals($md, $this->converter->parseString($html));
    }


    public function providerFixBreaks()
    {
        $data = [];
        $data['break1']['html'] = "<strong>Hello,<br>How are you doing?</strong>";
        $data['break1']['md'] = "**Hello,  \nHow are you doing?**";
        $data['break2']['html'] = "<b>Hey,<br> How you're doing?</b><br><br><b>Sorry<br><br> You can't get through</b>";
        $data['break2']['md'] = "**Hey,  \nHow you're doing?**  \n  \n**Sorry  \n  \nYou can't get through**";

        return $data;
    }

    /* FIX TAG SPACES TESTS
     *************************************************************************/

    /**
     * @dataProvider providerFixTagSpaces
     */
    public function testFixTagSpaces($html, $md)
    {
        $this->assertEquals($md, $this->converter->parseString($html));
    }


    public function providerFixTagSpaces()
    {
        $data = [];
        $data['strong']['html'] = "<p>This is<strong> strong</strong> text</p>";
        $data['strong']['md'] = "This is **strong** text";
        $data['em']['html'] = "<p>This is<em> italic </em>text</p>";
        $data['em']['md'] = "This is _italic_ text";
        $data['b']['html'] = "<p>Not bold,<b> bolder    </b>boldst</p>";
        $data['b']['md'] = "Not bold, **bolder** boldst";
        $data['i']['html'] = "<p>Not italic, <i>italic </i></p>";
        $data['i']['md'] = "Not italic, _italic_";
        $data['a']['html'] = "<p>This is a paragraph of text with <a href='http://example.com'>a link </a>to something awesome.</p>";
        $data['a']['md'] = "This is a paragraph of text with [a link][1] to something awesome.

 [1]: http://example.com";

        return $data;
    }

    /* FIX STATE RESET
     *************************************************************************/

    public function testResetState()
    {
        // Broken (unclosed) tags cause properties (such as indents) to run onto subsequent strings,
        $blockquote = 'Test blockquote <blockquote>Here it is';
        $linebreaks = 'Test<br /><br />Linebreaks';

        $converter = new Converter();
        $bqOutput = $converter->parseString($blockquote);

        $this->assertContains('>', $bqOutput);

        $lbOutput = $converter->parseString($linebreaks);

        $this->assertNotContains('>', $lbOutput);
    }
}
