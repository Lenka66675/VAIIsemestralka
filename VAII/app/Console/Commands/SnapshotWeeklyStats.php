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
        $weekStart = $this->option('week_start')
            ? Carbon::parse($this->option('week_start'))->startOfWeek()
            : now()->startOfWeek();

        $weekEnd = $weekStart->copy()->endOfWeek();
        $this->info("📆 Vypočítavam štatistiky od {$weekStart->toDateString()} do {$weekEnd->toDateString()}");

        // Dáta vytvorené 1 až 30 dní pred pondelkom
        $cutoffStart = $weekStart->copy()->subDays(30);
        $cutoffEnd = $weekStart->copy()->subDay();

        $data = UploadedData::whereBetween('created', [$cutoffStart, $cutoffEnd])->get();

        $openStatuses = [
            'Pending with CVMT team',
            'Pending with AP Manager Approver',
        ];

        // 🔴 1) Backlog = otvorené, nefinalizované, vytvorené pred pondelkom
        $backlog = $data->filter(fn($d) =>
            is_null($d->finalized) &&
            in_array($d->status, $openStatuses) &&
            Carbon::parse($d->created)->lt($weekStart)
        )->count();

        $this->line("➡️  Backlog: $backlog");

        // 🔁 2) Finalizované tento týždeň
        $finalized = UploadedData::whereBetween('finalized', [$weekStart, $weekEnd])
            ->whereNotNull('created')
            ->whereNotNull('finalized')
            ->get();

        $finished = $finalized->count();
        $this->line("✅ Finalizované požiadavky tento týždeň: $finished");

        // 📊 3) Priemerný počet dní spracovania
        $avgDays = $finalized->avg(fn($d) =>
        Carbon::parse($d->created)->diffInDays($d->finalized)
        );
        $avgDays = round($avgDays ?? 0, 2);

        // ⏱️ 4) Percento spracovaných do 4 dní
        $onTime = $finalized->filter(fn($d) =>
            Carbon::parse($d->created)->diffInDays($d->finalized) <= 4
        )->count();

        $onTimePercentage = $finished > 0 ? round(($onTime / $finished) * 100, 2) : 0;

        // 🧮 (nové) Backlog in days – používame 30-dňový priemer finalizácií
        $this->line("🕒 Počítam backlog_in_days...");
        $this->line("📅 Rozsah finalizácií: " . now()->subDays(30)->toDateString() . " - " . now()->toDateString());

        $lastMonthFinalized = UploadedData::whereBetween('finalized', [now()->subDays(30), now()])
            ->whereNotNull('created')
            ->get();

        $finalizedCount = $lastMonthFinalized->count();
        $this->line("✅ Finalizované za 30 dní: $finalizedCount");

        $finalizedCount = $lastMonthFinalized->count();
        $daysCount = 21; // pracovných dní za 30 dní
        $dailyAvg = $finalizedCount > 0 ? $finalizedCount / $daysCount : 0;

        $this->line("📈 Priemerný počet vyriešených denne: $dailyAvg");

        $backlogInDays = $dailyAvg > 0 ? ceil($backlog / $dailyAvg) : 0;


        // 💾 Uloženie
        WeeklySnapshot::updateOrCreate(
            ['snapshot_date' => $weekStart->toDateString()],
            [
                'backlog' => $backlog,
                'backlog_in_days' => $backlogInDays,
                'avg_processing_days' => $avgDays,
                'on_time_percentage' => $onTimePercentage,
            ]
        );

        $this->info("📦 Úspešne uložené do backlog_snapshots!");
    }
}
