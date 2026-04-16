</div><!-- /page-content -->
    </main>
</div><!-- /admin-wrapper -->

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Toast notifications -->
<?php if (!empty($_SESSION['flash'])): ?>
<?php $isOk = $_SESSION['flash']['type'] === 'ok'; ?>
<div id="toast" class="<?= $isOk ? 'toast-ok' : 'toast-err' ?>">
    <?php if ($isOk): ?>
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
         stroke-width="2.5" stroke-linecap="round">
        <polyline points="20 6 9 17 4 12"/>
    </svg>
    <?php else: ?>
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
         stroke-width="2.5" stroke-linecap="round">
        <circle cx="12" cy="12" r="10"/>
        <line x1="12" y1="8" x2="12" y2="12"/>
        <line x1="12" y1="16" x2="12.01" y2="16"/>
    </svg>
    <?php endif; ?>
    <?= Sanitize::html($_SESSION['flash']['msg']) ?>
</div>
<script>setTimeout(() => document.getElementById('toast')?.remove(), 4000)</script>
<?php unset($_SESSION['flash']); endif; ?>

</body>
</html>