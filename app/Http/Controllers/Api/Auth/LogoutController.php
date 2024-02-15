<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LogoutController extends Controller
{
    /**
     * sanctum middleware
     */
    public function __construct()
    {
        $this->middleware(['auth:sanctum']);
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $request->user()->tokens()->delete();
            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Tokens Revoked',
            ], 201);
        } catch (\Exception $exception) {
            DB::rollBack();
            abort(403, $exception->getCode() . ' ' . $exception->getMessage());
        }

    }
}
