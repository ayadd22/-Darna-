<?php

namespace App\Http\Controllers;

use App\Models\Governorate;
use Illuminate\Http\JsonResponse;

class GovernorateController extends Controller
{
    public function index(): JsonResponse
    {
        $governorates = Governorate::query()
            ->orderBy('name')
            ->get();

        return response()->json([
            'message' => 'تم جلب المحافظات بنجاح.',
            'data' => $governorates,
        ]);
    }
}