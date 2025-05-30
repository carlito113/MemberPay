<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentAuthController extends Controller
{
    public function dashboard()
    {
        $student = Auth::guard('student')->user(); // Get logged-in student
        return view('student.dashboard', compact('student'));
    }

    public function viewCardOne()
    {
        $student = auth()->user();
    
        $yearLevelOrgs = ['FCO', 'SCO', 'JCO', 'SENCO'];
    
        // Get all valid semester IDs where admin matches, and admin is NOT a year-level org
        $validSemesterIds = \DB::table('semester_student')
            ->join('semesters', 'semester_student.semester_id', '=', 'semesters.id')
            ->join('admins', 'semesters.admin_id', '=', 'admins.id')
            ->where('semester_student.student_id', $student->id)
            ->whereColumn('semester_student.admin_id', 'semesters.admin_id')
            ->whereNotIn('admins.username', $yearLevelOrgs) // Exclude year-level orgs
            ->pluck('semester_student.semester_id')
            ->toArray();
    
        // Fetch semesters excluding year-level orgs
        $semesters = $student->semesters()
            ->whereIn('semester_id', $validSemesterIds)
            ->wherePivot('student_id', $student->id)
            ->with('admin')
            ->get();
    
        // Show latest course org (if available)
        $organizationName = $semesters->last()?->admin?->username ?? 'Unknown Organization';
    
        $adminMap = \App\Models\Admin::pluck('name', 'id');

    
        return view('student.organizationcard', compact('student', 'semesters', 'organizationName', 'adminMap'));
    }
    


  public function viewCardTwo()
{
    $student = auth()->user();

    $yearToOrgMap = [
        1 => 'FCO',
        2 => 'SCO',
        3 => 'JCO',
        4 => 'SENCO',
    ];

    $expectedOrg = $yearToOrgMap[$student->year_level] ?? null;

    if (!$expectedOrg) {
        abort(404, 'Organization not found for this year level.');
    }

    // Fix: ensure semester admin is the same as pivot admin, and match by username
    $validSemesterIds = \DB::table('semester_student')
        ->join('semesters', 'semester_student.semester_id', '=', 'semesters.id')
        ->join('admins', 'semesters.admin_id', '=', 'admins.id')
        ->where('semester_student.student_id', $student->id)
        ->where('admins.username', $expectedOrg)
        ->whereColumn('semester_student.admin_id', 'semesters.admin_id') // Ensure match
        ->pluck('semester_student.semester_id')
        ->toArray();

    $semesters = $student->semesters()
        ->whereIn('semester_id', $validSemesterIds)
        ->with('admin')
        ->get();

    $organizationName = $semesters->first()?->admin?->username ?? $expectedOrg;

    $adminMap = \App\Models\Admin::pluck('name', 'id');
//     dd([
//     'expectedOrg' => $expectedOrg,
//     'validSemesterIds' => $validSemesterIds,
//     'semesters' => $semesters,
// ]);


    return view('student.yearorganizationcard', compact('student', 'semesters', 'organizationName', 'adminMap'));
}

    

    public function studentProfile()
    {
        $student = Auth::guard('student')->user(); // Get logged-in student
        return view('student.profile', compact('student'));
    }

    public function showPayments()
    {
    $student = Auth::user(); // or fetch by param
    
    $semesters = $student->semesters()
        ->withPivot('payment_status', 'admin_id')
        ->with('students') // just to ensure relationships are available
        ->get();

    return view('student.payment', compact('student', 'semesters'));
    }


}

