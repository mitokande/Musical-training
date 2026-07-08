<?php

namespace App\Services\EmailCenter;

use App\Models\EmailMessage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

/**
 * Turns a template body into the final HTML for one recipient:
 * {{variable}} substitution, click-tracking link rewriting with UTM
 * parameters, open pixel and unsubscribe footer injection.
 */
class TemplateRenderer
{
    /**
     * @param  array<string, string>  $context  extra variables from campaigns/automations
     */
    public function render(string $html, EmailMessage $message, array $context = []): string
    {
        $variables = $this->variables($message, $context);

        $html = $this->substitute($html, $variables);
        $html = $this->rewriteLinks($html, $message);
        $html = $this->injectOpenPixel($html, $message);

        if ($message->isMarketing() && ! str_contains($html, $variables['unsubscribe_url'])) {
            $html = $this->appendUnsubscribeFooter($html, $variables['unsubscribe_url']);
        }

        return $html;
    }

    public function renderSubject(string $subject, EmailMessage $message, array $context = []): string
    {
        return $this->substitute($subject, $this->variables($message, $context));
    }

    /**
     * Preview rendering for the admin UI — no tracking, sample variables.
     */
    public function preview(string $html, array $context = []): string
    {
        $sample = array_merge([
            'user_name' => 'Alex Morgan',
            'user_first_name' => 'Alex',
            'user_email' => 'alex@example.com',
            'app_name' => config('app.name'),
            'app_url' => config('app.url'),
            'unsubscribe_url' => '#unsubscribe',
            'current_year' => date('Y'),
        ], $context);

        return $this->substitute($html, $sample);
    }

    public function variables(EmailMessage $message, array $context = []): array
    {
        $user = $message->user;

        return array_merge([
            'user_name' => trim(($user->name ?? '').' '.($user->surname ?? '')) ?: 'there',
            'user_first_name' => $user->name ?? 'there',
            'user_email' => $message->recipient_email,
            'app_name' => config('app.name'),
            'app_url' => config('app.url'),
            'unsubscribe_url' => URL::signedRoute('email.unsubscribe', ['token' => $message->tracking_token]),
            'current_year' => date('Y'),
        ], $context);
    }

    protected function substitute(string $text, array $variables): string
    {
        return preg_replace_callback('/\{\{\s*([a-z0-9_]+)\s*\}\}/i', function ($m) use ($variables) {
            return $variables[strtolower($m[1])] ?? $m[0];
        }, $text);
    }

    /**
     * Wrap every http(s) link in the internal signed click-tracking redirect
     * and append UTM parameters to harmoniva.app destinations. SES click
     * events remain the primary metric; this layer adds attribution.
     */
    protected function rewriteLinks(string $html, EmailMessage $message): string
    {
        return preg_replace_callback('/href="(https?:\/\/[^"]+)"/i', function ($m) use ($message) {
            $target = html_entity_decode($m[1]);

            // never wrap unsubscribe links — they must work even if signing/DB fails
            if (str_contains($target, '/email/unsubscribe/')) {
                return $m[0];
            }

            $target = $this->appendUtm($target, $message);

            $tracked = URL::signedRoute('email.click', [
                'token' => $message->tracking_token,
                'url' => $target,
            ]);

            return 'href="'.e($tracked).'"';
        }, $html);
    }

    protected function appendUtm(string $url, EmailMessage $message): string
    {
        $appHost = parse_url((string) config('app.url'), PHP_URL_HOST);
        $urlHost = parse_url($url, PHP_URL_HOST);

        if (! $appHost || ! $urlHost || ! Str::endsWith($urlHost, $appHost)) {
            return $url; // only tag our own domain
        }

        $utm = http_build_query([
            'utm_source' => 'harmoniva_email',
            'utm_medium' => 'email',
            'utm_campaign' => $message->campaign?->slug ?? $message->automation?->key ?? $message->email_type,
            'utm_content' => $message->template?->slug ?? 'custom',
        ]);

        return $url.(str_contains($url, '?') ? '&' : '?').$utm;
    }

    protected function injectOpenPixel(string $html, EmailMessage $message): string
    {
        $pixel = '<img src="'.e(route('email.open', ['token' => $message->tracking_token])).'" width="1" height="1" alt="" style="display:none;border:0;" />';

        if (stripos($html, '</body>') !== false) {
            return str_ireplace('</body>', $pixel.'</body>', $html);
        }

        return $html.$pixel;
    }

    protected function appendUnsubscribeFooter(string $html, string $unsubscribeUrl): string
    {
        $footer = '<div style="text-align:center;padding:16px;font-size:12px;color:#9ca3af;font-family:sans-serif;">'
            .'<a href="'.e($unsubscribeUrl).'" style="color:#9ca3af;">Unsubscribe</a> from marketing emails.'
            .'</div>';

        if (stripos($html, '</body>') !== false) {
            return str_ireplace('</body>', $footer.'</body>', $html);
        }

        return $html.$footer;
    }
}
