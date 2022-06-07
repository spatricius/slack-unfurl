<?php

namespace App\Service\SlackRequestParser;

use Gitlab\Client;

class GitlabCommitParser implements SlackRequestParserInterface
{
    protected int $iid;
    protected Client $gitlabClient;
    protected ?GitlabProjectParser $projectUrlData;
    protected array $details = array();
    protected array $parentsDetails = array();

    public function __construct(Client $gitlabClient)
    {
        $this->gitlabClient   = $gitlabClient;
    }

    protected function getMatches($url): array
    {
        // eg. /-/commit/2fcea17ea675467503ceed145ededd8d97697751
        preg_match('#/-/commit/(?<iid>\w+)/*#', $url, $matches);

        return $matches;
    }

    public function supports(string $url): bool
    {
        $matches = $this->getMatches($url);
        if (empty($matches['iid'])){
            return false;
        }

        return true;
    }

    public function parse(string $url): void
    {
        $matches = $this->getMatches($url);
        $this->iid = (int)$matches['iid'];
    }

    public function getIid(): int
    {
        return $this->iid;
    }

    public function getLazyDetails(): array
    {
        if (empty($this->details)) {
            $this->details = $this->gitlabClient->repositories()->commit(
                $this->projectUrlData->getId(),
                $this->getIid()
            );
        }

        return $this->details;
    }

    public function getLazyParentsDetails(): array
    {
        if (empty($this->parentsDetails)) {
            $commitDetails = $this->getLazyDetails();
            if (!empty($commitDetails['parent_ids'])) {
                foreach ($commitDetails['parent_ids'] as $parentId) {
                    $parentCommit            = $this->gitlabClient->repositories()->commit(
                        $this->projectUrlData->getId(),
                        $parentId
                    );
                    $this->parentsDetails[] = $parentCommit;
                }
            }
        }

        return $this->parentsDetails;
    }
}