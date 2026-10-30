<?php

namespace App\Http\Controllers;

use App\Enums\UserRoleEnum;
use App\Models\Owner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OwnerController extends Controller
{
    public function index(Request $request): JsonResponse
  {
    $user = $request->user();

    $owners = Owner::query()
        ->when(
            $user->role === UserRoleEnum::OFFICE,
            fn ($query) => $query->whereHas(
                'properties',
                fn ($query) => $query->where('office_id', $user->office_id)
            )
        )
        ->select(['id', 'name', 'phone'])
        ->orderBy('name')
        ->get();

    return response()->json([
        'message' => 'تم جلب المالكين بنجاح.',
        'data' => $owners,
    ]);
   }
}
