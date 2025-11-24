<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OngkirController extends Controller
{
    protected function apiBase()
    {
        return env('RAJAONGKIR_BASE_URL', 'https://api.rajaongkir.com/starter');
    }

    protected function apiKey()
    {
        return env('RAJAONGKIR_API_KEY');
    }

    public function getProvinces()
    {
        $resp = Http::withHeaders(['key' => $this->apiKey()])
                    ->get($this->apiBase() . '/province');

        if ($resp->successful()) {
            $data = $resp->json();
            return response()->json($data['rajaongkir']['results'] ?? []);
        }

        Log::error('RajaOngkir getProvinces error', ['resp' => $resp->body()]);
        return response()->json([], 500);
    }

    public function getCities(Request $request, $province_id)
    {
        $resp = Http::withHeaders(['key' => $this->apiKey()])
                    ->get($this->apiBase() . '/city', [
                        'province' => $province_id
                    ]);

        if ($resp->successful()) {
            $data = $resp->json();
            return response()->json($data['rajaongkir']['results'] ?? []);
        }

        Log::error('RajaOngkir getCities error', ['resp' => $resp->body()]);
        return response()->json([], 500);
    }

    public function getCost(Request $request)
    {
        $request->validate([
            'origin' => 'required',
            'destination' => 'required',
            'weight' => 'required|integer',
            'courier' => 'required|string',
        ]);

        $payload = [
            'origin' => $request->origin,
            'destination' => $request->destination,
            'weight' => $request->weight,
            'courier' => $request->courier,
        ];

        $resp = Http::withHeaders(['key' => $this->apiKey()])
                    ->post($this->apiBase() . '/cost', $payload);

        if ($resp->successful()) {
            $data = $resp->json();
            // Struktur: results[0].costs
            return response()->json($data['rajaongkir']['results'][0]['costs'] ?? []);
        }

        Log::error('RajaOngkir getCost error', ['resp' => $resp->body()]);
        return response()->json([], 500);
    }
}
