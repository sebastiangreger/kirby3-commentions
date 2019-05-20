
	<div class="commentions-form">
		
		<form action="<?= $page->url() ?>" method="post">

			<h3>Leave a comment</h3>

			<?php if ( in_array( 'name', $fields ) ) : ?>
			<div class="commentions-form-name">
				<label for="name">Name (optional)</label>
				<input type="text" id="name" name="name">
			</div>
			<?php endif; ?>

			<?php if ( in_array( 'email', $fields ) ) : ?>
			<div class="commentions-form-email">
				<label for="email">E-Mail (optional; if you'd like a personal reply)</label>
				<input type="email" id="email" name="email">
			</div>
			<?php endif; ?>

			<div class="commentions-form-honeypot">
				<label for="website">Please leave this field empty!</label>
				<input type="url" id="website" name="website">
			</div>

			<?php if ( in_array( 'url', $fields ) ) : ?>
			<div class="commentions-form-website">
				<label for="realwebsite">Website (optional; publicly linked if provided)</label>
				<input type="url" id="realwebsite" name="realwebsite">
			</div>
			<?php endif; ?>

			<div class="commentions-form-message">
				<label for="message">Comment <abbr title="required">*</abbr></label>
				<textarea id="message" name="message" rows="8" required></textarea>
			</div>

			<?php /* "commentions" value enables identifying commentions submissions in route:before hook + creation timestamp is used for spam protection */ ?>
			<input type="hidden" name="commentions" value="<?php e ( !$page->isCacheable(), time(), 0 ) ?>">

			<input type="submit" name="submit" value="Submit">

		</form>

		<form action="<?= site()->url() . '/' . option( 'sgkirby.commentions.endpoint', 'webmention-endpoint' ) ?>" method="post">

			<h3>Replied on your own website? Send a webmention!</h3>

			<div class="commentions-form-source">
				<label for="source">URL of the response on your site (make sure it has a hyperlink to this page)</label>
				<input type="url" id="source" name="source" pattern=".*http.*" required>
			</div>

			<input type="hidden" name="target" value="<?= $page->url() ?>">
			<input type="hidden" name="manualmention" value="true">

			<input type="submit" name="submit" value="Send webmention">

		</form>

	</div>
