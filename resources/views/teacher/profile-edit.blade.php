@extends('teacher.layouts.crm')

@section('title', __('teacher.profile.title'))

@section('content')
<div class="max-w-4xl" x-data="{ section: 'general' }">

    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ __('teacher.profile.title') }}</h1>
            <p class="text-sm text-gray-500 mt-1">{{ __('teacher.profile.subtitle') }}</p>
        </div>
        <div class="flex items-center gap-2">
            @if($profile->canBeSubmitted())
                <form method="POST" action="{{ route('teacher.profile.submit') }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-primary-600 hover:bg-primary-700 rounded-lg transition">
                        <i data-lucide="send" class="w-4 h-4"></i> {{ __('teacher.dashboard.submit_for_review') }}
                    </button>
                </form>
            @endif
            <a href="{{ route('teacher.profile.preview') }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition">
                <i data-lucide="eye" class="w-4 h-4"></i> {{ __('teacher.nav.view_as_student') }}
            </a>
        </div>
    </div>

    @if($profile->isPubliclyVisible())
        <div class="mb-6 px-4 py-3 bg-primary-50 border border-primary-100 rounded-xl flex items-center gap-3 text-sm">
            <i data-lucide="link" class="w-4 h-4 text-primary-600 shrink-0"></i>
            <span class="text-gray-600">{{ __('teacher.profile.public_url') }}:</span>
            <a href="{{ $profile->publicUrl() }}" class="font-semibold text-primary-700 truncate">{{ $profile->publicUrl() }}</a>
        </div>
    @endif

    {{-- Section tabs --}}
    <div class="flex flex-wrap gap-2 mb-6">
        @foreach([
            'general' => __('teacher.profile.section_general'),
            'music' => __('teacher.profile.section_music'),
            'services' => __('teacher.profile.section_services'),
            'videos' => __('teacher.profile.section_videos'),
            'media' => __('teacher.profile.section_media'),
            'payment' => __('teacher.profile.section_payment_links'),
            'seo' => __('teacher.profile.section_seo'),
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
    <form method="POST" action="{{ route('teacher.profile.update') }}" x-show="['general','music','seo'].includes(section)">
        @csrf
        @method('PUT')

        {{-- GENERAL --}}
        <div class="card p-6 space-y-5" x-show="section === 'general'" x-cloak>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[15px] font-semibold text-gray-800 mb-1.5">{{ __('teacher.fields.headline') }}</label>
                    <input type="text" name="headline" value="{{ old('headline', $profile->headline) }}" maxlength="160"
                           placeholder="{{ __('teacher.fields.headline_placeholder') }}"
                           class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-3 text-[15px] text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-primary-500 focus:border-primary-500">
                </div>
                <div>
                    <label class="block text-[15px] font-semibold text-gray-800 mb-1.5">{{ __('teacher.fields.expertise') }}</label>
                    <input type="text" name="expertise" value="{{ old('expertise', $profile->expertise) }}"
                           placeholder="{{ __('teacher.fields.expertise_placeholder') }}"
                           class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-3 text-[15px] text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-primary-500 focus:border-primary-500">
                </div>
            </div>

            <div>
                <label class="block text-[15px] font-semibold text-gray-800 mb-1.5">{{ __('teacher.fields.about') }}</label>
                <textarea name="about" rows="5" class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-3 text-[15px] text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-primary-500 focus:border-primary-500">{{ old('about', $profile->about) }}</textarea>
            </div>

            <div>
                <label class="block text-[15px] font-semibold text-gray-800 mb-1.5">{{ __('teacher.fields.teaching_methodology') }}</label>
                <textarea name="teaching_methodology" rows="4" class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-3 text-[15px] text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-primary-500 focus:border-primary-500">{{ old('teaching_methodology', $profile->teaching_methodology) }}</textarea>
            </div>

            <div>
                <label class="block text-[15px] font-semibold text-gray-800 mb-2">{{ __('teacher.fields.teaching_formats') }}</label>
                <div class="flex flex-wrap gap-4">
                    @foreach(['online' => __('teacher.fields.format_online'), 'in_person' => __('teacher.fields.format_in_person'), 'hybrid' => __('teacher.fields.format_hybrid')] as $format => $label)
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
                    <label class="block text-[15px] font-semibold text-gray-800 mb-1.5">{{ __('teacher.fields.lesson_types') }}</label>
                    <input type="text" name="lesson_types" value="{{ old('lesson_types', $toCsv($profile->lesson_types)) }}"
                           class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-3 text-[15px] text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-primary-500 focus:border-primary-500">
                    <p class="text-xs text-gray-400 mt-1">{{ __('teacher.fields.lesson_types_hint') }}</p>
                </div>
                <div>
                    <label class="block text-[15px] font-semibold text-gray-800 mb-1.5">{{ __('teacher.fields.languages') }}</label>
                    <input type="text" name="languages" value="{{ old('languages', $toCsv($profile->languages)) }}"
                           class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-3 text-[15px] text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-primary-500 focus:border-primary-500">
                    <p class="text-xs text-gray-400 mt-1">{{ __('teacher.fields.languages_hint') }}</p>
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[15px] font-semibold text-gray-800 mb-1.5">{{ __('teacher.fields.country') }}</label>
                    <input type="text" name="country" value="{{ old('country', $profile->country) }}"
                           class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-3 text-[15px] text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-primary-500 focus:border-primary-500">
                </div>
                <div>
                    <label class="block text-[15px] font-semibold text-gray-800 mb-1.5">{{ __('teacher.fields.city') }}</label>
                    <input type="text" name="city" value="{{ old('city', $profile->city) }}"
                           class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-3 text-[15px] text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-primary-500 focus:border-primary-500">
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[15px] font-semibold text-gray-800 mb-1.5">{{ __('teacher.fields.public_email') }}</label>
                    <input type="email" name="public_email" value="{{ old('public_email', $profile->public_email) }}"
                           class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-3 text-[15px] text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-primary-500 focus:border-primary-500">
                    <label class="inline-flex items-center gap-2 mt-2 text-[15px] text-gray-700">
                        <input type="checkbox" name="show_email" value="1" @checked(old('show_email', $profile->show_email)) class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                        {{ __('teacher.fields.show_email') }}
                    </label>
                </div>
                <div>
                    <label class="block text-[15px] font-semibold text-gray-800 mb-1.5">{{ __('teacher.fields.public_phone') }}</label>
                    <input type="text" name="public_phone" value="{{ old('public_phone', $profile->public_phone) }}"
                           class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-3 text-[15px] text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-primary-500 focus:border-primary-500">
                    <label class="inline-flex items-center gap-2 mt-2 text-[15px] text-gray-700">
                        <input type="checkbox" name="show_phone" value="1" @checked(old('show_phone', $profile->show_phone)) class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                        {{ __('teacher.fields.show_phone') }}
                    </label>
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[15px] font-semibold text-gray-800 mb-1.5">{{ __('teacher.fields.website') }}</label>
                    <input type="url" name="website_url" value="{{ old('website_url', $profile->website_url) }}"
                           class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-3 text-[15px] text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-primary-500 focus:border-primary-500">
                </div>
                @foreach(['instagram', 'tiktok', 'youtube', 'linkedin', 'facebook'] as $network)
                    <div>
                        <label class="block text-[15px] font-semibold text-gray-800 mb-1.5">{{ __('teacher.fields.'.$network) }}</label>
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
                    <label class="block text-[15px] font-semibold text-gray-800 mb-1.5">{{ __('teacher.fields.primary_instrument') }}</label>
                    <input type="text" name="primary_instrument" value="{{ old('primary_instrument', $profile->primary_instrument) }}"
                           class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-3 text-[15px] text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-primary-500 focus:border-primary-500">
                </div>
                <div>
                    <label class="block text-[15px] font-semibold text-gray-800 mb-1.5">{{ __('teacher.fields.other_instruments') }}</label>
                    <input type="text" name="instruments" value="{{ old('instruments', $profile->instruments->pluck('instrument')->implode(', ')) }}"
                           class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-3 text-[15px] text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-primary-500 focus:border-primary-500">
                    <p class="text-xs text-gray-400 mt-1">{{ __('teacher.fields.languages_hint') }}</p>
                </div>
            </div>

            <div>
                <label class="block text-[15px] font-semibold text-gray-800 mb-1.5">{{ __('teacher.fields.education_status') }}</label>
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
                <label class="block text-[15px] font-semibold text-gray-800 mb-2">{{ __('teacher.fields.educations') }}</label>
                <template x-for="(row, index) in rows" :key="index">
                    <div class="grid sm:grid-cols-4 gap-2 mb-2 items-start">
                        <input type="text" :name="'educations['+index+'][institution]'" x-model="row.institution"
                               placeholder="{{ __('teacher.fields.institution') }}"
                               class="rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-3 text-[15px] text-gray-900 focus:bg-white focus:ring-primary-500 focus:border-primary-500">
                        <input type="text" :name="'educations['+index+'][program]'" x-model="row.program"
                               placeholder="{{ __('teacher.fields.program') }}"
                               class="rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-3 text-[15px] text-gray-900 focus:bg-white focus:ring-primary-500 focus:border-primary-500">
                        <input type="text" :name="'educations['+index+'][field_of_study]'" x-model="row.field_of_study"
                               placeholder="{{ __('teacher.fields.field_of_study') }}"
                               class="rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-3 text-[15px] text-gray-900 focus:bg-white focus:ring-primary-500 focus:border-primary-500">
                        <div class="flex gap-2">
                            <input type="number" :name="'educations['+index+'][graduation_year]'" x-model="row.graduation_year"
                                   placeholder="{{ __('teacher.fields.graduation_year') }}" min="1940" max="2100"
                                   class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-3 text-[15px] text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-primary-500 focus:border-primary-500">
                            <button type="button" @click="rows.splice(index, 1)" class="p-2 text-gray-400 hover:text-red-500" x-show="rows.length > 1">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>
                </template>
                <button type="button" @click="rows.push({institution:'',program:'',field_of_study:'',graduation_year:''}); $nextTick(() => lucide.createIcons())"
                        class="inline-flex items-center gap-1 text-sm font-semibold text-primary-600 hover:text-primary-700 mt-1">
                    <i data-lucide="plus" class="w-4 h-4"></i> {{ __('teacher.profile.add_more') }}
                </button>
            </div>

            <div class="grid sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-[15px] font-semibold text-gray-800 mb-1.5">{{ __('teacher.fields.certificates') }}</label>
                    <textarea name="certificates" rows="3" class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-3 text-[15px] text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-primary-500 focus:border-primary-500">{{ old('certificates', $profile->certificates) }}</textarea>
                </div>
                <div>
                    <label class="block text-[15px] font-semibold text-gray-800 mb-1.5">{{ __('teacher.fields.workshops') }}</label>
                    <textarea name="workshops" rows="3" class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-3 text-[15px] text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-primary-500 focus:border-primary-500">{{ old('workshops', $profile->workshops) }}</textarea>
                </div>
                <div>
                    <label class="block text-[15px] font-semibold text-gray-800 mb-1.5">{{ __('teacher.fields.masterclasses') }}</label>
                    <textarea name="masterclasses" rows="3" class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-3 text-[15px] text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-primary-500 focus:border-primary-500">{{ old('masterclasses', $profile->masterclasses) }}</textarea>
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[15px] font-semibold text-gray-800 mb-1.5">{{ __('teacher.fields.experience_years') }}</label>
                    <input type="number" name="experience_years" value="{{ old('experience_years', $profile->experience_years) }}" min="0" max="80"
                           class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-3 text-[15px] text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-primary-500 focus:border-primary-500">
                </div>
                <div>
                    <label class="block text-[15px] font-semibold text-gray-800 mb-1.5">{{ __('teacher.fields.teaching_experience') }}</label>
                    <textarea name="teaching_experience" rows="2" class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-3 text-[15px] text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-primary-500 focus:border-primary-500">{{ old('teaching_experience', $profile->teaching_experience) }}</textarea>
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                @foreach(['genres', 'expertise_areas', 'age_groups', 'skill_levels', 'teaching_languages'] as $listField)
                    <div>
                        <label class="block text-[15px] font-semibold text-gray-800 mb-1.5">{{ __('teacher.fields.'.$listField) }}</label>
                        <input type="text" name="{{ $listField }}" value="{{ old($listField, $toCsv($profile->{$listField})) }}"
                               class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-3 text-[15px] text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-primary-500 focus:border-primary-500">
                        <p class="text-xs text-gray-400 mt-1">{{ __('teacher.fields.languages_hint') }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- SEO --}}
        <div class="card p-6 space-y-5" x-show="section === 'seo'" x-cloak>
            <div>
                <label class="block text-[15px] font-semibold text-gray-800 mb-1.5">{{ __('teacher.fields.seo_title') }}</label>
                <input type="text" name="seo_title" value="{{ old('seo_title', $profile->seo_title) }}" maxlength="255"
                       class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-3 text-[15px] text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-primary-500 focus:border-primary-500">
            </div>
            <div>
                <label class="block text-[15px] font-semibold text-gray-800 mb-1.5">{{ __('teacher.fields.seo_description') }}</label>
                <textarea name="seo_description" rows="3" maxlength="320" class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-3 text-[15px] text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-primary-500 focus:border-primary-500">{{ old('seo_description', $profile->seo_description) }}</textarea>
            </div>
        </div>

        <div class="mt-4">
            <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-semibold text-white bg-primary-600 hover:bg-primary-700 rounded-lg transition">
                <i data-lucide="save" class="w-4 h-4"></i> {{ __('teacher.profile.save_draft') }}
            </button>
        </div>
    </form>

    {{-- =============== COVER IMAGE (general section, separate form for file upload) =============== --}}
    <div class="card p-6 mt-6" x-show="section === 'general'" x-cloak>
        <label class="block text-[15px] font-semibold text-gray-800 mb-2">{{ __('teacher.fields.cover_image') }}</label>
        @if($profile->coverImageUrl())
            <img src="{{ $profile->coverImageUrl() }}" alt="" class="w-full h-40 object-cover rounded-xl mb-3">
        @endif
        <form method="POST" action="{{ route('teacher.profile.cover') }}" enctype="multipart/form-data" class="flex flex-wrap items-center gap-3">
            @csrf
            <input type="file" name="cover" accept="image/*" required class="text-sm text-gray-600">
            <button type="submit" class="px-4 py-2 text-sm font-semibold text-primary-700 bg-primary-50 hover:bg-primary-100 rounded-lg transition">{{ __('teacher.fields.upload_cover') }}</button>
        </form>
    </div>

    {{-- =============== SERVICES =============== --}}
    <div x-show="section === 'services'" x-cloak>
        <div class="card p-6 mb-4">
            <h2 class="font-semibold text-gray-900 mb-4">{{ __('teacher.services.add') }}</h2>
            <form method="POST" action="{{ route('teacher.services.store') }}" class="grid sm:grid-cols-2 gap-4">
                @csrf
                <div>
                    <label class="block text-[15px] font-semibold text-gray-800 mb-1.5">{{ __('teacher.services.service_title') }}</label>
                    <input type="text" name="title" required class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-3 text-[15px] text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-primary-500 focus:border-primary-500">
                </div>
                <div>
                    <label class="block text-[15px] font-semibold text-gray-800 mb-1.5">{{ __('teacher.services.lesson_type') }}</label>
                    <input type="text" name="lesson_type" class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-3 text-[15px] text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-primary-500 focus:border-primary-500">
                </div>
                <div>
                    <label class="block text-[15px] font-semibold text-gray-800 mb-1.5">{{ __('teacher.services.format') }}</label>
                    <select name="format" class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-3 text-[15px] text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-primary-500 focus:border-primary-500">
                        <option value="">—</option>
                        <option value="online">{{ __('teacher.fields.format_online') }}</option>
                        <option value="in_person">{{ __('teacher.fields.format_in_person') }}</option>
                        <option value="hybrid">{{ __('teacher.fields.format_hybrid') }}</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-[15px] font-semibold text-gray-800 mb-1.5">{{ __('teacher.services.duration') }}</label>
                        <input type="number" name="duration_minutes" min="5" max="480" class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-3 text-[15px] text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    <div>
                        <label class="block text-[15px] font-semibold text-gray-800 mb-1.5">{{ __('teacher.services.price_text') }}</label>
                        <input type="text" name="price_text" class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-3 text-[15px] text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-primary-500 focus:border-primary-500">
                    </div>
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-[15px] font-semibold text-gray-800 mb-1.5">{{ __('teacher.services.description') }}</label>
                    <textarea name="description" rows="2" class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-3 text-[15px] text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-primary-500 focus:border-primary-500"></textarea>
                </div>
                <div class="sm:col-span-2">
                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-primary-600 hover:bg-primary-700 rounded-lg transition">
                        <i data-lucide="plus" class="w-4 h-4"></i> {{ __('teacher.services.add') }}
                    </button>
                </div>
            </form>
        </div>

        @forelse($profile->services as $service)
            <div class="card p-4 mb-2 flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="font-semibold text-gray-900 text-sm">{{ $service->title }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">
                        {{ $service->lesson_type }}
                        @if($service->duration_minutes) · {{ $service->duration_minutes }} min @endif
                        @if($service->price_text) · {{ $service->price_text }} @endif
                    </p>
                    @if($service->description)<p class="text-sm text-gray-600 mt-1">{{ $service->description }}</p>@endif
                </div>
                <form method="POST" action="{{ route('teacher.services.destroy', $service) }}" onsubmit="return confirm('{{ __('teacher.profile.remove') }}?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="p-2 text-gray-400 hover:text-red-500"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                </form>
            </div>
        @empty
            <p class="text-sm text-gray-400">{{ __('teacher.services.none') }}</p>
        @endforelse
    </div>

    {{-- =============== VIDEOS =============== --}}
    <div x-show="section === 'videos'" x-cloak>
        <div class="card p-6 mb-4">
            <h2 class="font-semibold text-gray-900 mb-4">{{ __('teacher.videos.add') }}</h2>
            <form method="POST" action="{{ route('teacher.videos.store') }}" class="grid sm:grid-cols-2 gap-4">
                @csrf
                <div>
                    <label class="block text-[15px] font-semibold text-gray-800 mb-1.5">{{ __('teacher.videos.video_title') }}</label>
                    <input type="text" name="title" required class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-3 text-[15px] text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-primary-500 focus:border-primary-500">
                </div>
                <div>
                    <label class="block text-[15px] font-semibold text-gray-800 mb-1.5">{{ __('teacher.videos.youtube_url') }}</label>
                    <input type="url" name="url" required placeholder="https://www.youtube.com/watch?v=..." class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-3 text-[15px] text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-primary-500 focus:border-primary-500">
                </div>
                <div class="sm:col-span-2">
                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-primary-600 hover:bg-primary-700 rounded-lg transition">
                        <i data-lucide="plus" class="w-4 h-4"></i> {{ __('teacher.videos.add') }}
                    </button>
                </div>
            </form>
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
            @forelse($profile->videos as $video)
                <div class="card overflow-hidden">
                    <img src="{{ $video->thumbnailUrl() }}" alt="{{ $video->title }}" class="w-full h-36 object-cover">
                    <div class="p-3 flex items-center justify-between gap-2">
                        <p class="text-sm font-semibold text-gray-900 truncate">{{ $video->title }}</p>
                        <form method="POST" action="{{ route('teacher.videos.destroy', $video) }}" onsubmit="return confirm('{{ __('teacher.profile.remove') }}?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="p-1.5 text-gray-400 hover:text-red-500"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                        </form>
                    </div>
                </div>
            @empty
                <p class="text-sm text-gray-400">{{ __('teacher.videos.none') }}</p>
            @endforelse
        </div>
    </div>

    {{-- =============== MEDIA =============== --}}
    <div x-show="section === 'media'" x-cloak>
        <div class="card p-6 mb-4">
            <h2 class="font-semibold text-gray-900 mb-4">{{ __('teacher.media.add') }}</h2>
            <form method="POST" action="{{ route('teacher.media.store') }}" enctype="multipart/form-data" class="grid sm:grid-cols-2 gap-4">
                @csrf
                <div>
                    <label class="block text-[15px] font-semibold text-gray-800 mb-1.5">{{ __('teacher.media.kind') }}</label>
                    <select name="kind" class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-3 text-[15px] text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-primary-500 focus:border-primary-500">
                        <option value="photo">{{ __('teacher.media.kind_photo') }}</option>
                        <option value="document">{{ __('teacher.media.kind_document') }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[15px] font-semibold text-gray-800 mb-1.5">{{ __('teacher.media.visibility') }}</label>
                    <select name="visibility" class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-3 text-[15px] text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-primary-500 focus:border-primary-500">
                        <option value="private">{{ __('teacher.media.visibility_private') }}</option>
                        <option value="public">{{ __('teacher.media.visibility_public') }}</option>
                        <option value="shared">{{ __('teacher.media.visibility_shared') }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[15px] font-semibold text-gray-800 mb-1.5">{{ __('teacher.media.file') }}</label>
                    <input type="file" name="file" required class="text-sm text-gray-600">
                </div>
                <div>
                    <label class="block text-[15px] font-semibold text-gray-800 mb-1.5">{{ __('teacher.services.service_title') }}</label>
                    <input type="text" name="title" class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-3 text-[15px] text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-primary-500 focus:border-primary-500">
                </div>
                <div class="sm:col-span-2">
                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-primary-600 hover:bg-primary-700 rounded-lg transition">
                        <i data-lucide="upload" class="w-4 h-4"></i> {{ __('teacher.media.add') }}
                    </button>
                </div>
            </form>
        </div>

        @forelse($profile->media as $item)
            <div class="card p-4 mb-2 flex items-center justify-between gap-3">
                <div class="flex items-center gap-3 min-w-0">
                    @if($item->kind === 'photo' && $item->isPublic())
                        <img src="{{ $item->publicUrl() }}" alt="" class="w-12 h-12 object-cover rounded-lg shrink-0">
                    @else
                        <div class="w-12 h-12 rounded-lg bg-gray-100 flex items-center justify-center shrink-0">
                            <i data-lucide="{{ $item->kind === 'photo' ? 'image' : 'file-text' }}" class="w-5 h-5 text-gray-400"></i>
                        </div>
                    @endif
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-gray-900 truncate">{{ $item->title ?: $item->original_name }}</p>
                        <p class="text-xs text-gray-400">{{ __('teacher.media.kind_'.$item->kind) }} · {{ __('teacher.media.visibility_'.$item->visibility) }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-1 shrink-0">
                    @unless($item->isPublic())
                        <a href="{{ route('teacher.media.download', $item) }}" class="p-2 text-gray-400 hover:text-primary-600"><i data-lucide="download" class="w-4 h-4"></i></a>
                    @endunless
                    <form method="POST" action="{{ route('teacher.media.destroy', $item) }}" onsubmit="return confirm('{{ __('teacher.profile.remove') }}?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="p-2 text-gray-400 hover:text-red-500"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                    </form>
                </div>
            </div>
        @empty
            <p class="text-sm text-gray-400">{{ __('teacher.media.none') }}</p>
        @endforelse
    </div>

    {{-- =============== PAYMENT LINKS =============== --}}
    <div x-show="section === 'payment'" x-cloak>
        @if($capabilities['useExternalPaymentLinks'])
            <div class="card p-6 mb-4">
                <h2 class="font-semibold text-gray-900 mb-1">{{ __('teacher.payment_links.add') }}</h2>
                <p class="text-xs text-gray-500 mb-4">{{ __('teacher.payment_links.intro') }}</p>
                <form method="POST" action="{{ route('teacher.payment-links.store') }}" class="grid sm:grid-cols-2 gap-4">
                    @csrf
                    <div>
                        <label class="block text-[15px] font-semibold text-gray-800 mb-1.5">{{ __('teacher.payment_links.label') }}</label>
                        <input type="text" name="label" required placeholder="{{ __('teacher.payment_links.label_placeholder') }}" class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-3 text-[15px] text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    <div>
                        <label class="block text-[15px] font-semibold text-gray-800 mb-1.5">{{ __('teacher.payment_links.url') }}</label>
                        <input type="url" name="url" required placeholder="https://" class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-3 text-[15px] text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    <div>
                        <label class="block text-[15px] font-semibold text-gray-800 mb-1.5">{{ __('teacher.payment_links.price_text') }}</label>
                        <input type="text" name="price_text" class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-3 text-[15px] text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    <div>
                        <label class="block text-[15px] font-semibold text-gray-800 mb-1.5">{{ __('teacher.payment_links.lesson_type') }}</label>
                        <input type="text" name="lesson_type" class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-3 text-[15px] text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    <div>
                        <label class="block text-[15px] font-semibold text-gray-800 mb-1.5">{{ __('teacher.payment_links.visibility') }}</label>
                        <select name="visibility" class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-3 text-[15px] text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-primary-500 focus:border-primary-500">
                            <option value="public">{{ __('teacher.payment_links.visibility_public') }}</option>
                            <option value="approved_students">{{ __('teacher.payment_links.visibility_approved_students') }}</option>
                            <option value="appointment_confirmation">{{ __('teacher.payment_links.visibility_appointment_confirmation') }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[15px] font-semibold text-gray-800 mb-1.5">{{ __('teacher.payment_links.description') }}</label>
                        <input type="text" name="description" class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-3 text-[15px] text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    <div class="sm:col-span-2">
                        <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-primary-600 hover:bg-primary-700 rounded-lg transition">
                            <i data-lucide="plus" class="w-4 h-4"></i> {{ __('teacher.payment_links.add') }}
                        </button>
                    </div>
                </form>
            </div>

            @forelse($profile->paymentLinks as $link)
                <div class="card p-4 mb-2 flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-gray-900">{{ $link->label }} @if($link->price_text)<span class="text-gray-500 font-normal">· {{ $link->price_text }}</span>@endif</p>
                        <p class="text-xs text-gray-400 truncate">{{ $link->url }} · {{ __('teacher.payment_links.visibility_'.$link->visibility) }}</p>
                    </div>
                    <form method="POST" action="{{ route('teacher.payment-links.destroy', $link) }}" onsubmit="return confirm('{{ __('teacher.profile.remove') }}?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="p-2 text-gray-400 hover:text-red-500"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                    </form>
                </div>
            @empty
                <p class="text-sm text-gray-400">{{ __('teacher.payment_links.none') }}</p>
            @endforelse
        @else
            <div class="card p-8 text-center">
                <i data-lucide="lock" class="w-8 h-8 text-amber-400 mx-auto mb-3"></i>
                <p class="text-sm text-gray-600">{{ __('teacher.payment_links.premium_required') }}</p>
            </div>
        @endif
    </div>
</div>
@endsection
