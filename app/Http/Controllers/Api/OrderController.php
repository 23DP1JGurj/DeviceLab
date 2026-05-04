<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\OrderCreatedMail;
use App\Mail\OrderReadyMail;
use App\Models\Device;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Part;
use App\Models\Payment;
use App\Models\Service;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Throwable;

class OrderController extends Controller
{
    public function __construct(private NotificationService $notifications)
    {
    }

    private const STAFF_ACTIVE_STATUSES = [
        'confirmed',
        'diagnostics',
        'in_progress',
        'waiting_parts',
        'ready',
    ];

    private const HISTORY_STATUSES = [
        'done',
        'cancelled',
    ];

    private const CLIENT_ACTIVE_STATUSES = [
        'new',
        'confirmed',
        'diagnostics',
        'in_progress',
        'waiting_parts',
        'ready',
    ];

    public function clientIndex(Request $request)
    {
        $query = $this->buildOrdersQuery($request, $request->user(), true);

        if ($request->input('scope') === 'active') {
            $query->whereIn('status', self::CLIENT_ACTIVE_STATUSES);
        } elseif ($request->input('scope') === 'history') {
            $query->whereIn('status', self::HISTORY_STATUSES);
        }

        return $this->applySort($query, $request)->paginate(10);
    }

    public function index(Request $request)
    {
        return $this->applySort($this->buildOrdersQuery($request, $request->user()), $request)->paginate(10);
    }

    public function unassigned(Request $request)
    {
        return $this->applySort($this->buildOrdersQuery($request, $request->user())
            ->whereNull('assigned_staff_id')
            ->where('status', 'new'), $request)
            ->paginate(10);
    }

    public function assignedToMe(Request $request)
    {
        return $this->applySort($this->buildOrdersQuery($request, $request->user())
            ->where('assigned_staff_id', $request->user()->id)
            ->whereIn('status', self::STAFF_ACTIVE_STATUSES), $request)
            ->paginate(10);
    }

    public function staffHistory(Request $request)
    {
        $query = $this->buildOrdersQuery($request, $request->user())
            ->whereIn('status', self::HISTORY_STATUSES);

        if ($request->user()->hasRole(User::ROLE_STAFF)) {
            $query->where('assigned_staff_id', $request->user()->id);
        }

        return $this->applySort($query, $request)->paginate(10);
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
            'request_type' => ['nullable', 'string', 'in:general,screen_battery,quick_diagnostics'],
            'repair_option' => ['nullable', 'string', 'in:screen,battery'],
            'problem_description' => ['nullable', 'string'],
            'items' => ['nullable', 'array', 'min:1'],
            'items.*.item_type' => ['required', 'in:service,part'],
            'items.*.service_id' => ['nullable', 'integer', 'exists:services,id'],
            'items.*.part_id' => ['nullable', 'integer', 'exists:parts,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        $validator->after(function ($validator) use ($request) {
            if (! $request->filled('items') && $request->input('request_type') === 'screen_battery' && ! $request->filled('repair_option')) {
                $validator->errors()->add('repair_option', 'Izvēlies remonta veidu.');
            }

            foreach ((array) $request->input('items', []) as $index => $item) {
                $itemType = $item['item_type'] ?? null;
                $serviceId = $item['service_id'] ?? null;
                $partId = $item['part_id'] ?? null;

                if ($itemType === 'service' && ! $serviceId) {
                    $validator->errors()->add("items.$index.service_id", 'Izvēlies pakalpojumu šai pozīcijai.');
                }

                if ($itemType === 'part' && ! $partId) {
                    $validator->errors()->add("items.$index.part_id", 'Izvēlies detaļu šai pozīcijai.');
                }
            }
        });

        $data = $validator->validate();

        $this->ensureDeviceCanBeUsed($user, (int) $data['device_id']);

        $createdOrder = DB::transaction(function () use ($data, $user) {
            $items = $this->buildClientRequestItems($data);
            $problemDescription = $this->resolveProblemDescription($data);

            $order = Order::create([
                'order_number' => $this->generateOrderNumber(),
                'user_id' => $user->id,
                'branch_id' => $data['branch_id'],
                'device_id' => $data['device_id'],
                'status' => 'new',
                'request_type' => $data['request_type'] ?? 'general',
                'problem_description' => $problemDescription,
            ]);

            $total = 0;

            foreach ($items as $item) {
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

            $this->notifyStaffAndAdmins(
                $order,
                'order_created',
                'Jauns pasūtījums',
                "Sistēmā izveidots jauns pasūtījums {$order->order_number}."
            );

            return $this->loadOrderRelations($order);
        });

        $this->sendOrderCreatedEmail($createdOrder);

        return $createdOrder;
    }

    public function show(Request $request, Order $order)
    {
        $this->authorize('view', $order);

        return $this->loadOrderRelations($order);
    }

    public function payment(Request $request, Order $order)
    {
        $this->ensureClientOwnsOrder($request->user(), $order);

        if ($order->payment) {
            return $order->payment;
        }

        if ($order->status === 'ready' && (float) $order->final_cost > 0) {
            return response()->json([
                'order_id' => $order->id,
                'user_id' => $request->user()->id,
                'amount' => $order->final_cost,
                'status' => Payment::STATUS_PENDING,
                'paid_at' => null,
                'method' => null,
            ]);
        }

        return response()->json([
            'order_id' => $order->id,
            'amount' => $order->final_cost,
            'status' => 'unavailable',
            'paid_at' => null,
            'method' => null,
        ]);
    }

    public function pay(Request $request, Order $order)
    {
        $this->ensureClientOwnsOrder($request->user(), $order);

        return DB::transaction(function () use ($request, $order) {
            $lockedOrder = Order::query()
                ->whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            $existingPayment = Payment::query()
                ->where('order_id', $lockedOrder->id)
                ->lockForUpdate()
                ->first();

            if ($existingPayment?->status === Payment::STATUS_PAID) {
                abort(409, 'Pasūtījums jau ir apmaksāts.');
            }

            if ($lockedOrder->user_id !== $request->user()->id) {
                abort(403);
            }

            if ($lockedOrder->status !== 'ready') {
                throw ValidationException::withMessages([
                    'order' => ['Rēķinu var apmaksāt tikai tad, kad pasūtījums ir gatavs saņemšanai.'],
                ]);
            }

            if ((float) $lockedOrder->final_cost <= 0) {
                throw ValidationException::withMessages([
                    'amount' => ['Apmaksas summa nav korekta.'],
                ]);
            }

            $payment = Payment::updateOrCreate(
                ['order_id' => $lockedOrder->id],
                [
                    'user_id' => $request->user()->id,
                    'amount' => $lockedOrder->final_cost,
                    'status' => Payment::STATUS_PAID,
                    'paid_at' => now(),
                    'method' => 'demo',
                ]
            );

            $oldStatus = $lockedOrder->status;
            $lockedOrder->status = 'done';
            $lockedOrder->save();

            $this->recordStatusHistory(
                $lockedOrder,
                $oldStatus,
                'done',
                $request->user()->id,
                'Klients apmaksāja pasūtījumu. Pasūtījums pabeigts.'
            );

            $this->notifyStaffAndAdmins(
                $lockedOrder,
                'order_paid',
                'Pasūtījums apmaksāts',
                "Klients apmaksāja pasūtījumu {$lockedOrder->order_number}."
            );

            return $this->loadOrderRelations($lockedOrder)->setRelation('payment', $payment->fresh());
        });
    }

    public function update(Request $request, Order $order)
    {
        $this->authorize('update', $order);

        $data = $request->validate([
            'status' => ['sometimes', 'in:new,confirmed,diagnostics,in_progress,waiting_parts,ready,done,cancelled'],
            'diagnosis' => ['sometimes', 'nullable', 'string'],
            'work_log' => ['sometimes', 'nullable', 'string'],
            'status_comment' => ['sometimes', 'nullable', 'string', 'max:1000'],
        ]);

        $oldStatus = $order->status;
        $newStatus = $data['status'] ?? $oldStatus;

        unset($data['status_comment']);

        $order->update($data);

        if ($newStatus !== $oldStatus) {
            $this->recordStatusHistory(
                $order,
                $oldStatus,
                $newStatus,
                $request->user()?->id,
                $request->input('status_comment')
            );

            $this->notifyClientAboutStatusChange($order->fresh(), $newStatus, $request->input('status_comment'));

            if ($oldStatus !== 'ready' && $newStatus === 'ready') {
                $this->sendOrderReadyEmail($order->fresh());
            }
        }

        return $this->loadOrderRelations($order);
    }

    public function destroy(Request $request, Order $order)
    {
        $this->authorize('delete', $order);

        $order->delete();

        return response()->noContent();
    }

    public function cancel(Request $request, Order $order)
    {
        $this->ensureClientOwnsOrder($request->user(), $order);

        return DB::transaction(function () use ($request, $order) {
            $lockedOrder = Order::query()
                ->whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedOrder->user_id !== $request->user()->id) {
                abort(403);
            }

            if ($lockedOrder->status !== 'new' || $lockedOrder->assigned_staff_id !== null) {
                throw ValidationException::withMessages([
                    'order' => ['Pieteikumu var atcelt tikai pirms to pieņem darbinieks.'],
                ]);
            }

            if ($lockedOrder->payment?->status === Payment::STATUS_PAID) {
                throw ValidationException::withMessages([
                    'payment' => ['Apmaksātu pasūtījumu nevar atcelt.'],
                ]);
            }

            $oldStatus = $lockedOrder->status;
            $lockedOrder->status = 'cancelled';
            $lockedOrder->save();

            $this->recordStatusHistory(
                $lockedOrder,
                $oldStatus,
                'cancelled',
                $request->user()->id,
                'Klients atcēla pieteikumu.'
            );

            $this->notifyStaffAndAdmins(
                $lockedOrder,
                'order_cancelled',
                'Pieteikums atcelts',
                "Klients atcēla pieteikumu {$lockedOrder->order_number}."
            );

            return $this->loadOrderRelations($lockedOrder);
        });
    }

    public function storeItem(Request $request, Order $order)
    {
        $this->ensureStaffCanManageItems($request->user(), $order);

        $data = $request->validate([
            'item_type' => ['required', 'in:service,part'],
            'service_id' => ['nullable', 'integer', 'exists:services,id'],
            'part_id' => ['nullable', 'integer', 'exists:parts,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        return DB::transaction(function () use ($data, $order) {
            $quantity = (int) $data['quantity'];

            if ($data['item_type'] === 'service') {
                if (empty($data['service_id'])) {
                    throw ValidationException::withMessages([
                        'service_id' => ['Izvēlies pakalpojumu.'],
                    ]);
                }

                $service = Service::query()
                    ->whereKey($data['service_id'])
                    ->where('is_active', 1)
                    ->firstOrFail();

                $unitPrice = (float) $service->base_price;

                OrderItem::create([
                    'order_id' => $order->id,
                    'item_type' => 'service',
                    'service_id' => $service->id,
                    'part_id' => null,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'line_total' => $unitPrice * $quantity,
                ]);
            } else {
                if (empty($data['part_id'])) {
                    throw ValidationException::withMessages([
                        'part_id' => ['Izvēlies detaļu.'],
                    ]);
                }

                $part = Part::query()
                    ->whereKey($data['part_id'])
                    ->where('is_active', 1)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($part->stock_qty < $quantity) {
                    throw ValidationException::withMessages([
                        'quantity' => ['Noliktavā nepietiek detaļu.'],
                    ]);
                }

                $part->stock_qty -= $quantity;
                $part->save();

                $unitPrice = (float) $part->unit_price;

                OrderItem::create([
                    'order_id' => $order->id,
                    'item_type' => 'part',
                    'service_id' => null,
                    'part_id' => $part->id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'line_total' => $unitPrice * $quantity,
                ]);
            }

            $this->recalculateFinalCost($order);

            return $this->loadOrderRelations($order);
        });
    }

    public function updateItem(Request $request, Order $order, OrderItem $item)
    {
        $this->ensureStaffCanManageItems($request->user(), $order);
        $this->ensureItemBelongsToOrder($item, $order);

        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        return DB::transaction(function () use ($data, $order, $item) {
            $item = OrderItem::query()
                ->whereKey($item->id)
                ->lockForUpdate()
                ->firstOrFail();

            $newQuantity = (int) $data['quantity'];
            $oldQuantity = (int) $item->quantity;

            if ($item->item_type === 'part' && $item->part_id) {
                $part = Part::query()
                    ->whereKey($item->part_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $diff = $newQuantity - $oldQuantity;

                if ($diff > 0 && $part->stock_qty < $diff) {
                    throw ValidationException::withMessages([
                        'quantity' => ['Noliktavā nepietiek detaļu.'],
                    ]);
                }

                $part->stock_qty -= $diff;
                $part->save();
            }

            $item->quantity = $newQuantity;
            $item->line_total = (float) $item->unit_price * $newQuantity;
            $item->save();

            $this->recalculateFinalCost($order);

            return $this->loadOrderRelations($order);
        });
    }

    public function destroyItem(Request $request, Order $order, OrderItem $item)
    {
        $this->ensureStaffCanManageItems($request->user(), $order);
        $this->ensureItemBelongsToOrder($item, $order);

        return DB::transaction(function () use ($order, $item) {
            $item = OrderItem::query()
                ->whereKey($item->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($item->item_type === 'part' && $item->part_id) {
                $part = Part::query()
                    ->whereKey($item->part_id)
                    ->lockForUpdate()
                    ->first();

                if ($part) {
                    $part->stock_qty += (int) $item->quantity;
                    $part->save();
                }
            }

            $item->delete();
            $this->recalculateFinalCost($order);

            return $this->loadOrderRelations($order);
        });
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

            $oldStatus = $lockedOrder->status;
            $lockedOrder->assigned_staff_id = $request->user()->id;

            if ($lockedOrder->status === 'new') {
                $lockedOrder->status = 'confirmed';
            }

            $lockedOrder->save();

            $this->recordStatusHistory(
                $lockedOrder,
                $oldStatus,
                $lockedOrder->status,
                $request->user()->id,
                $oldStatus === $lockedOrder->status
                    ? 'Pasūtījums piešķirts darbiniekam.'
                    : 'Pasūtījumu pieņēma darbinieks.'
            );

            $this->notifications->notify(
                $lockedOrder->user_id,
                'order_claimed',
                'Pasūtījums pieņemts',
                "Jūsu pasūtījumu pieņēma darbinieks {$request->user()->name}.",
                $lockedOrder
            );

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
            'device:id,type,component_type,brand,model,specs,serial_number',
            'items.service:id,name,base_price',
            'items.part:id,name,unit_price',
            'statusHistory.changedBy:id,name,email',
            'payment:id,order_id,user_id,amount,status,paid_at,method',
            'review.user:id,name,email',
            'review.branch:id,name,address',
            'review.staff:id,name,email,specialization',
            'attachments:id,order_id,user_id,file_path,original_name,mime_type,file_size,created_at',
        ]);

        if ($forceOwnOrders || $user->hasRole(User::ROLE_CLIENT)) {
            $query->where('user_id', $user->id);
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function ($query) use ($search) {
                $query->where('order_number', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('assignedStaff', fn ($staffQuery) => $staffQuery->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('device', function ($deviceQuery) use ($search) {
                        $deviceQuery->where('brand', 'like', "%{$search}%")
                            ->orWhere('model', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', (string) $request->input('status'));
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', (int) $request->input('branch_id'));
        }

        if ($request->filled('request_type')) {
            $query->where('request_type', (string) $request->input('request_type'));
        }

        if ($request->filled('payment_status')) {
            $paymentStatus = (string) $request->input('payment_status');

            if ($paymentStatus === 'paid') {
                $query->whereHas('payment', fn ($paymentQuery) => $paymentQuery->where('status', Payment::STATUS_PAID));
            } elseif (in_array($paymentStatus, ['unpaid', 'pending'], true)) {
                $query->where(function ($query) {
                    $query->whereDoesntHave('payment')
                        ->orWhereHas('payment', fn ($paymentQuery) => $paymentQuery->where('status', '!=', Payment::STATUS_PAID));
                });
            }
        }

        if ($request->filled('assigned_staff_id') && $user->hasRole(User::ROLE_ADMIN)) {
            $query->where('assigned_staff_id', (int) $request->input('assigned_staff_id'));
        }

        if ($request->filled('has_review')) {
            $request->boolean('has_review')
                ? $query->whereHas('review')
                : $query->whereDoesntHave('review');
        }

        return $query;
    }

    private function applySort($query, Request $request)
    {
        return $request->input('sort') === 'oldest'
            ? $query->oldest('created_at')
            : $query->latest('created_at');
    }

    private function loadOrderRelations(Order $order): Order
    {
        return $order->fresh()->load([
            'user:id,name,email,phone',
            'assignedStaff:id,name,email,phone,specialization',
            'branch:id,name,address',
            'device:id,type,component_type,brand,model,specs,serial_number',
            'items.service:id,name,base_price',
            'items.part:id,name,unit_price',
            'statusHistory.changedBy:id,name,email',
            'payment:id,order_id,user_id,amount,status,paid_at,method',
            'review.user:id,name,email',
            'review.branch:id,name,address',
            'review.staff:id,name,email,specialization',
            'attachments:id,order_id,user_id,file_path,original_name,mime_type,file_size,created_at',
        ]);
    }

    private function recordStatusHistory(
        Order $order,
        ?string $oldStatus,
        string $newStatus,
        ?int $changedBy,
        ?string $comment = null
    ): void {
        OrderStatusHistory::create([
            'order_id' => $order->id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'changed_by' => $changedBy,
            'comment' => $comment,
        ]);
    }

    private function sendOrderCreatedEmail(Order $order): void
    {
        $order->loadMissing(['user', 'branch', 'device']);

        if (! $order->user?->email) {
            return;
        }

        try {
            Mail::to($order->user->email)->send(new OrderCreatedMail($order));
        } catch (Throwable $exception) {
            Log::warning('Order created email failed.', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private function sendOrderReadyEmail(Order $order): void
    {
        $order->loadMissing(['user', 'branch', 'device']);

        if (! $order->user?->email) {
            return;
        }

        try {
            Mail::to($order->user->email)->send(new OrderReadyMail($order));
        } catch (Throwable $exception) {
            Log::warning('Order ready email failed.', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private function notifyClientAboutStatusChange(Order $order, string $status, ?string $comment = null): void
    {
        $label = $this->statusLabel($status);

        if ($status === 'ready') {
            $title = 'Pasūtījums gatavs';
            $message = 'Jūsu ierīce ir gatava saņemšanai.';
        } else {
            $title = 'Statuss mainīts';
            $message = "Pasūtījuma {$order->order_number} statuss: {$label}.";
        }

        $comment = trim((string) $comment);

        if ($comment !== '') {
            $message .= ' ' . mb_substr($comment, 0, 220);
        }

        $this->notifications->notify(
            $order->user_id,
            'order_status_changed',
            $title,
            $message,
            $order,
            ['status' => $status]
        );
    }

    private function notifyStaffAndAdmins(Order $order, string $type, string $title, string $message, array $data = []): void
    {
        if (in_array($type, ['order_created', 'order_cancelled'], true)) {
            $recipientIds = User::query()
                ->whereIn('role', [User::ROLE_STAFF, User::ROLE_ADMIN])
                ->pluck('id')
                ->all();
        } else {
            $recipientIds = User::query()
                ->where('role', User::ROLE_ADMIN)
                ->pluck('id')
                ->all();

            if ($order->assigned_staff_id) {
                $recipientIds[] = $order->assigned_staff_id;
            }
        }

        $this->notifications->notifyMany($recipientIds, $type, $title, $message, $order, $data);
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            'new' => 'Jauns',
            'confirmed' => 'Apstiprināts',
            'diagnostics' => 'Diagnostika',
            'in_progress' => 'Remontā',
            'waiting_parts' => 'Gaida detaļas',
            'ready' => 'Gatavs saņemšanai',
            'done' => 'Pabeigts',
            'cancelled' => 'Atcelts',
            default => $status,
        };
    }

    private function recalculateFinalCost(Order $order): void
    {
        $total = OrderItem::query()
            ->where('order_id', $order->id)
            ->sum('line_total');

        $order->forceFill(['final_cost' => $total])->save();
    }

    private function ensureStaffCanManageItems(User $user, Order $order): void
    {
        if ($user->hasRole(User::ROLE_ADMIN)) {
            return;
        }

        if ($user->hasRole(User::ROLE_STAFF) && $order->assigned_staff_id === $user->id) {
            return;
        }

        abort(403, 'Šo pasūtījumu var rediģēt tikai piešķirtais darbinieks vai administrators.');
    }

    private function ensureItemBelongsToOrder(OrderItem $item, Order $order): void
    {
        if ($item->order_id !== $order->id) {
            abort(404);
        }
    }

    private function buildClientRequestItems(array $data): array
    {
        $requestType = $data['request_type'] ?? 'general';
        $serviceName = match ($requestType) {
            'quick_diagnostics' => 'Ātrā diagnostika',
            'screen_battery' => ($data['repair_option'] ?? null) === 'screen'
                ? 'Ekrāna maiņa'
                : 'Akumulatora maiņa',
            default => 'Diagnostika',
        };

        $service = $this->findServiceByName($serviceName);

        return [[
            'item_type' => 'service',
            'service_id' => $service->id,
            'part_id' => null,
            'quantity' => 1,
        ]];
    }

    private function resolveProblemDescription(array $data): ?string
    {
        $comment = trim((string) ($data['problem_description'] ?? ''));
        $prefix = match ($data['request_type'] ?? 'general') {
            'quick_diagnostics' => 'Klients pieteica ātro diagnostiku.',
            'screen_battery' => ($data['repair_option'] ?? null) === 'screen'
                ? 'Klients pieteica ekrāna maiņu.'
                : 'Klients pieteica akumulatora maiņu.',
            default => '',
        };

        if ($prefix === '') {
            return $comment !== '' ? $comment : null;
        }

        return $comment !== '' ? $prefix . "\n" . $comment : $prefix;
    }

    private function findServiceByName(string $name): Service
    {
        $service = Service::query()
            ->where('is_active', 1)
            ->where('name', $name)
            ->first();

        if ($service) {
            return $service;
        }

        return Service::query()
            ->where('is_active', 1)
            ->where('name', 'like', '%' . $name . '%')
            ->firstOrFail();
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
                'device_id' => ['Izvēlētā ierīce nepieder pašreizējam klientam.'],
            ]);
        }
    }

    private function ensureClientOwnsOrder(User $user, Order $order): void
    {
        if ($order->user_id !== $user->id) {
            abort(403);
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
