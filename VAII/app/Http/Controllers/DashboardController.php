<?php

namespace App\Http\Controllers;

use App\Models\Country;
use Illuminate\Http\Request;
use App\Models\UploadedData;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Models\WeeklySnapshot;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{

    public function summary(Request $request)
    {
        $query = UploadedData::select('status', DB::raw('count(*) as total'))
            ->groupBy('status');

        if ($request->has('system')) {
            $query->where('source_type', $request->input('system'));
        }

        if ($request->has('country')) {
            $query->where('country', $request->input('country'));
        }

        return response()->json($query->get());
    }



    public function createdVsFinalized(Request $request)
    {
        $query = UploadedData::select(
            DB::raw('DATE(created) as created_date'),
            DB::raw('count(*) as created_count'),
            DB::raw('count(finalized) as finalized_count')
        )
            ->groupBy('created_date')
            ->orderBy('created_date');

        if ($request->has('system')) {
            $query->where('source_type', $request->input('system'));
        }

        if ($request->has('country')) {
            $query->where('country', $request->input('country'));
        }

        return response()->json($query->get());
    }


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
            ->whereYear('created', substr($month, 0, 4))
            ->whereMonth('created', substr($month, 5, 2))
            ->groupBy('created_date')
            ->orderBy('created_date', 'asc')
            ->get();

        return response()->json($data);
    }


    public function backlogTable(Request $request)
    {
        $month = $request->input('month', now()->format('Y-m'));

        $year = substr($month, 0, 4);
        $monthNum = substr($month, 5, 2);
        $startOfMonth = Carbon::createFromDate($year, $monthNum)->startOfMonth();
        $endOfMonth = Carbon::createFromDate($year, $monthNum)->endOfMonth();

        $lastRelevantCreated = UploadedData::whereNull('finalized')
            ->whereIn('status', ['Pending with CVMT team', 'Pending with AP Manager Approver'])
            ->whereBetween('created', [$startOfMonth->copy()->subMonths(12), $endOfMonth])
            ->orderByDesc('created')
            ->value('created');

        if (!$lastRelevantCreated) {
            return response()->json([]);
        }

        $referenceDate = Carbon::parse($lastRelevantCreated);
        $cutoffStart = $referenceDate->copy()->subDays(30);

        $backlog = UploadedData::select(
            'request',
            'created',
            'status',
            'country',
            DB::raw("DATEDIFF('" . $referenceDate->toDateString() . "', created) as backlog_days")
        )
            ->whereNull('finalized')
            ->whereIn('status', ['Pending with CVMT team', 'Pending with AP Manager Approver'])
            ->whereBetween('created', [$cutoffStart, $referenceDate])
            ->whereNotIn('status', ['Closed', 'Completed'])
            ->having('backlog_days', '>=', 4) // Zahrni len tie, ktoré sú staršie ako 4 dni
            ->orderBy('backlog_days', 'desc')
            ->get();

        return response()->json($backlog);
    }




    public function mapData()
    {
        $data = UploadedData::select('country', DB::raw('COUNT(*) as count'))
            ->whereNotNull('country')
            ->groupBy('country')
            ->get();

        foreach ($data as $item) {
            $countryInfo = Country::where('name', $item->country)->first();

            if ($countryInfo && !is_null($countryInfo->latitude) && !is_null($countryInfo->longitude)) {
                $item->latitude = (float) $countryInfo->latitude;
                $item->longitude = (float) $countryInfo->longitude;
                $item->region = $countryInfo->region;
            } else {
                $item->latitude = null;
                $item->longitude = null;
                $item->region = 'Unknown';
            }
        }

        return response()->json($data);
    }

    public function fetchCoordinates($country)
    {
        $url = "https://nominatim.openstreetmap.org/search";

        $response = Http::withHeaders([
            'User-Agent' => 'MyLaravelApp/1.0 (myemail@example.com)'
        ])->get($url, [
            'q' => $country,
            'format' => 'json',
            'limit' => 1
        ]);

        $data = $response->json();

        if (!empty($data) && isset($data[0]['lat'], $data[0]['lon'])) {
            return ['lat' => $data[0]['lat'], 'lon' => $data[0]['lon']];
        }

        return null;
    }




    public function filtersCountry()
    {
        $countries = UploadedData::select('country')
            ->distinct()
            ->pluck('country');

        $countriesWithRegions = Country::whereIn('name', $countries)->get(['name', 'region']);

        return response()->json([
            'systems' => UploadedData::select('source_type')->distinct()->pluck('source_type'),
            'countries' => $countriesWithRegions,
            'statuses' => UploadedData::select('status')->distinct()->pluck('status')
        ]);
    }


    public function getCountries()
    {
        $countries = Country::select('name', 'region')->get();
        return response()->json($countries);
    }


    public function getStats(Request $request)
    {
        $query = UploadedData::query();

        if ($request->has('region') && $request->region) {
            $countryNames = Country::where('region', $request->region)->pluck('name');
            $query->whereIn('country', $countryNames);
        }

        if ($request->has('countries') && is_array($request->countries)) {
            $query->whereIn('country', $request->countries);
        }

        return response()->json([
            'created' => $query->whereNotNull('created')->count(),
            'finalized' => $query->whereNotNull('finalized')->count(),
        ]);
    }





    public function snapshotForMonth(Request $request)
    {
        $month = $request->input('month', now()->format('Y-m'));

        $firstDayOfMonth = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $lastDayOfMonth = Carbon::createFromFormat('Y-m', $month)->endOfMonth();

        $snapshots = WeeklySnapshot::whereBetween('snapshot_date', [$firstDayOfMonth, $lastDayOfMonth])->get();

        if ($snapshots->isEmpty()) {
            return response()->json([
                'backlog' => 0,
                'backlog_in_days' => 0,
                'avg_processing_days' => 0,
                'on_time_percentage' => 0,
            ]);
        }

        return response()->json([
            'backlog' => $snapshots->avg('backlog'),
            'backlog_in_days' => $snapshots->avg('backlog_in_days'),
            'avg_processing_days' => round($snapshots->avg('avg_processing_days'), 2),
            'on_time_percentage' => round($snapshots->avg('on_time_percentage'), 2),
        ]);
    }

    public function regionSnapshotLatest()
    {
        $lastDate = UploadedData::where('source_type', 'MDG')->max('created');

        if (!$lastDate) {
            return response()->json([]);
        }

        $weekStart = \Carbon\Carbon::parse($lastDate)->startOfWeek();
        $weekEnd = $weekStart->copy()->endOfWeek();

        $cutoffStart = $weekStart->copy()->subDays(30);
        $cutoffEnd = $weekStart->copy()->subDay();

        $regions = ['EMEA', 'AMER', 'APAC'];
        $openStatuses = ['Pending with CVMT team', 'Pending with AP Manager Approver'];
        $results = [];

        foreach ($regions as $region) {
            $countries = Country::where('region', $region)->pluck('name');
            $data = UploadedData::where('source_type', 'MDG')
                ->whereIn('country', $countries)
                ->whereBetween('created', [$cutoffStart, $cutoffEnd])
                ->get();

            $backlog = $data->filter(fn($d) =>
                is_null($d->finalized) &&
                in_array($d->status, $openStatuses) &&
                \Carbon\Carbon::parse($d->created)->lt($weekStart)
            )->count();

            $finalized = UploadedData::where('source_type', 'MDG')
                ->whereIn('country', $countries)
                ->whereBetween('finalized', [$weekStart, $weekEnd])
                ->whereNotNull('created')
                ->whereNotNull('finalized')
                ->get();

            $finished = $finalized->count();
            $avgDays = round($finalized->avg(fn($d) =>
            \Carbon\Carbon::parse($d->created)->diffInDays($d->finalized)
            ) ?? 0, 2);

            $onTime = $finalized->filter(fn($d) =>
                \Carbon\Carbon::parse($d->created)->diffInDays($d->finalized) <= 4
            )->count();

            $onTimePercent = $finished > 0 ? round(($onTime / $finished) * 100, 2) : 0;

            $lastMonthFinalized = UploadedData::where('source_type', 'MDG')
                ->whereIn('country', $countries)
                ->whereBetween('finalized', [now()->subDays(30), now()])
                ->whereNotNull('created')
                ->get();

            $dailyAvg = $lastMonthFinalized->count() > 0
                ? $lastMonthFinalized->count() / 21
                : 0;

            $backlogInDays = $dailyAvg > 0 ? ceil($backlog / $dailyAvg) : 0;

            $results[] = [
                'region' => $region,
                'backlog' => $backlog,
                'backlog_in_days' => $backlogInDays,
                'avg_processing_days' => $avgDays,
                'on_time_percentage' => $onTimePercent
            ];
        }

        return response()->json($results);
    }




    public function bestCountriesLatest()
    {
        $lastDate = UploadedData::max('created');

        if (!$lastDate) {
            return response()->json([]);
        }

        $weekStart = \Carbon\Carbon::parse($lastDate)->startOfWeek();
        $weekEnd = $weekStart->copy()->endOfWeek();
        $cutoffStart = $weekStart->copy()->subDays(30);
        $cutoffEnd = $weekStart->copy()->subDay();

        $countries = UploadedData::whereBetween('created', [$cutoffStart, $cutoffEnd])
            ->pluck('country')
            ->unique()
            ->filter();

        $openStatuses = ['Pending with CVMT team', 'Pending with AP Manager Approver'];
        $results = [];

        foreach ($countries as $country) {
            $data = UploadedData::where('country', $country)
                ->whereBetween('created', [$cutoffStart, $cutoffEnd])
                ->get();


            $backlog = $data->filter(fn($d) =>
                is_null($d->finalized) &&
                in_array($d->status, $openStatuses) &&
                \Carbon\Carbon::parse($d->created)->lt($weekStart)
            )->count();

            $finalized = UploadedData::where('country', $country)
                ->whereBetween('finalized', [$weekStart, $weekEnd])
                ->whereNotNull('created')
                ->whereNotNull('finalized')
                ->get();

            $avgDays = round($finalized->avg(fn($d) =>
            \Carbon\Carbon::parse($d->created)->diffInDays($d->finalized)
            ) ?? 0, 2);

            $onTime = $finalized->filter(fn($d) =>
                \Carbon\Carbon::parse($d->created)->diffInDays($d->finalized) <= 4
            )->count();

            $onTimePercent = $finalized->count() > 0 ? round(($onTime / $finalized->count()) * 100, 2) : 0;

            $lastMonthFinalized = UploadedData::where('country', $country)
                ->whereBetween('finalized', [now()->subDays(30), now()])
                ->whereNotNull('created')
                ->get();

            $dailyAvg = $lastMonthFinalized->count() > 0
                ? $lastMonthFinalized->count() / 21
                : 0;

            $backlogInDays = $dailyAvg > 0 ? ceil($backlog / $dailyAvg) : 0;

            $results[] = [
                'country' => $country,
                'backlog' => $backlog,
                'backlog_in_days' => $backlogInDays,
                'avg_processing_days' => $avgDays,
                'on_time_percentage' => $onTimePercent,
            ];
        }
        return response()->json($results);
    }
}
