# Cockpit Next

[![Backers on Open Collective](https://opencollective.com/cockpit/backers/badge.svg)](#backers) [![Sponsors on Open Collective](https://opencollective.com/cockpit/sponsors/badge.svg)](#sponsors) [![Gitter](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/aheinze/cockpit)

* Homepage: [http://getcockpit.com](http://getcockpit.com)
* Twitter: [@getcockpit](http://twitter.com/getcockpit)


### Requirements

* PHP >= 7.0
* PDO + SQLite (or MongoDB)
* GD extension
* mod_rewrite enabled (on apache)

make also sure that <code>$_SERVER['DOCUMENT_ROOT']</code> exists and is set correctly.


### Installation

1. Download Cockpit and put the cockpit folder in the root of your web project
2. Make sure that the __/cockpit/storage__ folder and all its subfolders are writable
3. Go to __/cockpit/install__ via Browser
4. You're ready to use Cockpit :-)


### Build (Only if you modify JS components)

You need [nodejs](https://nodejs.org/) installed on your system.

First run `npm install` to install development dependencies

1. Run `npm run build` - For one-time build of styles and components
2. Run `npm run watch` - For continuous build every time styles or components change

### Dockerized Development

You need docker installed on your system: https://www.docker.com.

1. Run `npm run docker-init` to build the initial image.
2. Run `npm run docker` to start an Apache environment suited for Cockpit on port 8080 (this folder mapped to /var/www/html).


## Backers

Thank you to all our backers! 🙏 [[Become a backer](https://opencollective.com/cockpit#backer)]

<a href="https://opencollective.com/cockpit#backers" target="_blank"><img src="https://opencollective.com/cockpit/backers.svg?width=890"></a>


## Sponsors

Support this project by becoming a sponsor. Your logo will show up here with a link to your website. [[Become a sponsor](https://opencollective.com/cockpit#sponsor)]

<a href="https://opencollective.com/cockpit/sponsor/0/website" target="_blank"><img src="https://opencollective.com/cockpit/sponsor/0/avatar.svg"></a>
<a href="https://opencollective.com/cockpit/sponsor/1/website" target="_blank"><img src="https://opencollective.com/cockpit/sponsor/1/avatar.svg"></a>
<a href="https://opencollective.com/cockpit/sponsor/2/website" target="_blank"><img src="https://opencollective.com/cockpit/sponsor/2/avatar.svg"></a>
<a href="https://opencollective.com/cockpit/sponsor/3/website" target="_blank"><img src="https://opencollective.com/cockpit/sponsor/3/avatar.svg"></a>
<a href="https://opencollective.com/cockpit/sponsor/4/website" target="_blank"><img src="https://opencollective.com/cockpit/sponsor/4/avatar.svg"></a>
<a href="https://opencollective.com/cockpit/sponsor/5/website" target="_blank"><img src="https://opencollective.com/cockpit/sponsor/5/avatar.svg"></a>
<a href="https://opencollective.com/cockpit/sponsor/6/website" target="_blank"><img src="https://opencollective.com/cockpit/sponsor/6/avatar.svg"></a>
<a href="https://opencollective.com/cockpit/sponsor/7/website" target="_blank"><img src="https://opencollective.com/cockpit/sponsor/7/avatar.svg"></a>
<a href="https://opencollective.com/cockpit/sponsor/8/website" target="_blank"><img src="https://opencollective.com/cockpit/sponsor/8/avatar.svg"></a>
<a href="https://opencollective.com/cockpit/sponsor/9/website" target="_blank"><img src="https://opencollective.com/cockpit/sponsor/9/avatar.svg"></a>



### Copyright and license

Copyright 2015 [Agentejo](http://www.agentejo.com) under the MIT license.

The MIT License (MIT)

Copyright (c) 2015 Agentejo, http://agentejo.com

Permission is hereby granted, free of charge, to any person obtaining a copy of
this software and associated documentation files (the "Software"), to deal in
the Software without restriction, including without limitation the rights to
use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
the Software, and to permit persons to whom the Software is furnished to do so,
subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

### 💐 SPONSORED BY


[![ginetta](https://user-images.githubusercontent.com/321047/29219315-f1594924-7eb7-11e7-9d58-4dcf3f0ad6d6.png)](https://www.ginetta.net)<br>
We create websites and apps that click with users.


[![BrowserStack](https://user-images.githubusercontent.com/355427/27389060-9f716c82-569d-11e7-923c-bd5fe7f1c55a.png)](https://www.browserstack.com)<br>
Live, Web-Based Browser Testing
