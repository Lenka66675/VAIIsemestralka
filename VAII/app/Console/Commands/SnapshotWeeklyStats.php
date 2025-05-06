<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UploadedData;
use App\Models\WeeklySnapshot;
use Carbon\Carbon;

class SnapshotWeeklyStats extends Command
{
    protected $signature = 'snapshot:weekly {--week_start=}';
    protected $description = 'Uloží týždenný prehľad požiadaviek do tabuľky backlog_snapshots';

    public function handle()
    {
        $snapshotWeekStart = $this->option('week_start')
            ? Carbon::parse($this->option('week_start'))->startOfWeek()
            : now()->startOfWeek();

        $snapshotWeekEnd = $snapshotWeekStart->copy()->endOfWeek();

        $source = 'MDG';

        $dataWeekStart = $snapshotWeekStart->copy();
        $maxLookbackWeeks = 12;
        $weeksTried = 0;

        while ($weeksTried < $maxLookbackWeeks) {
            $dataWeekEnd = $dataWeekStart->copy()->endOfWeek();

            $hasData = UploadedData::where('source_type', $source)
                ->whereBetween('created', [$dataWeekStart, $dataWeekEnd])
                ->exists();

            if ($hasData) break;

            $dataWeekStart->subWeek();
            $weeksTried++;
        }

        if ($weeksTried === $maxLookbackWeeks) {
            $this->error("Nenašiel som žiadne použiteľné dáta v posledných 12 týždňoch.");
            return;
        }

        $dataWeekEnd = $dataWeekStart->copy()->endOfWeek();
        $this->info("Vytváram snapshot pre týždeň {$snapshotWeekStart->toDateString()} (dáta z týždňa {$dataWeekStart->toDateString()})");

        $cutoffStart = $dataWeekStart->copy()->subDays(30);
        $cutoffEnd = $dataWeekStart->copy()->subDay();

        $data = UploadedData::where('source_type', $source)
            ->whereBetween('created', [$cutoffStart, $cutoffEnd])
            ->get();

        $openStatuses = [
            'Pending with CVMT team',
            'Pending with AP Manager Approver',
        ];

        $backlogData = $data->filter(fn($d) =>
            is_null($d->finalized) &&
            in_array($d->status, $openStatuses) &&
            Carbon::parse($d->created)->lt($dataWeekStart)
        );

        $backlog = $backlogData->count();
        $this->line("Celkový backlog: $backlog");

        $finalized = UploadedData::where('source_type', $source)
            ->whereBetween('finalized', [$dataWeekStart, $dataWeekEnd])
            ->whereNotNull('created')
            ->whereNotNull('finalized')
            ->get();

        $daysList = [];

        foreach ($finalized as $item) {
            $created = Carbon::parse($item->created);
            $finalizedAt = Carbon::parse($item->finalized);
            $diff = $created->diffInDays($finalizedAt, false);

            if ($diff >= 0) {
                $daysList[] = $diff;
            }
        }

        $avgDays = count($daysList) > 0 ? round(array_sum($daysList) / count($daysList), 2) : 0;
        $onTime = collect($daysList)->filter(fn($d) => $d <= 4)->count();
        $onTimePercentage = count($daysList) > 0 ? round(($onTime / count($daysList)) * 100, 2) : 0;

        $lastMonthFinalized = UploadedData::where('source_type', $source)
            ->where('finalized', '<=', $dataWeekEnd)
            ->whereNotNull('created')
            ->whereNotNull('finalized')
            ->orderByDesc('finalized')
            ->take(1000)
            ->get();

        $validFinalized = $lastMonthFinalized->filter(function ($item) use ($dataWeekStart) {
            return Carbon::parse($item->finalized)->gte($dataWeekStart->copy()->subDays(30));
        });

        if ($validFinalized->isEmpty()) {
            $this->warn("Žiadne nové dáta – používam posledné známe finalizované požiadavky.");
            $validFinalized = $lastMonthFinalized;
        }

        $finalizedCount = $validFinalized->count();
        $daysCount = 21;
        $dailyAvg = $finalizedCount > 0 ? $finalizedCount / $daysCount : 0;
        $backlogInDays = $dailyAvg > 0 ? ceil($backlog / $dailyAvg) : 0;

        WeeklySnapshot::updateOrCreate(
            ['snapshot_date' => $snapshotWeekStart->toDateString()],
            [
                'backlog' => $backlog,
                'backlog_in_days' => $backlogInDays,
                'avg_processing_days' => $avgDays,
                'on_time_percentage' => $onTimePercentage,
            ]
        );

        $this->info("Snapshot pre {$snapshotWeekStart->toDateString()} úspešne uložený.");
    }
}
