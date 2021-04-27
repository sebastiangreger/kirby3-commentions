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
