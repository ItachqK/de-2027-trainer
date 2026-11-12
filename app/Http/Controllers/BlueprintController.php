<?php

namespace App\Http\Controllers;

use App\Support\Blueprint;
use Illuminate\Http\Request;

class BlueprintController extends Controller
{
    private const SESSION_KEY = 'blueprint.input';

    public function index(Request $request)
    {
        $topic = (string) $request->query('topic', '');

        $input = Blueprint::blank($topic);

        return view('blueprint.index', [
            'input' => $input,
            'preview' => Blueprint::derive($input['topic']),
            'result' => null,
        ]);
    }

    public function build(Request $request)
    {
        $request->validate([
            'topic' => ['required', 'string', 'min:2', 'max:80'],
            'project_name' => ['nullable', 'string', 'max:120'],
            'purpose' => ['nullable', 'string', 'max:160'],
            'object_singular' => ['nullable', 'string', 'max:80'],
            'object_plural' => ['nullable', 'string', 'max:80'],
            'object_options' => ['nullable', 'string', 'max:200'],
            'date_label' => ['nullable', 'string', 'max:120'],
            'status_new' => ['nullable', 'string', 'max:80'],
            'status_assigned' => ['nullable', 'string', 'max:80'],
            'status_done' => ['nullable', 'string', 'max:80'],
            'pay_1' => ['nullable', 'string', 'max:80'],
            'pay_2' => ['nullable', 'string', 'max:80'],
            'review_text' => ['nullable', 'string', 'max:120'],
            'admin_login' => ['nullable', 'string', 'max:60'],
            'admin_password' => ['nullable', 'string', 'max:60'],
        ], [
            'topic.required' => 'Введите тему — например, «Обмен книгами» или «Пицца на районе».',
            'topic.min' => 'Тема слишком короткая: нужно хотя бы два символа.',
        ]);

        $input = Blueprint::normalize($request->all());
        $request->session()->put(self::SESSION_KEY, $input);

        return redirect()->route('blueprint.result');
    }

    public function result(Request $request)
    {
        $input = $request->session()->get(self::SESSION_KEY);

        if (! $input) {
            return redirect()->route('blueprint.index')
                ->with('blueprint_error', 'Сначала укажите тему — инструкция собирается из неё.');
        }

        return view('blueprint.index', [
            'input' => $input,
            'preview' => $input,
            'result' => Blueprint::build($input),
        ]);
    }

    public function download(Request $request)
    {
        $input = $request->session()->get(self::SESSION_KEY);

        if (! $input) {
            return redirect()->route('blueprint.index');
        }

        return response(Blueprint::markdown($input), 200, [
            'Content-Type' => 'text/markdown; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.Blueprint::fileName($input, 'md').'"',
        ]);
    }

    public function project(Request $request)
    {
        $input = $request->session()->get(self::SESSION_KEY);

        if (! $input) {
            return redirect()->route('blueprint.index');
        }

        try {
            $path = Blueprint::zip($input);
        } catch (\RuntimeException $e) {
            return redirect()->route('blueprint.result')
                ->with('blueprint_error', $e->getMessage());
        }

        return response()->download($path, Blueprint::fileName($input, 'zip'))->deleteFileAfterSend(true);
    }
}
