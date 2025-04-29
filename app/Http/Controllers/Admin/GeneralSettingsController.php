<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GeneralSetting;
use Illuminate\Support\Facades\Storage;

class GeneralSettingsController extends Controller
{
    public function index()
    {
        // Fetch the first entry of general settings (assuming single settings)
        $settings = GeneralSetting::first();
        
        // If settings don't exist, create a default one
        if (!$settings) {
            $settings = GeneralSetting::create([
                'system_name' => 'My System',
                'main_color' => '#000000',
                'background_color' => '#ffffff',
                'text_color' => '#000000',
                'logo' => null,
            ]);
        }

        return view('admin.settings', compact('settings'));
    }
    public function viewer()
    {
        $settings = GeneralSetting::first();
    
        return response()->json([
            'settings' => [
                'system_name' => $settings->system_name,          
                'logo' => asset('storage/' . $settings->logo),      
                'main_color' => $settings->main_color,
                'background_color' => $settings->background_color,
                'text_color' => $settings->text_color,
                'sidebar_bg_color' => $settings->sidebar_bg_color,  
                'topbar_bg_color' => $settings->topbar_bg_color,   
                'font_style' => $settings->font_style             
            ]
        ]);
    }
    
    public function update(Request $request) 
    {
        // Validate input, including new fields for sidebar, topbar, and font style
        $request->validate([
            'system_name' => 'required|string|max:255',
            'main_color' => 'required|string|max:7',
            'background_color' => 'required|string|max:7',
            'text_color' => 'required|string|max:7',
            'sidebar_bg_color' => 'required|string|max:7', 
            'topbar_bg_color' => 'required|string|max:7',    
            'font_style' => 'required|string|max:100',      
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
    
        // Retrieve the first GeneralSetting instance
        $settings = GeneralSetting::first();
    
        // Handle logo upload, ensuring the logo is saved to the 'logos' folder in the public storage
        if ($request->hasFile('logo')) {
            // Store the logo in the 'logos' directory in the public storage disk
            $logo = $request->file('logo');
            $logoPath = $logo->store('logos', 'public');  // This ensures it is stored in storage/app/public/logos
        } else {
            // Keep the current logo if no new logo is uploaded
            $logoPath = $settings->logo;
        }
    
        // Update the settings with the new data, including the new fields
        $settings->update([
            'system_name' => $request->system_name,
            'main_color' => $request->main_color,
            'background_color' => $request->background_color,
            'text_color' => $request->text_color,
            'sidebar_bg_color' => $request->sidebar_bg_color,  
            'topbar_bg_color' => $request->topbar_bg_color,   
            'font_style' => $request->font_style,              
            'logo' => $logoPath,  
        ]);
    
        // Redirect back with success message
        return redirect()->back()->with('success', 'Settings updated successfully.');
    }
       
}
