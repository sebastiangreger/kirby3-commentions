      <?php foreach($fields as $name => $data): ?>

        <?php if ($data['type'] != 'hidden'): ?>
          <div class="commentions-form-<?= $name ?>">
            <label for="<?= $data['id'] ?>"><?= $data['label'] ?></label>
            <p class="alert" id="commentions-form-<?= $data['id'] ?>-errors"><?= (isset($data['error'])) ? $data['error'] : '' ?></p>
        <?php endif; ?>

          <?php if ($data['type'] === 'textarea'): ?>
            <textarea
              id="<?= $data['id'] ?>"
              name="<?= $data['id'] ?>"
              aria-describedby="commentions-form-<?= $data['id'] ?>-errors"
              rows="8"
              <?php foreach(['required', 'placeholder','autofocus'] as $attribute): ?>
                <?= (!empty($data[$attribute]) ? ' ' . $attribute . '="' . $data[$attribute] . '"' : '') ?>
              <?php endforeach ?>
            ><?= $data['value'] ?? '' ?></textarea>
            <?php if($name === 'text') commentions('help'); ?>

          <?php else: ?>
            <input
              type="<?= $data['type'] ?>"
              id="<?= $data['id'] ?>"
              name="<?= $data['id'] ?>"
              aria-describedby="commentions-form-<?= $data['id'] ?>-errors"
              <?php foreach(['required', 'value', 'autocomplete', 'placeholder','autofocus'] as $attribute): ?>
                <?= (!empty($data[$attribute]) ? ' ' . $attribute . '="' . $data[$attribute] . '"' : '') ?>
              <?php endforeach ?>
            >

          <?php endif; ?>

        <?php if ($data['type'] != 'hidden'): ?>
          </div>
        <?php endif; ?>

      <?php endforeach; ?>
