<x-guest-layout>
    <x-auth-card>
        <x-slot name="logo">
        </x-slot>

        <div style="max-width:480px" class="container mt-5 w-100">
          <div class="card p-5 text-center">
              <a href="{{ url('') }}" class="d-flex align-items-center justify-content-center mb-4">
                <!--Logo start-->
                <div class="logo-main">
                    @if(file_exists(base_path("assets/linkstack/images/").findFile('avatar')))
                    <div class="logo-normal">
                      <img class="img logo" src="{{ asset('assets/linkstack/images/'.findFile('avatar')) }}" style="width:auto;height:30px;">
                    </div>
                    @else
                    <div class="logo-normal">
                      <img class="img logo" type="image/svg+xml" src="{{ asset('assets/linkstack/images/logo.svg') }}" width="30px" height="30px">
                    </div>
                    @endif
                </div>
                <!--logo End-->
                <h4 class="logo-title ms-3">{{ env('APP_NAME') }}</h4>
              </a>

              <div class="mx-auto mb-4 d-flex align-items-center justify-content-center"
                   style="width:64px;height:64px;border-radius:50%;background:linear-gradient(135deg,#ee17fe,#19d0e0);">
                <i class="bi bi-link-45deg" style="color:#06051c;font-size:1.75rem;"></i>
              </div>

              <h2 class="mb-2">Página no encontrada</h2>
              <p class="text-center mb-4">
                  El enlace que buscas no existe o ya no está disponible.
              </p>

              <a href="{{ url('') }}" class="btn btn-primary">
                  Volver al inicio
              </a>
          </div>
        </div>

    </x-auth-card>
</x-guest-layout>
