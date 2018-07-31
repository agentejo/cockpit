<?php

namespace Test\Markdownify;

use Markdownify\ConverterExtra;

require_once(__DIR__ . '/../vendor/autoload.php');

class ConverterExtraTest extends ConverterTestCase
{


    /* UTILS
     *************************************************************************/
    public function setUp()
    {
        $this->converter = new ConverterExtra;
    }


    /* HEADING TEST METHODS
     *************************************************************************/
    /**
     * @dataProvider providerHeadingConversion
     */
    public function testHeadingConversion_withAttribute($level, $attributesHTML, $attributesMD = null)
    {
        $innerHTML = 'Heading ' . $level;
        $md = str_pad('', $level, '#') . ' ' . $innerHTML . $attributesMD;
        $html = '<h' . $level . $attributesHTML . '>' . $innerHTML . '</h' . $level . '>';
        $this->assertEquals($md, $this->converter->parseString($html));
    }

    public function providerHeadingConversion()
    {
        $attributes = [' id="idAttribute"', ' class=" class1  class2 "'];
        $data = [];
        for ($i = 1; $i <= 6; $i++) {
            $data[] = [$i, '', ''];
            $data[] = [$i, $attributes[0], ' {#idAttribute}'];
            $data[] = [$i, $attributes[1], ' {.class1.class2}'];
            $data[] = [$i, $attributes[0] . $attributes[1], ' {#idAttribute.class1.class2}'];
        }
        return $data;
    }


    /* LINK TEST METHODS
     *************************************************************************/
    public function providerLinkConversion()
    {
        $data = parent::providerLinkConversion();

        // Link with href + title + id attributes
        $data['url-title-id']['md'] = 'This is [an example][1]{#myLink} inline link.

 [1]: http://example.com/ "Title"';

        // Link with href + title + class attributes
        $data['url-title-class']['md'] = 'This is [an example][1]{.external} inline link.

 [1]: http://example.com/ "Title"';

        // Link with href + title + multiple classes attributes
        $data['url-title-multiple-class']['md'] = 'This is [an example][1]{.class1.class2} inline link.

 [1]: http://example.com/ "Title"';

        // Link with href + title + multiple classes attributes
        $data['url-title-multiple-class-id']['md'] = 'This is [an example][1]{#myLink.class1.class2} inline link.

 [1]: http://example.com/ "Title"';

        return $data;
    }


    /* TABLE TEST METHODS
     *************************************************************************/
    public function testTableConversion()
    {
        $html = <<<EOF
<table>
<thead>
<tr>
  <th>First Header</th>
  <th>Second Header</th>
</tr>
</thead>
<tbody>
<tr>
  <td>Content Cell</td>
  <td>Content Cell</td>
</tr>
<tr>
  <td> </td>
  <td>Content Cell</td>
</tr>
</tbody>
</table>
EOF;
        $md = <<<EOF
| First Header | Second Header |
| ------------ | ------------- |
| Content Cell | Content Cell  |
|              | Content Cell  |
EOF;
        $this->assertEquals($md, $this->converter->parseString($html));
    }

    public function testTableConversionWithEmptyCell()
    {
        $html = <<<EOF
<table>
<thead>
<tr>
  <th>First Header</th>
  <th>Second Header</th>
</tr>
</thead>
<tbody>
<tr>
  <td>Content Cell</td>
  <td>Content Cell</td>
</tr>
<tr>
  <td></td>
  <td>Content Cell</td>
</tr>
</tbody>
</table>
EOF;
        $md = <<<EOF
| First Header | Second Header |
| ------------ | ------------- |
| Content Cell | Content Cell  |
|              | Content Cell  |
EOF;
        $this->assertEquals($md, $this->converter->parseString($html));
    }
}
