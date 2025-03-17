<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UploadedData;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    // 1️⃣ Počet požiadaviek podľa statusu (pre koláčový graf)
    public function summary(Request $request)
    {
        $query = UploadedData::select('status', DB::raw('count(*) as total'))
            ->groupBy('status');

        // Ak je vybraný systém, pridáme podmienku
        if ($request->has('system')) {
            $query->where('source_type', $request->input('system'));
        }

        // Ak je vybraná krajina, pridáme podmienku
        if ($request->has('country')) {
            $query->where('country', $request->input('country'));
        }

        return response()->json($query->get());
    }


    // 2️⃣ Počet vytvorených vs. uzavretých žiadostí (pre stĺpcový graf)
    public function createdVsFinalized(Request $request)
    {
        $query = UploadedData::select(
            DB::raw('DATE(created) as created_date'),
            DB::raw('count(*) as created_count'),
            DB::raw('count(finalized) as finalized_count')
        )

            ->groupBy('created_date')
            ->orderBy('created_date');

        // Ak je vybraný systém, pridáme podmienku
        if ($request->has('system')) {
            $query->where('source_type', $request->input('system'));
        }

        // Ak je vybraná krajina, pridáme podmienku
        if ($request->has('country')) {
            $query->where('country', $request->input('country'));
        }

        return response()->json($query->get());
    }

    // 3️⃣ Možnosti filtrovania (systémy, krajiny, statusy)
    public function filters()
    {
        return response()->json([
            'systems' => UploadedData::select('source_type')->distinct()->pluck('source_type'),
            'countries' => UploadedData::select('country')->distinct()->pluck('country'),
            'statuses' => UploadedData::select('status')->distinct()->pluck('status')
        ]);
    }

    public function monthlySummary(Request $request)
    {
        $month = $request->input('month', now()->format('Y-m'));

        $data = UploadedData::select(
            DB::raw('DATE(created) as created_date'),
            DB::raw('COUNT(*) as created_count'),
            DB::raw('SUM(CASE WHEN DATEDIFF(NOW(), created) > 4 AND status NOT IN ("Closed", "Completed") THEN 1 ELSE 0 END) as backlog_count')
        )
            ->whereYear('created', substr($month, 0, 4)) // Vyberie rok z YYYY-MM
            ->whereMonth('created', substr($month, 5, 2)) // Vyberie mesiac z YYYY-MM
            ->groupBy('created_date')
            ->orderBy('created_date', 'asc')
            ->get();

        return response()->json($data);
    }

// 📋 2️⃣ Tabuľka backlog požiadaviek
    public function backlogTable(Request $request)
    {
        $month = $request->input('month', now()->format('Y-m'));

        $backlog = UploadedData::select(
            'request',
            'created',
            'status',
            'country',
            DB::raw('IFNULL(DATEDIFF(NOW(), created), 0) as backlog_days')
        )
            ->whereYear('created', substr($month, 0, 4))
            ->whereMonth('created', substr($month, 5, 2))
            ->whereNotIn('status', ['Closed', 'Completed'])
            ->having('backlog_days', '>', 4)
            ->orderBy('backlog_days', 'desc')
            ->get();

        return response()->json($backlog);
    }



}
