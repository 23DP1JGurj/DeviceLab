<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PhoneModel;
use Illuminate\Http\Request;

class DeviceCatalogController extends Controller
{
    public function brands(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        return PhoneModel::query()
            ->select('brand')
            ->distinct()
            ->when($search !== '', fn ($query) => $query->where('brand', 'like', $search . '%'))
            ->orderBy('brand')
            ->limit(12)
            ->pluck('brand');
    }

    public function models(Request $request)
    {
        $brand = trim((string) $request->query('brand', ''));
        $search = trim((string) $request->query('search', ''));

        if ($brand === '') {
            return [];
        }

        return PhoneModel::query()
            ->where('brand', $brand)
            ->when($search !== '', fn ($query) => $query->where('model', 'like', '%' . $search . '%'))
            ->orderBy('model')
            ->limit(20)
            ->pluck('model');
    }
}
