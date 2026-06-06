<?php

namespace App\Services\Ingestion;

use Illuminate\Support\Str;

class ArticleProcessingService
{
    public function process(array $rawItem, string $sourceName): array
    {
        $title = $this->cleanText($rawItem['title'] ?? '');
        $content = $this->cleanHtml($rawItem['content'] ?? $rawItem['description'] ?? '');
        $summary = $this->extractSummary($content, $rawItem['description'] ?? '');
        $images = $this->extractImages($rawItem);
        $author = $this->extractAuthor($rawItem);
        $publishedAt = $this->normalizePublishDate($rawItem['published_at'] ?? null);
        $externalUrl = $rawItem['link'] ?? $rawItem['url'] ?? null;
        $canonicalUrl = $this->generateCanonicalUrl($externalUrl);
        $guid = $rawItem['guid'] ?? $rawItem['id'] ?? md5($title.$externalUrl);

        return [
            'title' => $title,
            'slug' => Str::slug(Str::limit($title, 80, '')).'-'.Str::random(6),
            'summary' => $summary,
            'content' => $content,
            'image_url' => $images[0]['url'] ?? null,
            'images' => $images,
            'author' => $author,
            'source_name' => $sourceName,
            'external_url' => $externalUrl,
            'canonical_url' => $canonicalUrl,
            'guid' => $guid,
            'published_at' => $publishedAt,
            'title_hash' => hash('sha256', strtolower(trim($title))),
            'content_hash' => hash('sha256', strtolower(trim(strip_tags($content)))),
            'tags' => $this->extractTags($title, $content),
        ];
    }

    public function cleanHtml(string $html): string
    {
        if (empty($html)) {
            return '';
        }

        $html = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $html = preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $html);
        $html = preg_replace('/<style\b[^>]*>.*?<\/style>/is', '', $html);
        $html = strip_tags($html, '<p><br><b><i><strong><em><ul><ol><li><a><h1><h2><h3><blockquote>');
        $html = preg_replace('/\s+/', ' ', $html);

        return trim($html);
    }

    public function cleanText(string $text): string
    {
        return trim(html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }

    public function extractImages(array $rawItem): array
    {
        $images = [];

        if (! empty($rawItem['image'])) {
            $images[] = ['url' => $rawItem['image'], 'is_primary' => true];
        }

        if (! empty($rawItem['enclosure_url']) && str_starts_with($rawItem['enclosure_type'] ?? '', 'image/')) {
            $images[] = ['url' => $rawItem['enclosure_url'], 'is_primary' => empty($images)];
        }

        $content = $rawItem['content'] ?? $rawItem['description'] ?? '';
        if (preg_match_all('/<img[^>]+src=["\']([^"\']+)["\']/', $content, $matches)) {
            foreach ($matches[1] as $url) {
                if (filter_var($url, FILTER_VALIDATE_URL)) {
                    $images[] = ['url' => $url, 'is_primary' => empty($images)];
                }
            }
        }

        return array_slice($images, 0, 5);
    }

    public function extractAuthor(array $rawItem): ?string
    {
        if (! empty($rawItem['author'])) {
            return $this->cleanText($rawItem['author']);
        }

        if (! empty($rawItem['creator'])) {
            return $this->cleanText($rawItem['creator']);
        }

        if (! empty($rawItem['dc_creator'])) {
            return $this->cleanText($rawItem['dc_creator']);
        }

        return null;
    }

    public function normalizePublishDate(?string $date): ?\DateTimeInterface
    {
        if (empty($date)) {
            return now();
        }

        try {
            return new \DateTimeImmutable($date);
        } catch (\Throwable) {
            return now();
        }
    }

    public function generateCanonicalUrl(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        $parsed = parse_url($url);
        if (! $parsed) {
            return $url;
        }

        $canonical = ($parsed['scheme'] ?? 'https').'://'.($parsed['host'] ?? '');
        $canonical .= $parsed['path'] ?? '';

        return rtrim($canonical, '/');
    }

    private function extractSummary(string $content, string $fallback): string
    {
        $text = strip_tags($content ?: $fallback);

        return Str::limit(trim($text), 300);
    }

    private function extractTags(string $title, string $content): array
    {
        $text = strtolower($title.' '.strip_tags($content));
        $words = str_word_count($text, 1);
        $freq = array_count_values(array_filter($words, fn ($w) => strlen($w) > 4));
        arsort($freq);

        return array_slice(array_keys($freq), 0, 8);
    }
}
