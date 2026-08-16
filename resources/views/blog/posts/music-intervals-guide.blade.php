{{--
    /blog/music-intervals-guide

    Body only — hero, byline, table of contents, author box, CTA and all
    structured data come from blog.post-layout. Every string is addressed
    through $t(), which is bound to this post's blog.* section by
    BlogPostController, so translating the post means copying that one section
    into resources/lang/{locale}/blog.php and nothing else.

    Heading ids (s1…s12) line up with the `toc` list in config('blog.posts');
    keep the two in step when adding or removing an H2.
--}}
@extends('blog.post-layout')

@section('article')

    {{-- Questions for the in-article exercise boxes. Generated per request, so
         no two readers get the same five — and a reload is a fresh set. --}}
    @php
        $melodicQuestions = $exercises->intervals('melodic-interval-practice', 'easy', 'ascending');
        $harmonicQuestions = $exercises->intervals('harmonic-interval-practice', 'medium');
        $mixedQuestions = $exercises->intervals('melodic-interval-practice', 'medium', 'mixed');
    @endphp

    <p>{{ $t('intro_1') }}</p>
    <p>{{ $t('intro_2') }}</p>
    <p>{{ $t('intro_3') }}</p>
    <p>{{ $t('intro_4') }}</p>

    {{-- 1 ─────────────────────────────────────────────────────────────────── --}}
    @include('blog.partials.section-heading', ['n' => 1, 'id' => 's1', 'key' => 'what_h'])
    <p>{{ $t('what_p1') }}</p>
    <p>{{ $t('what_example_label') }}</p>
    <ul>
        <li>{{ $t('what_ex_1') }}</li>
        <li>{{ $t('what_ex_2') }}</li>
        <li>{{ $t('what_ex_3') }}</li>
    </ul>
    <p>{{ $t('what_p2') }}</p>
    <ul>
        <li><strong>{{ $t('what_visual_label') }}:</strong> {{ $t('what_visual') }}</li>
        <li><strong>{{ $t('what_aural_label') }}:</strong> {{ $t('what_aural') }}</li>
    </ul>
    <p>{{ $t('what_p3') }}</p>

    @include('blog.partials.figure', ['fig' => 'staff-interval', 'caption' => 'fig_staff_interval_caption', 'tone' => 'purple'])
    @include('blog.partials.takeaway', ['key' => 'take_what'])

    {{-- 2 ─────────────────────────────────────────────────────────────────── --}}
    @include('blog.partials.section-heading', ['n' => 2, 'id' => 's2', 'key' => 'nq_h'])
    <p>{{ $t('nq_p1') }}</p>

    <h3>{{ $t('nq_number_h') }}</h3>
    <p>{{ $t('nq_number_p1') }}</p>
    <p>{{ $t('nq_number_p2') }}</p>
    <p>{{ $t('nq_number_p3') }}</p>
    <div class="hv-callout"><span class="hv-mono">{{ $t('nq_number_count') }}</span></div>
    <p>{{ $t('nq_number_p4') }}</p>

    @include('blog.partials.figure', ['fig' => 'counting', 'caption' => 'fig_counting_caption', 'tone' => 'orange'])
    <p>{{ $t('nq_number_p5') }}</p>
    <p>{{ $t('nq_number_p6') }}</p>

    <h3>{{ $t('nq_quality_h') }}</h3>
    <p>{{ $t('nq_quality_p1') }}</p>
    <p>{{ $t('nq_quality_p2') }}</p>
    <ul>
        @foreach (range(1, 5) as $i)
            <li>{{ $t('nq_quality_'.$i) }}</li>
        @endforeach
    </ul>
    <p>{{ $t('nq_quality_p3') }}</p>
    <p>{{ $t('nq_quality_p4') }}</p>
    <p>{{ $t('nq_quality_p5') }}</p>
    <p>{{ $t('nq_quality_p6') }}</p>
    <p>{{ $t('nq_quality_p7') }}</p>

    @include('blog.partials.figure', ['fig' => 'quality', 'caption' => 'fig_quality_caption', 'tone' => 'purple'])
    @include('blog.partials.takeaway', ['key' => 'take_nq'])

    {{-- 3 ─────────────────────────────────────────────────────────────────── --}}
    @include('blog.partials.section-heading', ['n' => 3, 'id' => 's3', 'key' => 'main_h'])
    <p>{{ $t('main_p1') }}</p>
    <p>{{ $t('main_p2') }}</p>

    @php
        // token => [abbreviation, semitones]. Names and examples come from the
        // lang file (iv_* / ex_*) so the whole table translates with the post.
        // A null abbreviation is one that reads as a phrase rather than pure
        // notation, and comes from the lang file too (abbr_*).
        $intervalRows = [
            'p1' => ['P1', 0],
            'm2' => ['m2', 1],
            'M2' => ['M2', 2],
            'm3' => ['m3', 3],
            'M3' => ['M3', 4],
            'p4' => ['P4', 5],
            'tt' => [null, 6],
            'p5' => ['P5', 7],
            'm6' => ['m6', 8],
            'M6' => ['M6', 9],
            'm7' => ['m7', 10],
            'M7' => ['M7', 11],
            'p8' => ['P8', 12],
        ];
    @endphp

    <div class="hv-table-wrap">
        <table class="hv-table">
            <caption>{{ $t('table_caption') }}</caption>
            <thead>
                <tr>
                    <th scope="col">{{ $t('table_interval') }}</th>
                    <th scope="col">{{ $t('table_abbrev') }}</th>
                    <th scope="col" class="is-num">{{ $t('table_semitones') }}</th>
                    <th scope="col">{{ $t('table_example') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($intervalRows as $token => $row)
                <tr>
                    <td class="is-name">{{ $t('iv_'.$token) }}</td>
                    <td class="is-abbrev">{{ $row[0] ?? $t('abbr_'.$token) }}</td>
                    <td class="is-num">{{ $row[1] }}</td>
                    <td>{{ $t('ex_'.$token) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <p>{{ $t('main_p3') }}</p>
    <p>{{ $t('main_example_label') }}</p>
    <ul>
        <li>{{ $t('main_tt_1') }}</li>
        <li>{{ $t('main_tt_2') }}</li>
    </ul>
    <p>{{ $t('main_p4') }}</p>

    @include('blog.partials.figure', ['fig' => 'semitone-ruler', 'caption' => 'fig_ruler_figcaption', 'tone' => 'purple'])

    {{-- 4 ─────────────────────────────────────────────────────────────────── --}}
    @include('blog.partials.section-heading', ['n' => 4, 'id' => 's4', 'key' => 'mh_h'])
    <p>{{ $t('mh_p1') }}</p>

    @include('blog.partials.figure', ['fig' => 'melodic-harmonic', 'caption' => 'fig_melodic_harmonic_caption', 'tone' => 'orange'])

    <h3>{{ $t('mh_mel_h') }}</h3>
    <p>{{ $t('mh_mel_p1') }}</p>
    <p>{{ $t('mh_mel_p2') }}</p>
    <ul>
        <li>{{ $t('mh_mel_1') }}</li>
        <li>{{ $t('mh_mel_2') }}</li>
        <li>{{ $t('mh_mel_3') }}</li>
    </ul>
    <p>{{ $t('mh_mel_p3') }}</p>
    <p>{{ $t('mh_mel_p4') }}</p>
    <p>{{ $t('mh_mel_p5') }}</p>

    @include('blog.partials.interval-exercise', [
        'exId' => 'melodic',
        'mode' => 'melodic',
        'questions' => $melodicQuestions,
    ])

    <h3>{{ $t('mh_har_h') }}</h3>
    <p>{{ $t('mh_har_p1') }}</p>
    <p>{{ $t('mh_har_p2') }}</p>
    <p>{{ $t('mh_har_p3') }}</p>
    <p>{{ $t('mh_har_p4') }}</p>
    <p>{{ $t('mh_har_p5') }}</p>

    @include('blog.partials.takeaway', ['key' => 'take_mh'])

    @include('blog.partials.interval-exercise', [
        'exId' => 'harmonic',
        'mode' => 'harmonic',
        'questions' => $harmonicQuestions,
    ])

    {{-- 5 ─────────────────────────────────────────────────────────────────── --}}
    @include('blog.partials.section-heading', ['n' => 5, 'id' => 's5', 'key' => 'comp_h'])
    <p>{{ $t('comp_p1') }}</p>
    <p>{{ $t('comp_example_label') }}</p>
    <ul>
        @foreach (range(1, 4) as $i)
            <li>{{ $t('comp_'.$i) }}</li>
        @endforeach
    </ul>
    <p>{{ $t('comp_p2') }}</p>
    <p>{{ $t('comp_p3') }}</p>
    <div class="hv-callout"><span class="hv-mono">{{ $t('comp_math') }}</span></div>
    <p>{{ $t('comp_p4') }}</p>

    @include('blog.partials.figure', ['fig' => 'compound', 'caption' => 'fig_compound_caption', 'tone' => 'green'])
    <p>{{ $t('comp_p5') }}</p>

    {{-- 6 ─────────────────────────────────────────────────────────────────── --}}
    @include('blog.partials.section-heading', ['n' => 6, 'id' => 's6', 'key' => 'cons_h'])
    <p>{{ $t('cons_p1') }}</p>
    <p>{{ $t('cons_p2') }}</p>
    <p>{{ $t('cons_p3') }}</p>

    @include('blog.partials.figure', ['fig' => 'contour', 'caption' => 'fig_contour_caption', 'tone' => 'green'])
    <p>{{ $t('cons_p4') }}</p>
    <ul>
        @foreach (range(1, 5) as $i)
            <li>{{ $t('cons_'.$i) }}</li>
        @endforeach
    </ul>
    <p>{{ $t('cons_p5') }}</p>
    <p>{{ $t('cons_p6') }}</p>
    <p>{{ $t('cons_p7') }}</p>
    <p>{{ $t('cons_p8') }}</p>

    @include('blog.partials.figure', ['fig' => 'spectrum', 'caption' => 'fig_spectrum_caption', 'tone' => 'orange'])
    @include('blog.partials.takeaway', ['key' => 'take_cons'])

    {{-- 7 ─────────────────────────────────────────────────────────────────── --}}
    @include('blog.partials.section-heading', ['n' => 7, 'id' => 's7', 'key' => 'inv_h'])
    <p>{{ $t('inv_p1') }}</p>
    <p>{{ $t('inv_p2') }}</p>
    <ul>
        @foreach (range(1, 4) as $i)
            <li>{{ $t('inv_n'.$i) }}</li>
        @endforeach
    </ul>
    <p>{{ $t('inv_p3') }}</p>
    <ul>
        @foreach (range(1, 5) as $i)
            <li>{{ $t('inv_q'.$i) }}</li>
        @endforeach
    </ul>
    <p>{{ $t('inv_p4') }}</p>

    @include('blog.partials.figure', ['fig' => 'inversion', 'caption' => 'fig_inversion_caption', 'tone' => 'purple'])
    <p>{{ $t('inv_p5') }}</p>

    {{-- 8 ─────────────────────────────────────────────────────────────────── --}}
    @include('blog.partials.section-heading', ['n' => 8, 'id' => 's8', 'key' => 'why_h'])
    <p>{{ $t('why_p1') }}</p>

    <h3>{{ $t('why_read_h') }}</h3>
    <p>{{ $t('why_read_p1') }}</p>
    <p>{{ $t('why_read_p2') }}</p>
    <p>{{ $t('why_read_p3') }}</p>
    <p>{{ $t('why_read_p4') }}</p>

    <h3>{{ $t('why_ear_h') }}</h3>
    <p>{{ $t('why_ear_p1') }}</p>
    <ul>
        @foreach (range(1, 7) as $i)
            <li>{{ $t('why_ear_'.$i) }}</li>
        @endforeach
    </ul>
    <p>{{ $t('why_ear_p2') }}</p>

    <h3>{{ $t('why_sing_h') }}</h3>
    <p>{{ $t('why_sing_p1') }}</p>
    <p>{{ $t('why_sing_p2') }}</p>
    <p>{{ $t('why_sing_p3') }}</p>

    <h3>{{ $t('why_inst_h') }}</h3>
    <p>{{ $t('why_inst_p1') }}</p>
    <ul>
        @foreach (range(1, 4) as $i)
            <li>{{ $t('why_inst_'.$i) }}</li>
        @endforeach
    </ul>
    <p>{{ $t('why_inst_p2') }}</p>

    <h3>{{ $t('why_harm_h') }}</h3>
    <p>{{ $t('why_harm_p1') }}</p>
    <ul>
        <li>{{ $t('why_harm_1') }}</li>
        <li>{{ $t('why_harm_2') }}</li>
    </ul>
    <p>{{ $t('why_harm_p2') }}</p>
    <p>{{ $t('why_harm_p3') }}</p>
    <p>{{ $t('why_harm_p4') }}</p>

    <h3>{{ $t('why_comp_h') }}</h3>
    <p>{{ $t('why_comp_p1') }}</p>
    <p>{{ $t('why_comp_p2') }}</p>
    <p>{{ $t('why_comp_p3') }}</p>

    {{-- 9 ─────────────────────────────────────────────────────────────────── --}}
    @include('blog.partials.section-heading', ['n' => 9, 'id' => 's9', 'key' => 'staff_h'])
    <p>{{ $t('staff_p1') }}</p>
    <ol>
        @foreach (range(1, 3) as $i)
            <li>{{ $t('staff_'.$i) }}</li>
        @endforeach
    </ol>
    <p>{{ $t('staff_p2') }}</p>
    <div class="hv-callout"><span class="hv-mono">{{ $t('staff_count') }}</span></div>
    <p>{{ $t('staff_p3') }}</p>
    <p>{{ $t('staff_p4') }}</p>
    <p>{{ $t('staff_p5') }}</p>
    <p>{{ $t('staff_p6') }}</p>
    <ul>
        <li>{{ $t('staff_enh_1') }}</li>
        <li>{{ $t('staff_enh_2') }}</li>
    </ul>
    <p>{{ $t('staff_p7') }}</p>
    <p>{{ $t('staff_p8') }}</p>

    @include('blog.partials.figure', ['fig' => 'staff-shapes', 'caption' => 'fig_staff_shapes_figcaption', 'tone' => 'purple'])
    @include('blog.partials.takeaway', ['key' => 'take_staff'])

    {{-- 10 ────────────────────────────────────────────────────────────────── --}}
    @include('blog.partials.section-heading', ['n' => 10, 'id' => 's10', 'key' => 'ear_h'])
    <p>{{ $t('ear_p1') }}</p>
    <p>{{ $t('ear_p2') }}</p>
    <ul>
        @foreach (range(1, 4) as $i)
            <li>{{ $t('ear_easy_'.$i) }}</li>
        @endforeach
    </ul>
    <p>{{ $t('ear_p3') }}</p>
    <ul>
        @foreach (range(1, 3) as $i)
            <li>{{ $t('ear_hard_'.$i) }}</li>
        @endforeach
    </ul>
    <p>{{ $t('ear_p4') }}</p>

    @include('blog.partials.interval-exercise', [
        'exId' => 'mixed',
        'mode' => 'melodic',
        'questions' => $mixedQuestions,
    ])

    <p>{{ $t('ear_p5') }}</p>
    <ul>
        @foreach (range(1, 6) as $i)
            <li>{{ $t('ear_adv_'.$i) }}</li>
        @endforeach
    </ul>
    <p>{{ $t('ear_p6') }}</p>
    <p>{{ $t('ear_p7') }}</p>
    <p>{{ $t('ear_p8') }}</p>
    <p>{{ $t('ear_p9') }}</p>
    <ol>
        @foreach (range(1, 4) as $i)
            <li>{{ $t('ear_step_'.$i) }}</li>
        @endforeach
    </ol>
    <p>{{ $t('ear_p10') }}</p>

    @include('blog.partials.takeaway', ['key' => 'take_ear'])

    {{-- 11 ────────────────────────────────────────────────────────────────── --}}
    @include('blog.partials.section-heading', ['n' => 11, 'id' => 's11', 'key' => 'hv_h'])
    <p>{{ $t('hv_p1') }}</p>
    <p>{{ $t('hv_p2') }}</p>
    <ul>
        @foreach (range(1, 7) as $i)
            <li>{{ $t('hv_start_'.$i) }}</li>
        @endforeach
    </ul>

    <h3>{{ $t('hv_lp_h') }}</h3>
    <p>{{ $t('hv_lp_p1') }}</p>
    <ul>
        @foreach (range(1, 5) as $i)
            <li>{{ $t('hv_lp_'.$i) }}</li>
        @endforeach
    </ul>
    <p>{{ $t('hv_lp_p2') }}</p>
    <p>{{ $t('hv_lp_p3') }}</p>
    <p>{{ $t('hv_lp_p4') }}</p>

    <h3>{{ $t('hv_studio_h') }}</h3>
    <p>{{ $t('hv_studio_p1') }}</p>
    <ul>
        @foreach (range(1, 4) as $i)
            <li>{{ $t('hv_studio_'.$i) }}</li>
        @endforeach
    </ul>
    <p>{{ $t('hv_studio_p2') }}</p>
    <ul>
        @foreach (range(1, 5) as $i)
            <li>{{ $t('hv_studio_o'.$i) }}</li>
        @endforeach
    </ul>
    <p>{{ $t('hv_studio_p3') }}</p>
    <p>{{ $t('hv_studio_p4') }}</p>
    <p>{{ $t('hv_studio_p5') }}</p>

    <h3>{{ $t('hv_blitz_h') }}</h3>
    <p>{{ $t('hv_blitz_p1') }}</p>
    <p>{{ $t('hv_blitz_p2') }}</p>
    <p>{{ $t('hv_blitz_p3') }}</p>

    <h3>{{ $t('hv_routine_h') }}</h3>
    <p>{{ $t('hv_routine_p1') }}</p>
    <div class="hv-callout">
        <ul style="margin:0;padding:0;list-style:none;">
            @foreach (range(1, 3) as $i)
            <li style="padding-left:0;display:flex;align-items:baseline;gap:12px;">
                <span class="hv-mono" style="padding:3px 10px;font-size:13px;flex-shrink:0;">{{ $t('hv_routine_'.$i.'_time') }}</span>
                <span>{{ $t('hv_routine_'.$i.'_text') }}</span>
            </li>
            @endforeach
        </ul>
    </div>
    <p>{{ $t('hv_routine_p2') }}</p>

    {{-- 12 ────────────────────────────────────────────────────────────────── --}}
    @include('blog.partials.section-heading', ['n' => 12, 'id' => 's12', 'key' => 'faq_h'])
    @foreach (range(1, $post['faq_count']) as $i)
        <h3>{{ $t('faq_'.$i.'_q') }}</h3>
        <p>{{ $t('faq_'.$i.'_a') }}</p>
    @endforeach

    {{-- Close ──────────────────────────────────────────────────────────────── --}}
    @include('blog.partials.section-heading', ['key' => 'final_h'])
    <p>{{ $t('final_p1') }}</p>
    <p>{{ $t('final_p2') }}</p>
    <ul>
        @foreach (range(1, 6) as $i)
            <li>{{ $t('final_'.$i) }}</li>
        @endforeach
    </ul>
    <p>{{ $t('final_p3') }}</p>
    <p>{{ $t('final_p4') }}</p>
    <p>{{ $t('final_p5') }}</p>

@endsection
