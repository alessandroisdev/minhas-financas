<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Minhas Finanças') }}</title>

    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=outfit:400,600,700,800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Scripts and Styles -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body>
    @yield('content')

    <!-- Toast Container -->
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1055;">
        @if(session('status'))
            <div class="toast align-items-center text-bg-success border-0 show" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="5000">
                <div class="d-flex">
                    <div class="toast-body fw-semibold">
                        {{ session('status') }}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        @endif

        @if($errors->any())
            @foreach($errors->all() as $error)
                <div class="toast align-items-center text-bg-danger border-0 show mt-2" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="6000">
                    <div class="d-flex">
                        <div class="toast-body fw-semibold">
                            {{ $error }}
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                </div>
            @endforeach
        @endif
    </div>

    <!-- Bootstrap JS nativo garantido via CDN (Evita dependência paralela do Vite para modais) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Automatically initialize toasts and SSE Global -->
    <script type="module">
        document.addEventListener('DOMContentLoaded', function () {
            // Se possivel, expor o bootstrap globalmente para as blades não falharem (Vite isolate prevention)
            if(typeof bootstrap !== 'undefined' && !window.bootstrap) {
                window.bootstrap = bootstrap;
            }

            const toastElList = document.querySelectorAll('.toast');
            toastElList.forEach(toastEl => {
                if (typeof bootstrap !== 'undefined') {
                    const toast = new bootstrap.Toast(toastEl);
                    toast.show();
                } else {
                    toastEl.classList.add('show'); // Fallback visual
                }
            });

            // Requisita permissão do navegador para notificações WebPush (Aba minimizada)
            if ("Notification" in window && Notification.permission === "default") {
                Notification.requestPermission();
            }

            @auth
            // Conecta no SSE e mantém a conexão FPM aberta orquestrando mensagens do Redis
            const sse = new EventSource("{{ route('stream') }}");
            sse.onmessage = function(event) {
                try {
                    const data = JSON.parse(event.data);
                    
                    if(data.type === 'notification' || data.type === 'toast') {
                        const container = document.querySelector('.toast-container');
                        const bgClass = data.status === 'error' ? 'text-bg-danger' : 
                                       (data.status === 'success' ? 'text-bg-success' : 'text-bg-primary');
                        
                        const toastHTML = `
                            <div class="toast align-items-center ${bgClass} border-0 show mt-2" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="7000">
                                <div class="d-flex">
                                    <div class="toast-body fw-semibold">
                                        ${data.message}
                                    </div>
                                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                                </div>
                            </div>
                        `;
                        container.insertAdjacentHTML('beforeend', toastHTML);

                        if ("Notification" in window && Notification.permission === "granted") {
                           new Notification("Minhas Finanças", { body: data.message });
                        }
                    }
                } catch(e) {}
            };
            @endauth
        });
    </script>
</body>
</html>
