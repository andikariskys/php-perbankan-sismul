<div class="modal fade" id="modalUploadFoto" tabindex="-1" aria-labelledby="modalUploadFotoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-white border-bottom border-light">
                <h5 class="modal-title" id="modalUploadFotoLabel">
                    <i class="fas fa-camera me-2 text-success"></i>Upload Foto Profil
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="upload_foto.php" method="POST" enctype="multipart/form-data" id="formUploadFoto">
                <div class="modal-body text-center">
                    <div class="mx-auto rounded-circle overflow-hidden border border-dashed border-secondary mb-3 d-flex align-items-center justify-content-center bg-light" style="width: 150px; height: 150px;">
                        <img src="../../assets/uploads/<?= htmlspecialchars($user['foto_profil']) ?>"
                            alt="Preview"
                            id="preview-foto"
                            class="w-100 h-100 object-fit-cover"
                            style="display: block;">
                        <i class="fas fa-user text-secondary" id="preview-placeholder" style="display: none; font-size: 3rem;"></i>
                    </div>

                    <div class="mb-3">
                        <label for="foto_profil" class="form-label fw-semibold">Pilih Foto Baru</label>
                        <input type="file"
                            class="form-control"
                            id="foto_profil"
                            name="foto_profil"
                            accept="image/jpeg,image/png,image/jpg"
                            required
                            onchange="previewFoto(this)">
                    </div>

                    <div class="bg-light rounded p-3 small text-start text-secondary border border-light">
                        <i class="fas fa-info-circle me-1 text-primary"></i>
                        <strong>Ketentuan Upload:</strong>
                        <ul class="mb-0 mt-1">
                            <li>Format yang diizinkan: <strong>JPG, JPEG, PNG</strong></li>
                            <li>Ukuran maksimal: <strong>5 MB</strong></li>
                            <li>Gambar akan dikompresi otomatis menggunakan GD Library</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top border-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success" id="btnUpload">
                        <i class="fas fa-upload me-2"></i>Upload & Kompres
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
