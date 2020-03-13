
	<div class="commentions-form">

		<?php if ( option( 'sgkirby.commentions.hideforms' ) ) : ?>

		<h3 class="expander" id="commentions-form-comment">
			<button aria-expanded="false">
				<svg aria-hidden="true" focusable="false" width="16px" viewBox="0 0 10 10"><rect class="vert" height="8" width="2" y="1" x="4"/><rect height="2" width="8" y="4" x="1"/></svg>
				<span>Leave a comment</span>
			</button>
		</h3>
		
		<?php else : ?>

		<h3 id="commentions-form-comment">Leave a comment</h3>

		<?php endif; ?>

		<form action="<?= $page->url() ?>" method="post" <?php if ( option( 'sgkirby.commentions.hideforms' ) ) echo 'class="expandertarget"'; ?>>

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

		<?php if ( option( 'sgkirby.commentions.expand' ) ) : ?>

		<h3 class="expander" id="commentions-form-webmention">
			<button aria-expanded="false">
				<svg aria-hidden="true" focusable="false" width="16px" viewBox="0 0 10 10"><rect class="vert" height="8" width="2" y="1" x="4"/><rect height="2" width="8" y="4" x="1"/></svg>
				<span>Replied on your own website? Send a webmention!</span>
			</button>
		</h3>

		<?php else : ?>

		<h3 id="commentions-form-comment">Replied on your own website? Send a webmention!</h3>

		<?php endif; ?>

		<form action="<?= site()->url() . '/' . option( 'sgkirby.commentions.endpoint', 'webmention-endpoint' ) ?>" method="post" <?php if ( option( 'sgkirby.commentions.expand' ) ) echo 'class="expandertarget"'; ?>>

			<div class="commentions-form-source">
				<label for="source">URL of the response on your site (make sure it has a hyperlink to this page)</label>
				<input type="url" id="source" name="source" pattern=".*http.*" required>
			</div>

			<input type="hidden" name="target" value="<?= $page->url() ?>">
			<input type="hidden" name="manualmention" value="true">

			<input type="submit" name="submit" value="Send webmention">

		</form>

	</div>
