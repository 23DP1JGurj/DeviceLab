<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DeviceController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()?->id ?? 1;

        return Device::query()
            ->select('id', 'user_id', 'type', 'brand', 'model', 'serial_number')
            ->where('user_id', $userId)
            ->latest('id')
            ->get();
    }

    public function store(Request $request)
    {
        $userId = $request->user()?->id ?? 1;

        $data = $request->validate([
            'type' => ['required', 'string', 'max:255'],
            'brand' => ['required', 'string', 'max:255'],
            'model' => ['required', 'string', 'max:255'],
            'serial_number' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('devices', 'serial_number')->whereNotNull('serial_number'),
            ],
        ]);

        $device = Device::create([
            'user_id' => $userId,
            'type' => $data['type'],
            'brand' => $data['brand'],
            'model' => $data['model'],
            'serial_number' => $data['serial_number'] ?? null,
        ]);

        return response()->json($device, 201);
    }
}
