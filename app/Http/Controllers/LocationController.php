<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

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
            ->whereBetween('created_at', [$startDate, $endDate])->get();

        return response()->json($locations);
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
                'comment' => 'nullable|string|max:255',
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

            Location::create($validatedData);

            return response()->json(['message' => 'Guardado'], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Unauthorized'], 401);
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
}
