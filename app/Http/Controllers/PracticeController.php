<?php

namespace App\Http\Controllers;

use App\Models\SingleNotePractice;
use Illuminate\Http\Request;

class PracticeController extends Controller
{
    //


    public function getSingleNotePractices() {
        $singleNotePractices = SingleNotePractice::all();
        return response()->json($singleNotePractices);
    }
}
