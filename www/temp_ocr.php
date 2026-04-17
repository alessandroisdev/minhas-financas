<?php foreach (\App\Models\Document::whereNull('content_text')->get() as $d) { \App\Jobs\ProcessDocumentContent::dispatch($d); } echo 'D'; 
