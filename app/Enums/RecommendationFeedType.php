<?php

namespace App\Enums;

enum RecommendationFeedType: string
{
    case ForYou = 'for_you';
    case Following = 'following';
    case Trending = 'trending';
    case Breaking = 'breaking';
    case Local = 'local';
}
