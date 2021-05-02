<?php
/*
 * This snippet displays the help text for the main text field in snippet `form-field.php`.
 *
 * To modify, copy this file into the folder `site/snippets/commentions` of your Kirby site.
 * When updating the Commentions plugin to a new version, you may have to implement small changes to your copy
 * to enable new or modified functionalities.
 */
?>

<?php if ($formattingEnabled): ?>
  <details class="commentions-form-help">
    <summary><?= t('commentions.snippet.form.help.title') ?></summary>

    <p><?= t('commentions.snippet.form.help.intro') ?></p>

    <dl>
      <?= t('commentions.snippet.form.help.common') ?>

      <?php if ($allowlinks): ?>
        <?= t('commentions.snippet.form.help.links') ?>
        <?php if ($autolinks): ?>
          <?= t('commentions.snippet.form.help.autolinks') ?>
        <?php endif ?>
      <?php endif ?>

    </dl>

  </details>
<?php else: ?>
  <div class="commentions-form-help">
    <p><?= t('commentions.snippet.form.help.noformatting') ?></p>
  </div>
<?php endif ?>
