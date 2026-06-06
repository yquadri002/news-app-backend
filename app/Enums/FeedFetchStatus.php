<?php

namespace App\Enums;

enum FeedFetchStatus: string
{
    case Started = 'started';
    case Success = 'success';
    case Partial = 'partial';
    case Failed = 'failed';
    case Retrying = 'retrying';
}
