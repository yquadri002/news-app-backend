<?php

namespace App\Services\Ingestion;

use App\Models\Article;
use App\Models\Category;
use Illuminate\Support\Collection;

class CategoryAssignmentService
{
    private const KEYWORDS = [
        'local' => ['local', 'city', 'district', 'municipal', 'neighborhood', 'town', 'village', 'region'],
        'india' => ['india', 'indian', 'delhi', 'mumbai', 'bangalore', 'chennai', 'kolkata', 'modi', 'bjp', 'congress', 'lok sabha', 'parliament india'],
        'world' => ['world', 'global', 'international', 'foreign', 'overseas', 'united nations', 'nato', 'europe', 'africa', 'asia pacific'],
        'politics' => ['politics', 'political', 'election', 'government', 'minister', 'president', 'prime minister', 'parliament', 'senate', 'democrat', 'republican', 'policy', 'legislation', 'vote'],
        'technology' => ['technology', 'tech', 'software', 'hardware', 'ai', 'artificial intelligence', 'startup', 'app', 'digital', 'cyber', 'internet', 'smartphone', 'apple', 'google', 'microsoft'],
        'business' => ['business', 'economy', 'market', 'stock', 'finance', 'trade', 'corporate', 'company', 'earnings', 'revenue', 'investment', 'bank', 'gdp', 'inflation'],
        'sports' => ['sports', 'football', 'cricket', 'basketball', 'tennis', 'soccer', 'olympics', 'championship', 'match', 'tournament', 'player', 'team', 'league', 'ipl'],
        'entertainment' => ['entertainment', 'movie', 'film', 'music', 'celebrity', 'bollywood', 'hollywood', 'actor', 'actress', 'tv show', 'series', 'concert', 'award'],
        'health' => ['health', 'medical', 'medicine', 'hospital', 'doctor', 'disease', 'virus', 'vaccine', 'wellness', 'fitness', 'mental health', 'covid', 'pandemic'],
        'science' => ['science', 'research', 'study', 'scientist', 'space', 'nasa', 'climate', 'environment', 'physics', 'biology', 'discovery', 'experiment'],
    ];

    public function assign(Article $article): array
    {
        $text = strtolower($article->title.' '.strip_tags($article->content ?? '').' '.($article->summary ?? ''));
        $categories = $this->getCategories();
        $scores = [];

        foreach (self::KEYWORDS as $slug => $keywords) {
            $score = 0;
            foreach ($keywords as $keyword) {
                if (str_contains($text, $keyword)) {
                    $score += substr_count($text, $keyword) * (strlen($keyword) > 5 ? 2 : 1);
                }
            }
            if ($score > 0 && isset($categories[$slug])) {
                $scores[$slug] = $score;
            }
        }

        if (empty($scores)) {
            $default = $categories['world'] ?? $categories->first();

            return [
                'primary' => $default,
                'confidence' => 0.3,
                'all' => [['category' => $default, 'confidence' => 0.3]],
            ];
        }

        arsort($scores);
        $primarySlug = array_key_first($scores);
        $maxScore = $scores[$primarySlug];
        $confidence = min(1.0, $maxScore / 20);

        $all = [];
        foreach ($scores as $slug => $score) {
            if (isset($categories[$slug])) {
                $all[] = [
                    'category' => $categories[$slug],
                    'confidence' => min(1.0, $score / 20),
                ];
            }
        }

        return [
            'primary' => $categories[$primarySlug],
            'confidence' => $confidence,
            'all' => $all,
        ];
    }

    public function syncArticleCategories(Article $article): void
    {
        $result = $this->assign($article);

        $article->update([
            'auto_category_id' => $result['primary']->id,
            'category_id' => $article->category_id ?? $result['primary']->id,
        ]);

        $syncData = [];
        foreach ($result['all'] as $item) {
            $syncData[$item['category']->id] = [
                'assignment_source' => 'auto',
                'confidence' => $item['confidence'],
                'is_primary' => $item['category']->id === $result['primary']->id,
            ];
        }

        $article->assignedCategories()->sync($syncData);
    }

    private function getCategories(): Collection
    {
        return Category::where('is_enabled', true)->get()->keyBy('slug');
    }
}
