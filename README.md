# Component Library Module

Adds support for a Twig Template loader to use for component library templates.

Component Library templates are defined in the generated `components-map.json` file,
at the path hard coded in the module:

```
$componentMapPath = CRAFT_BASE_PATH . '/components-map.json';
```

See `USChamber\ComponentLibrary\twig\ComponentLibraryLoader`

## Installation

The Component Library is a private module and requires a few steps to add to a project:

1. Add the module to your project via a vcs repository in `composer.json`
2. Add the module to the `config/app.php` file
3. Add the Github token in the `COMPOSER_AUTH` environment variable

### Composer

Private repositories require a GitHub token in order to include it via composer. Add the following to your `composer.json` file:

``` json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/USChamber/component-library"
        }
    ],
    "require": {
        "uschamber/component-library": "^3.0",
    }
}
```

### Load Module

Load the module in the `config/app.php` file:

``` php
<?php

return [
    'modules' => [
        'component-library' => \USChamber\ComponentLibrary\ComponentLibrary::class,
    ],
    'bootstrap' => [
        'component-library'
    ],
];
```

### Composer Auth

[This article](https://dudi.dev/composer-private-packages-github-repository) describes what is needed to authenticate a private repository. We're using Option 1 _Authorizing composer using `auth.json`
file_.

We're using `COMPOSER_AUTH` to keep the creditials out of the repo as described in
the [composer docs](https://getcomposer.org/doc/articles/authentication-for-private-packages.md#authentication-using-the-composer-auth-environment-variable).