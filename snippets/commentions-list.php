
	<div class="commentions-list">
		
		<?php if ( $page->comments()->toStructure()->count() > 0 ) : ?>
		
		   <h3>Comments</h3>
		   
		   <ul>

			   <?php foreach ( $page->comments()->toStructure() as $comment ) :
			   
					if ($comment->approved() != "true")
						continue;
					
					?>

					<li class="commentions-list-type-<?= $comment->type() ?>">

						<h4>

							<?php
							if ( htmlspecialchars( $comment->name() ) != '' )
								$name = htmlspecialchars( $comment->name() );
							else
								$name = 'Anonymous';
							?>

							<?php if ( $comment->website() != '' ) : ?>
								<a href="<?= $comment->website() ?>"><?= $name ?></a>
							<?php else : ?>
								<?= $name ?>
							<?php endif;
							
							$domain = str_replace( 'www.', '', parse_url($comment->source(), PHP_URL_HOST) );
							
							switch ( $comment->type() ) {
								case 'webmention':
								case 'mention':
								case 'trackback':
								case 'pingback':
									echo 'mentioned this at <a href="' . $comment->source() . '">' . $domain . '</a>';
									break;
								case 'like':
									echo 'liked this at <a href="' . $comment->source() . '">' . $domain . '</a>';
									break;
								case 'bookmark':
									echo 'bookmarked this at <a href="' . $comment->source() . '">' . $domain . '</a>';
									break;
								case 'reply':
									echo 'replied at <a href="' . $comment->source() . '">' . $domain . '</a>:';
									break;
								default:
									echo ":";
									break;
							}
						
							?>
						
						</h4>

						<p class="commentions-list-date">
							<?= date ( 'Y-m-d H:i', strtotime( $comment->timestamp() ) ) ?>
						</p>

						<?php if ( $comment->type() == 'reply' ) : ?>
						<div class="commentions-list-message">
							<?= $comment->message()->kt() ?>
						</div>
						<?php endif; ?>

				   </li>

			   <?php endforeach; ?>

			</ul>

		<?php endif; ?>

	</div>
