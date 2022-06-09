<?php

namespace App\Service;

use App\Service\SlackRequestParser\SlackRequestParserInterface;

class SlackRequestParserResolver
{
    protected array $parsers;

    public function __construct(array $resolvers)
    {
        $this->parsers = $resolvers;
    }

    public function resolve(string $url): ?SlackRequestParserInterface
    {
        /** @var SlackRequestParserInterface $parser */
        foreach ($this->parsers as $parser) {
            if ($parser->supports($url)) {
                return $parser;
            }
        }

        return null;
    }
}