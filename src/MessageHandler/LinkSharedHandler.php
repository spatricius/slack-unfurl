<?php

namespace App\MessageHandler;

use App\Message\LinkSharedMessage;
use Gitlab\Client;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class LinkSharedHandler implements MessageHandlerInterface
{
    private string $slackAppToken;
    private Client $gitlabClient;
    private LoggerInterface $logger;

    public function __construct(string $slackAppToken, Client $gitlabClient, LoggerInterface $logger)
    {
        $this->slackAppToken = $slackAppToken;
        $this->gitlabClient  = $gitlabClient;
        $this->logger = $logger;
    }

    public function __invoke(LinkSharedMessage $message)
    {
        $this->logger->info('LinkSharedHandler invoked');

        $eventObject = $message->getEventObject();
        $unfurls     = array();

        foreach ($eventObject->links as $linkObject) {
            $url = $linkObject->url;

            $projectId = null;
            $projects  = $this->gitlabClient->projects()->all(array(
                'simple'            => true,
                'search_namespaces' => true,
                'search'            => 'lionline',
                'order_by'          => 'last_activity_at',
            ));
            foreach ($projects as $project) {
                if (str_starts_with($url, $project['web_url'])) {
                    $projectId = $project['id'];
                    break;
                }
            }

            if (!$projectId) {
                $this->logger->warning('No project id');
                return;
            }

            $text = $this->generateUnfurlText($projectId, $url);
            $unfurls[$url] = array(
                'blocks' =>
                    array(
                        array(
                            'type'      => 'section',
                            'text'      =>
                                array(
                                    'type' => 'mrkdwn',
                                    'text' => $text,
                                ),
                            'accessory' =>
                                array(
                                    'type'      => 'image',
                                    'image_url' => 'https://is4-ssl.mzstatic.com/image/thumb/Purple124/v4/a3/42/32/a34232ab-ebe7-4752-4b86-e69952b14543/source/512x512bb.jpg',
                                    'alt_text'  => 'Gitlab createIT',
                                ),
                        ),
                    ),
            );
        }

        $request = array(
            'channel' => $eventObject->channel,
            'ts'      => $eventObject->message_ts,
            'unfurls' => json_encode($unfurls),
        );

        $this->logger->info('Sending callback');
        try {
            $client   = \JoliCode\Slack\ClientFactory::create($this->slackAppToken);
            $response = $client->chatUnfurl($request);
        } catch (\Exception $e) {
            $e->getMessage();
        }
    }

    private function generateUnfurlText($projectId, $url)
    {
        $this->logger->info('Generating unfurl text');

        // eg. /-/merge_requests/316/diffs
        preg_match('#merge_requests/(?<iid>\d+)#', $url, $matches);
        $iid = $matches['iid'] ?? null;
        if ($iid) {
            $show    = $this->gitlabClient->mergeRequests()->show($projectId, $iid);
            $commits = $this->gitlabClient->mergeRequests()->commits($projectId, $iid);

            $text = '';
            $text .= 'MR';
            $text .= ' ';
            $text .= $show['title'];
            $text .= " ";
            $text .= '('.$show['state'].')';
            $text .= "\n";
            $text .= 'Updated at: '.$show['updated_at'];
            $text .= "\n";
            $text .= 'Commits: ';

            foreach ($commits as $commit) {
                $text .= "<{$commit['web_url']}|{$commit['short_id']}>";
                $text .= ", ";
            }

            return $text;
        }

        // eg. /-/commit/2fcea17ea675467503ceed145ededd8d97697751
        preg_match('#/-/commit/(?<iid>\w+)/*#', $url, $matches);
        $iid = $matches['iid'] ?? null;
        if ($iid) {
            $commit      = $this->gitlabClient->repositories()->commit($projectId, $iid);
            $parentLinks = '';
            foreach ($commit['parent_ids'] as $parentId) {
                $parentCommit = $this->gitlabClient->repositories()->commit($projectId, $parentId);
                if ($parentCommit) {
                    $parentLinks .= "<{$parentCommit['web_url']}|{$parentCommit['short_id']}>, ";
                }
            }

            $text = '';
            $text .= 'Commit';
            $text .= ' ';
            $text .= "'{$commit['title']}'";
            $text .= " ";
            $text .= '('.$commit['short_id'].')';
            $text .= " by {$commit['committer_name']}";
            $text .= "\n";
            $text .= "Committed at {$commit['committed_date']}";
            $text .= "\n";
            $text .= "Total changes: {$commit['stats']['total']}";
            $text .= "\n";
            if ($parentLinks) {
                $text .= "Parents: $parentLinks";
            }

            return $text;
        }

        // eg. /-/compare/symfony4...1504_scripts_optimization?from_project_id=158
        preg_match('#/-/compare/(?<branch1>\w+)\.\.\.(?<branch2>\w+)/*#', $url, $matches);
        $branch1 = $matches['branch1'] ?? null;
        $branch2 = $matches['branch2'] ?? null;
        if ($branch1 && $branch2) {
            $compare      = $this->gitlabClient->repositories()->compare($projectId, $branch1, $branch2);
            $filesChanged = array();
            foreach ($compare['diffs'] as $diff) {
                $filesChanged[$diff['new_path']] = 1;
                $filesChanged[$diff['old_path']] = 1;
            }
            $filesChangedText = '- '.implode(",\n- ", array_keys($filesChanged));

            $text = '';
            $text .= "Comparing $branch1 against $branch2 with commit '{$compare['commit']['title']}'";
            $text .= " ";
            $text .= '('.$compare['commit']['short_id'].')';
            $text .= " by {$compare['commit']['committer_name']}";
            $text .= "\n";
            $text .= "Committed at {$compare['commit']['committed_date']}";
            $text .= "\n";
            $text .= "Files changed: \n$filesChangedText";
        }

        return $text;
    }
}