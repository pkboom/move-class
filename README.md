# Very short description of the package

[![Latest Stable Version](https://poser.pugx.org/pkboom/move-class/v)](//packagist.org/packages/pkboom/move-class)
[![Total Downloads](https://poser.pugx.org/pkboom/move-class/downloads)](//packagist.org/packages/pkboom/move-class)

This is where your description should go. Try and limit it to a paragraph or two.
<img src="/images/demo.png" width="800"  title="demo">

## Installation

You can install the package via composer:

```bash
composer require pkboom/move-class
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --provider="Pkboom\MoveClass\MoveClassServiceProvider" --tag="move-class-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --provider="Pkboom\MoveClass\MoveClassServiceProvider" --tag="move-class-config"
```

This is the contents of the published config file:

```php
return [
];
```

## Usage

```php
$move-class = new Pkboom\MoveClass();
echo $move-class->echoPhrase('Hello, pkboom!');
```

## Testing

```bash
composer test
```
