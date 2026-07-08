<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\TeacherProfile;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /**
     * Only approved, publicly visible teacher profiles are listed —
     * draft/rejected/suspended/hidden profiles must never appear here.
     */
    public function index(): Response
    {
        $profiles = TeacherProfile::publiclyVisible()
            ->select(['slug', 'updated_at'])
            ->orderBy('slug')
            ->get();

        $xml = view('sitemap', ['profiles' => $profiles])->render();

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }
}
