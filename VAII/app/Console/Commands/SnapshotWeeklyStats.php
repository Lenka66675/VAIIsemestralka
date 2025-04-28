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

        $source = 'MDG';

        // ⏪ Dáta pre backlog
        $cutoffStart = $weekStart->copy()->subDays(30);
        $cutoffEnd = $weekStart->copy()->subDay();

        $data = UploadedData::where('source_type', $source)
            ->whereBetween('created', [$cutoffStart, $cutoffEnd])
            ->get();

        $openStatuses = [
            'Pending with CVMT team',
            'Pending with AP Manager Approver',
        ];

        $backlogData = $data->filter(fn($d) =>
            is_null($d->finalized) &&
            $d->source_type === 'MDG' &&
            in_array($d->status, $openStatuses) &&
            Carbon::parse($d->created)->lt($weekStart)
        );

        $this->line("📋 Požiadavky zahrnuté do backlogu:");
        foreach ($backlogData as $req) {
            $this->line("➡️ Request: {$req->request}, Status: {$req->status}, Created: {$req->created}");
        }

        $backlog = $backlogData->count();
        $this->line("🧮 Celkový backlog: $backlog");

        // ✅ Finalizované požiadavky tento týždeň
        $finalized = UploadedData::where('source_type', $source)
            ->whereBetween('finalized', [$weekStart, $weekEnd])
            ->whereNotNull('created')
            ->whereNotNull('finalized')
            ->get();

        $finished = $finalized->count();
        $this->line("✅ Finalizované požiadavky tento týždeň (iba MDG): $finished");

        // 📊 Priemerný počet dní spracovania
        $this->line("🧮 Výpočet priemerného počtu dní spracovania:");
        $daysList = [];

        foreach ($finalized as $item) {
            $created = Carbon::parse($item->created);
            $finalizedAt = Carbon::parse($item->finalized);
            $diff = $created->diffInDays($finalizedAt, false); // false = môže byť aj záporné

            if ($diff < 0) {
                $this->line("⚠️ Záporný rozdiel – preskakujem. Request: {$item->request}, Created: {$created}, Finalized: {$finalizedAt}");
                continue;
            }

            $this->line("📄 Request: {$item->request} | Created: {$created->toDateString()} | Finalized: {$finalizedAt->toDateString()} | Days: $diff");
            $daysList[] = $diff;
        }

        $avgDays = count($daysList) > 0 ? round(array_sum($daysList) / count($daysList), 2) : 0;
        $this->line("📊 Priemerný čas spracovania: {$avgDays} dní");

        // ⏱️ Percento vybavených do 4 dní
        $onTime = collect($daysList)->filter(fn($d) => $d <= 4)->count();
        $onTimePercentage = count($daysList) > 0 ? round(($onTime / count($daysList)) * 100, 2) : 0;
        $this->line("📌 % dokončených do 4 dní: {$onTimePercentage}%");

        // 📈 Backlog v dňoch (len MDG)
        $this->line("🕒 Počítam backlog_in_days pre MDG...");

        $lastMonthFinalized = UploadedData::where('source_type', $source)
            ->whereBetween('finalized', [$weekStart->copy()->subDays(30), $weekEnd])
            ->whereNotNull('created')
            ->whereNotNull('finalized')
            ->get();

        $finalizedCount = $lastMonthFinalized->count();
        $daysCount = 21; // odhad pracovných dní

        $dailyAvg = $finalizedCount > 0 ? $finalizedCount / $daysCount : 0;
        $this->line("📈 Priemerný počet vyriešených denne: $dailyAvg");

        $backlogInDays = $dailyAvg > 0 ? ceil($backlog / $dailyAvg) : 0;
        $this->line("📌 Odhadovaný backlog v dňoch: {$backlogInDays}");

        // 💾 Uloženie do snapshots
        WeeklySnapshot::updateOrCreate(
            ['snapshot_date' => $weekStart->toDateString()],
            [
                'backlog' => $backlog,
                'backlog_in_days' => $backlogInDays,
                'avg_processing_days' => $avgDays,
                'on_time_percentage' => $onTimePercentage,
            ]
        );

        $this->info("✅ Snapshot úspešne uložený!");
    }
}
