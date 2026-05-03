<?php

namespace App\Http\Controllers;

use App\Services\ExerciseService;
use App\Services\AppSettingService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ExerciseController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected ExerciseService    $exerciseService,
        protected AppSettingService  $settingService
    ) {}

    // POST /api/exercises/generate
    // المدرب يولد الأسئلة ويدخل البيانات
    public function generate(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'operation_type' => 'required|in:addition,subtraction,multiplication,division,mixed',
            'level'          => 'required|integer|min:1|max:3',
            'rows_count'     => 'required|integer|min:1|max:10',
            'count'          => 'nullable|integer|min:1|max:50',
        ]);

        if ($v->fails()) return $this->error('خطأ في البيانات', 422, $v->errors());

        try {
            $count = $request->count ?? 1;

            if ($count === 1) {
                $question = $this->exerciseService->generateQuestion(
                    $request->operation_type,
                    $request->level,
                    $request->rows_count
                );
            } else {
                $question = $this->exerciseService->generateMultipleQuestions(
                    $request->operation_type,
                    $request->level,
                    $request->rows_count,
                    $count
                );
            }

            return $this->success($question, 'تم توليد السؤال');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    // POST /api/exercises/start
    // بدء جلسة تدريب للطالب
    public function startSession(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'student_id'     => 'required|integer|exists:students,id',
            'operation_type' => 'required|in:addition,subtraction,multiplication,division,mixed',
            'level'          => 'required|integer|min:1|max:3',
            'rows_count'     => 'required|integer|min:1|max:10',
        ]);

        if ($v->fails()) return $this->error('خطأ في البيانات', 422, $v->errors());

        try {
            // جلب إعدادات التطبيق لمعرفة عدد الأسئلة
            $setting = $this->settingService->getFor($request->operation_type, $request->level);
            $count   = $setting?->questions_count ?? 15;

            $questions = $this->exerciseService->generateMultipleQuestions(
                $request->operation_type,
                $request->level,
                $request->rows_count,
                $count
            );

            // حفظ كل سؤال في الـ DB
            $sessions = [];
            foreach ($questions as $q) {
                $session    = $this->exerciseService->saveTrainingQuestion($request->student_id, $q);
                $sessions[] = array_merge($q, ['session_id' => $session->id]);
            }

            return $this->success([
                'sessions'        => $sessions,
                'total_questions' => count($sessions),
                'duration'        => $setting?->duration_minutes ?? 2,
            ], 'تم بدء جلسة التدريب');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    // POST /api/exercises/answer
    // الطالب يجيب على سؤال
    public function submitAnswer(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'session_id'     => 'required|integer|exists:training_sessions,id',
            'student_answer' => 'required|integer',
        ]);

        if ($v->fails()) return $this->error('خطأ في البيانات', 422, $v->errors());

        try {
            $result = $this->exerciseService->submitAnswer(
                $request->session_id,
                auth()->user()->studentProfile->id,
                $request->student_answer
            );
            return $this->success($result, $result['message']);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), $e->getCode() ?: 400);
        }
    }

    // POST /api/exercises/trainer-answer
    // المدرب يدخل إجابة الطالب يدوياً
    public function trainerSubmitAnswer(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'session_id'     => 'required|integer|exists:training_sessions,id',
            'student_id'     => 'required|integer|exists:students,id',
            'student_answer' => 'required|integer',
        ]);

        if ($v->fails()) return $this->error('خطأ في البيانات', 422, $v->errors());

        try {
            $result = $this->exerciseService->submitAnswer(
                $request->session_id,
                $request->student_id,
                $request->student_answer
            );
            return $this->success($result, $result['message']);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), $e->getCode() ?: 400);
        }
    }
}
