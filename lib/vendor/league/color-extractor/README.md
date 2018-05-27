ColorExtractor
==============

[![Build Status](https://travis-ci.org/thephpleague/color-extractor.png?branch=master)](https://travis-ci.org/thephpleague/color-extractor)
[![Total Downloads](https://poser.pugx.org/league/color-extractor/downloads.png)](https://packagist.org/packages/league/color-extractor)
[![Latest Stable Version](https://poser.pugx.org/league/color-extractor/v/stable.png)](https://packagist.org/packages/league/color-extractor)

Extract colors from an image like a human would do.

## Install

Via Composer

``` bash
$ composer require league/color-extractor:0.3.*
```

## Usage

```php
require 'vendor/autoload.php';

use League\ColorExtractor\Color;
use League\ColorExtractor\ColorExtractor;
use League\ColorExtractor\Palette;

$palette = Palette::fromFilename('./some/image.png');

// $palette is an iterator on colors sorted by pixel count
foreach($palette as $color => $count) {
    // colors are represented by integers
    echo Color::fromIntToHex($color), ': ', $count, "\n";
}

// it offers some helpers too
$topFive = $palette->getMostUsedColors(5);

$colorCount = count($palette);

$blackCount = $palette->getColorCount(Color::fromHexToInt('#000000'));


// an extractor is built from a palette
$extractor = new ColorExtractor($palette);

// it defines an extract method which return the most “representative” colors
$colors = $extractor->extract(5);

```

## Handling transparency

By default **any pixel with alpha value greater than zero will be discarded**. This is because transparent colors are not perceived
as is. For exemple fully transparent black would be seen white on a white background. So if you want to take transparency into account
when building a palette you have to specify this background color. You can do this with the second argument of `Palette` constructors.
Its default value is `null`, meaning a color won't be added to the palette if its alpha component exists and is greater than zero.

You can set it as an integer representing the color, then transparent colors will be blended before addition to the palette.

```php
// we set a white background so fully transparent colors will be added as white in the palette
// pure red #FF0000 at 50% opacity will be stored as #FF8080 as it would be perceived
$palette = Palette::fromFilename('./some/image.png', Color::fromHexToInt('#FFFFFF'));
```

## Contributing

Please see [CONTRIBUTING](https://github.com/thephpleague/color-extractor/blob/master/CONTRIBUTING.md) for details.


## Credits

- [Mathieu Lechat](https://github.com/MatTheCat)
- [All Contributors](https://github.com/thephpleague/color-extractor/contributors)


## License

The MIT License (MIT). Please see [License File](https://github.com/thephpleague/color-extractor/blob/master/LICENSE) for more information.
