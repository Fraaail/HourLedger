<?php

namespace App\Http\Controllers;

use App\Models\Journal;
use Illuminate\Http\Request;

class JournalController extends Controller
{
    public function store(Request $request, $date)
    {
        $request->validate(['content' => 'nullable|string']);
        Journal::updateOrCreate(
            ['date' => $date],
            ['content' => $request->input('content')]
        );

        return back();
    }
}
