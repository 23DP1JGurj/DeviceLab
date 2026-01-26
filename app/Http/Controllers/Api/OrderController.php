<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Part;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
public function index(Request $request)
{
    $q = Order::with(['items.service', 'items.part']);

    // search по номеру заказа
    if ($request->filled('search')) {
        $search = $request->string('search');
        $q->where('order_number', 'like', "%{$search}%");
    }

    // фильтр по статусу
    if ($request->filled('status')) {
        $q->where('status', $request->string('status'));
    }

    // фильтр по branch_id
    if ($request->filled('branch_id')) {
        $q->where('branch_id', (int)$request->input('branch_id'));
    }

    return $q->latest()->paginate(10);
}

    public function store(Request $request)
    {
        $data = $request->validate([
            'branch_id' => ['required', 'integer', 'exists:branches,id'],
            'device_id' => ['required', 'integer', 'exists:devices,id'],
            'problem_description' => ['nullable', 'string'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.item_type' => ['required', 'in:service,part'],
            'items.*.service_id' => ['nullable', 'integer', 'exists:services,id'],
            'items.*.part_id' => ['nullable', 'integer', 'exists:parts,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        return DB::transaction(function () use ($data) {
            $userId = 1; // демо (без логина)

            $todayCount = Order::whereDate('created_at', today())->count() + 1;
            $orderNumber = 'DL-' . now()->format('Ymd') . '-' . str_pad((string)$todayCount, 4, '0', STR_PAD_LEFT);

            $order = Order::create([
                'order_number' => $orderNumber,
                'user_id' => $userId,
                'branch_id' => $data['branch_id'],
                'device_id' => $data['device_id'],
                'status' => 'new',
                'problem_description' => $data['problem_description'] ?? null,
            ]);

            $total = 0;

            foreach ($data['items'] as $item) {
                if ($item['item_type'] === 'service') {
                    $service = Service::findOrFail($item['service_id']);
                    $unitPrice = (float)$service->base_price;
                    $serviceId = $service->id;
                    $partId = null;
                } else {
                    $part = Part::findOrFail($item['part_id']);
                    $unitPrice = (float)$part->unit_price;
                    $serviceId = null;
                    $partId = $part->id;
                }

                $qty = (int)$item['quantity'];
                $lineTotal = $unitPrice * $qty;
                $total += $lineTotal;

                OrderItem::create([
                    'order_id' => $order->id,
                    'item_type' => $item['item_type'],
                    'service_id' => $serviceId,
                    'part_id' => $partId,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'line_total' => $lineTotal,
                ]);
            }

            $order->update(['final_cost' => $total]);

            return Order::with(['items.service', 'items.part'])->findOrFail($order->id);
        });
    }

    public function show(Order $order)
    {
        return $order->fresh()->load(['items.service', 'items.part']);
    }

    public function update(Request $request, Order $order)
    {
        $data = $request->validate([
            'status' => ['sometimes', 'in:new,confirmed,in_progress,waiting_parts,done,cancelled'],
            'diagnosis' => ['sometimes', 'nullable', 'string'],
            'work_log' => ['sometimes', 'nullable', 'string'],
        ]);

        $order->update($data);

        return $order->fresh();
    }

    public function destroy(Order $order)
    {
        $order->delete();
        return response()->noContent();
    }
}
