<?php

namespace App\Console\Commands;

use App\Services\EnterpriseSearchService;
use Illuminate\Console\Command;

class UpdateSearchSynonyms extends Command
{
    protected $signature = 'search:update-synonyms';

    protected $description = 'Push kamus sinonim Bahasa Indonesia ke semua Meilisearch index';

    public function handle(EnterpriseSearchService $service): int
    {
        if (!$service->isAvailable()) {
            $this->error('Meilisearch tidak tersedia.');
            return self::FAILURE;
        }

        $this->info('Mempush kamus sinonim Bahasa Indonesia...');

        $service->pushSynonymDictionary();

        $this->info('Sinonim berhasil di-push ke semua index.');
        $this->info('Sinonim termasuk:');
        $this->line('  gaji <-> salary / payroll / upah / honor');
        $this->line('  cuti <-> leave / libur / vacation');
        $this->line('  pelanggan <-> customer / klien / client / pembeli');
        $this->line('  faktur <-> invoice / tagihan / bill');
        $this->line('  pesanan <-> order / orderan / purchase');
        $this->line('  karyawan <-> pegawai / employee / staff / pekerja');
        $this->line('  tiket <-> ticket / keluhan / complaint');
        $this->line('  proyek <-> project / projek');
        $this->line('  aset <-> asset / harta / barang');
        $this->line('  tugas <-> task / pekerjaan / job');
        $this->line('  rapat <-> meeting / pertemuan');
        $this->line('  kontrak <-> contract / perjanjian');
        $this->line('  produk <-> product / barang / item');
        $this->line('  dokumen <-> document / template / surat');

        return self::SUCCESS;
    }
}
