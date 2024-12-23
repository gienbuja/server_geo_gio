<?php

namespace App\Http\Controllers;

use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ZoneController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $zones = Auth::user()->zones()->get();
        return response()->json($zones, 201);
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
        //
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'color' => 'nullable|string',
                'visible' => 'nullable|boolean',
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
                'radius' => 'required|numeric',
            ]);
            $validatedData['user_id'] = auth()->id();
            $zone = Zone::create($validatedData);
            return response()->json($zone, 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json($e->errors(), 422);
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 401);
        }
    }

    public function storeAll(Request $request)
    {
        try {
            $zones = $request->all();
            $validZones = [];
            $invalidZones = [];

            // Validar cada zona en el array
            foreach ($zones as $zone) {
                $validator = Validator::make($zone, [
                    'name' => 'required|string|max:255',
                    'description' => 'nullable|string',
                    'color' => 'nullable|string',
                    'visible' => 'nullable|boolean',
                    'latitude' => 'required|numeric',
                    'longitude' => 'required|numeric',
                    'radius' => 'required|numeric',
                ]);

                if ($validator->fails()) {
                    $invalidZones[] = [
                        'zone' => $zone,
                        'errors' => $validator->errors()
                    ];
                } else {
                    $validZones[] = $zone;
                }
            }

            // Procesar zonas válidas
            $validZones = array_map(function ($zone) {
                $zone['user_id'] = auth()->id();
                return Zone::create($zone);
            }, $validZones);

            // Devolver zonas no válidas
            return response()->json([
                'saved_zones' => $validZones,
                'invalid_zones' => $invalidZones
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
    public function show(Zone $zone)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Zone $zone)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Zone $zone)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Zone $zone)
    {
        //
    }
}
