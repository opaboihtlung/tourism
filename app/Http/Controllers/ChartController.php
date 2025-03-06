<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

// get total number of visitors of portal
class ChartController extends Controller
{
    public function bookingsMonthly(Request $request)
    {
        $year = $request->query('year', date('Y')); // Get the year from the query parameters or use the current year if not provided

        // Query to get the total bookings month-wise for the specified year
        $bookings = DB::table('user_bookings')
            ->select(DB::raw('MONTH(created_at) as month'), DB::raw('COUNT(DISTINCT order_id) as total_bookings'))
            ->where('status', 'Confirmed')
            ->whereYear('created_at', $year)
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->get();

        Log::info($bookings);

        // Return the result as JSON
        return response()->json(['data' => $bookings]);
    }

    public function getUsersMonthly(Request $request)
    {
        $year = $request->query('year', date('Y')); // Get the year from the query parameters or use the current year if not provided

        // Query to get the total number of users month-wise for the specified year
        $users = DB::table('users')
            ->select(DB::raw('MONTH(created_at) as month'), DB::raw('COUNT(DISTINCT id) as total_users'))
            ->whereYear('created_at', $year)
            ->where('role', 'citizen')
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->get();


        // Return the result as JSON
        return response()->json(['data' => $users]);
    }
}
