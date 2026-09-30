<?php

namespace App\Console\Commands\Concerns;

use Illuminate\Support\Facades\Storage;

/** Dipakai db:backup & backup:product-images - hapus file backup lebih lama dari N hari. */
trait PrunesOldBackups
{
    protected function pruneOldBackups(string $disk, string $folder, int $keepDays): void
    {
        if ($keepDays <= 0) {
            return;
        }

        $cutoff = now()->subDays($keepDays)->timestamp;

        foreach (Storage::disk($disk)->files($folder) as $file) {
            try {
                if (Storage::disk($disk)->lastModified($file) < $cutoff) {
                    Storage::disk($disk)->delete($file);
                    $this->line('  dihapus (lebih dari '.$keepDays.' hari): '.$file);
                }
            } catch (\Throwable $e) {
                // Abaikan file yang gagal dibaca metadatanya.
            }
        }
    }
}
