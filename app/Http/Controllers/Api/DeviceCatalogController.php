<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeviceModelSuggestion;
use App\Models\PhoneModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeviceCatalogController extends Controller
{
    private const SUGGESTION_TYPES = ['phone', 'tablet', 'laptop'];

    public function brands(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $type = $this->normalizeType($request);

        if ($type === 'phone') {
            return PhoneModel::query()
                ->select('brand')
                ->distinct()
                ->when($search !== '', fn ($query) => $query->where('brand', 'like', $search . '%'))
                ->orderBy('brand')
                ->limit(20)
                ->pluck('brand');
        }

        if ($type !== null) {
            return DeviceModelSuggestion::query()
                ->where('device_type', $type)
                ->select('brand')
                ->distinct()
                ->when($search !== '', fn ($query) => $query->where('brand', 'like', $search . '%'))
                ->orderBy('brand')
                ->limit(20)
                ->pluck('brand');
        }

        return collect()
            ->merge(PhoneModel::query()
                ->select('brand')
                ->distinct()
                ->when($search !== '', fn ($query) => $query->where('brand', 'like', $search . '%'))
                ->orderBy('brand')
                ->limit(20)
                ->pluck('brand'))
            ->merge(DeviceModelSuggestion::query()
                ->select('brand')
                ->distinct()
                ->when($search !== '', fn ($query) => $query->where('brand', 'like', $search . '%'))
                ->orderBy('brand')
                ->limit(20)
                ->pluck('brand'))
            ->unique()
            ->values()
            ->take(20);
    }

    public function models(Request $request)
    {
        $brand = trim((string) $request->query('brand', ''));
        $search = trim((string) $request->query('search', ''));
        $type = $this->normalizeType($request);

        if ($brand === '') {
            return [];
        }

        if ($type === 'phone') {
            return PhoneModel::query()
                ->where('brand', $brand)
                ->when($search !== '', fn ($query) => $query->where('model', 'like', '%' . $search . '%'))
                ->orderBy('model')
                ->limit(30)
                ->pluck('model');
        }

        if ($type !== null) {
            return DeviceModelSuggestion::query()
                ->where('device_type', $type)
                ->where('brand', $brand)
                ->when($search !== '', fn ($query) => $query->where('model', 'like', '%' . $search . '%'))
                ->orderByDesc('popularity')
                ->orderBy('model')
                ->limit(30)
                ->pluck('model');
        }

        return collect()
            ->merge(PhoneModel::query()
                ->where('brand', $brand)
                ->when($search !== '', fn ($query) => $query->where('model', 'like', '%' . $search . '%'))
                ->orderBy('model')
                ->limit(30)
                ->pluck('model'))
            ->merge(DeviceModelSuggestion::query()
                ->where('brand', $brand)
                ->when($search !== '', fn ($query) => $query->where('model', 'like', '%' . $search . '%'))
                ->orderByDesc('popularity')
                ->orderBy('model')
                ->limit(30)
                ->pluck('model'))
            ->unique()
            ->values()
            ->take(30);
    }

    public function suggestions(Request $request)
    {
        $type = $this->normalizeType($request);
        $brand = trim((string) $request->query('brand', ''));
        $search = trim((string) $request->query('q', $request->query('search', '')));

        if ($type === 'phone') {
            return PhoneModel::query()
                ->select([
                    'id',
                    DB::raw("'phone' as device_type"),
                    'brand',
                    'model',
                ])
                ->when($brand !== '', fn ($query) => $query->where('brand', $brand))
                ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                    $query->where('model', 'like', '%' . $search . '%')
                        ->orWhere('brand', 'like', '%' . $search . '%');
                }))
                ->orderBy('brand')
                ->orderBy('model')
                ->limit(30)
                ->get();
        }

        return DeviceModelSuggestion::query()
            ->select('id', 'device_type', 'brand', 'model')
            ->when($type !== null, fn ($query) => $query->where('device_type', $type))
            ->when($brand !== '', fn ($query) => $query->where('brand', $brand))
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('model', 'like', '%' . $search . '%')
                    ->orWhere('brand', 'like', '%' . $search . '%');
            }))
            ->orderByDesc('popularity')
            ->orderBy('brand')
            ->orderBy('model')
            ->limit(30)
            ->get();
    }

    private function normalizeType(Request $request): ?string
    {
        $type = trim((string) $request->query('type', ''));

        return in_array($type, self::SUGGESTION_TYPES, true) ? $type : null;
    }
}
