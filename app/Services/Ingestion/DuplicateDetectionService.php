<?php

namespace App\Services\Ingestion;

use App\Models\Article;
use App\Repositories\Contracts\ArticleRepositoryInterface;

class DuplicateDetectionService
{
    private const TITLE_SIMILARITY_THRESHOLD = 85;

    private const CONTENT_SIMILARITY_THRESHOLD = 80;

    public function __construct(
        private readonly ArticleRepositoryInterface $articleRepository,
    ) {
    }

    public function detect(Article $article): ?Article
    {
        if ($article->duplicate_of_id) {
            return $article->duplicateOf;
        }

        $candidates = $this->articleRepository->getRecentForDuplicateCheck(72);

        foreach ($candidates as $candidate) {
            if ($candidate->id === $article->id) {
                continue;
            }

            if ($this->isExactHashMatch($article, $candidate)) {
                return $candidate;
            }

            if ($this->isTitleSimilar($article->title, $candidate->title)) {
                if ($this->isContentSimilar($article->content ?? $article->summary, $candidate->content ?? $candidate->summary)) {
                    return $candidate;
                }

                if ($this->isSameSourceStory($article, $candidate)) {
                    return $candidate;
                }
            }
        }

        return null;
    }

    public function mergeDuplicate(Article $article, Article $original): void
    {
        $article->update([
            'is_duplicate' => true,
            'duplicate_of_id' => $original->id,
            'status' => \App\Enums\ArticleStatus::Rejected,
            'moderation_status' => \App\Enums\ModerationStatus::Rejected,
            'rejection_reason' => "Duplicate of article #{$original->id}",
        ]);

        $metrics = $original->metrics;
        if ($metrics) {
            $metrics->increment('source_confirmation_count');
        }
    }

    public function calculateTitleSimilarity(string $title1, string $title2): float
    {
        similar_text(strtolower($title1), strtolower($title2), $percent);

        return round($percent, 2);
    }

    private function isExactHashMatch(Article $a, Article $b): bool
    {
        return $a->title_hash && $a->title_hash === $b->title_hash;
    }

    private function isTitleSimilar(string $title1, string $title2): bool
    {
        return $this->calculateTitleSimilarity($title1, $title2) >= self::TITLE_SIMILARITY_THRESHOLD;
    }

    private function isContentSimilar(?string $content1, ?string $content2): bool
    {
        if (! $content1 || ! $content2) {
            return false;
        }

        $c1 = strtolower(strip_tags($content1));
        $c2 = strtolower(strip_tags($content2));

        if (empty($c1) || empty($c2)) {
            return false;
        }

        similar_text($c1, $c2, $percent);

        return $percent >= self::CONTENT_SIMILARITY_THRESHOLD;
    }

    private function isSameSourceStory(Article $a, Article $b): bool
    {
        if ($a->rss_source_id === $b->rss_source_id) {
            return $this->calculateTitleSimilarity($a->title, $b->title) >= 70;
        }

        $urlA = parse_url($a->canonical_url ?? $a->external_url ?? '', PHP_URL_HOST);
        $urlB = parse_url($b->canonical_url ?? $b->external_url ?? '', PHP_URL_HOST);

        return $urlA && $urlA === $urlB
            && $this->calculateTitleSimilarity($a->title, $b->title) >= 75;
    }
}
