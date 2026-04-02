<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Part;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function clientIndex(Request $request)
    {
        return $this->buildOrdersQuery($request, $request->user(), true)->latest()->paginate(10);
    }

    public function index(Request $request)
    {
        return $this->buildOrdersQuery($request, $request->user())->latest()->paginate(10);
    }

    public function unassigned(Request $request)
    {
        return $this->buildOrdersQuery($request, $request->user())
            ->whereNull('assigned_staff_id')
            ->latest()
            ->paginate(10);
    }

    public function assignedToMe(Request $request)
    {
        return $this->buildOrdersQuery($request, $request->user())
            ->where('assigned_staff_id', $request->user()->id)
            ->latest()
            ->paginate(10);
    }

    public function myOrders(Request $request)
    {
        return $this->clientIndex($request);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'branch_id' => ['required', 'integer', 'exists:branches,id'],
            'device_id' => ['required', 'integer', 'exists:devices,id'],
            'problem_description' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_type' => ['required', 'in:service,part'],
            'items.*.service_id' => ['nullable', 'integer', 'exists:services,id'],
            'items.*.part_id' => ['nullable', 'integer', 'exists:parts,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        $validator->after(function ($validator) use ($request) {
            foreach ((array) $request->input('items', []) as $index => $item) {
                $itemType = $item['item_type'] ?? null;
                $serviceId = $item['service_id'] ?? null;
                $partId = $item['part_id'] ?? null;

                if ($itemType === 'service' && ! $serviceId) {
                    $validator->errors()->add("items.$index.service_id", 'Choose a service for this item.');
                }

                if ($itemType === 'part' && ! $partId) {
                    $validator->errors()->add("items.$index.part_id", 'Choose a part for this item.');
                }
            }
        });

        $data = $validator->validate();

        $this->ensureDeviceCanBeUsed($user, (int) $data['device_id']);

        return DB::transaction(function () use ($data, $user) {
            $order = Order::create([
                'order_number' => $this->generateOrderNumber(),
                'user_id' => $user->id,
                'branch_id' => $data['branch_id'],
                'device_id' => $data['device_id'],
                'status' => 'new',
                'problem_description' => $data['problem_description'] ?? null,
            ]);

            $total = 0;

            foreach ($data['items'] as $item) {
                if ($item['item_type'] === 'service') {
                    $service = Service::findOrFail($item['service_id']);
                    $unitPrice = (float) $service->base_price;
                    $serviceId = $service->id;
                    $partId = null;
                } else {
                    $part = Part::findOrFail($item['part_id']);
                    $unitPrice = (float) $part->unit_price;
                    $serviceId = null;
                    $partId = $part->id;
                }

                $qty = (int) $item['quantity'];
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

            return $this->loadOrderRelations($order);
        });
    }

    public function show(Request $request, Order $order)
    {
        $this->authorize('view', $order);

        return $this->loadOrderRelations($order);
    }

    public function update(Request $request, Order $order)
    {
        $this->authorize('update', $order);

        $data = $request->validate([
            'status' => ['sometimes', 'in:new,confirmed,in_progress,waiting_parts,done,cancelled'],
            'diagnosis' => ['sometimes', 'nullable', 'string'],
            'work_log' => ['sometimes', 'nullable', 'string'],
        ]);

        $order->update($data);

        return $this->loadOrderRelations($order);
    }

    public function destroy(Request $request, Order $order)
    {
        $this->authorize('delete', $order);

        $order->delete();

        return response()->noContent();
    }

    public function claim(Request $request, Order $order)
    {
        $claimedOrder = DB::transaction(function () use ($order, $request) {
            $lockedOrder = Order::query()
                ->whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedOrder->assigned_staff_id !== null) {
                abort(409, 'Šo pasūtījumu jau pieņēma cits darbinieks.');
            }

            $lockedOrder->assigned_staff_id = $request->user()->id;

            if ($lockedOrder->status === 'new') {
                $lockedOrder->status = 'confirmed';
            }

            $lockedOrder->save();

            return $lockedOrder;
        });

        return $this->loadOrderRelations($claimedOrder);
    }

    private function buildOrdersQuery(Request $request, User $user, bool $forceOwnOrders = false)
    {
        $query = Order::with([
            'user:id,name,email,phone',
            'assignedStaff:id,name,email,phone,specialization',
            'branch:id,name,address',
            'device:id,type,brand,model,serial_number',
            'items.service:id,name,base_price',
            'items.part:id,name,unit_price',
        ]);

        if ($forceOwnOrders || $user->hasRole(User::ROLE_CLIENT)) {
            $query->where('user_id', $user->id);
        }

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where('order_number', 'like', "%{$search}%");
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', (int) $request->input('branch_id'));
        }

        return $query;
    }

    private function loadOrderRelations(Order $order): Order
    {
        return $order->fresh()->load([
            'user:id,name,email,phone',
            'assignedStaff:id,name,email,phone,specialization',
            'branch:id,name,address',
            'device:id,type,brand,model,serial_number',
            'items.service:id,name,base_price',
            'items.part:id,name,unit_price',
        ]);
    }

    private function ensureDeviceCanBeUsed(User $user, int $deviceId): void
    {
        if (! $user->hasRole(User::ROLE_CLIENT)) {
            return;
        }

        $belongsToUser = Device::query()
            ->whereKey($deviceId)
            ->where('user_id', $user->id)
            ->exists();

        if (! $belongsToUser) {
            throw ValidationException::withMessages([
                'device_id' => ['Selected device does not belong to the current user.'],
            ]);
        }
    }

    private function generateOrderNumber(): string
    {
        $prefix = 'DL-' . now()->format('Ymd') . '-';
        $latestOrderNumber = Order::query()
            ->where('order_number', 'like', $prefix . '%')
            ->orderByDesc('order_number')
            ->value('order_number');

        $nextNumber = 1;

        if ($latestOrderNumber) {
            $lastSequence = (int) substr($latestOrderNumber, strlen($prefix));
            $nextNumber = $lastSequence + 1;
        }

        do {
            $orderNumber = $prefix . str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);

            if (! Order::query()->where('order_number', $orderNumber)->exists()) {
                return $orderNumber;
            }

            $nextNumber++;
        } while (true);
    }
}
