<div class="card border-0 shadow-sm mb-4 rounded-3">
    <div class="card-header bg-white border-bottom border-light py-3">
        <h5 class="mb-0">
            <i class="fas fa-user-edit me-2 text-success"></i>Edit Profil
        </h5>
    </div>
    <div class="card-body">
        <form action="update_profil.php" method="POST">
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                    <input type="text"
                        class="form-control"
                        id="nama_lengkap"
                        name="nama_lengkap"
                        value="<?= htmlspecialchars($user['nama_lengkap']) ?>"
                        required
                        placeholder="Masukkan nama lengkap">
                </div>
                <div class="col-md-6">
                    <label for="email" class="form-label">Email</label>
                    <input type="email"
                        class="form-control"
                        id="email"
                        value="<?= htmlspecialchars($user['email']) ?>"
                        disabled
                        readonly>
                    <small class="text-muted">Email tidak dapat diubah.</small>
                </div>
                <div class="col-md-6">
                    <label for="no_hp" class="form-label">Nomor HP</label>
                    <input type="text"
                        class="form-control"
                        id="no_hp"
                        name="no_hp"
                        value="<?= htmlspecialchars($user['no_hp'] ?? '') ?>"
                        required
                        placeholder="Contoh: 081234567890">
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-2"></i>Simpan Perubahan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
