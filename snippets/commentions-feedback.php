<div class="commentions-feedback">

  <?php if (isset($alert)): ?>
    <?php foreach ($alert as $message): ?>
      <p class="alert"><?= html($message) ?></p>
    <?php endforeach ?>
  <?php endif ?>

  <?php if (get('thx') == 'queued') : ?>
    <p class="success"><?= $success ?></p>
  <?php endif ?>

  <?php if (get('thx') == 'accepted') : ?>
    <p class="success"><?= $accepted ?></p>
  <?php endif ?>

</div>
