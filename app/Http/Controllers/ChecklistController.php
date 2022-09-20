<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class ChecklistController extends Controller
{
    public function index()
    {
        return Inertia::render('Checklist/Index');
    }
}
