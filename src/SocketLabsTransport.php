<?php

namespace Rhysnhall\LaravelSocketLabsDriver;

use Illuminate\Mail\Transport\Transport;
use Swift_Mime_SimpleMessage;
use Swift_MimePart;
use Swift_Attachment;
use Swift_Image;
use Socketlabs\SocketLabsClient;
use Socketlabs\Message\{
  BasicMessage,
  EmailAddress,
  Attachment
};

class SocketLabsTransport extends Transport {

  /**
   * @var SocketLabsClient
   */
  private $client;

  /**
   * @var BasicMessage
   */
  private $basic_message;

  /**
   * @var string
   */
  private $mailing_id;

  /**
   * @var string
   */
  private $message_id;

  public function __construct(
    SocketLabsClient $client,
    array $config
  ) {
    if($config['retries'] ?? false) {
      $client->numberOfRetries = $config['retries'];
    }

    if($config['timeout'] ?? false) {
      $client->requestTimeout = $config['timeout'];
    }

    if($config['proxy_url'] ?? false) {
      $client->proxyUrl = $config['proxy_url'];
    }

    $this->client = $client;
    $this->mailing_id = $config['mailing_id'] ?? null;
    $this->message_id = $config['message_id'] ?? null;
  }

  /**
   * @param Swift_Mime_SimpleMessage $message
   * @param array|null
   * @return integer
   */
  public function send(
    Swift_Mime_SimpleMessage $message,
    &$failedRecipients = null
  ) {
    $this->beforeSendPerformed($message);

    // Create a basic message.
    $this->basic_message = new BasicMessage();

    // Build the email.
    $this->setProperty('subject', $message->getSubject());
    $this->setContent($message);
    $from = $message->getSender() ?: $message->getFrom();
    $this->setProperty('from', new EmailAddress(key($from), $from[key($from)]));
    $this->setEmailProperty('to', $message->getTo());
    $this->setEmailProperty('cc', $message->getCc() ?? []);
    $this->setEmailProperty('bcc', $message->getBcc() ?? []);

    // optional
    $reply_to = $message->getReplyTo();
    if($reply_to && count($reply_to)) {
      $this->setProperty('replyTo', new EmailAddress(key($reply_to), $reply_to[key($reply_to)]));
    }
    $this->setProperty('charset', $message->getCharset() ?? 'utf-8');
    $this->setProperty('mailingId', $this->mailing_id);
    $this->setProperty('messageId', $this->message_id);

    // Add attachments.
    $this->addAttachments($message);
    
    // Send the email.
    $response = $this->client->send($this->basic_message);

    $this->sendPerformed($message);
    return $this->numberOfRecipients($message);
  }

  /**
   * @param Swift_Mime_SimpleMessage $message
   * @return void
   */
  private function addAttachments(Swift_Mime_SimpleMessage $message) {
    foreach($message->getChildren() as $child) {
      if(($child instanceof Swift_Attachment) || ($child instanceof Swift_Image)) {
        $attachment = new Attachment;
        $attachment->name = $child->getFileName();
        $attachment->mimeType = $child->getContentType();
        $attachment->contentId = $child->getId();
        $attachment->content = $child->getBody();
        $this->basic_message->attachments[] = $attachment;
      }
    }
  }

  /**
   * Adds the email body to the socketlabs message.
   *
   * @param Swift_Mime_SimpleMessage $message
   * @return void
   */
  private function setContent(Swift_Mime_SimpleMessage $message) {
    switch ($message->getContentType()) {
      case 'text/html':
        $this->setProperty('htmlBody', $message->getBody());
        break;
      case 'text/plain':
        $this->setProperty('plainTextBody', $message->getBody());
        break;
      case 'multipart/alternative':
      default:
        foreach($message->getChildren() ?? [] as $child) {
          if(($child instanceof Swift_MimePart)
            && $child->getContentType() == 'text/plain') {
              $this->setProperty('plainTextBody', $child->getBody());
            }
        }
        $this->setProperty('htmlBody', $message->getBody());
        break;
    }
  }

  /**
   * Set a property on the socketlabs message.
   *
   * @param string $property
   * @param mixed $value
   * @return void
   */
  private function setProperty(string $property, $value) {
    $this->basic_message->{$property} = $value;
  }

  /**
   * Creates an Email and sets it against a property on the socketlabs message.
   *
   * @param string $property
   * @param array $value
   * @return void
   */
  private function setEmailProperty(string $property, array $data) {
    foreach($data as $email => $friendly_name) {
      $this->basic_message->{$property}[] = new EmailAddress($email, $friendly_name);
    }
  }

}
