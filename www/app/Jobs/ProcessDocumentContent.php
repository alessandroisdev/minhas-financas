<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessDocumentContent implements ShouldQueue
{
    use Queueable;

    protected $document;

    public function __construct(\App\Models\Document $document)
    {
        $this->document = $document;
    }

    public function handle(): void
    {
        $path = storage_path('app/' . $this->document->file_path);
        if (!file_exists($path)) return;

        $text = '';
        $ext = strtolower(pathinfo($this->document->title, PATHINFO_EXTENSION));
        $mime = strtolower($this->document->file_type);

        try {
            if ($ext === 'pdf' || str_contains($mime, 'pdf')) {
                $parser = new \Smalot\PdfParser\Parser();
                $pdf = $parser->parseFile($path);
                $text = $pdf->getText();
            } 
            elseif (in_array($ext, ['png', 'jpg', 'jpeg', 'webp']) || str_contains($mime, 'image')) {
                $text = (new \thiagoalessio\TesseractOCR\TesseractOCR($path))->lang('por', 'eng')->run();
            }
            elseif ($ext === 'txt' || str_contains($mime, 'text/plain')) {
                $text = file_get_contents($path);
            }

            // Sanitiza para não ocupar espaço absurdo com formatação quebrada
            $text = preg_replace('/\s+/', ' ', $text);
            
            if (!empty(trim($text))) {
                $this->document->update(['content_text' => trim($text)]);
            }
        } catch (\Exception $e) {
            \Log::error("Falha no OCR do Documento {$this->document->id}: " . $e->getMessage());
        }
    }
}
