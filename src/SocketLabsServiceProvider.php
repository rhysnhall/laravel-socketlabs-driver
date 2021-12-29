<?php

namespace Rhysnhall\LaravelSocketLabsDriver;

use Illuminate\Support\{ServiceProvider, Arr};
use Illuminate\Mail\MailManager;
use Socketlabs\SocketLabsClient;
use Rhysnhall\LaravelSocketLabsDriver\SocketLabsTransport;

class SocketLabsServiceProvider extends ServiceProvider {

  public function register() {
    $this->app->afterResolving(MailManager::class, function(
      MailManager $manager
    ) {
      $manager->extend("socketlabs", function($config) {
        $config = array_merge(
          $this->app['config']->get('services.socketlabs', []),
          $this->app['config']->get('socketlabs', []),
          $config
        );
        $credentials = Arr::only($config, ['key', 'id']);
        $config = Arr::except($config, ['transport', 'key', 'id']);

        // Create the socketlabs client.
        $client = new SocketLabsClient(
          $credentials['id'],
          $credentials['key']
        );

        return new SocketLabsTransport(
          $client,
          $config
        );
      });
    });
  }

}
