<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LaptopModel;
use App\Models\PhoneModel;
use App\Models\TabletModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeviceCatalogController extends Controller
{
    private const SUGGESTION_TYPES = ['phone', 'tablet', 'laptop'];

    public function brands(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $type = $this->normalizeType($request);

        $modelClass = $this->modelClassForType($type);

        if ($modelClass !== null) {
            return $modelClass::query()
                ->select('brand')
                ->distinct()
                ->when($search !== '', fn ($query) => $query->where('brand', 'like', $search . '%'))
                ->orderBy('brand')
                ->limit(20)
                ->pluck('brand');
        }

        return collect()
            ->merge($this->brandResults(PhoneModel::class, $search))
            ->merge($this->brandResults(LaptopModel::class, $search))
            ->merge($this->brandResults(TabletModel::class, $search))
            ->unique()
            ->sort()
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

        $modelClass = $this->modelClassForType($type);

        if ($modelClass !== null) {
            return $modelClass::query()
                ->where('brand', $brand)
                ->when($search !== '', fn ($query) => $query->where('model', 'like', '%' . $search . '%'))
                ->orderBy('model')
                ->limit(30)
                ->pluck('model');
        }

        return collect()
            ->merge($this->modelResults(PhoneModel::class, $brand, $search))
            ->merge($this->modelResults(LaptopModel::class, $brand, $search))
            ->merge($this->modelResults(TabletModel::class, $brand, $search))
            ->unique()
            ->sort()
            ->values()
            ->take(30);
    }

    public function suggestions(Request $request)
    {
        $type = $this->normalizeType($request);
        $brand = trim((string) $request->query('brand', ''));
        $search = trim((string) $request->query('q', $request->query('search', '')));

        $modelClass = $this->modelClassForType($type);

        if ($modelClass !== null) {
            return $modelClass::query()
                ->select([
                    'id',
                    DB::raw("'" . $type . "' as device_type"),
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

        return collect()
            ->merge($this->suggestionResults(PhoneModel::class, 'phone', $brand, $search))
            ->merge($this->suggestionResults(LaptopModel::class, 'laptop', $brand, $search))
            ->merge($this->suggestionResults(TabletModel::class, 'tablet', $brand, $search))
            ->values()
            ->take(30);
    }

    private function normalizeType(Request $request): ?string
    {
        $type = trim((string) $request->query('type', ''));

        return in_array($type, self::SUGGESTION_TYPES, true) ? $type : null;
    }

    private function modelClassForType(?string $type): ?string
    {
        return match ($type) {
            'phone' => PhoneModel::class,
            'laptop' => LaptopModel::class,
            'tablet' => TabletModel::class,
            default => null,
        };
    }

    private function brandResults(string $modelClass, string $search)
    {
        return $modelClass::query()
            ->select('brand')
            ->distinct()
            ->when($search !== '', fn ($query) => $query->where('brand', 'like', $search . '%'))
            ->orderBy('brand')
            ->limit(20)
            ->pluck('brand');
    }

    private function modelResults(string $modelClass, string $brand, string $search)
    {
        return $modelClass::query()
            ->where('brand', $brand)
            ->when($search !== '', fn ($query) => $query->where('model', 'like', '%' . $search . '%'))
            ->orderBy('model')
            ->limit(30)
            ->pluck('model');
    }

    private function suggestionResults(string $modelClass, string $type, string $brand, string $search)
    {
        return $modelClass::query()
            ->select([
                'id',
                DB::raw("'" . $type . "' as device_type"),
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
}
