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
            ->select('id', 'user_id', 'type', 'component_type', 'brand', 'model', 'specs', 'serial_number')
            ->where('user_id', $request->user()->id)
            ->latest('id')
            ->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'type' => ['required', 'string', 'in:phone,laptop,tablet,desktop_pc,pc_component,other'],
            'component_type' => ['nullable', 'required_if:type,pc_component', 'string', 'max:80'],
            'brand' => ['required', 'string', 'max:255'],
            'model' => ['required', 'string', 'max:255'],
            'specs' => ['nullable', 'string', 'max:2000'],
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
            'component_type' => $data['component_type'] ?? null,
            'brand' => $data['brand'],
            'model' => $data['model'],
            'specs' => $data['specs'] ?? null,
            'serial_number' => $data['serial_number'] ?? null,
        ]);

        return response()->json($device, 201);
    }

    public function destroy(Request $request, Device $device)
    {
        if ($device->user_id !== $request->user()->id) {
            abort(403, 'Drīkst dzēst tikai savas ierīces.');
        }

        $hasActiveOrders = $device->orders()
            ->whereNotIn('status', ['done', 'cancelled'])
            ->exists();

        if ($hasActiveOrders) {
            throw ValidationException::withMessages([
                'device' => ['Šai ierīcei ir aktīvi pasūtījumi, tāpēc to nevar dzēst.'],
            ]);
        }

        $device->delete();

        return response()->noContent();
    }
}
