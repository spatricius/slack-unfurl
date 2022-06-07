<?php

namespace App\MessageHandler;

use App\Message\LinkSharedMessage;
use App\Service\SlackRequestParserResolver;
use App\Service\SlackResponseRenderer\SlackResponseResolver;
use App\Service\SlackUnfurlBuilder;
use JoliCode\Slack\ClientFactory;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class LinkSharedHandler implements MessageHandlerInterface
{
    protected string $slackAppToken;
    protected LoggerInterface $logger;
    protected SlackRequestParserResolver $slackRequestParserResolver;
    protected SlackResponseResolver $slackResponseResolver;
    protected SlackUnfurlBuilder $slackUnfurlBuilder;

    public function __construct(
        string $slackAppToken,
        SlackRequestParserResolver $slackRequestParserResolver,
        SlackResponseResolver $slackResponseResolver,
        SlackUnfurlBuilder $slackUnfurlBuilder,
        LoggerInterface $logger
    ) {
        $this->slackAppToken = $slackAppToken;
        $this->slackRequestParserResolver = $slackRequestParserResolver;
        $this->slackResponseResolver = $slackResponseResolver;
        $this->slackUnfurlBuilder = $slackUnfurlBuilder;
        $this->logger = $logger;
    }

    public function __invoke(LinkSharedMessage $message)
    {
        $eventObject = $message->getEventObject();
        $this->logger->info(sprintf('LinkSharedHandler invoked for links %s', $eventObject->links));

        $unfurls = [];
        foreach ($eventObject->links as $linkObject) {
            $slackRequestParser = $this->slackRequestParserResolver->resolve($linkObject->url);
            if (!$slackRequestParser) {
                $this->logger->warning(sprintf('No slack request parser found for url %s', $linkObject->url));
            }

            $text = $this->slackResponseResolver->resolve($slackRequestParser);
            if (!$text) {
                $this->logger->warning(sprintf('No text response found for url %s', $linkObject->url));
            }

            $unfurls[$linkObject->url] = $this->slackUnfurlBuilder->build($text);
        }

        $request = [
            'channel' => $eventObject->channel,
            'ts' => $eventObject->message_ts,
            'unfurls' => json_encode($unfurls),
        ];

        $this->logger->info('Sending callback');
        try {
            $client = ClientFactory::create($this->slackAppToken);
            $response = $client->chatUnfurl($request);
            $this->logger->info(sprintf('Callback sent with response: %s', $response));
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $this->logger->error(sprintf('Exception when sending callback: %s, with links: %s', $message, var_export($eventObject->links, true)));
        }
    }
}