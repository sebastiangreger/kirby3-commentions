<div class="commentions-list">

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
    <h3><?= t('commentions.snippet.list.comments') ?></h3>

    <ul>
        <?php foreach ($comments as $comment) : ?>

        <li class="commentions-list-item commentions-list-item-<?= $comment->type() ?><?= r($comment->isAuthenticated(), ' commentions-list-item-authenticated') ?>">
          <h4>
            <?= $comment->sourceFormatted() ?>
          </h4>

          <p class="commentions-list-date">
            <?= $comment->dateFormatted() ?>
          </p>

          <?php if ($comment->safeText()->isNotEmpty()): ?>
            <div class="commentions-list-message">
              <?= $comment->safeText() ?>
            </div>
          <?php endif ?>
          </li>

        <?php endforeach ?>
    </ul>
  <?php endif ?>

</div>
