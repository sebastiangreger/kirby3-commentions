<?php
/*
 * This snippet displays the comment form, called by the `<?= commentions() ?>` or `<?= commentions('form') ?>` helper.
 *
 * To modify, copy this file into the folder `site/snippets/commentions` of your Kirby site.
 * When updating the Commentions plugin to a new version, you may have to implement small changes to your copy
 * to enable new or modified functionalities.
 */

use sgkirby\Commentions\Commentions as Commentions;
use sgkirby\Commentions\Frontend as Frontend;

?>

  <div class="commentions-form <?= $attrs['class'] ?? '' ?>" id="<?= $attrs['id'] ?? '' ?>">

    <?php if (Commentions::accepted($page, 'comments')) : ?>

    <?php if (!empty($attrs['collapse']) || !empty($attrs['collapse-comments'])) : ?>
    <details <?= (!empty($attrs['open']) || !empty($attrs['open-comments'])) ? ' open' : '' ?>>
    <summary>
    <?php endif ?>

    <h3 id="commentions-form-comment"><?= Frontend::uistring('snippet.form.ctacomment') ?></h3>

    <?php if (!empty($attrs['collapse']) || !empty($attrs['collapse-comments'])) : ?>
    </summary>
    <?php endif ?>

    <form
      action="<?= $page->url() ?><?= !empty($attrs['jump']) ? '#' . $attrs['jump'] : '' ?>"
      method="post"
      <?= $attrs['novalidate'] === true ? 'novalidate' : ''?>
    >

      <?php foreach($fields as $field):
        snippet('commentions/field', $field);
      endforeach; ?>

      <input type="submit" name="submit" value="<?= Frontend::uistring('snippet.form.submitcomment') ?>">
    </form>

    <?php if (!empty($attrs['collapse']) || !empty($attrs['collapse-comments'])) : ?>
    </details>
    <?php endif ?>

    <?php endif; ?>


    <?php if (Commentions::accepted($page, 'webmentions')) : ?>

    <?php if (!empty($attrs['collapse']) || !empty($attrs['collapse-webmentions'])) : ?>
    <details <?= (!empty($attrs['open']) || !empty($attrs['open-webmentions'])) ? ' open' : '' ?>>
    <summary>
    <?php endif ?>

    <h3 id="commentions-form-webmention"><?= Frontend::uistring('snippet.form.ctawebmention') ?></h3>

    <?php if (!empty($attrs['collapse']) || !empty($attrs['collapse-webmentions'])) : ?>
    </summary>
    <?php endif ?>

    <form action="<?= kirby()->urls()->base() . '/' . option('sgkirby.commentions.endpoint') ?>" method="post">

      <div class="commentions-form-source">
        <label for="source"><?= Frontend::uistring('snippet.form.responseurl') ?></label>
        <input type="url" id="source" name="source" pattern=".*http.*" required>
      </div>

      <input type="hidden" name="target" value="<?= $page->url() ?>">
      <input type="hidden" name="manualmention" value="true">

      <input type="submit" name="submit" value="<?= Frontend::uistring('snippet.form.submitwebmention') ?>">

    </form>

    <?php if (!empty($attrs['collapse']) || !empty($attrs['collapse-webmentions'])) : ?>
    </details>
    <?php endif ?>

    <?php endif; ?>


  </div>
