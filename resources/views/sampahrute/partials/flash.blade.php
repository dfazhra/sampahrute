{{-- Notifikasi singkat (toast) untuk pesan sukses dan error --}}
<div class="toast-stack" aria-live="polite">
    @if (session('success'))
        <div class="toast-item toast-success" role="status">
            <span aria-hidden="true">✓</span>
            <p>{{ session('success') }}</p>
            <button type="button" data-toast-close aria-label="Tutup">×</button>
        </div>
    @endif
    @if (session('error'))
        <div class="toast-item toast-error" role="alert">
            <span aria-hidden="true">!</span>
            <p>{{ session('error') }}</p>
            <button type="button" data-toast-close aria-label="Tutup">×</button>
        </div>
    @endif
    @if ($errors->any() && ! request()->routeIs('login', 'warga.create'))
        <div class="toast-item toast-error" role="alert">
            <span aria-hidden="true">!</span>
            <p>{{ $errors->first() }}</p>
            <button type="button" data-toast-close aria-label="Tutup">×</button>
        </div>
    @endif
</div>
