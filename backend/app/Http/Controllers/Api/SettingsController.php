<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    /**
     * Get all settings or by group
     */
    public function index(Request $request)
    {
        try {
            if ($request->has('group')) {
                $settings = Setting::getGroup($request->group);
                
                return response()->json([
                    'success' => true,
                    'data' => $settings,
                ]);
            }

            $settings = Setting::getAllGrouped();

            return response()->json([
                'success' => true,
                'data' => $settings,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل تحميل الإعدادات: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get single setting by key
     */
    public function show($key)
    {
        try {
            $setting = Setting::where('key', $key)->first();

            if (!$setting) {
                return response()->json([
                    'success' => false,
                    'message' => 'الإعداد غير موجود',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'key' => $setting->key,
                    'value' => Setting::castValue($setting->value, $setting->type),
                    'type' => $setting->type,
                    'group' => $setting->group,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل تحميل الإعداد: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update multiple settings at once
     */
    public function update(Request $request)
    {
        try {
            $settings = $request->input('settings', []);
            $group = $request->input('group');

            foreach ($settings as $key => $data) {
                $value = $data['value'] ?? $data;
                $type = $data['type'] ?? 'string';
                
                Setting::set($key, $value, $type, $group);
            }

            return response()->json([
                'success' => true,
                'message' => 'تم حفظ الإعدادات بنجاح!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل حفظ الإعدادات: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update single setting
     */
    public function updateSingle(Request $request, $key)
    {
        try {
            $validated = $request->validate([
                'value' => 'required',
                'type' => 'sometimes|in:string,boolean,integer,json',
                'group' => 'sometimes|string',
            ]);

            $type = $validated['type'] ?? 'string';
            $group = $validated['group'] ?? null;

            Setting::set($key, $validated['value'], $type, $group);

            return response()->json([
                'success' => true,
                'message' => 'تم تحديث الإعداد بنجاح!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل تحديث الإعداد: ' . $e->getMessage(),
            ], 500);
        }
    }
}
