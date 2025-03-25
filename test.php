<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ConfigurationController extends Controller
{
    public function stageOne(Request $request)
    {
        // Retrieve inputs from the user
        $motorSpeed = $request->input('motor_speed');
        $power = $request->input('power');
        $motorType = $request->input('motor_type');

        // Check if the required values are provided
        if (empty($motorSpeed) || empty($power) || empty($motorType)) {
            session()->flash('error', 'Motor speed, power, and motor type are required.');
            return redirect()->route('admin.configuration.stage_one');
        }

        // Perform calculations for the first stage
        $efficiency = $this->calculateEfficiency($motorType);
        $adjustedPower = $this->adjustPowerBasedOnType($power, $motorType);

        // Store calculated values in the session
        session([
            'motor_speed' => $motorSpeed,
            'power' => $adjustedPower,
            'efficiency' => $efficiency,
        ]);

        // Redirect to the next stage
        return redirect()->route('admin.configuration.stage_two');
    }

    public function stageTwo(Request $request)
    {
        // Retrieve values from the session (from stage one)
        $motorSpeed = session('motor_speed');
        $adjustedPower = session('power');

        // Input for gear ratio and speed
        $gearSpeed = $request->input('gear_speed');
        $gearRatio = $request->input('gear_ratio');

        if (empty($gearSpeed) || empty($gearRatio)) {
            session()->flash('error', 'Gear speed and gear ratio are required.');
            return redirect()->route('admin.configuration.stage_two');
        }

        // Calculate the new motor speed after gear ratio adjustments
        $finalMotorSpeed = $this->applyGearRatio($motorSpeed, $gearSpeed, $gearRatio);

        // Store the new motor speed in the session for the next stage
        session([
            'final_motor_speed' => $finalMotorSpeed,
        ]);

        // Redirect to the final stage
        return redirect()->route('admin.configuration.final_stage');
    }

    public function finalStage(Request $request)
    {
        // Retrieve all values from the session (stages 1 and 2)
        $adjustedPower = session('power');
        $finalMotorSpeed = session('final_motor_speed');
        $efficiency = session('efficiency');

        // Input for final adjustments (e.g., load factor)
        $loadFactor = $request->input('load_factor');

        // Perform final configuration adjustments
        $finalPower = $this->applyLoadFactor($adjustedPower, $loadFactor);

        // Store the final configuration results
        session([
            'final_power' => $finalPower,
            'final_motor_speed' => $finalMotorSpeed,
        ]);

        // Display the final configuration to the user
        return view('admin.configuration.results', [
            'finalPower' => $finalPower,
            'finalMotorSpeed' => $finalMotorSpeed,
            'efficiency' => $efficiency,
        ]);
    }

    // Helper methods for calculations

    private function calculateEfficiency($motorType)
    {
        // Example: Different motors have different efficiencies
        $efficiencyValues = [
            'AC' => 0.9,
            'DC' => 0.8,
            'Induction' => 0.85,
        ];

        return $efficiencyValues[$motorType] ?? 0.85; // Default to 0.85
    }

    private function adjustPowerBasedOnType($power, $motorType)
    {
        // Example: Adjust power based on motor type
        $adjustmentFactor = $motorType == 'AC' ? 1.1 : 1.0;
        return $power * $adjustmentFactor;
    }

    private function applyGearRatio($motorSpeed, $gearSpeed, $gearRatio)
    {
        // Example: Adjust motor speed based on gear ratio
        return ($motorSpeed * $gearSpeed) / $gearRatio;
    }

    private function applyLoadFactor($power, $loadFactor)
    {
        // Example: Adjust power based on load factor
        return $power * $loadFactor;
    }
}

