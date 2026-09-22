<?php
/**
 * ============================================================================
 * Users - Add User
 * ============================================================================
 *
 * Description: Cari pengguna HRIS dan tetapkan role (single-page minimalis)
 *
 * Required: $roles — array of role objects (raw_id, name, slug, description)
 * Optional: none
 * Template: template/index
 */
?>
<?= $this->extend('template/index') ?>
<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="mb-1">Tambah Pengguna</h1>
        <p class="text-secondary mb-0" style="font-size:13px">Cari pengguna HRIS dan tetapkan role akses</p>
    </div>
    <a href="<?= site_url('/users') ?>" class="sap-btn sap-btn-secondary">
        <i class="fas fa-arrow-left me-1"></i> Kembali
    </a>
</div>

<div class="sap-card uw-card">
    <div class="sap-card-header">
        <h5 class="mb-0"><i class="fas fa-user-plus me-2"></i>Tambah Pengguna Baru</h5>
    </div>
    <div class="sap-card-body">

        <!-- ════════════ Cari Pengguna ════════════ -->
        <div class="uw-search">
            <label class="form-label fw-semibold" for="uwSearch">Cari pengguna HRIS</label>
            <div class="uw-search-wrap">
                <i class="fas fa-search uw-search-icon"></i>
                <input type="text" class="form-control uw-search-input" id="uwSearch"
                       placeholder="Ketik nama, username, atau departemen..."
                       autocomplete="off"
                       role="combobox"
                       aria-label="Cari pengguna HRIS"
                       aria-expanded="false"
                       aria-controls="uwPanel"
                       aria-autocomplete="list"
                       aria-activedescendant="">
                <button type="button" class="uw-search-clear" id="uwBtnClear"
                        aria-label="Hapus pencarian" style="display:none">
                    <i class="fas fa-times"></i>
                </button>
                <span class="uw-search-spinner" id="uwSpinner" style="display:none">
                    <i class="fas fa-spinner fa-spin"></i>
                </span>
            </div>
            <div class="uw-hint" id="uwHint">Ketik minimal 2 karakter</div>
            <div class="uw-count" id="uwCount" aria-live="polite"></div>
            <div class="uw-panel" id="uwPanel" role="listbox" aria-label="Hasil pencarian" style="display:none"></div>
        </div>

        <!-- ════════════ Pengguna Terpilih (compact chip) ════════════ -->
        <div class="uw-chip" id="uwChip" style="display:none">
            <div class="uw-chip-avatar" id="uwChipAvatar">?</div>
            <div class="uw-chip-info">
                <div class="uw-chip-name" id="uwChipName">—</div>
                <div class="uw-chip-meta">
                    <span id="uwChipUsername"></span>
                    <span class="uw-dot">&middot;</span>
                    <span id="uwChipDept"></span>
                    <span class="uw-dot">&middot;</span>
                    <span class="uw-mono" id="uwChipId"></span>
                </div>
            </div>
            <button type="button" class="uw-chip-remove" id="uwBtnRemove"
                    title="Hapus pilihan" aria-label="Hapus pengguna yang dipilih">
                <i class="fas fa-times"></i>
            </button>
            <div class="uw-exists-warn" id="uwExists" style="display:none">
                <i class="fas fa-exclamation-triangle me-1"></i>
                <span id="uwExistsText"></span>
            </div>
        </div>

        <!-- ════════════ Divider ════════════ -->
        <hr class="uw-divider" id="uwDivider" style="display:none">

        <!-- ════════════ Pilih Role ════════════ -->
        <div class="uw-role" id="uwRole" style="display:none">
            <label class="form-label fw-semibold" for="uwRoleId">Role <span class="text-danger">*</span></label>
            <select class="form-select uw-role-select" id="uwRoleId" aria-required="true"
                    aria-describedby="uwRoleHint uwRoleError">
                <option value="">— Pilih Role —</option>
                <?php foreach ($roles as $r): ?>
                    <option value="<?= esc($r['raw_id']) ?>"><?= esc($r['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <div class="uw-hint uw-role-hint" id="uwRoleHint"></div>
            <div class="uw-error" id="uwRoleError" role="alert"></div>
        </div>

        <!-- ════════════ Divider ════════════ -->
        <hr class="uw-divider" id="uwDivider2" style="display:none">

        <!-- ════════════ Ringkasan ════════════ -->
        <div class="uw-summary" id="uwSummary" style="display:none">
            <div class="uw-summary-grid">
                <div class="uw-summary-col">
                    <div class="uw-summary-label">User ID</div>
                    <div class="uw-summary-value" id="uwSumId">—</div>
                </div>
                <div class="uw-summary-col">
                    <div class="uw-summary-label">Nama</div>
                    <div class="uw-summary-value" id="uwSumName">—</div>
                </div>
                <div class="uw-summary-col">
                    <div class="uw-summary-label">Email</div>
                    <div class="uw-summary-value uw-mono" id="uwSumEmail">—</div>
                </div>
                <div class="uw-summary-col">
                    <div class="uw-summary-label">Role</div>
                    <div class="uw-summary-value" id="uwSumRole">—</div>
                </div>
            </div>
        </div>

        <!-- ════════════ Actions ════════════ -->
        <div class="uw-actions" id="uwActions" style="display:none">
            <button type="button" class="sap-btn sap-btn-primary" id="uwBtnSubmit"
                    onclick="UserAdd.submit()" disabled>
                <i class="fas fa-plus me-1"></i> Tambah Pengguna
            </button>
            <button type="button" class="sap-btn sap-btn-secondary" onclick="UserAdd.reset()">
                <i class="fas fa-times me-1"></i> Batal
            </button>
        </div>

    </div>
</div>

<!-- Hidden fields for JS -->
<input type="hidden" id="uwUserId">
<input type="hidden" id="uwUserName">
<input type="hidden" id="uwUserEmail">

<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= asset_url('public/assets/css/page/users/add_user.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
var UW_ROLES = <?= json_encode(array_map(fn($r) => [
    'raw_id' => (int)$r['raw_id'],
    'name'   => $r['name'],
    'slug'   => $r['slug'] ?? '',
    'description' => $r['description'] ?? '',
    'user_count'  => (int)($r['user_count'] ?? 0),
], $roles), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
</script>
<script defer src="<?= asset_url('public/assets/js/page/users/add_user.js') ?>"></script>
<?= $this->endSection() ?>
