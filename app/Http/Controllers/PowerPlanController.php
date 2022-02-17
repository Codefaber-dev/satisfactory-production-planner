<?php

namespace App\Http\Controllers;

use App\PowerPlanner\PowerPlanner;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PowerPlanController extends Controller
{
    public function index()
    {
        return Inertia::render("PowerPlanner/Index");
    }

    public function show($output)
    {
        return Inertia::render("PowerPlanner/Show", [
            "plans" => PowerPlanner::make($output)->calculate(),
            "output" => $output
        ]);
    }
}
