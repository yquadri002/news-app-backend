<?php

namespace App\Services\Ingestion;

use SimpleXMLElement;

class RssParserService
{
    public function parse(string $xmlContent): array
    {
        $xml = new SimpleXMLElement($xmlContent);
        $items = [];

        if (isset($xml->channel->item)) {
            foreach ($xml->channel->item as $item) {
                $items[] = $this->parseRssItem($item);
            }
        } elseif (isset($xml->entry)) {
            foreach ($xml->entry as $entry) {
                $items[] = $this->parseAtomEntry($entry);
            }
        }

        return $items;
    }

    private function parseRssItem(SimpleXMLElement $item): array
    {
        $content = (string) ($item->children('content', true)->encoded
            ?? $item->description
            ?? '');

        $enclosure = $item->enclosure ?? null;

        return [
            'title' => (string) $item->title,
            'description' => (string) $item->description,
            'content' => $content,
            'link' => (string) $item->link,
            'guid' => (string) ($item->guid ?? $item->link),
            'author' => (string) ($item->author ?? $item->children('dc', true)->creator ?? ''),
            'published_at' => (string) ($item->pubDate ?? $item->children('dc', true)->date ?? ''),
            'image' => $this->extractMediaImage($item),
            'enclosure_url' => $enclosure ? (string) $enclosure['url'] : null,
            'enclosure_type' => $enclosure ? (string) $enclosure['type'] : null,
        ];
    }

    private function parseAtomEntry(SimpleXMLElement $entry): array
    {
        $link = '';
        foreach ($entry->link as $l) {
            if ((string) ($l['rel'] ?? 'alternate') === 'alternate') {
                $link = (string) $l['href'];
                break;
            }
        }

        return [
            'title' => (string) $entry->title,
            'description' => (string) ($entry->summary ?? ''),
            'content' => (string) ($entry->content ?? $entry->summary ?? ''),
            'link' => $link,
            'guid' => (string) ($entry->id ?? $link),
            'author' => (string) ($entry->author->name ?? ''),
            'published_at' => (string) ($entry->published ?? $entry->updated ?? ''),
            'image' => null,
        ];
    }

    private function extractMediaImage(SimpleXMLElement $item): ?string
    {
        $media = $item->children('media', true);
        if (isset($media->content)) {
            return (string) $media->content['url'];
        }
        if (isset($media->thumbnail)) {
            return (string) $media->thumbnail['url'];
        }

        return null;
    }
}
