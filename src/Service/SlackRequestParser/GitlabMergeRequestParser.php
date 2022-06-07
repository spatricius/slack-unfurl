<?php

namespace App\Service\SlackRequestParser;

use Gitlab\Client;

class GitlabMergeRequestParser implements SlackRequestParserInterface
{
    protected Client $gitlabClient;
    protected ?GitlabProjectParser $projectUrlData;
    protected ?int $iid;
    protected array $details = array();
    protected array $commits = array();

    public function __construct(Client $gitlabClient)
    {
        $this->gitlabClient = $gitlabClient;
    }

    protected function getMatches($url): array
    {
        // eg. /-/merge_requests/316/diffs
        preg_match('#merge_requests/(?<iid>\d+)#', $url, $matches);

        return $matches;
    }

    public function supports(string $url): bool
    {
        $matches = $this->getMatches($url);
        if (empty($matches['iid'])) {
            return false;
        }

        return true;
    }

    public function parse(string $url): void
    {
        $matches = $this->getMatches($url);
        $this->iid = (int)$matches['iid'];
    }

    public function getLazyDetails(): array
    {
        if (empty($this->details)) {
            $this->details = $this->gitlabClient->mergeRequests()->show($this->iid, $this->projectUrlData->getId(), $this->options);
        }

        return $this->details;
    }

    public function getLazyCommits(): array
    {
        if (empty($this->commits)) {
            $this->commits = $this->gitlabClient->mergeRequests()->commits($this->iid, $this->projectUrlData->getId());
        }

        return $this->commits;
    }
}