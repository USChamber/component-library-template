# Component Library Module

I suppose most people will use this as a resource rather than forking the repo and using it as-is, 
so I'll do my best to explain the parts that can be useful.

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
            "url": "https://github.com/USChamber/component-library-template"
        }
    ],
    "require": {
        "uschamber/component-library-template": "^3.0",
    }
}
```

### Load Module

Load the module in the `config/app.php` file:

``` php
<?php

return [
    'modules' => [
        'component-library' => ComponentLibrary::class,
    ],
    'bootstrap' => [
        'component-library'
    ],
];
```

Make sure to run `ddev composer dumpautoload` after adding this

### Composer Auth

[This article](https://dudi.dev/composer-private-packages-github-repository) describes what is needed to authenticate a private repository. We're using Option 1 _Authorizing composer using `auth.json`
file_.

We're using `COMPOSER_AUTH` to keep the credentials out of the repo as described in
the [composer docs](https://getcomposer.org/doc/articles/authentication-for-private-packages.md#authentication-using-the-composer-auth-environment-variable).

## Features

### Custom Twig Loader

Adds support for a Twig Template loader to use for component library templates.

Component Library templates are defined in the generated `components-map.json` file,
at the path hard coded in the module:

```
$componentMapPath = CRAFT_BASE_PATH . '/components-map.json';
```

See `./twig/ComponentLibraryLoader.php`

### Component Library Viewer

### Formatters (not covered in conference talk)

This is kind of a whole other beast that I couldn't cover in my talk, but we actually keep 
not only the component templates in this module but also the business logic used to transform 
the data into the format needed by the templates.

This is somewhat like an ETL process in data warehousing, but for components. So basically we "extract"
the data from the source, "transform" it into the format needed by the component, and then "load" it into
the component template.

We pretty exclusively use Vizy to handle our content, so we have a formatter dedicated to each Vizy block type.

An example of these formatters is found in `./src/formatters/blocks/AccordionBlock.php`

These can also be overridden in the project utilizing the component library. The best way to override them is to
create a new formatter in the project that extends the formatter in the component library. Then you can override
the methods you need to change. You can tell the module about your extended formatters in the config file for the
module. 

