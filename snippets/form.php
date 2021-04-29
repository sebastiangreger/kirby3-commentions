
  <div class="commentions-form">

    <?php if (\sgkirby\Commentions\Commentions::accepted($page, 'comments')) : ?>

    <?php if (option('sgkirby.commentions.hideforms')) : ?>

    <h3 class="expander" id="commentions-form-comment">
      <button aria-expanded="false">
        <svg aria-hidden="true" focusable="false" width="16px" viewBox="0 0 10 10"><rect class="vert" height="8" width="2" y="1" x="4"/><rect height="2" width="8" y="4" x="1"/></svg>
        <span><?= t('commentions.snippet.form.ctacomment') ?></span>
      </button>
    </h3>

    <?php else : ?>

    <h3 id="commentions-form-comment"><?= t('commentions.snippet.form.ctacomment') ?></h3>

    <?php endif; ?>

    <form action="<?= $page->url() ?>" method="post" novalidate <?= option('sgkirby.commentions.hideforms') ? 'class="expandertarget"' : '' ?>>

      <?php snippet('commentions/form-fields',  ['fields' => $fields]); ?>

      <input type="submit" name="submit" value="<?= t('commentions.snippet.form.submitcomment') ?>">

    </form>

    <?php endif; ?>

    <?php if (\sgkirby\Commentions\Commentions::accepted($page, 'webmentions')) : ?>

    <?php if (option('sgkirby.commentions.expand')) : ?>

    <h3 class="expander" id="commentions-form-webmention">
      <button aria-expanded="false">
        <svg aria-hidden="true" focusable="false" width="16px" viewBox="0 0 10 10"><rect class="vert" height="8" width="2" y="1" x="4"/><rect height="2" width="8" y="4" x="1"/></svg>
        <span><?= t('commentions.snippet.form.ctawebmention') ?></span>
      </button>
    </h3>

    <?php else : ?>

    <h3 id="commentions-form-webmention"><?= t('commentions.snippet.form.ctawebmention') ?></h3>

    <?php endif; ?>

    <form action="<?= kirby()->urls()->base() . '/' . option('sgkirby.commentions.endpoint') ?>" method="post" <?= option('sgkirby.commentions.expand') ? 'class="expandertarget"' : '' ?>>

      <div class="commentions-form-source">
        <label for="source"><?= t('commentions.snippet.form.responseurl') ?></label>
        <input type="url" id="source" name="source" pattern=".*http.*" required>
      </div>

      <input type="hidden" name="target" value="<?= $page->url() ?>">
      <input type="hidden" name="manualmention" value="true">

      <input type="submit" name="submit" value="<?= t('commentions.snippet.form.submitwebmention') ?>">

    </form>

    <?php endif; ?>

  </div>
