<?php

namespace App\Service\SlackResponseRenderer;

use App\Service\GitlabTextResolver\SlackResponseRendererInterface;
use App\Service\SlackRequestParser\GitlabCompareParser;
use App\Service\SlackRequestParser\SlackRequestParserInterface;

class GitlabCompareRenderer implements SlackResponseRendererInterface
{
    public function supports(SlackRequestParserInterface $slackRequestParser): bool
    {
        return $slackRequestParser instanceof GitlabCompareParser;
    }

    /** @param GitlabCompareParser $slackRequestParser */
    public function resolve(SlackRequestParserInterface $slackRequestParser): string
    {
        $details = $slackRequestParser->getDetails();
        $branch1 = $slackRequestParser->getBranch1();
        $branch2 = $slackRequestParser->getBranch2();

        $filesChanged = array();
        foreach ($details['diffs'] as $diff) {
            $filesChanged[$diff['new_path']] = 1;
            $filesChanged[$diff['old_path']] = 1;
        }
        $filesChangedText = '- '.implode(",\n- ", array_keys($filesChanged));

        $text = <<<TEXT
Comparing $branch1 against $branch2 with commit '{$details['commit']['title']}' ({$details['commit']['short_id']}) by {$details['commit']['committer_name']}
Committed at {$details['commit']['committed_date']} 
Files changed:
$filesChangedText
TEXT;

        return $text;
    }
}