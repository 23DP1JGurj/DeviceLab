<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class MyDeviceController extends Controller
{
    public function index(Request $request)
    {
        return Device::query()
            ->select('id', 'user_id', 'type', 'brand', 'model', 'serial_number')
            ->where('user_id', $request->user()->id)
            ->latest('id')
            ->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'type' => ['required', 'string', 'max:255'],
            'brand' => ['nullable', 'string', 'max:255'],
            'model' => ['nullable', 'string', 'max:255'],
            'serial_number' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('devices', 'serial_number')->whereNotNull('serial_number'),
            ],
        ]);

        $device = Device::create([
            'user_id' => $request->user()->id,
            'type' => $data['type'],
            'brand' => $data['brand'] ?? null,
            'model' => $data['model'] ?? null,
            'serial_number' => $data['serial_number'] ?? null,
        ]);

        return response()->json($device, 201);
    }

    public function destroy(Request $request, Device $device)
    {
        if ($device->user_id !== $request->user()->id) {
            abort(403, 'You can delete only your own device.');
        }

        $hasActiveOrders = $device->orders()
            ->whereNotIn('status', ['done', 'cancelled'])
            ->exists();

        if ($hasActiveOrders) {
            throw ValidationException::withMessages([
                'device' => ['This device has active orders and cannot be deleted.'],
            ]);
        }

        $device->delete();

        return response()->noContent();
    }
}
