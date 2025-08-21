<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Earnings for a specific doctor grouped by month.
     */
    public function doctor($doctorId)
    {
        $monthly = Appointment::where('appointments.doctor_id', $doctorId)
            ->where('appointments.status', 'accepted')
            ->join('doctors', 'appointments.doctor_id', '=', 'doctors.id')
            ->selectRaw(
                "DATE_FORMAT(STR_TO_DATE(appointments.date, '%Y-%m-%d'), '%Y-%m') as month, " .
                'SUM(doctors.price) as earnings'
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $total = $monthly->sum('earnings');

        return response()->json([
            'totalEarnings' => (float) $total,
            'monthlyEarnings' => $monthly->map(function ($row) {
                return [
                    'month' => $row->month,
                    'earnings' => (float) $row->earnings,
                ];
            }),
        ]);
    }

    /**
     * Revenue across all doctors grouped by month for admin.
     */
    public function admin()
    {
        $monthly = Appointment::where('appointments.status', 'accepted')
            ->join('doctors', 'appointments.doctor_id', '=', 'doctors.id')
            ->selectRaw(
                "DATE_FORMAT(STR_TO_DATE(appointments.date, '%Y-%m-%d'), '%Y-%m') as month, " .
                'SUM(doctors.price) as revenue'
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $total = $monthly->sum('revenue');

        return response()->json([
            'totalRevenue' => (float) $total,
            'monthlyRevenue' => $monthly->map(function ($row) {
                return [
                    'month' => $row->month,
                    'revenue' => (float) $row->revenue,
                ];
            }),
        ]);
    }
}
