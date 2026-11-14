@include('errors.layout', ['code' => 403, 'title' => 'Akses ditolak', 'message' => $exception->getMessage() ?: 'Akun Anda tidak punya akses ke halaman ini. Masuk dengan peran yang sesuai.'])
