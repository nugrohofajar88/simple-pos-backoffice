<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\PrunesOldBackups;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

/**
 * Foto produk (storage/app/public/products) TIDAK ikut ter-backup oleh db:backup
 * (itu cuma dump database) - ini pelengkapnya, disk & pola retensi sama.
 */
class BackupProductImages extends Command
{
    use PrunesOldBackups;

    protected $signature = 'backup:product-images {--disk=gdrive} {--keep=14}';

    protected $description = 'Zip foto produk (storage/app/public/products) lalu unggah ke storage off-site (default Google Drive).';

    public function handle(): int
    {
        $sourceDir = storage_path('app/public/products');

        if (! is_dir($sourceDir) || count(glob($sourceDir.'/*') ?: []) === 0) {
            $this->info('Belum ada foto produk, tidak ada yang perlu di-backup.');

            return self::SUCCESS;
        }

        $filename = 'products/products-'.now()->format('Y-m-d_His').'.zip';
        $tmp = storage_path('app/'.basename($filename));

        // 1) Zip semua file di folder produk (pure PHP, ZipArchive - gak butuh shell zip).
        $zip = new ZipArchive();
        if ($zip->open($tmp, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            $this->error('Gagal membuat file zip sementara.');

            return self::FAILURE;
        }

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($sourceDir, \RecursiveDirectoryIterator::SKIP_DOTS)
        );
        foreach ($files as $file) {
            if ($file->isFile()) {
                $relativePath = substr($file->getPathname(), strlen($sourceDir) + 1);
                $zip->addFile($file->getPathname(), $relativePath);
            }
        }
        $zip->close();

        // 2) Unggah ke disk tujuan (off-site).
        $disk = (string) $this->option('disk');
        try {
            $stream = fopen($tmp, 'r');
            Storage::disk($disk)->put($filename, $stream);
            if (is_resource($stream)) {
                fclose($stream);
            }
        } catch (\Throwable $e) {
            @unlink($tmp);
            $this->error('Upload ke disk "'.$disk.'" gagal: '.$e->getMessage());

            return self::FAILURE;
        }

        @unlink($tmp);

        // 3) Retensi: hapus backup lebih lama dari --keep hari.
        $this->pruneOldBackups($disk, 'products', (int) $this->option('keep'));

        $this->info('Backup sukses → '.$disk.':'.$filename);

        return self::SUCCESS;
    }
}
