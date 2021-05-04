<?php
/*
 * This snippet displays the fields in the comment form, called from the `form.php` snippet.
 *
 * Modifying this snippet is strongly advised against, as this might change frequently between minor versions
 * of the Commentions plugin. If you need additional options, you could suggest them in the GitHub Issues first.
 * If you still want to override this with your own, copy this file to the `site/snippets/commentions` folder.
 */
?>

        <?php if ($type != 'hidden'): ?>
          <div class="commentions-form-<?= $id ?>">
            <label for="<?= $id ?>"><?= $label ?></label>
            <?php if (isset($error)) : ?>
            <p class="alert" id="commentions-form-<?= $id ?>-errors"><?= t('commentions.snippet.form.error') ?>: <?= $error ?></p>
            <?php endif ?>
        <?php endif; ?>

          <?php if ($type === 'textarea'): ?>
            <textarea
              id="<?= $id ?>"
              name="<?= $id ?>"
              rows="8"
              <?php foreach(['required', 'placeholder','autofocus'] as $attribute): ?>
                <?= (!empty($data[$attribute]) ? ' ' . $attribute . '="' . $data[$attribute] . '"' : '') ?>
              <?php endforeach ?>
              <?php if (isset($error)) : ?>
              aria-describedby="commentions-form-<?= $id ?>-errors"
              <?php endif ?>
            ><?= $value ?? '' ?></textarea>
            <?php if($id === 'text') commentions('help'); ?>

          <?php else: ?>
            <input
              type="<?= $type ?>"
              id="<?= $id ?>"
              name="<?= $id ?>"
              <?php foreach(['required', 'value', 'autocomplete', 'placeholder','autofocus'] as $attribute): ?>
                <?= (!empty($data[$attribute]) ? ' ' . $attribute . '="' . $data[$attribute] . '"' : '') ?>
              <?php endforeach ?>
              <?php if(isset($error) && $type != 'hidden'): ?>
                aria-describedby="commentions-form-<?= $id ?>-errors"
              <?php endif ?>
            >

          <?php endif; ?>

        <?php if ($type != 'hidden'): ?>
          </div>
        <?php endif; ?>
