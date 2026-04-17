<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessDocumentContent implements ShouldQueue
{
    use Queueable;

    public $tries = 5;

    protected $document;

    public function __construct(\App\Models\Document $document)
    {
        $this->document = $document;
    }

    public function handle(): void
    {
        if (!\Illuminate\Support\Facades\Storage::disk('local')->exists($this->document->file_path)) {
            $this->fallbackTokenizer();
            return;
        }
        
        $path = \Illuminate\Support\Facades\Storage::disk('local')->path($this->document->file_path);

        $text = '';
        $ext = strtolower(pathinfo($this->document->title, PATHINFO_EXTENSION));
        $mime = strtolower($this->document->file_type);

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

        // Sanitiza
        $text = preg_replace('/\s+/', ' ', $text);
        
        if (!empty(trim($text))) {
            $this->document->update(['content_text' => trim($text)]);
        } else {
            // Motor rodou 100% mas não encontrou pixels com letras (Ex: Imagem branca)
            $this->fallbackTokenizer();
        }
    }

    public function failed(\Throwable $exception)
    {
        // Quando crasahr pelas 5 vezes seguidas por timeout ou erro pesado nativo
        \Log::error("A.I Engine Core Panic após 5 retries. Iniciando Tokenização Fail-Safe do Titulo. Doc ID: {$this->document->id}");
        $this->fallbackTokenizer();
    }

    private function fallbackTokenizer()
    {
        // Se a IA não puxou nada ou travou pesado, pegamos o titulo puro e indexamos pra não quebrar a busca!
        $this->document->update(['content_text' => $this->document->title]);
    }
}
