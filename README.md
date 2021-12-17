# ext-direct-bundle
A Symfony bundle to integrate Sencha Ext JS Ext.direct into a Symfony application

## Installation

You can install this bundle using composer

    composer require teqneers/ext-direct-bundle

or add the package to your composer.json file directly.

After you have installed the package, you just need to add the bundle to your AppKernel.php file:

```php
// in AppKernel::registerBundles()
$bundles = array(
    // ...
    new TQ\Bundle\ExtDirectBundle\TQExtDirectBundle(),
    // ...
);
```

## Configuration

The *ext-direct-bundle* requires at least one endpoint to be configured.

    # Default configuration for "TQExtDirectBundle"
    tq_ext_direct:
        debug:                true
        cache:                file
        file_cache_dir:       '%kernel.cache_dir%/tq_ext_direct'
        validate_arguments:   true
        strict_validation:    true
        convert_arguments:    true
        convert_result:       true
        endpoints:            # Required

            # Prototype
            id:
                descriptor:           Ext.app.REMOTING_API
                namespace:            Ext.global
                auto_discover:        true
                all_bundles:          true
                bundles:              []
                directories:          []

`auto_discover` enables auto-discovering service classes in bundles available to the application. If paired with
`all_bundles` all available bundles are checked, otherwise only bundles mentioned in the `bundles` array are
checked. For bundles it is required to place service classes into an `ExtDirect` directory inside the bundle root
directory. Additionally (or only - if `auto_discover` is disabled) individual `directories` can be set to be included
in the service discovery process. Services are discovered recursively starting with each configured directory.

Because the bundle provides its own controller to serve the API description and handle *Ext.direct* reqeusts, you also
need to configure your routing to include the bundle routes at a given prefix. Edit your `app/config/routing.yml`:

    # ...
    ext_app:
        resource: "@TQExtDirectBundle/Resources/config/routing.yml"
        prefix: /
    # ...

The most minimalistic configuration looks like:

    tq_ext_direct:
        endpoints:
            api: ~

This enables Ext direct services in all available bundles via the `api` endpoint.

## Usage

Using the Twig extension provided by the bundle you can easily integrate the *Ext.direct* API definition into your
application templates.

```twig
<!DOCTYPE HTML>
<html lang="en">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta charset="UTF-8">
    <meta http-equiv="content-type" content="text/html; charset=UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>Welcome!</title>

    <!-- Ext JS bootstrap, etc. -->

     <script type="text/javascript" src="{{ extDirectApiPath('api') }}"></script>
</head>
<body>
</body>
</html>
```

## License

The MIT License (MIT)

Copyright (c) 2015 TEQneers GmbH & Co. KG

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
