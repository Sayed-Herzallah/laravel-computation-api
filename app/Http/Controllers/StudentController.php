<?php

namespace App\Http\Controllers;

use App\Services\StudentService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class StudentController extends Controller
{
    use ApiResponse;

    public function __construct(protected StudentService $studentService) {}

    // GET /api/students
    public function index(): JsonResponse
    {
        try {
            $students = $this->studentService->getAllStudents();
            return $this->success($students, 'تم جلب الطلاب');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    // POST /api/students
    public function store(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'student_name'        => 'required|string|max:100',
            'student_national_id' => 'required|string',
            'student_phone'       => 'nullable|string',
            'parent_name'         => 'required|string|max:100',
            'parent_national_id'  => 'required|string',
            'parent_phone'        => 'required|string',
        ]);

        if ($v->fails()) return $this->error('خطأ في البيانات', 422, $v->errors());

        try {
            $result = $this->studentService->addStudent($request->all());
            return $this->success($result, 'تم إضافة الطالب بنجاح', 201);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), $e->getCode() ?: 400);
        }
    }

    // GET /api/students/{id}
    public function show(int $id): JsonResponse
    {
        try {
            $student = $this->studentService->getStudent($id);
            return $this->success($student, 'تم جلب بيانات الطالب');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), $e->getCode() ?: 404);
        }
    }

    // GET /api/students/{id}/report
    public function report(int $id): JsonResponse
    {
        try {
            $report = $this->studentService->getStudentReport($id);
            return $this->success($report, 'تقرير أداء الطالب');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    // PUT /api/students/{id}/level
    public function updateLevel(Request $request, int $id): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'level' => 'required|integer|min:1|max:3',
        ]);

        if ($v->fails()) return $this->error('خطأ في البيانات', 422, $v->errors());

        try {
            $student = $this->studentService->updateStudentLevel($id, $request->level);
            return $this->success($student, 'تم تحديث مستوى الطالب');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }
}
