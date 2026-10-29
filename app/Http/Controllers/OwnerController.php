<?php

namespace App\Http\Controllers;

use App\Models\Owner;
use Illuminate\Http\JsonResponse;

class OwnerController extends Controller
{
    public function index(): JsonResponse
    {
        $owners = Owner::query()
            ->orderBy('name')
            ->get();

        return response()->json([
            'message' => 'تم جلب المالكين بنجاح.',
            'data' => $owners,
        ]);
    }
}