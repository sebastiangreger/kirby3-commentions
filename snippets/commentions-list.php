
	<div class="commentions-list">
		
		<?php if ( $page->comments()->toStructure()->count() > 0 ) : ?>
		
		   <h3>Comments</h3>

		   <?php foreach ( $page->comments()->toStructure() as $comment ) :
		   
				if ($comment->approved() != "true")
					continue;
				
				?>
				
				<div id="<?= 'comment-' . $comment->number() ?>">
					<h4>
						<?php if ( $comment->type() == 'webmention' ) : ?><a href="<?= $comment->website() ?>"><?php endif; ?>
						<?= htmlspecialchars( $comment->name() ) ?>
						<?php if ( $comment->type() == 'webmention' ) : ?></a><?php endif; ?>
					</h4>
					<p>
						<?= $comment->timestamp() ?>
						<?php if ( $comment->type() == 'webmention' ) : ?>
						(webmention from: <a href="<?= $comment->source() ?>"><?= $comment->source() ?></a>)
						<?php endif; ?>
					</p>
					<?= $comment->message()->html() ?>
			   </div>

		   <?php endforeach; ?>

		<?php endif; ?>

	</div>
