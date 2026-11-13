<?php

namespace App\Http\Controllers;

use App\Services\PlanBuilder;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function index(Request $request, PlanBuilder $builder)
    {
        return view('plan.index', [
            'plan' => $builder->build($request->user()),
        ]);
    }
}
