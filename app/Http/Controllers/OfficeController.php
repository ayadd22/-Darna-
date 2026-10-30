<?php

namespace App\Http\Controllers;

use App\Models\Office;
use Illuminate\Http\JsonResponse;

class OfficeController extends Controller
{
 public function index(): JsonResponse
  {
    $offices = Office::query()
        ->where('is_active', true)
        ->select([
            'id',
            'name',
            'phone',
            'address',
        ])
        ->orderBy('name')
        ->get();

    return response()->json([
        'message' => 'تم جلب المكاتب بنجاح.',
        'data' => $offices,
    ]);
   }

}