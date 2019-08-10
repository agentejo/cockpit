# Cockpit Next

* Homepage: [http://getcockpit.com](https://getcockpit.com)
* Twitter: [@getcockpit](http://twitter.com/getcockpit)
* Support Forum: [https://discourse.getcockpit.com](https://discourse.getcockpit.com)


### Requirements

* PHP >= 7.0
* PDO + SQLite (or MongoDB)
* GD extension
* mod_rewrite, mod_versions enabled (on apache)

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


## 💐 Partners

[![ginetta](https://user-images.githubusercontent.com/321047/62825759-0fc9ce00-bbb1-11e9-866a-3148260e1548.png)](https://www.ginetta.net)<br>
We create websites and apps that click with users.


[![BrowserStack](https://user-images.githubusercontent.com/355427/27389060-9f716c82-569d-11e7-923c-bd5fe7f1c55a.png)](https://www.browserstack.com)<br>
Live, Web-Based Browser Testing


## 💐 Sponsors

[![Backers on Open Collective](https://opencollective.com/cockpit/backers/badge.svg)](#backers) [![Sponsors on Open Collective](https://opencollective.com/cockpit/sponsors/badge.svg)](#sponsors)

Become a backer or sponsor through:

- [Patreon](https://www.patreon.com/aheinze)
- [OpenCollective](https://opencollective.com/cockpit#backer)

Thank you to all our backers! 🙏


## Copyright and license

Copyright since 2015 [Agentejo](https://agentejo.com) under the MIT license.

See [LICENSE](LICENSE) for more information.
