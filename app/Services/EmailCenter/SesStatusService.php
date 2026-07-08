<?php

namespace App\Services\EmailCenter;

use Aws\Ses\SesClient;
use Illuminate\Support\Facades\Cache;

/**
 * Read-only SES account health for the admin settings screen.
 * Never exposes credentials — only statuses and quotas.
 */
class SesStatusService
{
    public function status(): array
    {
        return Cache::remember('email-center-ses-status', now()->addMinutes(5), function () {
            try {
                $client = new SesClient([
                    'version' => '2010-12-01',
                    'region' => config('services.ses.region'),
                    'credentials' => [
                        'key' => config('services.ses.key'),
                        'secret' => config('services.ses.secret'),
                    ],
                ]);

                $quota = $client->getSendQuota();

                $domain = parse_url((string) config('app.url'), PHP_URL_HOST) ?: 'harmoniva.app';
                $identity = $client->getIdentityVerificationAttributes(['Identities' => [$domain]]);
                $verificationStatus = $identity['VerificationAttributes'][$domain]['VerificationStatus'] ?? 'NotFound';

                $configSet = config('email-center.configuration_set');
                $configSetExists = false;
                $eventTypes = [];

                try {
                    $described = $client->describeConfigurationSet([
                        'ConfigurationSetName' => $configSet,
                        'ConfigurationSetAttributeNames' => ['eventDestinations'],
                    ]);
                    $configSetExists = true;
                    foreach ($described['EventDestinations'] ?? [] as $destination) {
                        $eventTypes = array_merge($eventTypes, $destination['MatchingEventTypes'] ?? []);
                    }
                } catch (\Throwable) {
                    // config set missing
                }

                return [
                    'ok' => true,
                    'region' => config('services.ses.region'),
                    'max_24_hour_send' => (int) $quota['Max24HourSend'],
                    'max_send_rate' => (int) $quota['MaxSendRate'],
                    'sent_last_24_hours' => (int) $quota['SentLast24Hours'],
                    'domain' => $domain,
                    'domain_verified' => $verificationStatus === 'Success',
                    'configuration_set' => $configSet,
                    'configuration_set_exists' => $configSetExists,
                    'event_types' => array_values(array_unique($eventTypes)),
                    'topic_arn' => config('email-center.allowed_topic_arns')[0] ?? null,
                ];
            } catch (\Throwable $e) {
                return ['ok' => false, 'error' => $e->getMessage()];
            }
        });
    }
}
