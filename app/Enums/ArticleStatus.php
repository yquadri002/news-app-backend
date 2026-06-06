<?php

namespace App\Enums;

enum ArticleStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Approved = 'approved';
    case Rejected = 'rejected';
}
