@extends('layouts.app')

@section('content')
@include('layouts.navbar')

<div class="container pb-5">
    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <h2 class="fw-bold mb-0"><i class="bi bi-safe2 text-info"></i> Cofre Documental</h2>
            <p class="text-muted small">Arquivos protegidos criptograficamente no Storage da Nuvem Privada.</p>
        </div>
        <div class="col-md-6">
            <input type="text" id="liveSearch" class="form-control form-control-lg bg-dark border-secondary text-white" placeholder="Filtro Instantâneo: Buscar por NF, Categoria, Tag...">
        </div>
    </div>

    <!-- Upload Zone -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="card bg-dark border-secondary border-dashed" style="border-style: dashed !important; border-width: 2px !important;">
                <div class="card-body text-center p-5">
                    <form action="{{ route('documents.store') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
                        @csrf
                        <i class="bi bi-cloud-arrow-up display-4 text-muted mb-3 d-block"></i>
                        <h5 class="fw-bold">Arraste seus documentos ou clique aqui</h5>
                        <p class="text-muted small mb-4">PDFs, Imagens, CSVs (Max 10MB)</p>
                        
                        <div class="row justify-content-center">
                            <div class="col-md-8">
                                <div class="input-group mb-3">
                                    <input type="file" name="files[]" class="form-control bg-dark border-secondary text-light" multiple required id="realFileInput" style="display: none;">
                                    <button class="btn btn-outline-info" type="button" onclick="document.getElementById('realFileInput').click()">Selecionar Arquivos</button>
                                    <select name="typology" class="form-select bg-dark border-secondary text-light" style="max-width: 150px;">
                                        <option value="invoice">Nota Fiscal</option>
                                        <option value="receipt">Recibo</option>
                                        <option value="statement">Extrato</option>
                                        <option value="declaration">Declaração</option>
                                        <option value="contract">Contrato</option>
                                        <option value="other" selected>Outros</option>
                                    </select>
                                </div>
                                <div class="d-flex justify-content-center gap-4">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" name="is_secured" id="isSecuredToggle">
                                        <label class="form-check-label text-warning fw-bold small" for="isSecuredToggle"><i class="bi bi-lock-fill"></i> Exigir Senha</label>
                                    </div>
                                    <button type="submit" class="btn btn-success btn-sm px-4 fw-bold d-none" id="btnUploadSubmit">Arquivar Agora</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Document Grid -->
    <div class="row g-4" id="documentGrid">
        @forelse($documents as $doc)
            <div class="col-md-4 col-sm-6 document-card" data-title="{{ strtolower($doc->title) }}" data-typology="{{ $doc->typology }}">
                <div class="card bg-dark border-0 shadow-sm h-100" style="background-color: rgba(0,0,0,0.2) !important;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="text-muted fs-4">
                                @if($doc->typology == 'invoice') <i class="bi bi-receipt text-danger"></i>
                                @elseif($doc->typology == 'statement') <i class="bi bi-table text-success"></i>
                                @elseif($doc->typology == 'contract') <i class="bi bi-file-earmark-text text-primary"></i>
                                @elseif($doc->typology == 'declaration') <i class="bi bi-file-earmark-ruled text-warning"></i>
                                @else <i class="bi bi-file-earmark text-muted"></i> @endif
                            </div>
                            @if($doc->is_secured)
                                <span class="badge text-bg-warning"><i class="bi bi-shield-lock-fill"></i> Secreto</span>
                            @endif
                        </div>
                        <h6 class="fw-bold mb-1 text-truncate" title="{{ $doc->title }}">{{ $doc->title }}</h6>
                        <p class="text-white-50 small mb-3">
                            {{ number_format($doc->file_size / 1024, 1) }} KB &bull; {{ $doc->created_at->format('d/m/Y') }}
                        </p>
                        
                        <div class="d-flex justify-content-between mt-auto">
                            <!-- Download/View Button com validação de senha no frontend (abre modal se secured) -->
                            @if($doc->is_secured)
                                <button class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#secureModal{{ $doc->id }}">
                                    <i class="bi bi-cloud-download"></i> Baixar
                                </button>
                            @else
                                <form action="{{ route('documents.download', $doc->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-info"><i class="bi bi-cloud-download"></i> Baixar</button>
                                </form>
                            @endif

                            <form action="{{ route('documents.destroy', $doc->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Triturar este documento permanentemente?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Secure Unlock Modal -->
            @if($doc->is_secured)
            <div class="modal fade" id="secureModal{{ $doc->id }}" tabindex="-1">
                <div class="modal-dialog modal-sm modal-dialog-centered">
                    <div class="modal-content bg-dark border-secondary shadow-lg">
                        <div class="modal-header border-0">
                            <h6 class="modal-title text-warning fw-bold"><i class="bi bi-incognito"></i> Acesso Restrito</h6>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <form action="{{ route('documents.download', $doc->id) }}" method="POST">
                            @csrf
                            <div class="modal-body">
                                <p class="small text-muted mb-3">Insira sua senha de login para destrancar este documento.</p>
                                <input type="password" name="password" class="form-control" required placeholder="Sua senha...">
                            </div>
                            <div class="modal-footer border-0">
                                <button type="submit" class="btn btn-warning w-100 fw-bold">Destrancar & Baixar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @endif
        @empty
            <div class="col-12 text-center text-muted py-5">
                <i class="bi bi-folder-x display-1 mb-3 d-block" style="opacity: 0.2"></i>
                <h5>Seu Cofre está vazio.</h5>
            </div>
        @endforelse
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Exibe o botão de submit apenas se o usuário escolher um arquivo
        const fileInput = document.getElementById('realFileInput');
        const submitBtn = document.getElementById('btnUploadSubmit');
        
        fileInput.addEventListener('change', function() {
            if(this.files.length > 0) {
                submitBtn.classList.remove('d-none');
                submitBtn.innerText = `Arquivar ${this.files.length} Item(ns)`;
            } else {
                submitBtn.classList.add('d-none');
            }
        });

        // Live Search Engine (Ultra Agile Frontend Filter)
        const searchInput = document.getElementById('liveSearch');
        const cards = document.querySelectorAll('.document-card');

        searchInput.addEventListener('input', function(e) {
            const term = e.target.value.toLowerCase().trim();
            
            cards.forEach(card => {
                const title = card.getAttribute('data-title');
                const typology = card.getAttribute('data-typology');
                
                if (title.includes(term) || typology.includes(term)) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });
</script>
@endsection
