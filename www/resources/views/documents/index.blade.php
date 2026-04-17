@extends('layouts.app')

@section('content')
@include('layouts.navbar')

<!-- Google Drive / Windows Explorer Dropzone Interface -->
<div id="driveContainer" class="container pb-5 position-relative" style="min-height: 80vh;">
    
    <!-- Drag Overlay Indicator -->
    <div id="dragOverlay" class="position-absolute top-0 start-0 w-100 h-100 bg-info bg-opacity-10 rounded d-none align-items-center justify-content-center z-3" style="border: 3px dashed #0dcaf0;">
        <div class="text-center">
            <i class="bi bi-cloud-arrow-up display-1 text-info"></i>
            <h2 class="text-info fw-bold mt-2">Solte para Fazer Upload Instantâneo</h2>
            <p class="text-info">Pastas e Arquivos soltos aqui subirão a árvore recursivamente.</p>
        </div>
    </div>

    <!-- Header & Breadcrumbs / Search -->
    <div class="row mb-4 align-items-center">
        <div class="col-md-6 mb-3 mb-md-0">
            <h2 class="fw-bold mb-2"><i class="bi bi-hdd-network text-info"></i> Cofre Documental</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('documents.index') }}" class="text-decoration-none text-light"><i class="bi bi-house-door-fill"></i> Início</a></li>
                    @foreach($breadcrumbs as $bc)
                        <li class="breadcrumb-item active text-info" aria-current="page"><a href="{{ route('documents.index', ['folder_id' => $bc->id]) }}" class="text-decoration-none fw-bold text-info">{{ $bc->name }}</a></li>
                    @endforeach
                </ol>
            </nav>
        </div>
        <div class="col-md-6 d-flex justify-content-md-end gap-2 align-items-center">
            
            <div class="dropdown me-2">
                <button class="btn btn-info fw-bold dropdown-toggle shadow-sm px-4" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-plus-lg"></i> Novo
                </button>
                <ul class="dropdown-menu dropdown-menu-dark shadow">
                    <li><a class="dropdown-item fw-bold" href="#" data-bs-toggle="modal" data-bs-target="#newFolderModal"><i class="bi bi-folder-plus text-warning me-2"></i> Nova Pasta</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="#" onclick="document.getElementById('fileUploadInput').click()"><i class="bi bi-file-earmark-plus me-2"></i> Upload de Arquivos</a></li>
                    <li><a class="dropdown-item" href="#" onclick="document.getElementById('folderUploadInput').click()"><i class="bi bi-folder-symlink me-2"></i> Upload de Pasta</a></li>
                </ul>
            </div>
            
            <form action="{{ route('documents.index') }}" method="GET" class="d-flex w-100" style="max-width: 350px;">
                <div class="input-group shadow-sm">
                    <select name="search_type" class="form-select bg-dark border-secondary text-info fw-bold" style="max-width: 130px;" onchange="this.form.submit()">
                        <option value="title" {{ request('search_type') != 'content' ? 'selected' : '' }}>No Título</option>
                        <option value="content" {{ request('search_type') == 'content' ? 'selected' : '' }}>No Conteúdo (IA)</option>
                    </select>
                    <input type="text" name="q" id="liveSearch" class="form-control bg-dark border-secondary text-white" value="{{ request('q') }}" placeholder="Pesquisar..." {{ request('search_type') == 'content' ? 'autofocus' : '' }}>
                    <button type="submit" class="btn btn-secondary border-secondary"><i class="bi bi-search"></i></button>
                </div>
            </form>
            
            @if(request()->has('q'))
                <a href="{{ route('documents.index') }}" class="btn btn-outline-danger btn-sm ms-2" title="Limpar Busca"><i class="bi bi-x-lg"></i></a>
            @endif
        </div>
    </div>

    <!-- Hidden Upload Forms -->
    <form action="{{ route('documents.store') }}" method="POST" enctype="multipart/form-data" id="uploadForm" class="d-none">
        @csrf
        <input type="hidden" name="current_folder_id" value="{{ $currentFolderId }}">
        <input type="file" name="files[]" id="fileUploadInput" multiple>
        <input type="file" name="files[]" id="folderUploadInput" webkitdirectory directory multiple>
    </form>

    <!-- File System Explorer Area -->
    <div class="row g-4" id="explorerGrid">
        
        <!-- FOLDERS -->
        @foreach($folders as $folder)
        <div class="col-6 col-md-3 explorer-item" data-title="{{ strtolower($folder->name) }}">
            <a href="{{ route('documents.index', ['folder_id' => $folder->id]) }}" class="text-decoration-none">
                <div class="card bg-dark border-0 shadow-sm h-100 text-center folder-card" style="background-color: rgba(0,0,0,0.3) !important;">
                    <div class="card-body p-4 position-relative">
                        <!-- Context Menu ex: Delete -->
                        <div class="position-absolute top-0 end-0 p-2">
                            <form action="{{ route('document_folders.destroy', $folder->id) }}" method="POST" onsubmit="return confirm('ATENÇÃO: Triturar esta Pasta excluirá TODOS os arquivos de dentro dela recursivamente!')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-link text-secondary p-0"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>

                        <i class="bi bi-folder-fill display-4 text-warning mb-2 d-block"></i>
                        <h6 class="fw-bold mb-0 text-white text-truncate">{{ $folder->name }}</h6>
                    </div>
                </div>
            </a>
        </div>
        @endforeach

        <!-- FILES -->
        @foreach($documents as $doc)
        <div class="col-6 col-md-3 col-lg-2 explorer-item" data-title="{{ strtolower($doc->title) }}">
            <div class="card bg-dark border-0 shadow-sm h-100 text-center file-card" style="background-color: rgba(0,0,0,0.15) !important;">
                <div class="card-body p-3 d-flex flex-column">
                    <!-- Ext Icon -->
                    <div class="mb-3 mt-2 flex-grow-1 d-flex justify-content-center align-items-center">
                        @if(str_contains(strtolower($doc->file_type), 'pdf'))
                            <i class="bi bi-file-earmark-pdf-fill display-3 text-danger"></i>
                        @elseif(str_contains(strtolower($doc->file_type), 'image'))
                            <i class="bi bi-file-earmark-image-fill display-3 text-info"></i>
                        @elseif(str_contains(strtolower($doc->file_type), 'sheet') || str_contains(strtolower($doc->file_type), 'csv'))
                            <i class="bi bi-file-earmark-spreadsheet-fill display-3 text-success"></i>
                        @else
                            <i class="bi bi-file-earmark-fill display-3 text-secondary"></i>
                        @endif
                    </div>
                    
                    <h6 class="small fw-bold mb-1 text-truncate text-white" title="{{ $doc->title }}">{{ $doc->title }}</h6>
                    <small class="text-white-50 d-block mb-3">{{ number_format($doc->file_size / 1024, 1) }} KB</small>
                    
                    <div class="d-flex justify-content-between mt-auto px-1">
                        @if($doc->is_secured)
                            <button class="btn btn-sm btn-outline-warning w-100 me-1 py-0 px-1" data-bs-toggle="modal" data-bs-target="#secureModal{{ $doc->id }}" title="Requer Senha">
                                <i class="bi bi-shield-lock-fill"></i>
                            </button>
                        @else
                            <form action="{{ route('documents.download', $doc->id) }}" method="POST" class="w-100 me-1">
                                @csrf
                                <button class="btn btn-sm btn-outline-info w-100 py-0 px-1" title="Baixar"><i class="bi bi-download"></i></button>
                            </form>
                        @endif

                        <form action="{{ route('documents.destroy', $doc->id) }}" method="POST" class="w-25" onsubmit="return confirm('Triturar este arquivo permanentemente?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger w-100 py-0 px-1" title="Excluir"><i class="bi bi-trash"></i></button>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Modal de Senha se Secreto -->
            @if($doc->is_secured)
            <div class="modal fade" id="secureModal{{ $doc->id }}" tabindex="-1">
                <div class="modal-dialog modal-sm modal-dialog-centered">
                    <div class="modal-content bg-dark border-secondary shadow-lg">
                        <div class="modal-header border-0 pb-0">
                            <h6 class="modal-title text-warning fw-bold"><i class="bi bi-incognito"></i> Documento Selado</h6>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <form action="{{ route('documents.download', $doc->id) }}" method="POST">
                            @csrf
                            <div class="modal-body">
                                <small class="text-muted mb-3 d-block">Acesso classificado. Insira sua Senha Mestra de login para extrair.</small>
                                <input type="password" name="password" class="form-control" required placeholder="Senha...">
                            </div>
                            <div class="modal-footer border-0 pt-0">
                                <button type="submit" class="btn btn-warning btn-sm w-100 fw-bold">Desbloquear</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @endif
        </div>
        @endforeach

        @if(count($folders) == 0 && count($documents) == 0)
            <div class="col-12 text-center text-muted py-5 mt-5">
                <i class="bi bi-inboxes display-1 d-block mb-3" style="opacity:0.2;"></i>
                <h5>Esta pasta está vazia.</h5>
                <p>Arraste arquivos e pastas para cá ou use o botão "+ Novo".</p>
            </div>
        @endif
    </div>
</div>

<!-- Modal Nova Pasta -->
<div class="modal fade" id="newFolderModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content bg-dark border-secondary shadow-lg">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-bold">Criar Pasta</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('document_folders.store') }}" method="POST">
                @csrf
                <input type="hidden" name="parent_id" value="{{ $currentFolderId }}">
                <div class="modal-body py-4">
                    <input type="text" name="name" class="form-control form-control-lg bg-dark text-white border-secondary" placeholder="Nome da pasta" required autofocus>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-info btn-sm fw-bold px-3">Criar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Overlay Upload Processor -->
<div class="modal fade" id="processingModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content bg-dark border-info shadow-lg text-center p-4">
            <h5 class="text-info fw-bold mb-3"><i class="bi bi-clock-history"></i> Processando Árvore</h5>
            <div class="spinner-border text-info mb-3 mx-auto" role="status"></div>
            <small class="text-light">Lendo arquivos e construindo pastas...</small>
        </div>
    </div>
</div>

<style>
.folder-card:hover { transform: translateY(-5px); transition: 0.2s; cursor: pointer; border-bottom: 2px solid #ffc107 !important; }
.file-card:hover { transform: translateY(-5px); transition: 0.2s; border-bottom: 2px solid #0dcaf0 !important; }
::-webkit-scrollbar { width: 8px; } ::-webkit-scrollbar-track { background: #0f172a; } ::-webkit-scrollbar-thumb { background: #334155; border-radius: 4px; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Live Search (Apenas para modo local no título)
    const searchInput = document.getElementById('liveSearch');
    const items = document.querySelectorAll('.explorer-item');
    const searchType = document.querySelector('select[name="search_type"]');
    
    searchInput.addEventListener('input', e => {
        if (searchType.value === 'content') return; // Se for IA Content, ignora liveJS e espera dar ENTER(Submit)
        
        const term = e.target.value.toLowerCase().trim();
        items.forEach(item => {
            const title = item.getAttribute('data-title');
            item.style.display = title.includes(term) ? '' : 'none';
        });
    });

    // Native Hidden HTML Input Listeners (For "+ Novo" Menu Actions)
    const form = document.getElementById('uploadForm');
    const fileInp = document.getElementById('fileUploadInput');
    const folderInp = document.getElementById('folderUploadInput');
    
    fileInp.addEventListener('change', () => { if(fileInp.files.length > 0) processAndSubmitDirectFiles(fileInp.files); });
    folderInp.addEventListener('change', () => { if(folderInp.files.length > 0) processAndSubmitDirectFiles(folderInp.files); });

    function processAndSubmitDirectFiles(files) {
        // Mostra popup de processando
        const processingModal = new bootstrap.Modal(document.getElementById('processingModal'));
        processingModal.show();
        
        const dt = new DataTransfer();
        // Construir form dinamicamente para garantir append do webkitRelativePath
        const formData = new FormData(form);
        formData.delete('files[]');
        
        for (let i = 0; i < files.length; i++) {
            formData.append('files[]', files[i]);
            formData.append('paths[]', files[i].webkitRelativePath || files[i].name);
        }
        
        // Faz a submissao nativa reescrevendo o navegador pra mesma pagina
        fetch(form.action, { method: 'POST', body: formData })
        .then(() => window.location.reload())
        .catch(() => { alert("Erro de rede."); window.location.reload(); });
    }

    // Advanced Drag N Drop API with Recursivity (for Webkit API window)
    const dragOverlay = document.getElementById('dragOverlay');
    const container = document.getElementById('driveContainer');
    let dragCounter = 0;

    container.addEventListener('dragenter', e => {
        e.preventDefault(); dragCounter++;
        dragOverlay.classList.remove('d-none');
        dragOverlay.classList.add('d-flex');
    });

    container.addEventListener('dragleave', e => {
        e.preventDefault(); dragCounter--;
        if (dragCounter === 0) {
            dragOverlay.classList.add('d-none');
            dragOverlay.classList.remove('d-flex');
        }
    });

    container.addEventListener('dragover', e => e.preventDefault());

    container.addEventListener('drop', async e => {
        e.preventDefault();
        dragCounter = 0;
        dragOverlay.classList.add('d-none');
        dragOverlay.classList.remove('d-flex');

        const itemsRaw = e.dataTransfer.items;
        if (!itemsRaw || itemsRaw.length === 0) return;

        const processingModal = new bootstrap.Modal(document.getElementById('processingModal'));
        processingModal.show();

        const formData = new FormData(form);
        formData.delete('files[]');
        
        let fileCount = 0;

        // Recursive directory scraper
        async function traverseFileTree(item, path) {
            path = path || "";
            if (item.isFile) {
                const file = await new Promise(resolve => item.file(resolve));
                formData.append('files[]', file);
                formData.append('paths[]', path + file.name);
                fileCount++;
            } else if (item.isDirectory) {
                let dirReader = item.createReader();
                let entries = [];
                // Recursively read all chunked entries (API returns max 100 entries per read)
                let readEntries = async () => {
                    let results = await new Promise(resolve => dirReader.readEntries(resolve));
                    if(results.length) { entries = entries.concat(results); await readEntries(); }
                };
                await readEntries();
                for(let i=0; i<entries.length; i++) {
                    await traverseFileTree(entries[i], path + item.name + "/");
                }
            }
        }

        for (let i=0; i<itemsRaw.length; i++) {
            let entry = itemsRaw[i].webkitGetAsEntry();
            if (entry) await traverseFileTree(entry);
        }

        if (fileCount > 0) {
            fetch(form.action, { method: 'POST', body: formData })
            .then(() => window.location.reload())
            .catch(() => { alert("Erro de rede ao arquivar."); window.location.reload(); });
        } else {
            window.location.reload();
        }
    });
});
</script>
@endsection
