<div class="delete-confirmation-modal" data-delete-modal hidden>
    <div class="delete-confirmation-backdrop" data-delete-modal-close></div>
    <section class="delete-confirmation-dialog" role="dialog" aria-modal="true" aria-labelledby="delete-confirmation-title" aria-describedby="delete-confirmation-description">
        <div class="delete-confirmation-icon" aria-hidden="true">
            <span data-delete-modal-icon><x-lucide-trash-2 /></span>
            <span data-confirm-modal-power-icon hidden><x-lucide-power /></span>
        </div>
        <h2 id="delete-confirmation-title" data-delete-modal-title>Delete Data</h2>
        <p id="delete-confirmation-description" data-delete-modal-description>Apakah anda yakin ingin menghapus data?</p>
        <div class="delete-confirmation-actions">
            <button class="secondary-button" type="button" data-delete-modal-close>Kembali</button>
            <button class="danger-button" type="button" data-delete-modal-confirm>
                <span data-delete-modal-confirm-trash aria-hidden="true"><x-lucide-trash-2 /></span>
                <span data-delete-modal-confirm-power aria-hidden="true" hidden><x-lucide-power /></span>
                <span data-delete-modal-confirm-label>Hapus Data</span>
            </button>
        </div>
    </section>
</div>
