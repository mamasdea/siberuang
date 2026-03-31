<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Storage;

class ConvertToPdfSppSpmGu implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $nama_file_pdf;
    public $nama_file_docx;
    public $pathSave;

    public function __construct($nama_file_docx, $nama_file_pdf, $pathSave)
    {
        $this->nama_file_docx = $nama_file_docx;
        $this->nama_file_pdf = $nama_file_pdf;
        $this->pathSave = $pathSave;
    }

    public function handle()
    {
        $pathWORD = storage_path('app/public/reports/spp-spm-gu/' . $this->nama_file_docx);

        $client = new Client();

        $response = $client->request('POST', 'http://10.90.237.12:8080/api/v1/convert/file/pdf', [
            'multipart' => [
                [
                    'name'     => 'fileInput',
                    'contents' => fopen($pathWORD, 'r'),
                ]
            ]
        ]);

        Storage::disk('local')->put('public/reports/laporan_belanja_' . $this->nama_file_pdf, $response->getBody());
    }
}
