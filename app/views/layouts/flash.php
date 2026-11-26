<?php if (!empty($error)): ?>
    <div class="notice error"><?= e($error) ?></div>
<?php endif; ?>

<?php if ($okMsg = okMessage()): ?>
    <div class="notice"><?= e($okMsg) ?></div>
<?php endif; ?>
