
<p><?= t('commentions.snippet.form.help.intro') ?></p>

<dl>
  <?= t('commentions.snippet.form.help.common') ?>

  <?php if (option('sgkirby.commentions.allowlinks')): ?>
    <?= t('commentions.snippet.form.help.links') ?>
    <?php if (option('sgkirby.commentions.autolinks')): ?>
      <?= t('commentions.snippet.form.help.autolinks') ?>
    <?php endif ?>
  <?php endif ?>

</dl>
