<?php

// app/Http/Controllers/Admin/ThemeController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ThemeTemplate;
use App\Services\ThemeService;
use Illuminate\Http\Request;

class AdminThemeController extends Controller
{
    protected $themeService;

    public function __construct(ThemeService $themeService)
    {
        $this->themeService = $themeService;
    }

    public function index()
    {
        $themes = $this->themeService->getAvailableThemes(false);
        return inertia('Admin/Themes/Index', compact('themes'));
    }

    public function create()
    {
        return inertia('Admin/Themes/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_public' => 'boolean',
            'colors' => 'required|array',
            'colors.primary' => 'required|string',
            'colors.primary_light' => 'required|string',
            'colors.text_primary' => 'required|string',
            'colors.text_secondary' => 'required|string',
            'colors.bg_dark' => 'required|string',
            'colors.bg_darker' => 'required|string',
            'colors.card_bg' => 'required|string',
            'colors.card_border' => 'required|string',
        ]);

        $theme = $this->themeService->createThemeTemplate($request->all());

        return redirect()->route('admin.themes.index')
            ->with('success', 'Theme created successfully');
    }

    public function setDefault($id)
    {
        ThemeTemplate::where('is_default', true)->update(['is_default' => false]);
        ThemeTemplate::findOrFail($id)->update(['is_default' => true]);

        return back()->with('success', 'Default theme updated');
    }
// app/Http/Controllers/Admin/ThemeController.php

public function edit(ThemeTemplate $theme)
{
    return inertia('Admin/Themes/Edit', [
        'theme' => $theme
    ]);
}

public function update(Request $request, ThemeTemplate $theme)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'is_public' => 'boolean',
        'colors' => 'required|array',
        'colors.primary' => 'required|string',
        'colors.primary_light' => 'required|string',
        'colors.text_primary' => 'required|string',
        'colors.text_secondary' => 'required|string',
        'colors.bg_dark' => 'required|string',
        'colors.bg_darker' => 'required|string',
        'colors.card_bg' => 'required|string',
        'colors.card_border' => 'required|string',
    ]);

    $theme->update($request->all());

    return redirect()->route('admin.themes.index')
        ->with('success', 'Theme updated successfully');
}
}