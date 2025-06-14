<?php

// app/Http/Controllers/ThemeController.php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\ThemeService;
use Illuminate\Http\Request;

class ThemeController extends Controller
{
    protected $themeService;

    public function __construct(ThemeService $themeService)
    {
        $this->themeService = $themeService;
    }

    public function index()
    {
        $themes = $this->themeService->getAvailableThemes();
        $userTheme = $this->themeService->getUserTheme(auth()->id());

        return inertia('Themes/Index', [
            'themes' => $themes,
            'userTheme' => $userTheme
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'theme_template_id' => 'nullable|exists:theme_templates,id',
            'custom_colors' => 'nullable|array',
            'custom_colors.primary' => 'required_with:custom_colors|string',
            'custom_colors.primary_light' => 'required_with:custom_colors|string',
            'custom_colors.text_primary' => 'required_with:custom_colors|string',
            'custom_colors.text_secondary' => 'required_with:custom_colors|string',
            'custom_colors.bg_dark' => 'required_with:custom_colors|string',
            'custom_colors.bg_darker' => 'required_with:custom_colors|string',
            'custom_colors.card_bg' => 'required_with:custom_colors|string',
            'custom_colors.card_border' => 'required_with:custom_colors|string',
        ]);

        $this->themeService->updateUserTheme(auth()->id(), $request->all());

        return back()->with('success', 'Theme updated successfully');
    }
}