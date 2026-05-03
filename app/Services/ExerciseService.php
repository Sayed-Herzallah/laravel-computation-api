<?php

namespace App\Services;

use App\Models\Exercise;
use App\Models\AppSetting;
use App\Models\TrainingSession;
use App\Models\StudentLevel;

class ExerciseService
{
    // ===================== توليد سؤال جديد =====================
    public function generateQuestion(string $operationType, int $level, int $rowsCount): array
    {
        $range = $this->getNumberRange($level);

        if ($operationType === 'division') {
            return $this->generateDivisionQuestion($level, $rowsCount, $range);
        }

        if ($operationType === 'mixed') {
            return $this->generateMixedQuestion($level, $rowsCount, $range);
        }

        $num1 = rand($range['min'], $range['max']);
        $num2 = rand($range['min'], $range['max']);

        // في الطرح: الرقم الأول لازم يكون أكبر عشان الناتج يكون موجب
        if ($operationType === 'subtraction' && $num1 < $num2) {
            [$num1, $num2] = [$num2, $num1];
        }

        $result = Exercise::calculateResult($operationType, $num1, $num2);

        return [
            'operation_type' => $operationType,
            'level'          => $level,
            'rows_count'     => $rowsCount,
            'num1'           => $num1,
            'num2'           => $num2,
            'num3'           => null,
            'correct_result' => $result,
        ];
    }

    // ===================== توليد أسئلة متعددة =====================
    public function generateMultipleQuestions(string $operationType, int $level, int $rowsCount, int $count): array
    {
        $questions = [];
        for ($i = 0; $i < $count; $i++) {
            $questions[] = $this->generateQuestion($operationType, $level, $rowsCount);
        }
        return $questions;
    }

    // ===================== حفظ سؤال التدريب في الـ DB =====================
    public function saveTrainingQuestion(int $studentId, array $question): TrainingSession
    {
        return TrainingSession::create([
            'student_id'      => $studentId,
            'operation_type'  => $question['operation_type'],
            'level'           => $question['level'],
            'questions_count' => 1,
            'rows_count'      => $question['rows_count'],
            'num1'            => $question['num1'],
            'num2'            => $question['num2'],
            'num3'            => $question['num3'] ?? null,
            'correct_result'  => $question['correct_result'],
            'started_at'      => now(),
        ]);
    }

    // ===================== تصحيح إجابة الطالب =====================
    public function submitAnswer(int $sessionId, int $studentId, int $answer): array
    {
        $session = TrainingSession::where('id', $sessionId)
            ->where('student_id', $studentId)
            ->whereNull('student_answer')
            ->firstOrFail();

        $isCorrect = ($answer == $session->correct_result);

        // في القسمة: لو الناتج كسر = خطأ تلقائي
        if ($session->operation_type === 'division') {
            $isCorrect = ($answer == $session->correct_result);
        }

        $session->update([
            'student_answer' => $answer,
            'is_correct'     => $isCorrect,
            'ended_at'       => now(),
        ]);

        // تحديث إحصائيات الطالب
        StudentLevel::updateStats(
            $studentId,
            $session->operation_type,
            $session->level,
            $isCorrect
        );

        return [
            'is_correct'     => $isCorrect,
            'correct_result' => $session->correct_result,
            'student_answer' => $answer,
            'message'        => $isCorrect ? '✅ إجابة صحيحة!' : '❌ إجابة خاطئة، الناتج الصحيح: ' . $session->correct_result,
        ];
    }

    // ===================== توليد سؤال قسمة (بدون كسور) =====================
    private function generateDivisionQuestion(int $level, int $rowsCount, array $range): array
    {
        // نولد num2 أولاً ثم نضرب في num1 عشان الناتج يكون صحيح بدون كسر
        $attempts = 0;
        do {
            $num2   = rand(max(1, $range['min']), $range['max']);
            $result = rand($range['min'], $range['max']);
            $num1   = $num2 * $result;
            $attempts++;
        } while ($num1 > $range['max'] * 10 && $attempts < 20);

        return [
            'operation_type' => 'division',
            'level'          => $level,
            'rows_count'     => $rowsCount,
            'num1'           => $num1,
            'num2'           => $num2,
            'num3'           => null,
            'correct_result' => $result,
        ];
    }

    // ===================== توليد سؤال ميكس (جمع + طرح) =====================
    private function generateMixedQuestion(int $level, int $rowsCount, array $range): array
    {
        $num1 = rand($range['min'], $range['max']);
        $num2 = rand($range['min'], $range['max']);
        $num3 = rand($range['min'], $range['max']);

        // الناتج النهائي لازم يكون موجب
        $result = $num1 + $num2 - $num3;
        if ($result < 0) {
            // نعدل num3 عشان الناتج يبقى موجب
            $num3   = rand(0, $num1 + $num2);
            $result = $num1 + $num2 - $num3;
        }

        return [
            'operation_type' => 'mixed',
            'level'          => $level,
            'rows_count'     => $rowsCount,
            'num1'           => $num1,
            'num2'           => $num2,
            'num3'           => $num3,
            'correct_result' => $result,
        ];
    }

    // ===================== نطاق الأرقام حسب المستوى =====================
    private function getNumberRange(int $level): array
    {
        return match($level) {
            1 => ['min' => 1,  'max' => 10],
            2 => ['min' => 1,  'max' => 50],
            3 => ['min' => 1,  'max' => 100],
            default => ['min' => 1, 'max' => 10],
        };
    }
}
