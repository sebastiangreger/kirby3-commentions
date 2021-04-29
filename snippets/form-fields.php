      <?php foreach($fields as $name => $data): ?>

        <?php if ($data['type'] != 'hidden'): ?>
          <div class="commentions-form-<?= $name ?>">
            <label for="<?= $data['id'] ?>"><?= $data['label'] ?></label>
        <?php endif; ?>

          <?php if ($data['type'] === 'textarea'): ?>
            <textarea
              id="<?= $data['id'] ?>"
              name="<?= $data['id'] ?>"
              rows="8"
              required
            ></textarea>
            <?php if($name === 'text') commentions('help'); ?>

          <?php else: ?>
            <input
              type="<?= $data['type'] ?>"
              id="<?= $data['id'] ?>"
              name="<?= $data['id'] ?>"
              <?php foreach(['required', 'value', 'autocomplete', 'placeholder'] as $attribute): ?>
                <?= (!empty($data[$attribute]) ? ' ' . $attribute . '="' . $data[$attribute] . '"' : '') ?>
              <?php endforeach ?>
            >

          <?php endif; ?>

        <?php if ($data['type'] != 'hidden'): ?>
          </div>
        <?php endif; ?>

      <?php endforeach; ?>