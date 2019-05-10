
	<style>
		.commentions-feedback { margin-top:4em; }
			.commentions-feedback p { padding:1em; }
			.commentions-feedback p.alert { color:#ff0000; background-color:#FFD8D8; }
			.commentions-feedback p.success { color:#0D6F00; background-color:#D2FFCC; }
	</style>

	<div class="commentions-feedback">
		
		<?php if ( isset( $alert ) ): ?>
			<?php foreach ( $alert as $message ): ?>
				<p class="alert"><?= html( $message ) ?></p>
			<?php endforeach ?>
		<?php endif ?>

		<?php if ( get('thx') == 'queued' ) : ?>
			<p class="success"><?= $success ?></p>
		<?php endif ?>

	</div>
