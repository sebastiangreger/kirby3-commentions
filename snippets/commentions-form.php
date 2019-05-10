
	<style>
		.commentions-form { margin-top:4em; }
			.commentions-form form > div { margin:2em 0; }
			.commentions-form label, .commentions-form input, .commentions-form textarea { display:block; width:100%; }
			.commentions-form input, .commentions-form textarea { background:white; border:2px solid black; padding:.5em; font-size:100%; }
			.commentions-form input[type="submit"] { background:black; color:white; }
	</style>

	<div class="commentions-form">
		
		<form action="<?= $page->url() ?>" method="post">

			<h3>Leave a comment</h3>

			<div>
				<label for="name">Name (optional)</label>
				<input type="text" id="name" name="name">
			</div>

			<div>
				<label for="message">Comment <abbr title="required">*</abbr></label>
				<textarea id="message" name="message" rows="8" required></textarea>
			</div>

			<input type="submit" name="submit" value="Submit">

		</form>

	</div>
