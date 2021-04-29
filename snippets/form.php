
  <div class="commentions-form">

    <?php if (\sgkirby\Commentions\Commentions::accepted($page, 'comments')) : ?>

    <?php if (!empty($attrs['collapse']) || !empty($attrs['collapse-comments'])) : ?>
    <details <?= (!empty($attrs['open']) || !empty($attrs['open-comments'])) ? ' open' : '' ?>>
    <summary>
    <?php endif ?>

    <h3 id="commentions-form-comment"><?= t('commentions.snippet.form.ctacomment') ?></h3>

    <?php if (!empty($attrs['collapse']) || !empty($attrs['collapse-comments'])) : ?>
    </summary>
    <?php endif ?>

    <form
      action="<?= $page->url() ?><?= !empty($attrs['jump']) ? '#' . $attrs['jump'] : '' ?>"
      method="post"
      <?= $attrs['novalidate'] === true ? 'novalidate' : ''?>
    >
      <?php snippet('commentions/form-fields',  ['fields' => $fields]); ?>
      <input type="submit" name="submit" value="<?= t('commentions.snippet.form.submitcomment') ?>">
    </form>

    <?php if (!empty($attrs['collapse']) || !empty($attrs['collapse-comments'])) : ?>
    </details>
    <?php endif ?>

    <?php endif; ?>


    <?php if (\sgkirby\Commentions\Commentions::accepted($page, 'webmentions')) : ?>

    <?php if (!empty($attrs['collapse']) || !empty($attrs['collapse-webmentions'])) : ?>
    <details <?= (!empty($attrs['open']) || !empty($attrs['open-webmentions'])) ? ' open' : '' ?>>
    <summary>
    <?php endif ?>

    <h3 id="commentions-form-webmention"><?= t('commentions.snippet.form.ctawebmention') ?></h3>

    <?php if (!empty($attrs['collapse']) || !empty($attrs['collapse-webmentions'])) : ?>
    </summary>
    <?php endif ?>

    <form action="<?= kirby()->urls()->base() . '/' . option('sgkirby.commentions.endpoint') ?>" method="post">

      <div class="commentions-form-source">
        <label for="source"><?= t('commentions.snippet.form.responseurl') ?></label>
        <input type="url" id="source" name="source" pattern=".*http.*" required>
      </div>

      <input type="hidden" name="target" value="<?= $page->url() ?>">
      <input type="hidden" name="manualmention" value="true">

      <input type="submit" name="submit" value="<?= t('commentions.snippet.form.submitwebmention') ?>">

    </form>

    <?php if (!empty($attrs['collapse']) || !empty($attrs['collapse-webmentions'])) : ?>
    </details>
    <?php endif ?>

    <?php endif; ?>


  </div>
