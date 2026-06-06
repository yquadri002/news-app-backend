<?php

namespace App\Enums;

enum AnalyticsEventType: string
{
    case ArticleView = 'article_view';
    case ArticleOpen = 'article_open';
    case Search = 'search';
    case CategoryView = 'category_view';
    case SessionStart = 'session_start';
    case AppOpen = 'app_open';
}
