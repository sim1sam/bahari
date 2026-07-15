<?php

namespace App\Console\Commands;

use App\Services\MetaConversionsApiService;
use App\Services\SiteSettingsService;
use Illuminate\Console\Command;

class TestMetaCapiCommand extends Command
{
    protected $signature = 'meta:capi-test {--event=PageView : Event name to send}';

    protected $description = 'Send a test Meta Conversions API event and print the result';

    public function handle(SiteSettingsService $settings, MetaConversionsApiService $capi): int
    {
        $this->line('Pixel: '.($settings->metaPixelId() ?: '(missing)'));
        $this->line('Token: '.(filled($settings->metaCapiAccessToken()) ? 'set ('.strlen($settings->metaCapiAccessToken()).' chars)' : '(missing)'));
        $this->line('Enabled: '.($settings->metaCapiEnabled() ? 'yes' : 'no'));
        $this->line('Test code: '.($settings->metaCapiTestEventCode() ?: '(none)'));

        if (! $settings->metaCapiEnabled()) {
            $this->error('CAPI is not enabled. Set META_PIXEL_ID and META_CAPI_ACCESS_TOKEN in .env');

            return self::FAILURE;
        }

        $event = (string) $this->option('event');
        $eventId = $capi->newEventId();
        $ok = $capi->send(
            $event,
            $eventId,
            ['content_name' => 'meta:capi-test'],
            $capi->userDataFromCustomer([]),
            config('app.url'),
        );

        if ($ok) {
            $this->info("Sent {$event} (event_id={$eventId}). Check Meta Events Manager → Test Events / Overview.");

            return self::SUCCESS;
        }

        $this->error('Send failed. Check storage/logs/laravel.log for Meta CAPI entries.');

        return self::FAILURE;
    }
}
