<?php

namespace App\Http\Middleware;

use App\Enums\UserRoleEnum;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'غير مصرح. يجب تسجيل الدخول أولاً.',
            ], 401);
        }

        if (!in_array($user->role, array_map(
            fn ($role) => UserRoleEnum::tryFrom($role),
            $roles
        ), true)) {
            return response()->json([
                'message' => 'ليس لديك صلاحية للوصول إلى هذا المورد.',
            ], 403);
        }

        return $next($request);
    }
}
