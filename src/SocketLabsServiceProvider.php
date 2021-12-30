<?php

namespace Rhysnhall\LaravelSocketLabsDriver;

use Illuminate\Support\{ServiceProvider, Arr};
use Illuminate\Mail\MailManager;
use Socketlabs\SocketLabsClient;
use Rhysnhall\LaravelSocketLabsDriver\SocketLabsTransport;

/**
 * SocketLabs service provider class.
 *
 * @author Rhys Hall hello@rhyshall.com
 */
class SocketLabsServiceProvider extends ServiceProvider {

  /**
   * Register the SocketLabs Transport class to Laravel's mail manager.
   *
   * @return void
   */
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

        $client->numberOfRetries = $config['retries'] ?? 0;
        $client->requestTimeout = $config['timeout'] ?? 120;
        $client->proxyUrl = $config['proxy_url'] ?? null;
        $client->endpointurl = $config['endpoint']
          ?? "https://inject.socketlabs.com/api/v1/email";

        return new SocketLabsTransport(
          $client,
          $config
        );
      });
    });
  }

}
