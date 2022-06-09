<?php

namespace App\Service\SlackRequestParser;

interface SlackRequestParserInterface
{
    public function supports(string $url): bool;

    public function parse(string $url): void;
}