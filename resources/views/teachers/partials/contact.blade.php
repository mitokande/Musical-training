{{-- Contact + socials card. Expects: $profile, $socials. Optional: $wrapperClass. --}}
@if(($profile->show_email && $profile->public_email) || ($profile->show_phone && $profile->public_phone) || $profile->website_url || $socials->isNotEmpty())
    <div class="card p-6 {{ $wrapperClass ?? '' }}">
        <h2 class="font-bold text-gray-900 text-lg mb-4">{{ __('teacher.public.contact') }}</h2>
        <div class="space-y-3 text-[15px]">
            @if($profile->show_email && $profile->public_email)
                <p class="flex items-center gap-2.5 text-gray-700"><i data-lucide="mail" class="w-4 h-4 text-primary-500"></i> {{ $profile->public_email }}</p>
            @endif
            @if($profile->show_phone && $profile->public_phone)
                <p class="flex items-center gap-2.5 text-gray-700"><i data-lucide="phone" class="w-4 h-4 text-primary-500"></i> {{ $profile->public_phone }}</p>
            @endif
            @if($profile->website_url)
                <a href="{{ $profile->website_url }}" target="_blank" rel="noopener nofollow" class="flex items-center gap-2.5 text-primary-600 hover:text-primary-700">
                    <i data-lucide="globe" class="w-4 h-4"></i> {{ parse_url($profile->website_url, PHP_URL_HOST) ?: $profile->website_url }}
                </a>
            @endif
        </div>
        @if($socials->isNotEmpty())
            @php
                $brandColors = [
                    'instagram' => '#E4405F', 'youtube' => '#FF0000', 'facebook' => '#1877F2',
                    'linkedin' => '#0A66C2', 'tiktok' => '#000000', 'twitter' => '#1DA1F2',
                    'x' => '#000000', 'website' => '#9333EA',
                ];
            @endphp
            <div class="flex flex-wrap items-center gap-2.5 mt-4">
                @foreach($socials as $network => $url)
                    <a href="{{ $url }}" target="_blank" rel="noopener nofollow"
                       style="background-color: {{ $brandColors[$network] ?? '#9333EA' }}"
                       class="w-12 h-12 rounded-full flex items-center justify-center text-white hover:opacity-90 transition" title="{{ ucfirst($network) }}">
                        <i data-lucide="{{ in_array($network, ['instagram', 'youtube', 'facebook', 'linkedin']) ? $network : 'link' }}" class="w-5 h-5"></i>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
@endif
