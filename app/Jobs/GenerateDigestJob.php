<?php

namespace App\Jobs;

use App\Enums\DigestType;
use App\Models\NotificationDigest;
use App\Services\NotificationIntelligence\DigestService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateDigestJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public ?DigestType $digestType = null)
    {
        $this->onQueue('notifications');
    }

    public function handle(DigestService $digestService): void
    {
        $types = $this->digestType ? [$this->digestType] : DigestType::cases();
        $currentHour = (int) now()->format('G');

        foreach ($types as $type) {
            if ($this->digestType === null && $type->defaultSendHour() !== $currentHour) {
                continue;
            }

            $digest = $digestService->generateDigest($type);

            $alreadySent = NotificationDigest::where('digest_type', $type)
                ->where('digest_date', now()->toDateString())
                ->where('status', 'sent')
                ->exists();

            if (! $alreadySent) {
                $digestService->sendDigest($digest);
            }
        }
    }
}
