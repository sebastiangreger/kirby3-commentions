<?php
/*
 * This snippet displays the list of comments and webmentions, called by the `<?= commentions() ?>`
 * or `<?= commentions('list') ?>` helpers.
 *
 * To modify, copy this file into the folder `site/snippets/commentions` of your Kirby site.
 * When updating the Commentions plugin to a new version, you may have to implement small changes to your copy
 * to enable new or modified functionalities.
 *
 * You can also access a raw array of comments and webmentions using the `$page->commentions()` page method
 * and roll your own custom snippet or template instead.
 */

use sgkirby\Commentions\Frontend as Frontend;

?>

<div class="commentions-list <?= $attrs['class'] ?? '' ?>" id="<?= $attrs['id'] ?? '' ?>">

  <?php foreach ($reactions as $type => $group) : ?>
    <h3><?= $group->label() ?></h3>

    <ul class="commentions-list-reactions commentions-list-reactions-<?= $type ?>">
      <?php foreach ($group->items() as $comment) : ?>

        <li>
          <a href="<?= $comment->source() ?>"><?= $comment->name()->html() ?></a>
        </li>

      <?php endforeach ?>
    </ul>
  <?php endforeach ?>

  <?php if ($comments->count() > 0) : ?>

    <?php if (!empty($attrs['collapse']) || !empty($attrs['collapse-list'])) : ?>
    <details <?= (!empty($attrs['open']) || !empty($attrs['open-list'])) ? ' open' : '' ?>>
    <summary>
    <?php endif ?>

    <h3><?= Frontend::uistring('snippet.list.comments') ?></h3>

    <?php if (!empty($attrs['collapse']) || !empty($attrs['collapse-list'])) : ?>
    </summary>
    <?php endif ?>

    <<?= $attrs['listtype'] ?>>
        <?php foreach ($comments as $comment) : ?>

        <li class="commentions-list-item commentions-list-item-<?= $comment->type() ?><?= r($comment->isAuthenticated(), ' commentions-list-item-authenticated') ?>">
          <h4>
            <?= $comment->sourceFormatted() ?>
          </h4>

          <p class="commentions-list-date">
            <?= $comment->dateFormatted() ?>
          </p>

          <?php if ($comment->text()->isNotEmpty()): ?>
            <div class="commentions-list-message">
              <?= $comment->text() ?>
            </div>
          <?php endif ?>
          </li>

        <?php endforeach ?>
    </<?= $attrs['listtype'] ?>>

    <?php if (!empty($attrs['collapse']) || !empty($attrs['collapse-list'])) : ?>
    </details>
    <?php endif ?>

  <?php endif ?>

</div>