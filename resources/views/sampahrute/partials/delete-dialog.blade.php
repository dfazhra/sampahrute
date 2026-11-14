<dialog class="sr-modal modal-small" id="delete-form" aria-labelledby="delete-form-title">
    <form method="POST" action="{{ route('demo.save') }}">
        @csrf
        <input type="hidden" name="entity" value="hapus">
        <input type="hidden" name="target">
        <div class="modal-head">
            <h2 id="delete-form-title" data-fill-text="_title">Hapus data?</h2>
            <button type="button" class="icon-btn" data-modal-close aria-label="Tutup">×</button>
        </div>
        <p class="modal-text">Data yang dihapus tidak bisa dikembalikan. Laporan dan riwayat yang terkait tetap tersimpan.</p>
        <div class="modal-actions">
            <button type="button" class="btn btn-soft" data-modal-close>Batal</button>
            <button type="submit" class="btn btn-danger">Ya, hapus</button>
        </div>
    </form>
</dialog>
