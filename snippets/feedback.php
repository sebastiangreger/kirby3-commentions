<?php
/*
 * This snippet displays success and error messages for the comment form.
 *
 * To modify, copy this file into the folder `site/snippets/commentions` of your Kirby site.
 * When updating the Commentions plugin to a new version, you may have to implement small changes to your copy
 * to enable new or modified functionalities.
 */
?>

<div class="commentions-feedback <?= $attrs['class'] ?? '' ?>" id="<?= $attrs['id'] ?? '' ?>">

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
