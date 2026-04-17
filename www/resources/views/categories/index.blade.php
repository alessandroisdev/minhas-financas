@extends('layouts.app')

@section('content')
@include('layouts.navbar')

<div class="container">
    <div class="row">
        <!-- Sidebar Esquerda -->
        <div class="col-md-3">
            @include('layouts.sidebar')
        </div>

        <div class="col-md-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold mb-0">Gerenciar Categorias</h2>
                <button class="btn btn-primary fw-bold px-4" data-bs-toggle="modal" data-bs-target="#newCategoryModal">Nova Categoria</button>
            </div>

            <div class="card bg-dark border-0 shadow-lg">
                <div class="card-body p-0">
                    <table class="table table-dark table-hover mb-0">
                        <thead class="small text-uppercase text-muted">
                            <tr>
                                <th class="ps-4">Cor</th>
                                <th>Nome</th>
                                <th>Tipo</th>
                                <th class="text-end pe-4">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($categories as $category)
                            <tr class="align-middle">
                                <td class="ps-4">
                                    <div style="width: 20px; height: 20px; border-radius: 50%; background-color: {{ $category->color }};"></div>
                                </td>
                                <td class="fw-bold">{{ $category->name }}</td>
                                <td>
                                    <span class="badge {{ $category->type == 'income' ? 'text-bg-success' : 'text-bg-danger' }}">
                                        {{ $category->type == 'income' ? 'Receita' : 'Despesa' }}
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <form action="{{ route('categories.destroy', $category->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Excluir categoria?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Remover</button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted fw-semibold py-4">
                                    Nenhuma categoria cadastrada ainda.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Nova Categoria -->
<div class="modal fade" id="newCategoryModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content bg-dark border-0 shadow-lg" style="background-color: #0F172A !important;">
      <div class="modal-header border-bottom-0">
        <h5 class="modal-title fw-bold">Criar Categoria</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form action="{{ route('categories.store') }}" method="POST">
          @csrf
          <div class="modal-body">
              <div class="mb-3">
                  <label class="form-label text-muted small text-uppercase">Nome da Categoria</label>
                  <input type="text" name="name" class="form-control" required placeholder="Ex: Supermercado">
              </div>
              <div class="mb-3">
                  <label class="form-label text-muted small text-uppercase">Tipo</label>
                  <select name="type" class="form-control" required>
                      <option value="expense">Despesa</option>
                      <option value="income">Receita</option>
                  </select>
              </div>
              <div class="mb-3">
                  <label class="form-label text-muted small text-uppercase">Cor (Hexadecimal)</label>
                  <input type="color" name="color" class="form-control form-control-color w-100" value="#D4AF37" required>
              </div>
          </div>
          <div class="modal-footer border-top-0">
              <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Cancelar</button>
              <button type="submit" class="btn btn-primary">Salvar Categoria</button>
          </div>
      </form>
    </div>
  </div>
</div>
@endsection
