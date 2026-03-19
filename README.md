# MyAdmin Webuzo VPS Plugin

[![Build Status](https://github.com/detain/myadmin-webuzo-vps/actions/workflows/tests.yml/badge.svg)](https://github.com/detain/myadmin-webuzo-vps/actions)
[![Latest Stable Version](https://poser.pugx.org/detain/myadmin-webuzo-vps/version)](https://packagist.org/packages/detain/myadmin-webuzo-vps)
[![Total Downloads](https://poser.pugx.org/detain/myadmin-webuzo-vps/downloads)](https://packagist.org/packages/detain/myadmin-webuzo-vps)
[![License](https://poser.pugx.org/detain/myadmin-webuzo-vps/license)](https://packagist.org/packages/detain/myadmin-webuzo-vps)

Webuzo VPS management plugin for the [MyAdmin](https://github.com/detain/myadmin) control panel framework. This package provides full integration with the Webuzo and Softaculous APIs, enabling automated provisioning, script installation, domain management, backup operations, and system application control on VPS servers.

## Features

- Automated Webuzo VPS configuration and initial setup (LAMP stack provisioning)
- Script installation, removal, import, and upgrade via the Softaculous/Webuzo SDK
- Domain management (add, remove, list)
- Backup creation, restoration, download, and removal
- System application installation and removal
- FTP user management, database management, DNS record management
- Cron job management, service control, and security settings
- Event-driven architecture using Symfony EventDispatcher

## Requirements

- PHP 8.2 or higher
- ext-soap
- ext-curl
- Symfony EventDispatcher 5.x, 6.x, or 7.x

## Installation

Install via Composer:

```sh
composer require detain/myadmin-webuzo-vps
```

## Usage

The plugin registers itself through the MyAdmin plugin system using Symfony event hooks. It exposes page requirements for each Webuzo feature through the `function.requirements` event.

```php
use Detain\MyAdminWebuzo\Plugin;

// Get registered hooks
$hooks = Plugin::getHooks();
// Returns: ['function.requirements' => [Plugin::class, 'getRequirements']]
```

## Running Tests

```sh
composer install
vendor/bin/phpunit
```

## License

This package is licensed under the [LGPL-2.1-only](https://www.gnu.org/licenses/old-licenses/lgpl-2.1.html) license.
