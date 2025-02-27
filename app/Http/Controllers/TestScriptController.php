<?php

namespace App\Http\Controllers;

use App\Models\TestScript;
use Illuminate\Http\Request;

class TestScriptController extends Controller
{
    // TestScriptController.php
    public function index(Request $request)
    {
        $testScripts = TestScript::where('creator_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->with('creator')
            ->get();

        return response()->json([
            'testScripts' => $testScripts
        ]);
    }
}
