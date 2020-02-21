# SimpleImage

A PHP class that makes working with images as simple as possible.

Developed and maintained by [Cory LaViska](https://github.com/claviska).

_If this project has you loving PHP image manipulation again, please consider making [a small donation](https://paypal.me/claviska) to support its development._

---

## Overview

```php
<?php
try {
  // Create a new SimpleImage object
  $image = new \claviska\SimpleImage();

  // Magic! ✨
  $image
    ->fromFile('image.jpg')                     // load image.jpg
    ->autoOrient()                              // adjust orientation based on exif data
    ->resize(320, 200)                          // resize to 320x200 pixels
    ->flip('x')                                 // flip horizontally
    ->colorize('DarkBlue')                      // tint dark blue
    ->border('black', 10)                       // add a 10 pixel black border
    ->overlay('watermark.png', 'bottom right')  // add a watermark image
    ->toFile('new-image.png', 'image/png')      // convert to PNG and save a copy to new-image.png
    ->toScreen();                               // output to the screen

  // And much more! 💪
} catch(Exception $err) {
  // Handle errors
  echo $err->getMessage();
}
```

## Requirements

- PHP 5.6+
- [GD extension](http://php.net/manual/en/book.image.php)

## Features

- Supports reading, writing, and converting GIF, JPEG, PNG, WEBP, BMP formats.
- Reads and writes files, data URIs, and image strings.
- Manipulation: crop, resize, overlay/watermark, adding TTF text
- Drawing: arc, border, dot, ellipse, line, polygon, rectangle, rounded rectangle
- Filters: blur, brighten, colorize, contrast, darken, desaturate, edge detect, emboss, invert, opacity, pixelate, sepia, sharpen, sketch
- Utilities: color adjustment, darken/lighten color, extract colors
- Properties: exif data, height/width, mime type, orientation
- Color arguments can be passed in as any CSS color (e.g. `LightBlue`), a hex color, or an RGB(A) array.
- Support for alpha-transparency (GIF, PNG, WEBP)
- Chainable methods
- Uses exceptions
- Load with Composer or manually (just one file)
- [Semantic Versioning](http://semver.org/)

## Installation

Install with Composer:

```
composer require claviska/simpleimage
```

Or include the library manually:

```php
<?php
require 'src/claviska/SimpleImage.php';
```

## About

SimpleImage is developed and maintained by [Cory LaViska](https://github.com/claviska). Copyright A Beautiful Site, LLC.

Contributions are appreciated! If you enjoy using SimpleImage, especially in commercial applications, please consider [making a contribution](https://paypal.me/claviska) to support its development.

Thanks! 🙌

## License

Licensed under the [MIT license](http://opensource.org/licenses/MIT).

## API

Order of awesomeness:

1. Load an image
2. Manipulate the image
3. Save/output the image

API tips:

- An asterisk denotes a required argument.
- Methods that return a SimpleImage object are chainable.
- You can pass a file or data URI to the constructor to avoid calling `fromFile` or `fromDataUri`.
- Static methods can be called with `$image::methodName()` or `\claviska\SimpleImage::methodName()`.
- Colors can be a CSS color (e.g. `white`), a hex string (e.g. '#ffffff'), or an RGBA array.
- You can pipe transparency to `normalizeColor` when you pass a CSS color or hex string: `white|0.25`

### Loaders

#### `fromDataUri($uri)`

Loads an image from a data URI.

- `$uri`* (string) - A data URI.

Returns a SimpleImage object.

#### `fromFile($file)`

Loads an image from a file.

- `$file`* (string) - The image file to load.

Returns a SimpleImage object.

#### `fromNew($width, $height, $color)`

Creates a new image.

- `$width`* (int) - The width of the image.
- `$height`* (int) - The height of the image.
- `$color` (string|array) - Optional fill color for the new image (default 'transparent').

Returns a SimpleImage object.

#### `fromString($string)`

Creates a new image from a string.

- `$string`* (string) - The raw image data as a string. Example:
  ```
  $string = file_get_contents('image.jpg');
  ```

Returns a SimpleImage object.

### Savers

#### `toDataUri($mimeType, $quality)`

Generates a data URI.

- `$mimeType` (string) - The image format to output as a mime type (defaults to the original mime type).
- `$quality` (int) - Image quality as a percentage (default 100).

Returns a string containing a data URI.

#### `toDownload($filename, $mimeType, $quality)`

Forces the image to be downloaded to the clients machine. Must be called before any output is sent to the screen.

- `$filename`* (string) - The filename (without path) to send to the client (e.g. 'image.jpeg').
- `$mimeType` (string) - The image format to output as a mime type (defaults to the original mime type).
- `$quality` (int) - Image quality as a percentage (default 100).

Returns a SimpleImage object.

#### `toFile($file, $mimeType, $quality)`

Writes the image to a file.

- `$mimeType` (string) - The image format to output as a mime type (defaults to the original mime type).
- `$quality` (int) - Image quality as a percentage (default 100).

Returns a SimpleImage object.

#### `toScreen($mimeType, $quality)`

Outputs the image to the screen. Must be called before any output is sent to the screen.

- `$mimeType` (string) - The image format to output as a mime type (defaults to the original mime type).
- `$quality` (int) - Image quality as a percentage (default 100).

Returns a SimpleImage object.

#### `toString($mimeType, $quality)`

Generates an image string.

- `$mimeType` (string) - The image format to output as a mime type (defaults to the original mime type).
- `$quality` (int) - Image quality as a percentage (default 100).

Returns a SimpleImage object.

### Utilities

#### `getAspectRatio()`

Gets the image's current aspect ratio.

Returns the aspect ratio as a float.

#### `getExif()`

Gets the image's exif data.

Returns an array of exif data or null if no data is available.

#### `getHeight()`

Gets the image's current height.

Returns the height as an integer.

#### `getMimeType()`

Gets the mime type of the loaded image.

Returns a mime type string.

#### `getOrientation()`

Gets the image's current orientation.

Returns a string: 'landscape', 'portrait', or 'square'

#### `getResolution()`

Gets the image's current resolution in DPI.

Returns an array of integers: [0 => 96, 1 => 96]

#### `getWidth()`

Gets the image's current width.

Returns the width as an integer.

### Manipulation

#### `autoOrient()`

Rotates an image so the orientation will be correct based on its exif data. It is safe to call this method on images that don't have exif data (no changes will be made).
Returns a SimpleImage object.

#### `bestFit($maxWidth, $maxHeight)`

Proportionally resize the image to fit inside a specific width and height.

- `$maxWidth`* (int) - The maximum width the image can be.
- `$maxHeight`* (int) - The maximum height the image can be.

Returns a SimpleImage object.

#### `crop($x1, $y1, $x2, $y2)`

Crop the image.

- $x1 - Top left x coordinate.
- $y1 - Top left y coordinate.
- $x2 - Bottom right x coordinate.
- $y2 - Bottom right x coordinate.

Returns a SimpleImage object.

#### `fitToHeight($height)` (DEPRECATED)

Proportionally resize the image to a specific height.

_This method was deprecated in version 3.2.2 and will be removed in version 4.0. Please use `resize(null, $height)` instead._

- `$height`* (int) - The height to resize the image to.

Returns a SimpleImage object.

#### `fitToWidth($width)`  (DEPRECATED)

Proportionally resize the image to a specific width.

_This method was deprecated in version 3.2.2 and will be removed in version 4.0. Please use `resize($width, null)` instead._

- `$width`* (int) - The width to resize the image to.

Returns a SimpleImage object.

#### `flip($direction)`

Flip the image horizontally or vertically.

- `$direction`* (string) - The direction to flip: x|y|both

Returns a SimpleImage object.

#### `maxColors($max, $dither)`

Reduces the image to a maximum number of colors.

- `$max`* (int) - The maximum number of colors to use.
- `$dither` (bool) - Whether or not to use a dithering effect (default true).

Returns a SimpleImage object.

#### `overlay($overlay, $anchor, $opacity, $xOffset, $yOffset)`

Place an image on top of the current image.

- `$overlay`* (string|SimpleImage) - The image to overlay. This can be a filename, a data URI, or a SimpleImage object.
- `$anchor` (string) - The anchor point: 'center', 'top', 'bottom', 'left', 'right', 'top left', 'top right', 'bottom left', 'bottom right' (default 'center')
- `$opacity` (float) - The opacity level of the overlay 0-1 (default 1).
- `$xOffset` (int) - Horizontal offset in pixels (default 0).
- `$yOffset` (int) - Vertical offset in pixels (default 0).

Returns a SimpleImage object.

#### `resize($width, $height)`

Resize an image to the specified dimensions. If only one dimension is specified, the image will be resized proportionally.

- `$width`* (int) - The new image width.
- `$height`* (int) - The new image height.

Returns a SimpleImage object.

#### `resolution($res_x, $res_y)`

Changes the resolution (DPI) of an image.

- `$res_x`* (int) - The horizontal resolution, in DPI.
- `$res_y` (int) - The vertical resolution, in DPI.

Returns a SimpleImage object.

#### `rotate($angle, $backgroundColor)`

Rotates the image.

- `$angle`* (int) - The angle of rotation (-360 - 360).
- `$backgroundColor` (string|array) - The background color to use for the uncovered zone area after rotation (default 'transparent').

Returns a SimpleImage object.

#### `text($text, $options, &$boundary)`

Adds text to the image.

- `$text*` (string) - The desired text.
- `$options` (array) - An array of options.
  - `fontFile`* (string) - The TrueType (or compatible) font file to use.
  - `size` (int) - The size of the font in pixels (default 12).
  - `color` (string|array) - The text color (default black).
  - `anchor` (string) - The anchor point: 'center', 'top', 'bottom', 'left', 'right',
    'top left', 'top right', 'bottom left', 'bottom right' (default 'center').
  - `xOffset` (int) - The horizontal offset in pixels (default 0).
  - `yOffset` (int) - The vertical offset in pixels (default 0).
  - `shadow` (array) - Text shadow params.
    - `x`* (int) - Horizontal offset in pixels.
    - `y`* (int) - Vertical offset in pixels.
    - `color`* (string|array) - The text shadow color.
- `$boundary` (array) - If passed, this variable will contain an array with coordinates that
  surround the text: [x1, y1, x2, y2, width, height]. This can be used for calculating the
  text's position after it gets added to the image.

Returns a SimpleImage object.

#### `thumbnail($width, $height, $anchor)`

Creates a thumbnail image. This function attempts to get the image as close to the provided dimensions as possible, then crops the remaining overflow to force the desired size. Useful for generating thumbnail images.

- `$width`* (int) - The thumbnail width.
- `$height`* (int) - The thumbnail height.
- `$anchor` (string) - The anchor point: 'center', 'top', 'bottom', 'left', 'right', 'top left', 'top right', 'bottom left', 'bottom right' (default 'center').

Returns a SimpleImage object.

###  Drawing

#### `arc($x, $y, $width, $height, $start, $end, $color, $thickness)`

Draws an arc.

- `$x`* (int) - The x coordinate of the arc's center.
- `$y`* (int) - The y coordinate of the arc's center.
- `$width`* (int) - The width of the arc.
- `$height`* (int) - The height of the arc.
- `$start`* (int) - The start of the arc in degrees.
- `$end`* (int) - The end of the arc in degrees.
- `$color`* (string|array) - The arc color.
- `$thickness` (int|string) - Line thickness in pixels or 'filled' (default 1).

Returns a SimpleImage object.

#### `border($color, $thickness)`

Draws a border around the image.

- `$color`* (string|array) - The border color.
- `$thickness` (int) - The thickness of the border (default 1).

Returns a SimpleImage object.

#### `dot($x, $y, $color)`

Draws a single pixel dot.

- `$x`* (int) - The x coordinate of the dot.
- `$y`* (int) - The y coordinate of the dot.
- `$color`* (string|array) - The dot color.

Returns a SimpleImage object.

#### `ellipse($x, $y, $width, $height, $color, $thickness)`

Draws an ellipse.

- `$x`* (int) - The x coordinate of the center.
- `$y`* (int) - The y coordinate of the center.
- `$width`* (int) - The ellipse width.
- `$height`* (int) - The ellipse height.
- `$color`* (string|array) - The ellipse color.
- `$thickness` (int|string) - Line thickness in pixels or 'filled' (default 1).

Returns a SimpleImage object.

#### `fill($color)`

Fills the image with a solid color.

- `$color` (string|array) - The fill color.

Returns a SimpleImage object.

#### `line($x1, $y1, $x2, $y2, $color, $thickness)`

Draws a line.

- `$x1`* (int) - The x coordinate for the first point.
- `$y1`* (int) - The y coordinate for the first point.
- `$x2`* (int) - The x coordinate for the second point.
- `$y2`* (int) - The y coordinate for the second point.
- `$color` (string|array) - The line color.
- `$thickness` (int) - The line thickness (default 1).

Returns a SimpleImage object.

#### `polygon($vertices, $color, $thickness)`

Draws a polygon.

- `$vertices`* (array) - The polygon's vertices in an array of x/y arrays. Example:
  ```
  [
    ['x' => x1, 'y' => y1],
    ['x' => x2, 'y' => y2],
    ['x' => xN, 'y' => yN]
  ]
  ```
- `$color`* (string|array) - The polygon color.
- `$thickness` (int|string) - Line thickness in pixels or 'filled' (default 1).

Returns a SimpleImage object.

#### `rectangle($x1, $y1, $x2, $y2, $color, $thickness)`

Draws a rectangle.

- `$x1`* (int) - The upper left x coordinate.
- `$y1`* (int) - The upper left y coordinate.
- `$x2`* (int) - The bottom right x coordinate.
- `$y2`* (int) - The bottom right y coordinate.
- `$color`* (string|array) - The rectangle color.
- `$thickness` (int|string) - Line thickness in pixels or 'filled' (default 1).

Returns a SimpleImage object.

#### `roundedRectangle($x1, $y1, $x2, $y2, $radius, $color, $thickness)`

Draws a rounded rectangle.

- `$x1`* (int) - The upper left x coordinate.
- `$y1`* (int) - The upper left y coordinate.
- `$x2`* (int) - The bottom right x coordinate.
- `$y2`* (int) - The bottom right y coordinate.
- `$radius`* (int) - The border radius in pixels.
- `$color`* (string|array) - The rectangle color.
- `$thickness` (int|string) - Line thickness in pixels or 'filled' (default 1).

Returns a SimpleImage object.

### Filters

#### `blur($type, $passes)`

Applies the blur filter.

- `$type` (string) - The blur algorithm to use: 'selective', 'gaussian' (default 'gaussian').
- `$passes` (int) - The number of time to apply the filter, enhancing the effect (default 1).

Returns a SimpleImage object.

#### `brighten($percentage)`

Applies the brightness filter to brighten the image.

- `$percentage`* (int) - Percentage to brighten the image (0 - 100).

Returns a SimpleImage object.

#### `colorize($color)`

Applies the colorize filter.

- `$color`* (string|array) - The filter color.

Returns a SimpleImage object.

#### `contrast($percentage)`

Applies the contrast filter.

- `$percentage`* (int) - Percentage to adjust (-100 - 100).

Returns a SimpleImage object.

#### `darken($percentage)`

Applies the brightness filter to darken the image.

- `$percentage`* (int) - Percentage to darken the image (0 - 100).

Returns a SimpleImage object.

#### `desaturate()`

Applies the desaturate (grayscale) filter.

Returns a SimpleImage object.

#### `duotone($lightColor, $darkColor)`

Applies the duotone filter to the image.

- `$lightColor`* (string|array) - The lightest color in the duotone.
- `$darkColor`* (string|array) - The darkest color in the duotone.

Returns a SimpleImage object.

#### `edgeDetect()`

Applies the edge detect filter.

Returns a SimpleImage object.

#### `emboss()`

Applies the emboss filter.

Returns a SimpleImage object.

#### `invert()`

Inverts the image's colors.

Returns a SimpleImage object.

#### `opacity()`

Changes the image's opacity level.

- `$opacity`* (float) - The desired opacity level (0 - 1).

Returns a SimpleImage object.

#### `pixelate($size)`

Applies the pixelate filter.

- `$size` (int) - The size of the blocks in pixels (default 10).

Returns a SimpleImage object.

#### `sepia()`

Simulates a sepia effect by desaturating the image and applying a sepia tone.

Returns a SimpleImage object.

#### `sharpen($amount)`

Sharpens the image.

- `$amount` (int) - Sharpening amount (1 - 100, default 50)

Returns a SimpleImage object.

#### `sketch()`

Applies the mean remove filter to produce a sketch effect.

Returns a SimpleImage object.

### Color utilities

#### `(static) adjustColor($color, $red, $green, $blue, $alpha)`

Adjusts a color by increasing/decreasing red/green/blue/alpha values independently.

- `$color`* (string|array) - The color to adjust.
- `$red`* (int) - Red adjustment (-255 - 255).
- `$green`* (int) - Green adjustment (-255 - 255).
- `$blue`* (int) - Blue adjustment (-255 - 255).
- `$alpha`* (float) - Alpha adjustment (-1 - 1).

Returns an RGBA color array.

#### `(static) darkenColor($color, $amount)`

Darkens a color.

- `$color`* (string|array) - The color to darken.
- `$amount`* (int) - Amount to darken (0 - 255).

Returns an RGBA color array.

#### `extractColors($count = 10, $backgroundColor = null)`

Extracts colors from an image like a human would do.™ This method requires the third-party library \League\ColorExtractor. If you're using Composer, it will be installed for you automatically.

- `$count` (int) - The max number of colors to extract (default 5).
- `$backgroundColor` (string|array) - By default any pixel with alpha value greater than zero will be discarded. This is because transparent colors are not perceived as is. For example, fully transparent black would be seen white on a white background. So if you want to take transparency into account, you have to specify a default background color.

Returns an array of RGBA colors arrays.

#### `getColorAt($x, $y)`

Gets the RGBA value of a single pixel.

- `$x`* (int) - The horizontal position of the pixel.
- `$y`* (int) - The vertical position of the pixel.

Returns an RGBA color array or false if the x/y position is off the canvas.

#### `(static) lightenColor($color, $amount)`

Lightens a color.

- `$color`* (string|array) - The color to lighten.
- `$amount`* (int) - Amount to darken (0 - 255).

Returns an RGBA color array.

#### `(static) normalizeColor($color)`

Normalizes a hex or array color value to a well-formatted RGBA array.

- `$color`* (string|array) - A CSS color name, hex string, or an array [red, green, blue, alpha].

You can pipe alpha transparency through hex strings and color names. For example:

  #fff|0.50 <-- 50% white
  red|0.25 <-- 25% red

Returns an array: [red, green, blue, alpha]

### Exceptions

SimpleImage throws standard exceptions when things go wrong. You should always use a try/catch block around your code to properly handle them.

```php
<?php
try {
  $image = new \claviska\SimpleImage('image.jpeg')
  // ...
} catch(Exception $err) {
  echo $err->getMessage();
}
```

To check for specific errors, compare `$err->getCode()` to the defined error constants.

```php
<?php
try {
  $image = new \claviska\SimpleImage('image.jpeg')
  // ...
} catch(Exception $err) {
  if($err->getCode() === $image::ERR_FILE_NOT_FOUND) {
    echo 'File not found!';
  } else {
    echo $err->getMessage();
  }
}
```

As a best practice, always use the defined constants instead of their integers values. The values will likely change in future versions, and WILL NOT be considered a breaking change.

- `ERR_FILE_NOT_FOUND` - The specified file could not be found or loaded for some reason.
- `ERR_FONT_FILE` - The specified font file could not be loaded.
- `ERR_FREETYPE_NOT_ENABLED` - Freetype support is not enabled in your version of PHP.
- `ERR_GD_NOT_ENABLED` - The GD extension is not enabled in your version of PHP.
- `ERR_LIB_NOT_LOADED` - A required library has not been loaded.
- `ERR_INVALID_COLOR` - An invalid color value was passed as an argument.
- `ERR_INVALID_DATA_URI` - The specified data URI is not valid.
- `ERR_INVALID_IMAGE` - The specified image is not valid.
- `ERR_UNSUPPORTED_FORMAT` - The image format specified is not valid.
- `ERR_WEBP_NOT_ENABLED` - WEBP support is not enabled in your version of PHP.
- `ERR_WRITE` - Unable to write to the file system.

### Useful Things To Know

- Color arguments can be a CSS color name (e.g. `LightBlue`), a hex color string (e.g. `#0099dd`), or an RGB(A) array (e.g. `['red' => 255, 'green' => 0, 'blue' => 0, 'alpha' => 1]`).

- When `$thickness` > 1, GD draws lines of the desired thickness from the center origin. For example, a rectangle drawn at [10, 10, 20, 20] with a thickness of 3 will actually be draw at [9, 9, 21, 21]. This is true for all shapes and is not a bug in the SimpleImage library.

## Differences from SimpleImage 2.x

- Normalized color arguments (colors can be a CSS color name, hex color, or RGB(A) array).
- Normalized alpha (opacity) arguments: 0 (transparent) - 1 (opaque)
- Added text shadow to `text` method.
- Added `fromString()` method to load images from strings.
- Added `toString()` method to generate image strings.
- Added `arc` method for drawing arcs.
- Added `border` method for drawing borders.
- Added `dot` method for drawing individual pixels.
- Added `ellipse` method for drawing ellipses and circles.
- Added `line` method for drawing lines.
- Added `polygon` method for drawing polygons.
- Added `rectangle` method for drawing rectangles.
- Added `roundedRectangle` method for drawing rounded rectangles.
- Added `adjustColor` method for modifying RGBA color channels to create relative color variations.
- Added `darkenColor` method to darken a color.
- Added `extractColors` method to get the most common colors from the image.
- Added `getColorAt` method to get the RGBA values of a specific pixel.
- Added `lightenColor` method to lighten a color.
- Added `toDownload` method to force the image to download on the client's machine.
- Added `duotone` filter to create duotone images.
- Added `sharpen` method to sharpen the image.
- Changed namespace from `abeautifulsite` to `claviska`.
- Changed `create` method to `fromNew`.
- Changed `load` method to `fromFile`.
- Changed `load_base64` method to `fromDataUri`.
- Changed `output` method to `toScreen`.x
- Changed `output_base64` method to `toDataUri`.
- Changed `save` method to `toFile`.
- Changed `text` method to accept an array of options instead of tons of arguments.
- Removed text stroke from `text` method because it produced dirty results and didn't support transparency.
- Removed `smooth` method because its arguments in the PHP manual aren't documented well.
- Removed deprecated method `adaptive_resize` (use `thumbnail` instead).
- Removed `get_meta_data` (use `getExif`, `getHeight`, `getMime`, `getOrientation`, and `getWidth` instead).
- Added [.editorconfig](http://editorconfig.org/) file. Please make sure your editor supports these settings before submitting contributions.
- Switched from four spaces to two for indentations (sorry PHP-FIG!).
- Switched from underscore_methods to camelCaseMethods.
- Organized methods into groups based on function
- Removed PHPDoc comments. At this time, I don't wish to incorporate them into the library.
