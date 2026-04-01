    </div><!-- /page content -->
  </main>
</div><!-- /flex wrapper -->

<!-- Toast notifications -->
<?php if (!empty($_SESSION['flash'])): ?>
<div id="toast"
     class="fixed bottom-6 right-6 z-50 flex items-center gap-3 px-5 py-3.5
            rounded-2xl shadow-lg text-[13px] font-semibold
            <?= $_SESSION['flash']['type'] === 'ok'
                ? 'bg-green-dark text-white'
                : 'bg-red-600 text-white' ?>">
  <?php if ($_SESSION['flash']['type'] === 'ok'): ?>
  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
       stroke-width="2.5" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
  <?php else: ?>
  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
       stroke-width="2.5" stroke-linecap="round">
    <circle cx="12" cy="12" r="10"/>
    <line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
  </svg>
  <?php endif; ?>
  <?= Sanitize::html($_SESSION['flash']['msg']) ?>
</div>
<script>setTimeout(() => document.getElementById('toast')?.remove(), 4000)</script>
<?php unset($_SESSION['flash']); endif; ?>

</body>
</html>
