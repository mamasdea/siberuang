<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Storage;

class ConvertToPdf implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $nama_file_pdf;
    public $nama_file_docx;
    public $pathSave;

    public function __construct($nama_file_docx, $nama_file_pdf, $pathSave)
    {
        $this->nama_file_docx = $nama_file_docx;
        $this->nama_file_pdf = $nama_file_pdf;
        $this->pathSave = $pathSave;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $pathWORD = storage_path('app/public/reports/laporan_belanja_' . $this->nama_file_docx);

        try {
            \Illuminate\Support\Facades\Log::info('ConvertToPdf: Memulai konversi untuk ' . $this->nama_file_docx);
            
            $client = new Client(['timeout' => 30]);

            $response = $client->request('POST', 'http://10.90.237.12:8080/api/v1/convert/file/pdf',  [
                'multipart' => [
                    [
                        'name'     => 'fileInput',
                        'contents' => fopen($pathWORD, 'r'),
                    ]
                ]
            ]);
            
            $statusCode = $response->getStatusCode();
            $bodySize = $response->getBody()->getSize();
            \Illuminate\Support\Facades\Log::info("ConvertToPdf: Response diterima. Status: {$statusCode}, Size: {$bodySize}");

            if ($statusCode == 200) {
                 Storage::disk('local')->put('public/reports/laporan_belanja_' . $this->nama_file_pdf, $response->getBody());
                 \Illuminate\Support\Facades\Log::info('ConvertToPdf: File berhasil disimpan ke ' . 'public/reports/laporan_belanja_' . $this->nama_file_pdf);
            } else {
                 \Illuminate\Support\Facades\Log::error('ConvertToPdf: Status code bukan 200. Konversi gagal.');
            }

        } catch (\Exception $e) {
            // Log error untuk debugging
            \Illuminate\Support\Facades\Log::error('ConvertToPdf Failed: ' . $e->getMessage());
            \Illuminate\Support\Facades\Log::error($e->getTraceAsString());
        }
    }
}
