# OPG LPA API v 1.0.1

The MIT License (MIT)

Copyright (c) 2013 Crown copyright

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWAR

Application Server (aka the API)
--------------------------------

The Application Server is written in PHP 5.4 using [Zend Framework 2](http://framework.zend.com/).

It exposes a RESTful API with endpoints for retrieving, storing and submitting Applications and Registrations.

Within the larger LPA front office system the Application Server is the point of authority for validation and workflow processing.

How to setup a development environment
--------------------------------------

Requirements:
PHP 5.4+ with PDO support for MySQL, SQLite and MongoDB

Create the following files:

```
config/autoload/local.php
```

Templates are available:

```
config/autoload/local.php.jinja
```

Run the following from the application's root directory:

```
php composer.phar install --dev
```

Give your webserver appropriate permissions to write into the following directories:

```
data
data/cache
```
