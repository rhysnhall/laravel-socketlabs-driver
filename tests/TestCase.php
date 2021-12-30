<?php

namespace Rhysnhall\LaravelSocketLabsDriver\Tests;

use Rhysnhall\LaravelSocketLabsDriver\SocketLabsTransport;
use Socketlabs\SocketLabsClient;

abstract class TestCase extends \PHPUnit\Framework\TestCase {

  protected function setUp(): void {
    parent::setUp();
  }

  protected function transport($config = []): SocketLabsTransport {
    return new SocketLabsTransport(new SocketLabsClient(null, null), $config);
  }
}
