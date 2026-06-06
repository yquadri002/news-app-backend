<?php

namespace App\Services\Ingestion;

use App\Enums\ArticleStatus;
use App\Enums\ModerationStatus;
use App\Jobs\DetectBreakingNewsJob;
use App\Models\Article;
use App\Models\ArticleImage;
use App\Models\ArticleMetric;
use App\Models\ArticleTag;
use App\Repositories\Contracts\ArticleRepositoryInterface;
use Illuminate\Database\UniqueConstraintViolationException;

class ArticleIngestionService
{
    public function __construct(
        private readonly ArticleRepositoryInterface $articleRepository,
        private readonly ArticleProcessingService $processingService,
        private readonly CategoryAssignmentService $categoryAssignment,
    ) {
    }

    public function ingest(int $sourceId, array $rawItem, string $sourceName, ?int $defaultCategoryId = null): ?Article
    {
        $processed = $this->processingService->process($rawItem, $sourceName);

        if (empty($processed['title'])) {
            return null;
        }

        if ($this->articleRepository->findByGuid($processed['guid'])) {
            return null;
        }

        try {
            $article = $this->articleRepository->create([
            'rss_source_id' => $sourceId,
            'category_id' => $defaultCategoryId,
            'title' => $processed['title'],
            'slug' => $processed['slug'],
            'summary' => $processed['summary'],
            'content' => $processed['content'],
            'image_url' => $processed['image_url'],
            'external_url' => $processed['external_url'],
            'canonical_url' => $processed['canonical_url'],
            'author' => $processed['author'],
            'source_name' => $processed['source_name'],
            'guid' => $processed['guid'],
            'title_hash' => $processed['title_hash'],
            'content_hash' => $processed['content_hash'],
            'status' => ArticleStatus::Processing,
            'moderation_status' => ModerationStatus::Pending,
            'published_at' => $processed['published_at'],
            'processed_at' => now(),
            ]);
        } catch (UniqueConstraintViolationException) {
            return $this->articleRepository->findByGuid($processed['guid']);
        }

        $this->storeImages($article, $processed['images']);
        $this->storeTags($article, $processed['tags']);
        ArticleMetric::create(['article_id' => $article->id]);

        $this->categoryAssignment->syncArticleCategories($article);

        $duplicateService = app(DuplicateDetectionService::class);
        $original = $duplicateService->detect($article->fresh());

        if ($original) {
            $duplicateService->mergeDuplicate($article, $original);

            return $article->fresh(['category', 'autoCategory', 'images', 'tags', 'metrics']);
        }

        $article->update([
            'status' => ArticleStatus::Approved,
            'moderation_status' => ModerationStatus::Approved,
        ]);

        DetectBreakingNewsJob::dispatch($article->id);

        return $article->fresh(['category', 'autoCategory', 'images', 'tags', 'metrics']);
    }

    private function storeImages(Article $article, array $images): void
    {
        foreach ($images as $index => $image) {
            ArticleImage::create([
                'article_id' => $article->id,
                'url' => $image['url'],
                'is_primary' => $image['is_primary'] ?? $index === 0,
            ]);
        }
    }

    private function storeTags(Article $article, array $tags): void
    {
        foreach ($tags as $tag) {
            ArticleTag::create([
                'article_id' => $article->id,
                'tag' => $tag,
                'source' => 'auto',
            ]);
        }
    }
}
