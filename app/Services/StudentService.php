<?php

namespace App\Services;

use App\Models\User;
use App\Models\Student;
use App\Models\ParentModel;
use App\Models\StudentLevel;
use App\Models\TrainingSession;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class StudentService
{
    // ===================== إضافة طالب جديد مع ولي أمره =====================
    public function addStudent(array $data): array
    {
        return DB::transaction(function () use ($data) {

            // 1. البحث عن ولي الأمر بالرقم القومي
            $parentUser = User::where('national_id', $data['parent_national_id'])
                ->where('role', 'parent')
                ->first();

            if ($parentUser) {
                // ولي الأمر موجود → نجيب بياناته تلقائياً
                $parent = ParentModel::where('user_id', $parentUser->id)->first();
            } else {
                // ولي الأمر مش موجود → نضيفه
                $parentUser = User::create([
                    'name'        => $data['parent_name'],
                    'national_id' => $data['parent_national_id'],
                    'phone'       => $data['parent_phone'],
                    'password'    => Hash::make($data['parent_national_id']), // الباسورد = الرقم القومي
                    'role'        => 'parent',
                ]);

                $parent = ParentModel::create([
                    'user_id'     => $parentUser->id,
                    'name'        => $data['parent_name'],
                    'national_id' => $data['parent_national_id'],
                    'phone'       => $data['parent_phone'],
                ]);
            }

            // 2. التحقق إن الطالب مش موجود
            if (User::where('national_id', $data['student_national_id'])->exists()) {
                throw new \Exception('الطالب موجود بالفعل', 409);
            }

            // 3. إنشاء حساب الطالب
            $studentUser = User::create([
                'name'        => $data['student_name'],
                'national_id' => $data['student_national_id'],
                'phone'       => $data['student_phone'] ?? $data['parent_phone'],
                'password'    => Hash::make($data['student_national_id']),
                'role'        => 'student',
            ]);

            // 4. إنشاء بروفايل الطالب
            $student = Student::create([
                'user_id'     => $studentUser->id,
                'parent_id'   => $parent->id,
                'name'        => $data['student_name'],
                'national_id' => $data['student_national_id'],
                'level'       => 1,
            ]);

            return [
                'student' => $student->load('parent'),
                'parent'  => $parent,
            ];
        });
    }

    // ===================== جلب كل الطلاب =====================
    public function getAllStudents(): \Illuminate\Database\Eloquent\Collection
    {
        return Student::with(['parent', 'levels'])->get();
    }

    // ===================== جلب بيانات طالب واحد =====================
    public function getStudent(int $studentId): Student
    {
        $student = Student::with(['parent', 'levels', 'trainingSessions' => function ($q) {
            $q->latest()->take(20);
        }])->find($studentId);

        if (!$student) throw new \Exception('الطالب غير موجود', 404);

        return $student;
    }

    // ===================== تقرير أداء الطالب =====================
    public function getStudentReport(int $studentId): array
    {
        $student = Student::with('levels')->findOrFail($studentId);

        $totalSessions  = TrainingSession::where('student_id', $studentId)->count();
        $correctAnswers = TrainingSession::where('student_id', $studentId)->where('is_correct', true)->count();
        $wrongAnswers   = $totalSessions - $correctAnswers;

        $byOperation = TrainingSession::where('student_id', $studentId)
            ->select('operation_type',
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN is_correct = 1 THEN 1 ELSE 0 END) as correct')
            )
            ->groupBy('operation_type')
            ->get();

        return [
            'student'          => $student,
            'total_sessions'   => $totalSessions,
            'correct_answers'  => $correctAnswers,
            'wrong_answers'    => $wrongAnswers,
            'success_rate'     => $totalSessions > 0 ? round(($correctAnswers / $totalSessions) * 100, 2) : 0,
            'by_operation'     => $byOperation,
            'levels'           => $student->levels,
        ];
    }

    // ===================== تحديث مستوى الطالب =====================
    public function updateStudentLevel(int $studentId, int $level): Student
    {
        $student = Student::findOrFail($studentId);
        $student->update(['level' => $level]);
        return $student->fresh();
    }
}
