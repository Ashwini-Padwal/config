<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CalculationController extends Controller
{
    public function generateConfiguration(Request $request)
    {
        // Retrieve input values from the form
        $ipkw = $request->input('kw');
        $hp = $request->input('hp');
        $ipmotorspeed = $request->input('speed');
        $pole = $request->input('pole');
        $gearspeed = $request->input('gbrpm');
        $ipratio = $request->input('ratio');

        // Check if motorspeed, kw, or hp are provided
        if (empty($ipmotorspeed) && empty($ipkw) && empty($hp)) {
            session()->flash('error', 'At least one of kw or hp and pole is required');
            return redirect()->route('admin.generate_combination.index');
        }

        // First stage calculations (Stage 1)
        if ($request->input('stage') == 1) {
            // Perform calculations for the first stage
            $outputRatio = $this->calculateRatio($ipmotorspeed, $gearspeed);
            $motorspeed = $this->applyStageEfficiencyspeed($ipmotorspeed, $outputRatio, 1);
            $kw = $this->applyStageEfficiency($ipkw, $request->input('fossetting'), 1);

            // Store values in the session for use in the second stage
            session([
                'first_stage_output_ratio' => $outputRatio,
                'first_stage_motorspeed' => $motorspeed,
                'first_stage_kw' => $kw,
            ]);

            // Set the stage to 2 for the next calculation
            return redirect()->route('admin.generate_combination.index')->with(['stage' => 2]);
        }

        // Second stage calculations (Stage 2)
        if ($request->input('stage') == 2) {
            // Retrieve the first stage values from the session
            $outputRatio = session('first_stage_output_ratio');
            $motorspeed = session('first_stage_motorspeed');
            $kw = session('first_stage_kw');

            if (!$outputRatio || !$motorspeed || !$kw) {
                session()->flash('error', 'Missing required values for second stage calculation.');
                return redirect()->route('admin.generate_combination.index');
            }

            // Perform second stage calculations using the stored values
            $newRatio = $this->applyStageEfficiencyoutputratio($outputRatio, $request->input('newMratio'), 2);
            $newMotorSpeed = $this->applyStageEfficiencyspeed($motorspeed, $newRatio, 2);
            $newKw = $this->applyStageEfficiency($kw, $request->input('fossetting'), 2);

            // Continue with further processing for stage 2...
            // Example of storing results for further use
            session([
                'second_stage_new_ratio' => $newRatio,
                'second_stage_new_motor_speed' => $newMotorSpeed,
                'second_stage_new_kw' => $newKw,
            ]);

            // Optionally return the results to the user
            return view('admin.results', compact('newRatio', 'newMotorSpeed', 'newKw'));
        }
    }

    // Helper functions to perform the necessary calculations

    private function calculateRatio($ipmotorspeed, $gearspeed)
    {
        // Example calculation for output ratio
        return $ipmotorspeed / $gearspeed;
    }

    private function applyStageEfficiencyspeed($motorspeed, $outputRatio, $stage)
    {
        // Example calculation for motor speed adjustment
        return $motorspeed * $outputRatio;
    }

    private function applyStageEfficiency($kw, $fossetting, $stage)
    {
        // Example calculation for kw adjustment
        return $kw * $fossetting;
    }

    private function applyStageEfficiencyoutputratio($outputRatio, $newMratio, $stage)
    {
        // Example adjustment to output ratio based on newMratio
        return $outputRatio * $newMratio;
    }
}

