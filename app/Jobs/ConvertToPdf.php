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

        $client = new Client();

        $response = $client->request('POST', 'http://10.90.237.12:8080/api/v1/convert/file/pdf',  [
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
