<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class OrderAttachmentController extends Controller
{
    public function clientIndex(Request $request, Order $order)
    {
        $this->ensureClientOwnsOrder($request, $order);

        return $this->attachments($order);
    }

    public function staffIndex(Request $request, Order $order)
    {
        $this->authorize('view', $order);

        return $this->attachments($order);
    }

    public function store(Request $request, Order $order)
    {
        $this->ensureClientOwnsOrder($request, $order);

        $data = $request->validate([
            'photos' => ['nullable', 'array', 'max:5'],
            'photos.*' => ['file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $created = collect($data['photos'] ?? [])->map(function ($file) use ($request, $order) {
            $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'jpg');
            $fileName = Str::uuid()->toString() . '.' . $extension;
            $path = $file->storeAs("order-attachments/{$order->id}", $fileName, 'public');

            return OrderAttachment::create([
                'order_id' => $order->id,
                'user_id' => $request->user()->id,
                'file_path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
            ]);
        });

        return response()->json($created->values(), 201);
    }

    public function destroy(Request $request, Order $order, OrderAttachment $attachment)
    {
        $this->ensureClientOwnsOrder($request, $order);

        if ($attachment->order_id !== $order->id) {
            abort(404);
        }

        if ($order->assigned_staff_id !== null || $order->status !== 'new') {
            abort(422, 'Fotoattēlus var dzēst tikai pirms pasūtījums ir pieņemts darbā.');
        }

        Storage::disk('public')->delete($attachment->file_path);
        $attachment->delete();

        return response()->noContent();
    }

    private function attachments(Order $order)
    {
        return $order->attachments()
            ->latest()
            ->get();
    }

    private function ensureClientOwnsOrder(Request $request, Order $order): void
    {
        if ($order->user_id !== $request->user()->id) {
            abort(403);
        }
    }
}
