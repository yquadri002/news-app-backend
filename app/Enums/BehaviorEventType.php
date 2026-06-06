<?php

namespace App\Enums;

enum BehaviorEventType: string
{
    case ArticleOpen = 'article_open';
    case ReadTime = 'read_time';
    case ScrollDepth = 'scroll_depth';
    case Bookmark = 'bookmark';
    case Unbookmark = 'unbookmark';
    case Share = 'share';
    case Search = 'search';
    case CategoryOpen = 'category_open';
    case SourceOpen = 'source_open';
    case FeedImpression = 'feed_impression';
    case SessionStart = 'session_start';
    case SessionEnd = 'session_end';
}
