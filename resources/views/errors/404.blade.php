@include('errors.layout', ['code' => 404, 'title' => 'Halaman tidak ditemukan', 'message' => $exception->getMessage() ?: 'Alamat yang Anda buka tidak ada atau sudah dipindahkan.'])
