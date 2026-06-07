<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-white border-bottom border-light py-3">
        <h5 class="mb-0">
            <i class="fas fa-lock me-2 text-warning"></i>Ubah Password
        </h5>
    </div>
    <div class="card-body">
        <form action="update_password.php" method="POST" id="formPassword">
            <div class="row g-3">
                <div class="col-md-12">
                    <label for="password_lama" class="form-label">Password Lama</label>
                    <div class="input-group">
                        <input type="password"
                            class="form-control"
                            id="password_lama"
                            name="password_lama"
                            required
                            placeholder="Masukkan password saat ini">
                        <button type="button" class="btn btn-outline-secondary toggle-password" data-target="password_lama">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-6">
                    <label for="password_baru" class="form-label">Password Baru</label>
                    <div class="input-group">
                        <input type="password"
                            class="form-control"
                            id="password_baru"
                            name="password_baru"
                            required
                            minlength="8"
                            placeholder="Minimal 8 karakter">
                        <button type="button" class="btn btn-outline-secondary toggle-password" data-target="password_baru">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-6">
                    <label for="konfirmasi_password" class="form-label">Konfirmasi Password Baru</label>
                    <div class="input-group">
                        <input type="password"
                            class="form-control"
                            id="konfirmasi_password"
                            name="konfirmasi_password"
                            required
                            minlength="8"
                            placeholder="Ulangi password baru">
                        <button type="button" class="btn btn-outline-secondary toggle-password" data-target="konfirmasi_password">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-key me-2"></i>Ubah Password
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
