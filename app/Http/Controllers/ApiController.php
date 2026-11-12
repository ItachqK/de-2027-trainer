<?php

namespace App\Http\Controllers;

use App\Models\Lab;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\Recipe;
use App\Models\UserState;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    public function course()
    {
        $modules = Module::with(['publishedLessons'])
            ->orderBy('position')
            ->get()
            ->map(fn (Module $module) => [
                'id' => 'm'.$module->num,
                'num' => $module->num,
                'title' => $module->title,
                'lead' => $module->lead,
                'lessons' => $module->publishedLessons->map->toCourseArray()->values(),
            ]);

        $recipes = Recipe::orderBy('position')->get()->map->toCourseArray()->values();

        $labs = Lab::where('is_published', true)
            ->orderBy('position')
            ->get()
            ->map->toCourseArray()
            ->values();

        return response()->json([
            'modules' => $modules,
            'recipes' => $recipes,
            'labs' => $labs,
            'total' => Lesson::where('is_published', true)->count(),
        ]);
    }

    public function state(Request $request)
    {
        $state = UserState::where('user_id', $request->user()->id)->value('data');

        return response()->json(['state' => $state]);
    }

    public function saveState(Request $request)
    {
        $data = $request->validate([
            'state' => ['required', 'array'],
        ]);

        $state = $data['state'];

        // Ограничиваем размер, чтобы один клиент не раздул базу.
        if (strlen(json_encode($state)) > 512000) {
            return response()->json(['message' => 'Слишком большой объём состояния.'], 422);
        }

        UserState::updateOrCreate(
            ['user_id' => $request->user()->id],
            ['data' => $state]
        );

        $request->user()->forceFill(['last_seen_at' => now()])->save();

        return response()->json(['ok' => true, 'saved_at' => now()->toIso8601String()]);
    }
}
