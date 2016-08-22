# Papi - Shortcake property

[Shortcake](https://github.com/wp-shortcake/shortcake) property for Papi.

## Installation

This property requires [Papi](https://wp-papi.github.io/) plugin.

If you're using Composer to manage WordPress, add Papi to your project's dependencies. Run:

```sh
composer require wp-papi/papi-property-shortcake
```

## Usage

```php
<?php

$this->box( 'Shortcake', [
    papi_property( [
        'sidebar'  => false,
        'title'    => 'Shortcake',
        'type'     => 'shortcake'
    ] )
] );
```

## License

MIT © [Fredrik Forsmo](https://github.com/frozzare)
