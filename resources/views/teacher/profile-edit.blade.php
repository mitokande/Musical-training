@extends('teacher.layouts.crm')

@section('title', crm_trans('profile.title'))

@section('content')
<div class="max-w-4xl" x-data="{ section: ['general','music','services','videos','media','payment','seo'].includes(window.location.hash.slice(1)) ? window.location.hash.slice(1) : 'general' }">

    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ crm_trans('profile.title') }}</h1>
            <p class="text-sm text-gray-500 mt-1">{{ crm_trans('profile.subtitle') }}</p>
        </div>
        <div class="flex items-center gap-2">
            @if($profile->canBeSubmitted())
                <form method="POST" action="{{ crm_route('profile.submit') }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-primary-600 hover:bg-primary-700 rounded-lg transition">
                        <i data-lucide="send" class="w-4 h-4"></i> {{ crm_trans('dashboard.submit_for_review') }}
                    </button>
                </form>
            @endif
            <a href="{{ crm_route('profile.preview') }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition">
                <i data-lucide="eye" class="w-4 h-4"></i> {{ crm_trans('nav.view_as_student') }}
            </a>
        </div>
    </div>

    @unless(auth()->user()->isEffectivelyPremium())
        {{-- Premium upgrade banner: unlocks payment links, content publishing, analytics, … --}}
        <a href="{{ route('checkout.show') }}" class="flex items-center gap-4 rounded-xl p-4 mb-6 text-white shadow-md hover:opacity-95 transition-all" style="background:linear-gradient(135deg,#9333ea,#7c3aed);">
            <div class="w-10 h-10 rounded-lg bg-white/20 flex items-center justify-center shrink-0">
                <i data-lucide="crown" class="w-5 h-5"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="font-bold text-sm">{{ __('app.dashboard.upgrade_premium') }}</p>
                <p class="text-white/85 text-xs leading-snug">{{ __('app.dashboard.premium_description') }}</p>
            </div>
            <span class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-white text-purple-700 text-sm font-bold shrink-0">
                {{ __('app.dashboard.upgrade_premium') }} <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </span>
        </a>
    @endunless

    @if($profile->isPubliclyVisible())
        <div class="mb-6 px-4 py-3 bg-primary-50 border border-primary-100 rounded-xl flex items-center gap-3 text-sm">
            <i data-lucide="link" class="w-4 h-4 text-primary-600 shrink-0"></i>
            <span class="text-gray-600">{{ crm_trans('profile.public_url') }}:</span>
            <a href="{{ $profile->publicUrl() }}" class="font-semibold text-primary-700 truncate">{{ $profile->publicUrl() }}</a>
        </div>
    @endif

    {{-- Section tabs --}}
    <div class="flex flex-wrap gap-2 mb-6">
        @foreach([
            'general' => crm_trans('profile.section_general'),
            'music' => crm_trans('profile.section_music'),
            'services' => crm_trans('profile.section_services'),
            'videos' => crm_trans('profile.section_videos'),
            'media' => crm_trans('profile.section_media'),
            'payment' => crm_trans('profile.section_payment_links'),
            'seo' => crm_trans('profile.section_seo'),
        ] as $key => $label)
            <button type="button" @click="section = '{{ $key }}'"
                    :class="section === '{{ $key }}' ? 'bg-primary-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-50 border border-gray-200'"
                    class="px-4 py-2 rounded-lg text-sm font-semibold transition">
                {{ $label }}
            </button>
        @endforeach
    </div>

    @php
        $toCsv = fn ($arr) => is_array($arr) ? implode(', ', $arr) : ($arr ?? '');
    @endphp

    {{-- =============== MAIN FORM: general + music + seo =============== --}}
    <form method="POST" action="{{ crm_route('profile.update') }}" x-show="['general','music','seo'].includes(section)">
        @csrf
        @method('PUT')

        {{-- GENERAL --}}
        <div class="card p-6 space-y-5" x-show="section === 'general'" x-cloak>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[15px] font-semibold text-gray-800 mb-1.5">{{ crm_trans('fields.headline') }}</label>
                    <input type="text" name="headline" value="{{ old('headline', $profile->headline) }}" maxlength="160"
                           placeholder="{{ crm_trans('fields.headline_placeholder') }}"
                           class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-3 text-[15px] text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-primary-500 focus:border-primary-500">
                </div>
                <div>
                    <label class="block text-[15px] font-semibold text-gray-800 mb-1.5">{{ crm_trans('fields.expertise') }}</label>
                    <input type="text" name="expertise" value="{{ old('expertise', $profile->expertise) }}"
                           placeholder="{{ crm_trans('fields.expertise_placeholder') }}"
                           class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-3 text-[15px] text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-primary-500 focus:border-primary-500">
                </div>
            </div>

            <div>
                <label class="block text-[15px] font-semibold text-gray-800 mb-1.5">{{ crm_trans('fields.about') }}</label>
                <textarea name="about" rows="5" class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-3 text-[15px] text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-primary-500 focus:border-primary-500">{{ old('about', $profile->about) }}</textarea>
            </div>

            <div>
                <label class="block text-[15px] font-semibold text-gray-800 mb-1.5">{{ crm_trans('fields.teaching_methodology') }}</label>
                <textarea name="teaching_methodology" rows="4" class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-3 text-[15px] text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-primary-500 focus:border-primary-500">{{ old('teaching_methodology', $profile->teaching_methodology) }}</textarea>
            </div>

            <div>
                <label class="block text-[15px] font-semibold text-gray-800 mb-2">{{ crm_trans('fields.teaching_formats') }}</label>
                <div class="flex flex-wrap gap-4">
                    @foreach(['online' => crm_trans('fields.format_online'), 'in_person' => crm_trans('fields.format_in_person'), 'hybrid' => crm_trans('fields.format_hybrid')] as $format => $label)
                        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                            <input type="checkbox" name="teaching_formats[]" value="{{ $format }}"
                                   @checked(in_array($format, old('teaching_formats', $profile->teaching_formats ?? [])))
                                   class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                            {{ $label }}
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[15px] font-semibold text-gray-800 mb-1.5">{{ crm_trans('fields.lesson_types') }}</label>
                    <input type="text" name="lesson_types" value="{{ old('lesson_types', $toCsv($profile->lesson_types)) }}"
                           class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-3 text-[15px] text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-primary-500 focus:border-primary-500">
                    <p class="text-xs text-gray-400 mt-1">{{ crm_trans('fields.lesson_types_hint') }}</p>
                </div>
                <div>
                    <label class="block text-[15px] font-semibold text-gray-800 mb-1.5">{{ crm_trans('fields.languages') }}</label>
                    <input type="text" name="languages" value="{{ old('languages', $toCsv($profile->languages)) }}"
                           class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-3 text-[15px] text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-primary-500 focus:border-primary-500">
                    <p class="text-xs text-gray-400 mt-1">{{ crm_trans('fields.languages_hint') }}</p>
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[15px] font-semibold text-gray-800 mb-1.5">{{ crm_trans('fields.country') }}</label>
                    <input type="text" name="country" value="{{ old('country', $profile->country) }}"
                           class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-3 text-[15px] text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-primary-500 focus:border-primary-500">
                </div>
                <div>
                    <label class="block text-[15px] font-semibold text-gray-800 mb-1.5">{{ crm_trans('fields.city') }}</label>
                    <input type="text" name="city" value="{{ old('city', $profile->city) }}"
                           class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-3 text-[15px] text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-primary-500 focus:border-primary-500">
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[15px] font-semibold text-gray-800 mb-1.5">{{ crm_trans('fields.public_email') }}</label>
                    <input type="email" name="public_email" value="{{ old('public_email', $profile->public_email) }}"
                           class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-3 text-[15px] text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-primary-500 focus:border-primary-500">
                    <label class="inline-flex items-center gap-2 mt-2 text-[15px] text-gray-700">
                        <input type="checkbox" name="show_email" value="1" @checked(old('show_email', $profile->show_email)) class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                        {{ crm_trans('fields.show_email') }}
                    </label>
                </div>
                <div>
                    <label class="block text-[15px] font-semibold text-gray-800 mb-1.5">{{ crm_trans('fields.public_phone') }}</label>
                    <input type="text" name="public_phone" value="{{ old('public_phone', $profile->public_phone) }}"
                           class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-3 text-[15px] text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-primary-500 focus:border-primary-500">
                    <label class="inline-flex items-center gap-2 mt-2 text-[15px] text-gray-700">
                        <input type="checkbox" name="show_phone" value="1" @checked(old('show_phone', $profile->show_phone)) class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                        {{ crm_trans('fields.show_phone') }}
                    </label>
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[15px] font-semibold text-gray-800 mb-1.5">{{ crm_trans('fields.website') }}</label>
                    <input type="url" name="website_url" value="{{ old('website_url', $profile->website_url) }}"
                           class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-3 text-[15px] text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-primary-500 focus:border-primary-500">
                </div>
                @foreach(['instagram', 'tiktok', 'youtube', 'linkedin', 'facebook'] as $network)
                    <div>
                        <label class="block text-[15px] font-semibold text-gray-800 mb-1.5">{{ crm_trans('fields.'.$network) }}</label>
                        <input type="url" name="social_links[{{ $network }}]" value="{{ old('social_links.'.$network, $profile->social_links[$network] ?? '') }}"
                               class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-3 text-[15px] text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-primary-500 focus:border-primary-500">
                    </div>
                @endforeach
            </div>
        </div>

        {{-- MUSIC PROFILE --}}
        <div class="card p-6 space-y-5" x-show="section === 'music'" x-cloak>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[15px] font-semibold text-gray-800 mb-1.5">{{ crm_trans('fields.primary_instrument') }}</label>
                    <input type="text" name="primary_instrument" value="{{ old('primary_instrument', $profile->primary_instrument) }}"
                           class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-3 text-[15px] text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-primary-500 focus:border-primary-500">
                </div>
                <div>
                    <label class="block text-[15px] font-semibold text-gray-800 mb-1.5">{{ crm_trans('fields.other_instruments') }}</label>
                    <input type="text" name="instruments" value="{{ old('instruments', $profile->instruments->pluck('instrument')->implode(', ')) }}"
                           class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-3 text-[15px] text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-primary-500 focus:border-primary-500">
                    <p class="text-xs text-gray-400 mt-1">{{ crm_trans('fields.languages_hint') }}</p>
                </div>
            </div>

            <div>
                <label class="block text-[15px] font-semibold text-gray-800 mb-1.5">{{ crm_trans('fields.education_status') }}</label>
                <input type="text" name="education_status" value="{{ old('education_status', $profile->education_status) }}"
                       class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-3 text-[15px] text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-primary-500 focus:border-primary-500">
            </div>

            {{-- Education entries (Add More) --}}
            <div x-data="{ rows: {{ json_encode($profile->educations->map(fn ($e) => [
                'institution' => $e->institution,
                'program' => $e->program,
                'field_of_study' => $e->field_of_study,
                'graduation_year' => $e->graduation_year,
            ])->values()->all() ?: [['institution' => '', 'program' => '', 'field_of_study' => '', 'graduation_year' => '']]) }} }">
                <label class="block text-[15px] font-semibold text-gray-800 mb-2">{{ crm_trans('fields.educations') }}</label>
                <template x-for="(row, index) in rows" :key="index">
                    <div class="grid sm:grid-cols-4 gap-2 mb-2 items-start">
                        <input type="text" :name="'educations['+index+'][institution]'" x-model="row.institution"
                               placeholder="{{ crm_trans('fields.institution') }}"
                               class="rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-3 text-[15px] text-gray-900 focus:bg-white focus:ring-primary-500 focus:border-primary-500">
                        <input type="text" :name="'educations['+index+'][program]'" x-model="row.program"
                               placeholder="{{ crm_trans('fields.program') }}"
                               class="rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-3 text-[15px] text-gray-900 focus:bg-white focus:ring-primary-500 focus:border-primary-500">
                        <input type="text" :name="'educations['+index+'][field_of_study]'" x-model="row.field_of_study"
                               placeholder="{{ crm_trans('fields.field_of_study') }}"
                               class="rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-3 text-[15px] text-gray-900 focus:bg-white focus:ring-primary-500 focus:border-primary-500">
                        <div class="flex gap-2">
                            <input type="number" :name="'educations['+index+'][graduation_year]'" x-model="row.graduation_year"
                                   placeholder="{{ crm_trans('fields.graduation_year') }}" min="1940" max="2100"
                                   class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-3 text-[15px] text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-primary-500 focus:border-primary-500">
                            <button type="button" @click="rows.splice(index, 1)" class="p-2 text-gray-400 hover:text-red-500" x-show="rows.length > 1">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>
                </template>
                <button type="button" @click="rows.push({institution:'',program:'',field_of_study:'',graduation_year:''}); $nextTick(() => lucide.createIcons())"
                        class="inline-flex items-center gap-1 text-sm font-semibold text-primary-600 hover:text-primary-700 mt-1">
                    <i data-lucide="plus" class="w-4 h-4"></i> {{ crm_trans('profile.add_more') }}
                </button>
            </div>

            <div class="grid sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-[15px] font-semibold text-gray-800 mb-1.5">{{ crm_trans('fields.certificates') }}</label>
                    <textarea name="certificates" rows="3" class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-3 text-[15px] text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-primary-500 focus:border-primary-500">{{ old('certificates', $profile->certificates) }}</textarea>
                </div>
                <div>
                    <label class="block text-[15px] font-semibold text-gray-800 mb-1.5">{{ crm_trans('fields.workshops') }}</label>
                    <textarea name="workshops" rows="3" class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-3 text-[15px] text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-primary-500 focus:border-primary-500">{{ old('workshops', $profile->workshops) }}</textarea>
                </div>
                <div>
                    <label class="block text-[15px] font-semibold text-gray-800 mb-1.5">{{ crm_trans('fields.masterclasses') }}</label>
                    <textarea name="masterclasses" rows="3" class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-3 text-[15px] text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-primary-500 focus:border-primary-500">{{ old('masterclasses', $profile->masterclasses) }}</textarea>
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[15px] font-semibold text-gray-800 mb-1.5">{{ crm_trans('fields.experience_years') }}</label>
                    <input type="number" name="experience_years" value="{{ old('experience_years', $profile->experience_years) }}" min="0" max="80"
                           class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-3 text-[15px] text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-primary-500 focus:border-primary-500">
                </div>
                <div>
                    <label class="block text-[15px] font-semibold text-gray-800 mb-1.5">{{ crm_trans('fields.teaching_experience') }}</label>
                    <textarea name="teaching_experience" rows="2" class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-3 text-[15px] text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-primary-500 focus:border-primary-500">{{ old('teaching_experience', $profile->teaching_experience) }}</textarea>
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                @foreach(['genres', 'expertise_areas', 'age_groups', 'skill_levels', 'teaching_languages'] as $listField)
                    <div>
                        <label class="block text-[15px] font-semibold text-gray-800 mb-1.5">{{ crm_trans('fields.'.$listField) }}</label>
                        <input type="text" name="{{ $listField }}" value="{{ old($listField, $toCsv($profile->{$listField})) }}"
                               class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-3 text-[15px] text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-primary-500 focus:border-primary-500">
                        <p class="text-xs text-gray-400 mt-1">{{ crm_trans('fields.languages_hint') }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- SEO --}}
        <div class="card p-6 space-y-5" x-show="section === 'seo'" x-cloak>
            <div>
                <label class="block text-[15px] font-semibold text-gray-800 mb-1.5">{{ crm_trans('fields.seo_title') }}</label>
                <input type="text" name="seo_title" value="{{ old('seo_title', $profile->seo_title) }}" maxlength="255"
                       placeholder="{{ crm_trans('fields.seo_title_placeholder') }}"
                       class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-3 text-[15px] text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-primary-500 focus:border-primary-500">
            </div>
            <div>
                <label class="block text-[15px] font-semibold text-gray-800 mb-1.5">{{ crm_trans('fields.seo_description') }}</label>
                <textarea name="seo_description" rows="3" maxlength="320"
                          placeholder="{{ crm_trans('fields.seo_description_placeholder') }}"
                          class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-3 text-[15px] text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-primary-500 focus:border-primary-500">{{ old('seo_description', $profile->seo_description) }}</textarea>
            </div>
        </div>

        <div class="mt-4">
            <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-semibold text-white bg-primary-600 hover:bg-primary-700 rounded-lg transition">
                <i data-lucide="save" class="w-4 h-4"></i> {{ crm_trans('profile.save_draft') }}
            </button>

            {{-- SEO usage guidance shown only under the SEO tab --}}
            <div x-show="section === 'seo'" x-cloak class="mt-4 flex gap-3 rounded-xl border border-primary-100 bg-primary-50/60 p-4">
                <i data-lucide="search" class="w-5 h-5 text-primary-600 shrink-0 mt-0.5"></i>
                <p class="text-[13px] leading-relaxed text-gray-600">{{ crm_trans('fields.seo_help') }}</p>
            </div>
        </div>
    </form>

    {{-- =============== COVER IMAGE (general section, separate form for file upload) =============== --}}
    <div class="card p-6 mt-6" x-show="section === 'general'" x-cloak>
        <label class="block text-[15px] font-semibold text-gray-800 mb-2">{{ crm_trans('fields.cover_image') }}</label>
        @if($profile->coverImageUrl())
            <img src="{{ $profile->coverImageUrl() }}" alt="" class="w-full h-40 object-cover rounded-xl mb-3">
        @endif
        <form method="POST" action="{{ crm_route('profile.cover') }}" enctype="multipart/form-data" class="flex flex-wrap items-center gap-3">
            @csrf
            {{-- Auto-upload on selection so a picked cover is never lost by
                 pressing the main Save button (which posts a different form). --}}
            <input type="file" name="cover" accept="image/*" required class="text-sm text-gray-600"
                   onchange="if (this.files.length) this.form.submit()">
            <button type="submit" class="px-4 py-2 text-sm font-semibold text-primary-700 bg-primary-50 hover:bg-primary-100 rounded-lg transition">{{ crm_trans('fields.upload_cover') }}</button>
        </form>
        <p class="text-xs text-gray-400 mt-2">JPG / PNG / WebP · max 8 MB</p>
        @error('cover')<p class="text-sm text-red-600 mt-2">{{ $message }}</p>@enderror
        @error('upload')<p class="text-sm text-red-600 mt-2">{{ $message }}</p>@enderror
    </div>

    {{-- =============== SERVICES =============== --}}
    <div x-show="section === 'services'" x-cloak>
        <div class="card p-6 mb-4">
            <h2 class="font-semibold text-gray-900 mb-4">{{ crm_trans('services.add') }}</h2>
            <form method="POST" action="{{ crm_route('services.store') }}" class="grid sm:grid-cols-2 gap-4">
                @csrf
                <div>
                    <label class="block text-[15px] font-semibold text-gray-800 mb-1.5">{{ crm_trans('services.service_title') }}</label>
                    <input type="text" name="title" required class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-3 text-[15px] text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-primary-500 focus:border-primary-500">
                </div>
                <div>
                    <label class="block text-[15px] font-semibold text-gray-800 mb-1.5">{{ crm_trans('services.lesson_type') }}</label>
                    <input type="text" name="lesson_type" class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-3 text-[15px] text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-primary-500 focus:border-primary-500">
                </div>
                <div>
                    <label class="block text-[15px] font-semibold text-gray-800 mb-1.5">{{ crm_trans('services.format') }}</label>
                    <select name="format" class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-3 text-[15px] text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-primary-500 focus:border-primary-500">
                        <option value="">—</option>
                        <option value="online">{{ crm_trans('fields.format_online') }}</option>
                        <option value="in_person">{{ crm_trans('fields.format_in_person') }}</option>
                        <option value="hybrid">{{ crm_trans('fields.format_hybrid') }}</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-[15px] font-semibold text-gray-800 mb-1.5">{{ crm_trans('services.duration') }}</label>
                        <input type="number" name="duration_minutes" min="5" max="480" class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-3 text-[15px] text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    <div>
                        <label class="block text-[15px] font-semibold text-gray-800 mb-1.5">{{ crm_trans('services.price_text') }}</label>
                        <input type="text" name="price_text" class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-3 text-[15px] text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-primary-500 focus:border-primary-500">
                    </div>
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-[15px] font-semibold text-gray-800 mb-1.5">{{ crm_trans('services.description') }}</label>
                    <textarea name="description" rows="2" class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-3 text-[15px] text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-primary-500 focus:border-primary-500"></textarea>
                </div>
                <div class="sm:col-span-2">
                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-primary-600 hover:bg-primary-700 rounded-lg transition">
                        <i data-lucide="plus" class="w-4 h-4"></i> {{ crm_trans('services.add') }}
                    </button>
                </div>
            </form>
        </div>

        @forelse($profile->services as $service)
            <div class="card p-4 mb-2" x-data="{ editing: false }">
                {{-- Display view --}}
                <div class="flex items-start justify-between gap-3" x-show="!editing">
                    <div class="min-w-0">
                        <p class="font-semibold text-gray-900 text-sm">{{ $service->title }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">
                            {{ $service->lesson_type }}
                            @if($service->duration_minutes) · {{ $service->duration_minutes }} min @endif
                            @if($service->price_text) · {{ $service->price_text }} @endif
                        </p>
                        @if($service->description)<p class="text-sm text-gray-600 mt-1">{{ $service->description }}</p>@endif
                    </div>
                    <div class="flex items-center shrink-0">
                        <button type="button" @click="editing = true; $nextTick(() => lucide.createIcons())" class="p-2 text-gray-400 hover:text-primary-600" title="{{ crm_trans('profile.edit') }}"><i data-lucide="pencil" class="w-4 h-4"></i></button>
                        <form method="POST" action="{{ crm_route('services.destroy', $service) }}" onsubmit="return confirm('{{ crm_trans('profile.remove') }}?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="p-2 text-gray-400 hover:text-red-500"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                        </form>
                    </div>
                </div>

                {{-- Edit view --}}
                <form method="POST" action="{{ crm_route('services.update', $service) }}" class="grid sm:grid-cols-2 gap-4" x-show="editing" x-cloak>
                    @csrf @method('PUT')
                    <div>
                        <label class="block text-[15px] font-semibold text-gray-800 mb-1.5">{{ crm_trans('services.service_title') }}</label>
                        <input type="text" name="title" value="{{ $service->title }}" required class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-3 text-[15px] text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    <div>
                        <label class="block text-[15px] font-semibold text-gray-800 mb-1.5">{{ crm_trans('services.lesson_type') }}</label>
                        <input type="text" name="lesson_type" value="{{ $service->lesson_type }}" class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-3 text-[15px] text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    <div>
                        <label class="block text-[15px] font-semibold text-gray-800 mb-1.5">{{ crm_trans('services.format') }}</label>
                        <select name="format" class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-3 text-[15px] text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-primary-500 focus:border-primary-500">
                            <option value="">—</option>
                            <option value="online" @selected($service->format === 'online')>{{ crm_trans('fields.format_online') }}</option>
                            <option value="in_person" @selected($service->format === 'in_person')>{{ crm_trans('fields.format_in_person') }}</option>
                            <option value="hybrid" @selected($service->format === 'hybrid')>{{ crm_trans('fields.format_hybrid') }}</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[15px] font-semibold text-gray-800 mb-1.5">{{ crm_trans('services.duration') }}</label>
                            <input type="number" name="duration_minutes" value="{{ $service->duration_minutes }}" min="5" max="480" class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-3 text-[15px] text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-primary-500 focus:border-primary-500">
                        </div>
                        <div>
                            <label class="block text-[15px] font-semibold text-gray-800 mb-1.5">{{ crm_trans('services.price_text') }}</label>
                            <input type="text" name="price_text" value="{{ $service->price_text }}" class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-3 text-[15px] text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-primary-500 focus:border-primary-500">
                        </div>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-[15px] font-semibold text-gray-800 mb-1.5">{{ crm_trans('services.description') }}</label>
                        <textarea name="description" rows="2" class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-3 text-[15px] text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-primary-500 focus:border-primary-500">{{ $service->description }}</textarea>
                    </div>
                    <div class="sm:col-span-2 flex items-center gap-2">
                        <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-primary-600 hover:bg-primary-700 rounded-lg transition">
                            <i data-lucide="save" class="w-4 h-4"></i> {{ crm_trans('profile.save') }}
                        </button>
                        <button type="button" @click="editing = false" class="px-4 py-2 text-sm font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition">{{ crm_trans('profile.cancel') }}</button>
                    </div>
                </form>
            </div>
        @empty
            <p class="text-sm text-gray-400">{{ crm_trans('services.none') }}</p>
        @endforelse
    </div>

    {{-- =============== VIDEOS =============== --}}
    <div x-show="section === 'videos'" x-cloak>
        <div class="card p-6 mb-4">
            <h2 class="font-semibold text-gray-900 mb-4">{{ crm_trans('videos.add') }}</h2>
            <form method="POST" action="{{ crm_route('videos.store') }}" class="grid sm:grid-cols-2 gap-4">
                @csrf
                <div>
                    <label class="block text-[15px] font-semibold text-gray-800 mb-1.5">{{ crm_trans('videos.video_title') }}</label>
                    <input type="text" name="title" required class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-3 text-[15px] text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-primary-500 focus:border-primary-500">
                </div>
                <div>
                    <label class="block text-[15px] font-semibold text-gray-800 mb-1.5">{{ crm_trans('videos.youtube_url') }}</label>
                    <input type="url" name="url" required placeholder="https://www.youtube.com/watch?v=..." class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-3 text-[15px] text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-primary-500 focus:border-primary-500">
                </div>
                <div class="sm:col-span-2">
                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-primary-600 hover:bg-primary-700 rounded-lg transition">
                        <i data-lucide="plus" class="w-4 h-4"></i> {{ crm_trans('videos.add') }}
                    </button>
                </div>
            </form>
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
            @forelse($profile->videos as $video)
                <div class="card overflow-hidden" x-data="{ editing: false }">
                    <img src="{{ $video->thumbnailUrl() }}" alt="{{ $video->title }}" class="w-full h-36 object-cover">
                    {{-- Display view --}}
                    <div class="p-3 flex items-center justify-between gap-2" x-show="!editing">
                        <p class="text-sm font-semibold text-gray-900 truncate">{{ $video->title }}</p>
                        <div class="flex items-center shrink-0">
                            <button type="button" @click="editing = true; $nextTick(() => lucide.createIcons())" class="p-1.5 text-gray-400 hover:text-primary-600" title="{{ crm_trans('profile.edit') }}"><i data-lucide="pencil" class="w-4 h-4"></i></button>
                            <form method="POST" action="{{ crm_route('videos.destroy', $video) }}" onsubmit="return confirm('{{ crm_trans('profile.remove') }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="p-1.5 text-gray-400 hover:text-red-500"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                            </form>
                        </div>
                    </div>
                    {{-- Edit view --}}
                    <form method="POST" action="{{ crm_route('videos.update', $video) }}" class="p-3 space-y-3" x-show="editing" x-cloak>
                        @csrf @method('PUT')
                        <div>
                            <label class="block text-[13px] font-semibold text-gray-800 mb-1">{{ crm_trans('videos.video_title') }}</label>
                            <input type="text" name="title" value="{{ $video->title }}" required class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-3 py-2 text-sm text-gray-900 focus:bg-white focus:ring-primary-500 focus:border-primary-500">
                        </div>
                        <div>
                            <label class="block text-[13px] font-semibold text-gray-800 mb-1">{{ crm_trans('videos.youtube_url') }}</label>
                            <input type="url" name="url" value="{{ $video->url }}" required class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-3 py-2 text-sm text-gray-900 focus:bg-white focus:ring-primary-500 focus:border-primary-500">
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-semibold text-white bg-primary-600 hover:bg-primary-700 rounded-lg transition">
                                <i data-lucide="save" class="w-4 h-4"></i> {{ crm_trans('profile.save') }}
                            </button>
                            <button type="button" @click="editing = false" class="px-3 py-1.5 text-sm font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition">{{ crm_trans('profile.cancel') }}</button>
                        </div>
                    </form>
                </div>
            @empty
                <p class="text-sm text-gray-400">{{ crm_trans('videos.none') }}</p>
            @endforelse
        </div>
    </div>

    {{-- =============== PHOTOS & CERTIFICATES =============== --}}
    <div x-show="section === 'media'" x-cloak x-data="{ lb: false, lbSrc: '', lbTitle: '' }">
        <div class="card p-6 mb-4">
            <h2 class="font-semibold text-gray-900 mb-1">{{ crm_trans('media.title') }}</h2>
            <p class="text-sm text-gray-500 mb-4">{{ crm_trans('media.intro') }}</p>
            <form method="POST" action="{{ crm_route('media.store') }}" enctype="multipart/form-data" class="grid sm:grid-cols-2 gap-4">
                @csrf
                <input type="hidden" name="kind" value="photo">
                <div>
                    <label class="block text-[15px] font-semibold text-gray-800 mb-1.5">{{ crm_trans('media.file') }}</label>
                    <input type="file" name="file" required accept=".jpg,.jpeg,.png,.webp,.pdf" class="text-sm text-gray-600">
                </div>
                <div>
                    <label class="block text-[15px] font-semibold text-gray-800 mb-1.5">{{ crm_trans('services.service_title') }}</label>
                    <input type="text" name="title" class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-3 text-[15px] text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-primary-500 focus:border-primary-500">
                </div>
                <div class="sm:col-span-2 flex items-center justify-between gap-3">
                    <p class="text-xs text-gray-400">{{ crm_trans('media.public_hint') }}</p>
                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-primary-600 hover:bg-primary-700 rounded-lg transition shrink-0">
                        <i data-lucide="upload" class="w-4 h-4"></i> {{ crm_trans('media.add') }}
                    </button>
                </div>
            </form>
        </div>

        @php $gallery = $profile->media->where('kind', 'photo'); @endphp
        @if($gallery->isEmpty())
            <p class="text-sm text-gray-400">{{ crm_trans('media.none') }}</p>
        @else
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
                @foreach($gallery as $item)
                    <div class="relative group card p-0 overflow-hidden" x-data="{ editing: false }">
                        @if($item->isImage())
                            <button type="button" @click="lb=true; lbSrc=@js($item->publicUrl()); lbTitle=@js($item->title ?: $item->original_name)" class="block w-full">
                                <img src="{{ $item->publicUrl() }}" alt="{{ $item->title }}" class="w-full h-32 object-cover">
                            </button>
                        @else
                            <a href="{{ $item->publicUrl() }}" target="_blank" rel="noopener" class="flex flex-col items-center justify-center h-32 bg-gray-50 text-gray-500 hover:bg-gray-100 transition">
                                <i data-lucide="file-text" class="w-8 h-8 text-primary-500"></i>
                                <span class="text-xs mt-2 px-2 truncate max-w-full">{{ $item->title ?: $item->original_name }}</span>
                            </a>
                        @endif
                        <div class="absolute top-1.5 right-1.5 flex items-center gap-1 opacity-0 group-hover:opacity-100 transition">
                            <button type="button" @click="editing = true; $nextTick(() => lucide.createIcons())" class="p-1.5 bg-white/90 rounded-lg text-gray-600 hover:text-primary-600" title="{{ crm_trans('profile.edit') }}"><i data-lucide="pencil" class="w-3.5 h-3.5"></i></button>
                            <form method="POST" action="{{ crm_route('media.destroy', $item) }}" onsubmit="return confirm('{{ crm_trans('profile.remove') }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="p-1.5 bg-white/90 rounded-lg text-red-600 hover:text-red-700"><i data-lucide="trash-2" class="w-3.5 h-3.5"></i></button>
                            </form>
                        </div>
                        {{-- Title edit overlay --}}
                        <div x-show="editing" x-cloak class="absolute inset-0 bg-white/95 p-3 flex flex-col justify-center" @click.outside="editing = false">
                            <form method="POST" action="{{ crm_route('media.update', $item) }}" class="space-y-2">
                                @csrf @method('PUT')
                                <label class="block text-[13px] font-semibold text-gray-800">{{ crm_trans('services.service_title') }}</label>
                                <input type="text" name="title" value="{{ $item->title }}" class="w-full rounded-lg border-2 border-gray-200 bg-gray-50/50 px-3 py-2 text-sm text-gray-900 focus:bg-white focus:ring-primary-500 focus:border-primary-500">
                                <div class="flex items-center gap-2">
                                    <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-white bg-primary-600 hover:bg-primary-700 rounded-lg transition">
                                        <i data-lucide="save" class="w-3.5 h-3.5"></i> {{ crm_trans('profile.save') }}
                                    </button>
                                    <button type="button" @click="editing = false" class="px-3 py-1.5 text-xs font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition">{{ crm_trans('profile.cancel') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Lightbox popup --}}
        <div x-show="lb" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none" @keydown.escape.window="lb=false">
            <div class="absolute inset-0 bg-black/80" @click="lb=false"></div>
            <div class="relative max-w-4xl max-h-[90vh]">
                <img :src="lbSrc" :alt="lbTitle" class="max-w-full max-h-[85vh] rounded-xl object-contain">
                <p class="text-center text-white/80 text-sm mt-3" x-text="lbTitle"></p>
                <button type="button" @click="lb=false" class="absolute -top-3 -right-3 w-9 h-9 bg-white rounded-full flex items-center justify-center text-gray-700 hover:text-gray-900 shadow-lg"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
        </div>
    </div>

    {{-- =============== PAYMENT LINKS =============== --}}
    <div x-show="section === 'payment'" x-cloak>
        @if($capabilities['useExternalPaymentLinks'])
            <div class="card p-6 mb-4">
                <h2 class="font-semibold text-gray-900 mb-1">{{ crm_trans('payment_links.add') }}</h2>
                <p class="text-xs text-gray-500 mb-4">{{ crm_trans('payment_links.intro') }}</p>
                <form method="POST" action="{{ crm_route('payment-links.store') }}" class="grid sm:grid-cols-2 gap-4">
                    @csrf
                    <div>
                        <label class="block text-[15px] font-semibold text-gray-800 mb-1.5">{{ crm_trans('payment_links.label') }}</label>
                        <input type="text" name="label" required placeholder="{{ crm_trans('payment_links.label_placeholder') }}" class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-3 text-[15px] text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    <div>
                        <label class="block text-[15px] font-semibold text-gray-800 mb-1.5">{{ crm_trans('payment_links.url') }}</label>
                        <input type="url" name="url" required placeholder="https://" class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-3 text-[15px] text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    <div>
                        <label class="block text-[15px] font-semibold text-gray-800 mb-1.5">{{ crm_trans('payment_links.price_text') }}</label>
                        <input type="text" name="price_text" class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-3 text-[15px] text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    <div>
                        <label class="block text-[15px] font-semibold text-gray-800 mb-1.5">{{ crm_trans('payment_links.lesson_type') }}</label>
                        <input type="text" name="lesson_type" class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-3 text-[15px] text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    <div>
                        <label class="block text-[15px] font-semibold text-gray-800 mb-1.5">{{ crm_trans('payment_links.visibility') }}</label>
                        <select name="visibility" class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-3 text-[15px] text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-primary-500 focus:border-primary-500">
                            <option value="public">{{ crm_trans('payment_links.visibility_public') }}</option>
                            <option value="approved_students">{{ crm_trans('payment_links.visibility_approved_students') }}</option>
                            <option value="appointment_confirmation">{{ crm_trans('payment_links.visibility_appointment_confirmation') }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[15px] font-semibold text-gray-800 mb-1.5">{{ crm_trans('payment_links.description') }}</label>
                        <input type="text" name="description" class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-3 text-[15px] text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    <div class="sm:col-span-2">
                        <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-primary-600 hover:bg-primary-700 rounded-lg transition">
                            <i data-lucide="plus" class="w-4 h-4"></i> {{ crm_trans('payment_links.add') }}
                        </button>
                    </div>
                </form>
            </div>

            @forelse($profile->paymentLinks as $link)
                <div class="card p-4 mb-2" x-data="{ editing: false }">
                    {{-- Display view --}}
                    <div class="flex items-center justify-between gap-3" x-show="!editing">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-gray-900">{{ $link->label }} @if($link->price_text)<span class="text-gray-500 font-normal">· {{ $link->price_text }}</span>@endif</p>
                            <p class="text-xs text-gray-400 truncate">{{ $link->url }} · {{ crm_trans('payment_links.visibility_'.$link->visibility) }}</p>
                        </div>
                        <div class="flex items-center shrink-0">
                            <button type="button" @click="editing = true; $nextTick(() => lucide.createIcons())" class="p-2 text-gray-400 hover:text-primary-600" title="{{ crm_trans('profile.edit') }}"><i data-lucide="pencil" class="w-4 h-4"></i></button>
                            <form method="POST" action="{{ crm_route('payment-links.destroy', $link) }}" onsubmit="return confirm('{{ crm_trans('profile.remove') }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="p-2 text-gray-400 hover:text-red-500"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                            </form>
                        </div>
                    </div>
                    {{-- Edit view --}}
                    <form method="POST" action="{{ crm_route('payment-links.update', $link) }}" class="grid sm:grid-cols-2 gap-4" x-show="editing" x-cloak>
                        @csrf @method('PUT')
                        <div>
                            <label class="block text-[15px] font-semibold text-gray-800 mb-1.5">{{ crm_trans('payment_links.label') }}</label>
                            <input type="text" name="label" value="{{ $link->label }}" required class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-3 text-[15px] text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-primary-500 focus:border-primary-500">
                        </div>
                        <div>
                            <label class="block text-[15px] font-semibold text-gray-800 mb-1.5">{{ crm_trans('payment_links.url') }}</label>
                            <input type="url" name="url" value="{{ $link->url }}" required class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-3 text-[15px] text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-primary-500 focus:border-primary-500">
                        </div>
                        <div>
                            <label class="block text-[15px] font-semibold text-gray-800 mb-1.5">{{ crm_trans('payment_links.price_text') }}</label>
                            <input type="text" name="price_text" value="{{ $link->price_text }}" class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-3 text-[15px] text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-primary-500 focus:border-primary-500">
                        </div>
                        <div>
                            <label class="block text-[15px] font-semibold text-gray-800 mb-1.5">{{ crm_trans('payment_links.lesson_type') }}</label>
                            <input type="text" name="lesson_type" value="{{ $link->lesson_type }}" class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-3 text-[15px] text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-primary-500 focus:border-primary-500">
                        </div>
                        <div>
                            <label class="block text-[15px] font-semibold text-gray-800 mb-1.5">{{ crm_trans('payment_links.visibility') }}</label>
                            <select name="visibility" class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-3 text-[15px] text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-primary-500 focus:border-primary-500">
                                <option value="public" @selected($link->visibility === 'public')>{{ crm_trans('payment_links.visibility_public') }}</option>
                                <option value="approved_students" @selected($link->visibility === 'approved_students')>{{ crm_trans('payment_links.visibility_approved_students') }}</option>
                                <option value="appointment_confirmation" @selected($link->visibility === 'appointment_confirmation')>{{ crm_trans('payment_links.visibility_appointment_confirmation') }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[15px] font-semibold text-gray-800 mb-1.5">{{ crm_trans('payment_links.description') }}</label>
                            <input type="text" name="description" value="{{ $link->description }}" class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-3 text-[15px] text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-primary-500 focus:border-primary-500">
                        </div>
                        <div class="sm:col-span-2 flex items-center gap-2">
                            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-primary-600 hover:bg-primary-700 rounded-lg transition">
                                <i data-lucide="save" class="w-4 h-4"></i> {{ crm_trans('profile.save') }}
                            </button>
                            <button type="button" @click="editing = false" class="px-4 py-2 text-sm font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition">{{ crm_trans('profile.cancel') }}</button>
                        </div>
                    </form>
                </div>
            @empty
                <p class="text-sm text-gray-400">{{ crm_trans('payment_links.none') }}</p>
            @endforelse
        @else
            <div class="card p-8 text-center">
                <i data-lucide="lock" class="w-8 h-8 text-amber-400 mx-auto mb-3"></i>
                <p class="text-sm text-gray-600">{{ crm_trans('payment_links.premium_required') }}</p>
            </div>
        @endif
    </div>
</div>
@endsection
