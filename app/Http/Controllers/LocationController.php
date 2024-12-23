<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class LocationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        if (!$startDate || !$endDate) {
            $startDate = Carbon::today()->startOfDay();
            $endDate = Carbon::today()->endOfDay();
        }

        $locations = Auth::user()->locations()
            ->whereBetween('datetime', [$startDate, $endDate])->get();

        return response()->json($locations, 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
                'title' => 'required_if:manual,true|string|max:255',
                'description' => 'nullable|string',
                'icon' => 'nullable|string',
                'manual' => 'nullable|boolean',
                'visible' => 'nullable|boolean',
                'datetime' => ['required', 'date', function ($attribute, $value, $fail) {
                    try {
                        $date = Carbon::parse($value);
                        if ($date->format('P') !== '+00:00') {
                            $fail('el campo ' . $attribute . ' debe ser una fecha en formato UTC');
                        }
                    } catch (\Exception $e) {
                        $fail('El campo ' . $attribute . ' no es una fecha válida');
                    }
                }],
            ]);
            $validatedData['user_id'] = Auth::user()->id;
            $validatedData['zone_id'] = null;

            $zones = Auth::user()->zones()->get();

            if (!$zones->isEmpty()) {
                $zones->each(function ($zone) use (&$validatedData) {
                    $radiusInMeters = $zone->radius;
                    if ($this->isWithinRadius($validatedData['latitude'], $validatedData['longitude'], $zone->latitude, $zone->longitude, $radiusInMeters)) {
                        $validatedData['zone_id'] = $zone->id;
                        return;
                    }
                });
            }

            $location = Location::create($validatedData);

            return response()->json($location, 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json($e->errors(), 422);
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 401);
        }
    }

    public function storeAll(Request $request)
    {
        try {
            $locations = $request->all();
            $validLocations = [];
            $invalidLocations = [];

            // Validar cada ubicación en el array
            foreach ($locations as $location) {
                $validator = Validator::make($location, [
                    'latitude' => 'required|numeric',
                    'longitude' => 'required|numeric',
                    'title' => 'required_if:manual,true|string|max:255',
                    'description' => 'nullable|string',
                    'icon' => 'nullable|string',
                    'manual' => 'nullable|boolean',
                    'visible' => 'nullable|boolean',
                    'datetime' => ['required', 'date', function ($attribute, $value, $fail) {
                        try {
                            $date = Carbon::parse($value);
                            if ($date->format('P') !== '+00:00') {
                                $fail('el campo ' . $attribute . ' debe ser una fecha en formato UTC');
                            }
                        } catch (\Exception $e) {
                            $fail('El campo ' . $attribute . ' no es una fecha válida');
                        }
                    }],
                ]);

                if ($validator->fails()) {
                    $invalidLocations[] = [
                        'location' => $location,
                        'errors' => $validator->errors()
                    ];
                } else {
                    $validLocations[] = $location;
                }
            }

            // Procesar ubicaciones válidas
            $validLocations = array_map(function ($location) {
                $location['user_id'] = Auth::user()->id;
                $location['zone_id'] = null;
                return $location;
            }, $validLocations);

            $zones = Auth::user()->zones()->get();

            if (!$zones->isEmpty()) {
                $validLocations = array_map(function ($location) use ($zones) {
                    $zones->each(function ($zone) use (&$location) {
                        $radiusInMeters = $zone->radius;
                        if ($this->isWithinRadius($location['latitude'], $location['longitude'], $zone->latitude, $zone->longitude, $radiusInMeters)) {
                            $location['zone_id'] = $zone->id;
                            return;
                        }
                    });
                    return $location;
                }, $validLocations);
            }

            // Guardar ubicaciones válidas
            Location::insert($validLocations);

            // Devolver ubicaciones no válidas
            return response()->json([
                'saved_locations' => $validLocations,
                'invalid_locations' => $invalidLocations
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json($e->errors(), 422);
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 401);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Location $location)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Location $location)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Location $location)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Location $location)
    {
        //
    }

    function isWithinRadius($lat1, $lon1, $lat2, $lon2, $radius)
    {
        // Convertir grados a radianes
        $lat1 = deg2rad($lat1);
        $lon1 = deg2rad($lon1);
        $lat2 = deg2rad($lat2);
        $lon2 = deg2rad($lon2);

        // Radio de la Tierra en metros
        $earthRadius = 6371000;

        // Diferencias de coordenadas
        $dLat = $lat2 - $lat1;
        $dLon = $lon2 - $lon1;

        // Fórmula de Haversine
        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos($lat1) * cos($lat2) *
            sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earthRadius * $c;

        // Comparar la distancia con el radio
        return $distance <= $radius;
    }
}
