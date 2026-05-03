<?php

namespace App\Http\Controllers;

use App\Services\CompetitionService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class CompetitionController extends Controller
{
    use ApiResponse;

    public function __construct(protected CompetitionService $competitionService) {}

    // GET /api/competitions
    public function index(): JsonResponse
    {
        try {
            $competitions = $this->competitionService->getAllCompetitions();
            return $this->success($competitions, 'تم جلب المسابقات');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    // POST /api/competitions  [trainer only]
    public function store(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'name'             => 'required|string|max:100',
            'start_time'       => 'required|date',
            'end_time'         => 'required|date|after:start_time',
            'duration_minutes' => 'nullable|integer|min:1|max:60',
        ]);

        if ($v->fails()) return $this->error('خطأ في البيانات', 422, $v->errors());

        try {
            $competition = $this->competitionService->createCompetition($request->all());
            return $this->success($competition, 'تم إنشاء المسابقة', 201);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    // POST /api/competitions/{id}/start  [student]
    public function startSession(Request $request, int $id): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'level'      => 'required|integer|min:1|max:3',
            'rows_count' => 'required|integer|min:1|max:10',
        ]);

        if ($v->fails()) return $this->error('خطأ في البيانات', 422, $v->errors());

        try {
            $studentId = auth()->user()->studentProfile->id;
            $result    = $this->competitionService->startCompetitionSession($id, $studentId, $request->all());
            return $this->success($result, 'تم بدء المسابقة');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), $e->getCode() ?: 400);
        }
    }

    // POST /api/competitions/answer
    public function submitAnswer(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'session_id'     => 'required|integer|exists:competition_sessions,id',
            'student_answer' => 'required|integer',
            'time_taken'     => 'required|integer|min:0',
        ]);

        if ($v->fails()) return $this->error('خطأ في البيانات', 422, $v->errors());

        try {
            $studentId = auth()->user()->studentProfile->id;
            $result    = $this->competitionService->submitCompetitionAnswer(
                $request->session_id,
                $studentId,
                $request->student_answer,
                $request->time_taken
            );
            return $this->success($result, $result['message']);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), $e->getCode() ?: 400);
        }
    }

    // GET /api/competitions/{id}/results
    public function results(int $id): JsonResponse
    {
        try {
            $results = $this->competitionService->getCompetitionResults($id);
            return $this->success($results, 'نتائج المسابقة');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }
}
