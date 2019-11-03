<p align="center" style="text-align: center">
<img src="logo.png" alt="Rackbeat throttler" height="100" />
<br/>Throttle PHP actions to only run a set amount of times within a given timeframe.
<br/>Especially useful to avoid rate limiting, high CPU usage or DOS'ing your own services (database etc.)
</p>
    
<p align="center" style="text-align: center"> 
<a href="https://travis-ci.org/Rackbeat/php-throttler"><img src="https://img.shields.io/travis/Rackbeat/php-throttler.svg?style=flat-square" alt="Build Status"></a>
<a href="https://coveralls.io/github/Rackbeat/php-throttler"><img src="https://img.shields.io/coveralls/Rackbeat/php-throttler.svg?style=flat-square" alt="Coverage"></a>
<a href="https://packagist.org/packages/rackbeat/php-throttler"><img src="https://img.shields.io/packagist/dt/rackbeat/php-throttler.svg?style=flat-square" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/rackbeat/php-throttler"><img src="https://img.shields.io/packagist/v/rackbeat/php-throttler.svg?style=flat-square" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/rackbeat/php-throttler"><img src="https://img.shields.io/packagist/l/rackbeat/php-throttler.svg?style=flat-square" alt="License"></a>
</p>

## Installation

You just require using composer and you're good to go! 

```bash
composer require rackbeat/php-throttler
```

## Usage

### Basic example

```php
use Rackbeat\Throttler\Throttler;

Throttler::make( range(1,100) )->allow(10)->every(1)->run( function($value, $key) {
    // do something with $value
} );
```

## Requirements
* PHP >= 7.3
