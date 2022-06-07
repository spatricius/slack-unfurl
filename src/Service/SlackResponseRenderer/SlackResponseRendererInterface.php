<?php

namespace App\Service\GitlabTextResolver;

use App\Service\SlackRequestParser\SlackRequestParserInterface;

interface SlackResponseRendererInterface
{
    public function supports(SlackRequestParserInterface $slackRequestParser): bool;

    public function resolve(SlackRequestParserInterface $slackRequestParser): string;
}