<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body text-center py-4">
        <div class="position-relative mx-auto rounded-circle overflow-hidden border border-4 border-success shadow-sm" style="width: 150px; height: 150px; cursor: pointer;" data-bs-toggle="modal" data-bs-target="#modalUploadFoto" title="Klik untuk ganti foto">
            <img src="../../assets/uploads/<?= htmlspecialchars($user['foto_profil']) ?>"
                alt="Foto Profil"
                class="w-100 h-100 object-fit-cover"
                id="foto-profil-display">
            <button type="button" class="position-absolute bottom-0 start-0 end-0 bg-dark bg-opacity-75 text-white border-0 py-1 small">
                <i class="fas fa-camera me-1"></i> Ganti Foto
            </button>
        </div>

        <h4 class="mt-3 mb-1 fw-bold"><?= htmlspecialchars($user['nama_lengkap']) ?></h4>
        <p class="text-muted mb-3">
            <i class="fas fa-envelope me-1"></i><?= htmlspecialchars($user['email']) ?>
        </p>

        <span class="badge bg-<?= $user['status_akun'] === 'Aktif' ? 'success' : ($user['status_akun'] === 'Pending' ? 'warning' : 'danger') ?> px-3 py-2" style="border-radius: 20px; font-size: 0.8rem;">
            <i class="fas fa-<?= $user['status_akun'] === 'Aktif' ? 'check-circle' : ($user['status_akun'] === 'Pending' ? 'clock' : 'times-circle') ?> me-1"></i>
            <?= htmlspecialchars($user['status_akun']) ?>
        </span>

        <hr class="my-3">

        <div class="text-start px-2">
            <div class="d-flex align-items-center gap-3 py-2 border-bottom border-light">
                <i class="fas fa-phone text-success" style="width: 20px; text-align: center;"></i>
                <span><?= htmlspecialchars($user['no_hp'] ?? '-') ?></span>
            </div>
            <div class="d-flex align-items-center gap-3 py-2 border-bottom border-light">
                <i class="fas fa-user-tag text-success" style="width: 20px; text-align: center;"></i>
                <span><?= htmlspecialchars($user['nama_role']) ?></span>
            </div>
            <div class="d-flex align-items-center gap-3 py-2 border-bottom border-light">
                <i class="fas fa-calendar-alt text-success" style="width: 20px; text-align: center;"></i>
                <span>Bergabung: <?= formatDate($user['created_at']) ?></span>
            </div>
            <?php if ($user['updated_at']): ?>
                <div class="d-flex align-items-center gap-3 py-2">
                    <i class="fas fa-sync-alt text-success" style="width: 20px; text-align: center;"></i>
                    <span>Diperbarui: <?= formatDate($user['updated_at']) ?></span>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
