<?php

namespace App\Http\Controllers\Api\Setting;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SettingController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $items = $request->toArray();
        DB::beginTransaction();
        try {
            foreach ($items as $object => $value) {
                Setting::query()
                    ->where('name', $object)
                    ->update([
                        'value' => $value ?? 0
                    ]);
            }
            DB::commit();
            return response()->json([
                'settings' => $this->getSettings()
            ], 201);
        } catch (\Exception $exception) {
            DB::rollBack();
            abort(403, $exception->getCode() . ' ' . $exception->getMessage());
        }
    }

    private function getSettings(): Collection
    {
        $settings = Setting::all();

        return collect($settings)->mapWithKeys(function ($item, int $key) {
            return [$item['name'] => (int)$item['value']];
        });


    }
}
