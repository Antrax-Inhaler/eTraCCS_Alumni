<?php

// app/Services/ThemeService.php

namespace App\Services;

use App\Models\ThemeTemplate;
use App\Models\UserTheme;

class ThemeService
{
    public function getAvailableThemes($forPublic = true)
    {
        return ThemeTemplate::when($forPublic, function ($query) {
            $query->where('is_public', true);
        })->get();
    }

    public function getUserTheme($userId)
    {
        return UserTheme::with('template')->firstOrCreate(
            ['user_id' => $userId],
            ['theme_template_id' => ThemeTemplate::where('is_default', true)->value('id')]
        );
    }

    public function updateUserTheme($userId, $data)
    {
        $userTheme = $this->getUserTheme($userId);

        if (isset($data['theme_template_id'])) {
            $userTheme->update([
                'theme_template_id' => $data['theme_template_id'],
                'custom_colors' => null,
                'is_custom' => false
            ]);
        } else {
            $userTheme->update([
                'custom_colors' => $data['custom_colors'],
                'is_custom' => true,
                'theme_template_id' => null
            ]);
        }

        return $userTheme;
    }

    public function createThemeTemplate($data)
    {
        return ThemeTemplate::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'is_public' => $data['is_public'] ?? false,
            'colors' => $data['colors'],
            'created_by' => auth()->id()
        ]);
    }
}