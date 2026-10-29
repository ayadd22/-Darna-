<?php

namespace App\Http\Controllers;

use App\Models\Office;
use Illuminate\Http\JsonResponse;

class OfficeController extends Controller
{
    public function index(): JsonResponse
    {
        $offices = Office::query()
            ->orderBy('name')
            ->get();

        return response()->json([
            'message' => 'تم جلب المكاتب بنجاح.',
            'data' => $offices,
        ]);
    }
}