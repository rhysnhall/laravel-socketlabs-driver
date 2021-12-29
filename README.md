# Laravel SocketLabs Driver
Adds a driver for the SocketLabs Injection API to Laravel's email services.

## Requirements
- PHP 7.3 or greater.
- A [SocketLabs](socketlabs) account with a server ID and API key.

## Install

Install the package with composer.
```php
composer require rhysnhall/laravel-socketlabs-driver
```

## Setup
Once you register and setup an account with SocketLabs you'll be presented with a server ID and an API key. Add both of these to your ENV file.

```
SOCKET_LABS_API_KEY={your_key}
SOCKET_LABS_SERVER_ID={server_id}
```

Next add the SocketLabs credentials to your `config\services.php` config file.

```php
'socketlabs' => [
  'key' => env('SOCKET_LABS_API_KEY'),
  'id' => env('SOCKET_LABS_SERVER_ID')
]
```

Add the SocketLabs service provider to the `config\app.php` config file.

```php
'providers' => [
  ...
  Rhysnhall\LaravelSocketLabsDriver\SocketLabsServiceProvider::class
]
```

The last step is to add the SocketLabs driver to the `config\mail.php` config file.

```php
'mailers' => [
  ...
  'socketlabs' => [
    'transport' => 'socketlabs'
  ]
]
```

Depending on your setup you may also want to set SocketLabs as your default mailer. You'll need to update the `MAIL_MAILER` variable in your ENV file.

```
MAIL_MAILER=socketlabs
```

## Config
You can either add your config variables to the `config\mail.php` config file directly or you can create a new config file to hold these.

**config\mail.php**
```php
'mailers' => [
  ...
  'socketlabs' => [
    'transport' => 'socketlabs',
    'retries' => 2,
    'timeout' => 120,
    'proxy_url' => 'https://example'
  ]
]
```

**config\socketlabs.php**
```php
<?php

return [
  'retries' => 2,
  'timeout' => 120,
  'proxy_url' => 'https://example'
];
```

## Usage
Use the driver as you would any other email driver.

**App\Mail\Test**
```php
class Test extends Mailable {
  public function build()
  {
      return $this->from('sender@example.com')
        ->view('emails.html.test')
        ->text('emails.plain.test')
        ->attach(storage_path('test_image.png'));
  }
}

```

```php
Mail::to('recipient@example.com')->send(new \App\Mail\Test);
```

## Contributing
Help improve this package by contributing.

Before opening a pull request, please first discuss the proposed changes via Github issue or <a href="mailto:hello@rhyshall.com">email</a>.

## License
This project is licensed under the MIT License - see the [LICENSE](https://github.com/rhysnhall/etsy-php-sdk/blob/master/LICENSE.md) file for details

[socketlabs]: https://www.socketlabs.com/
