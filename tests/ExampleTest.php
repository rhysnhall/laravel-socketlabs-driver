<?php

namespace Rhysnhall\LaravelSocketLabsDriver\Tests;

use Illuminate\Mail\Message;
use Rhysnhall\LaravelSocketLabsDriver\Tests\TestCase;

class ExampleTest extends TestCase {

  public function testCanBuildMessage() {
    $message = $this->getMessage();
    $message->from('sender@example.com', 'Test Sender')
      ->to('recipient@example.com', 'Test Recipient');

    $response = $this->transport()->send($message->getSwiftMessage());

    $this->assertEquals(1, $response);
  }

  public function testCanBuildMessageWithMultipleRecipients() {
    $message = $this->getMessage();
    $message->from('sender@example.com', 'Test Sender')
      ->to('recipient@example.com', 'Test Recipient')
      ->to('recipient2@example.com', 'Test Recipient 2')
      ->cc('recipientcc@example.com', 'Test CC Recipient');

    $response = $this->transport()->send($message->getSwiftMessage());

    $this->assertEquals(3, $response);
  }

  public function getMessage() {
    return new Message(
      new \Swift_Message('Test email subject', 'Test email body')
    );
  }

}
