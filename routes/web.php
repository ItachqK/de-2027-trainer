<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\AppController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BuildController;
use App\Http\Controllers\BlueprintController;
use App\Http\Controllers\CapstoneController;
use App\Http\Controllers\CaseStudyController;
use App\Http\Controllers\DrillController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\LabController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TopicController;
use App\Http\Controllers\VariantController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AppController::class, 'index'])->name('app');
Route::get('/api/course', [ApiController::class, 'course'])->name('api.course');

Route::middleware('auth')->group(function () {
    Route::get('/api/state', [ApiController::class, 'state'])->name('api.state');
    Route::post('/api/state', [ApiController::class, 'saveState'])->name('api.state.save');

    Route::get('/plan', [PlanController::class, 'index'])->name('plan.index');

    Route::get('/labs', [LabController::class, 'index'])->name('labs.index');
    Route::get('/labs/{lab:slug}', [LabController::class, 'show'])->name('labs.show');
    Route::post('/labs/{lab:slug}/check', [LabController::class, 'check'])->name('labs.check');
    Route::post('/labs/{lab:slug}/draft', [LabController::class, 'draft'])->name('labs.draft');

    Route::get('/drills', [DrillController::class, 'index'])->name('drills.index');
    Route::get('/drills/next', [DrillController::class, 'next'])->name('drills.next');
    Route::get('/drills/{drill:slug}', [DrillController::class, 'show'])->name('drills.show');
    Route::post('/drills/{drill:slug}/check', [DrillController::class, 'check'])->name('drills.check');

    Route::get('/variants', [VariantController::class, 'index'])->name('variants.index');
    Route::get('/variants/random', [VariantController::class, 'random'])->name('variants.random');
    Route::get('/variants/{variant:slug}', [VariantController::class, 'show'])->name('variants.show');
    Route::get('/variants/{variant:slug}/download', [VariantController::class, 'download'])->name('variants.download');
    Route::post('/variants/{variant:slug}/select', [VariantController::class, 'select'])->name('variants.select');

    Route::get('/exam', [ExamController::class, 'index'])->name('exam.index');
    Route::post('/exam', [ExamController::class, 'start'])->name('exam.start');
    Route::get('/exam/{session}/result', [ExamController::class, 'result'])->name('exam.result');
    Route::get('/exam/{session}', [ExamController::class, 'show'])->name('exam.show');
    Route::post('/exam/{session}/finish', [ExamController::class, 'finish'])->name('exam.finish');

    Route::get('/build', [BuildController::class, 'index'])->name('build.index');
    Route::get('/build/download', [BuildController::class, 'download'])->name('build.download');
    Route::post('/build/reset', [BuildController::class, 'reset'])->name('build.reset');
    Route::get('/build/{step:slug}', [BuildController::class, 'show'])->name('build.show');
    Route::post('/build/{step:slug}/done', [BuildController::class, 'complete'])->name('build.complete');

    Route::get('/cases', [CaseStudyController::class, 'index'])->name('cases.index');
    Route::get('/cases/{case:slug}/chronicle', [CaseStudyController::class, 'chronicle'])->name('cases.chronicle');
    Route::get('/cases/{case:slug}', [CaseStudyController::class, 'show'])->name('cases.show');

    Route::get('/project', [ProjectController::class, 'show'])->name('project.show');
    Route::post('/project', [ProjectController::class, 'save'])->name('project.save');

    Route::get('/topic', [TopicController::class, 'index'])->name('topic.index');
    Route::post('/topic', [TopicController::class, 'build'])->name('topic.build');
    Route::post('/topic/download', [TopicController::class, 'download'])->name('topic.download');

    Route::get('/blueprint', [BlueprintController::class, 'index'])->name('blueprint.index');
    Route::post('/blueprint', [BlueprintController::class, 'build'])->name('blueprint.build');
    Route::get('/blueprint/result', [BlueprintController::class, 'result'])->name('blueprint.result');
    Route::get('/blueprint/download', [BlueprintController::class, 'download'])->name('blueprint.download');
    Route::get('/blueprint/project', [BlueprintController::class, 'project'])->name('blueprint.project');

    Route::get('/capstone', [CapstoneController::class, 'index'])->name('capstone.index');
    Route::post('/capstone', [CapstoneController::class, 'audit'])->name('capstone.audit');
});

Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1')->name('register.store');

    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1')->name('login.store');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('dashboard');

    Route::get('/students', [AdminController::class, 'students'])->name('students');
    Route::get('/students/{user}', [AdminController::class, 'student'])->name('student');

    Route::get('/lessons', [AdminController::class, 'lessons'])->name('lessons');
    Route::get('/lessons/create', [AdminController::class, 'createLesson'])->name('lessons.create');
    Route::post('/lessons', [AdminController::class, 'storeLesson'])->name('lessons.store');
    Route::get('/lessons/{lesson}/edit', [AdminController::class, 'editLesson'])->name('lessons.edit');
    Route::put('/lessons/{lesson}', [AdminController::class, 'updateLesson'])->name('lessons.update');
    Route::delete('/lessons/{lesson}', [AdminController::class, 'destroyLesson'])->name('lessons.destroy');

    Route::get('/labs', [AdminController::class, 'labs'])->name('labs');
    Route::get('/labs/{lab}/edit', [AdminController::class, 'editLab'])->name('labs.edit');
    Route::put('/labs/{lab}', [AdminController::class, 'updateLab'])->name('labs.update');

    Route::get('/capstone', [AdminController::class, 'capstone'])->name('capstone');
    Route::get('/capstone/{submission}', [AdminController::class, 'capstoneShow'])->name('capstone.show');
});
