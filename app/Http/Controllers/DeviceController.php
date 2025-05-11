<?php

namespace App\Http\Controllers;

use App\Models\Device; // Changed from DeviceData to match migration
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeviceController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'temperature' => 'required|numeric|between:20,30',
            'humidity' => 'required|numeric|between:40,60',
            'device_id' => 'required|integer|between:1,10'
        ]);

        Device::create($validated);
        
        return response()->json([
            'message' => 'Data stored successfully',
            'data' => $validated
        ]);
    }

    public function index()
    {
        // Get data from last 24 hours by default
        $data = Device::where('created_at', '>=', now()->subDay())
                    ->orderBy('created_at', 'asc')
                    ->get();
        
        return response()->json($data);
    }

    public function dashboard()
    {
        // Get device list and latest readings
        $devices = Device::select('device_id', DB::raw('MAX(created_at) as last_seen'))
                    ->groupBy('device_id')
                    ->get();

        return view('dashboard', [
            'devices' => $devices,
            'totalReadings' => Device::count()
        ]);
    }
}