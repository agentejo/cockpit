# Markdownify

[![Build Status](https://travis-ci.org/Elephant418/Markdownify.png?branch=master)](https://travis-ci.org/Elephant418/Markdownify?branch=master)
[![Total Downloads](https://poser.pugx.org/pixel418/markdownify/downloads)](https://packagist.org/packages/pixel418/markdownify)
[![MIT](https://poser.pugx.org/pixel418/markdownify/license)](https://opensource.org/licenses/MIT)

The HTML to Markdown converter for PHP

[Code example](#code-example) | [How to Install](#how-to-install) | [How to Contribute](#how-to-contribute) | [Author & Community](#author--community)



Code example
--------

### Markdown

```php
$converter = new Markdownify\Converter;
$converter->parseString('<h1>Heading</h1>');
// Returns: # Heading
```

### Markdown Extra [as defined by @michelf](http://michelf.ca/projects/php-markdown/extra/)

```php
$converter = new Markdownify\ConverterExtra;
$converter->parseString('<h1 id="md">Heading</h1>');
// Returns: # Heading {#md}
```



How to Install
--------

This library package requires `PHP 5.4` or later.<br>
Install [Composer](http://getcomposer.org/doc/01-basic-usage.md#installation) and run the following command to get the latest version:

```sh
composer require pixel418/markdownify
```



How to Contribute
--------

1. Fork the Markdownify repository
2. Create a new branch for each feature or improvement
3. Send a pull request from each feature branch to the **v2.x** branch

If you don't know much about pull request, you can read [the Github article](https://help.github.com/articles/using-pull-requests)



Author & Community
--------

Markdownify is under [MIT License](https://opensource.org/licenses/MIT)<br>
It was created by [Milian Wolff](http://milianw.de)<br>
It was converted to a Symfony Bundle by [Peter Kruithof](https://github.com/pkruithof)<br>
It is maintained by [Thomas ZILLIOX](https://tzi.fr)
