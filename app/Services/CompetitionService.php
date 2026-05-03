<?php

namespace App\Services;

use App\Models\Competition;
use App\Models\CompetitionSession;
use App\Models\StudentLevel;
use Illuminate\Support\Facades\DB;

class CompetitionService
{
    protected ExerciseService $exerciseService;

    public function __construct(ExerciseService $exerciseService)
    {
        $this->exerciseService = $exerciseService;
    }

    // ===================== إنشاء مسابقة جديدة =====================
    public function createCompetition(array $data): Competition
    {
        return Competition::create([
            'name'             => $data['name'],
            'start_time'       => $data['start_time'],
            'end_time'         => $data['end_time'],
            'duration_minutes' => $data['duration_minutes'] ?? 3,
        ]);
    }

    // ===================== جلب كل المسابقات =====================
    public function getAllCompetitions(): \Illuminate\Database\Eloquent\Collection
    {
        return Competition::withCount('sessions')->latest()->get();
    }

    // ===================== بدء جلسة مسابقة للطالب =====================
    public function startCompetitionSession(int $competitionId, int $studentId, array $data): array
    {
        $competition = Competition::findOrFail($competitionId);

        if (!$competition->isActive()) {
            throw new \Exception('المسابقة غير متاحة حالياً', 403);
        }

        // توليد أسئلة المسابقة (جمع + طرح + قسمة + ضرب + ميكس)
        $questions = [];
        $operations = ['addition', 'subtraction', 'multiplication', 'division', 'mixed'];

        foreach ($operations as $op) {
            $q = $this->exerciseService->generateQuestion($op, $data['level'] ?? 1, $data['rows_count'] ?? 3);
            $session = CompetitionSession::create([
                'competition_id'    => $competitionId,
                'student_id'        => $studentId,
                'operation_type'    => $q['operation_type'],
                'level'             => $q['level'],
                'num1'              => $q['num1'],
                'num2'              => $q['num2'],
                'num3'              => $q['num3'],
                'correct_result'    => $q['correct_result'],
            ]);
            $questions[] = array_merge($q, ['session_id' => $session->id]);
        }

        return [
            'competition'       => $competition,
            'questions'         => $questions,
            'duration_minutes'  => $competition->duration_minutes,
        ];
    }

    // ===================== تصحيح إجابة مسابقة =====================
    public function submitCompetitionAnswer(int $sessionId, int $studentId, int $answer, int $timeTaken): array
    {
        $session = CompetitionSession::where('id', $sessionId)
            ->where('student_id', $studentId)
            ->whereNull('student_answer')
            ->firstOrFail();

        $isCorrect = ($answer == $session->correct_result);

        $session->update([
            'student_answer'     => $answer,
            'is_correct'         => $isCorrect,
            'time_taken_seconds' => $timeTaken,
        ]);

        StudentLevel::updateStats($studentId, $session->operation_type, $session->level, $isCorrect);

        return [
            'is_correct'     => $isCorrect,
            'correct_result' => $session->correct_result,
            'student_answer' => $answer,
            'message'        => $isCorrect ? '✅ إجابة صحيحة!' : '❌ إجابة خاطئة، الناتج الصحيح: ' . $session->correct_result,
        ];
    }

    // ===================== نتائج المسابقة =====================
    public function getCompetitionResults(int $competitionId): array
    {
        $results = CompetitionSession::where('competition_id', $competitionId)
            ->with('student:id,name,level')
            ->select(
                'student_id',
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN is_correct = 1 THEN 1 ELSE 0 END) as correct'),
                DB::raw('AVG(time_taken_seconds) as avg_time')
            )
            ->groupBy('student_id')
            ->orderByDesc('correct')
            ->orderBy('avg_time')
            ->get();

        return $results->map(function ($r, $index) {
            return [
                'rank'        => $index + 1,
                'student'     => $r->student,
                'total'       => $r->total,
                'correct'     => $r->correct,
                'score'       => round(($r->correct / $r->total) * 100, 1) . '%',
                'avg_time'    => round($r->avg_time, 1) . 's',
            ];
        })->toArray();
    }
}
