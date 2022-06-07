<?php

namespace App\Service;

class SlackUnfurlBuilder
{
    private string $accessoryImageUrl;
    private string $accessoryAltText;

    public function __construct(string $accessoryImageUrl = '', string $accessoryAltText = '')
    {
        $this->accessoryImageUrl = $accessoryImageUrl;
        $this->accessoryAltText = $accessoryAltText;
    }

    public function build(string $text)
    {
        return array(
            'blocks' => $this->getBlocks($text),
        );
    }

    protected function getBlocks(string $text): array
    {
        return [
            array_merge(
                [
                    'type' => $this->getType(),
                    'text' =>
                        [
                            'type' => 'mrkdwn',
                            'text' => $text,
                        ],
                ],
                $this->getAccessoryArray()
            ),
        ];
    }

    protected function getType(): string
    {
        return 'section';
    }

    protected function getAccessoryArray(): ?array
    {
        if ($this->accessoryImageUrl) {
            return [
                'accessory' =>
                    [
                        'type' => 'image',
                        'image_url' => $this->accessoryImageUrl,
                        'alt_text' => $this->accessoryAltText,
                    ],
            ];
        }

        return [];
    }
}

