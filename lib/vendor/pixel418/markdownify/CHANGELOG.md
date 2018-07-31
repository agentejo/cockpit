CHANGELOG
==============


27/03/2018 v2.3.0
--------------

 * Support: Remove PHP5.3 & hhvm support
 * Fix: inline spacing and empty tag (#33)
 * Fix: aside element conversion (#33) 
 * License: use the MIT license for now 
 * Refactor: use brackets notation for array (#28) 


22/12/2017 v2.2.2
--------------

 * Fix: Allow to strip `<u>` tags (#25)


21/09/2016 v2.2.1
--------------

 * Fix: Moving trailing whitespace from inline elements outside of the element
 * Feature: Use PSR-4
 * Feature: PHP 7.0 support in continuous integration
 * Doc: Update of the README


07/09/2016 v2.2.0
--------------

 * Fix: Reset state between each parsing


19/02/2016 v2.1.11
--------------

 * Fix: Empty table cell conversion


10/02/2016 v2.1.10
--------------

 * Fix: Handle nested table.


01/04/2015 v2.1.9
--------------

 * Fix: Handle HTML breaks & spaces in a less destructive way.


26/03/2015 v2.1.8
--------------

 * Fix: Use alternative italic character
 * Fix: Handle HTML breaks inside another tag
 * Fix: Handle HTML spaces around tags


07/11/2014 v2.1.7
--------------

 * Change composer name to "elephant418/markdownify"


14/07/2014 v2.1.6
--------------

 * Fix: Simulate a paragraph for inline text preceding block element
 * Fix: Nested lists
 * Fix: setKeepHTML method
 * Feature: PHP 5.5 & 5.6 support in continuous integration


16/03/2014 v2.1.5
--------------

Add display settings

 * Test: Add tests for footnotes after every paragraph or not
 * Feature: Allow to display link reference in paragraph, without footnotes


27/02/2014 v2.1.4
--------------

Improve how ConverterExtra handle id & class attributes:

 * Feature: Allow id & class attributes on links
 * Feature: Allow class attributes on headings