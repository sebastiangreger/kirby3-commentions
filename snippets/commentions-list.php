
	<div class="commentions-list">

		<?php if ( sizeof( $reactions ) > 0 ) : ?>

			<?php foreach ( $reactions as $groupname => $group ) : ?>

				<?php if ( is_array( $group ) ) : ?>

					<h3><?= $groupname ?></h3>

					<ul class="commentions-list-reactions">

						<?php foreach ( $group as $comment ) : ?>

							<li>
								<a href="<?= $comment['source'] ?>">
									<?=
										(
											( isset( $comment['name'] ) && $comment['name'] != null )
											? htmlspecialchars( $comment['name'] )
											: t('commentions.snippet.list.anonymous')
										) ?>
								</a>
							</li>

						<?php endforeach; ?>

					</ul>

				<?php endif; ?>

			<?php endforeach; ?>

		<?php endif; ?>

		<?php if ( sizeof( $comments ) > 0 ) : ?>
		
			<h3><?= t('commentions.snippet.list.comments') ?></h3>
		   
			<ul>

			   <?php foreach ( $comments as $comment ) : ?>

					<li class="commentions-list-type-<?= $comment['type'] ?><?php if ( isset( $comment['authenticated'] ) && $comment['authenticated'] == 'true' ) echo ' authenticated' ?>">

						<h4>

							<?php $name = (
								( isset( $comment['name'] ) && $comment['name'] != null )
								? htmlspecialchars( $comment['name'] )
								: t('commentions.snippet.list.anonymous')
							) ?>

							<?php if ( isset( $comment['website'] ) && $comment['website'] != '' ) : ?>
								<a href="<?= $comment['website'] ?>" rel="noopener"><?= $name ?></a>
							<?php else : ?>
								<?= $name ?>
							<?php endif;
							if ( isset( $comment['source'] ) )
								$domain = str_replace( 'www.', '', parse_url( $comment['source'], PHP_URL_HOST ) );
							
							switch ( $comment['type'] ) {
								case 'webmention':
								case 'mention':
								case 'trackback':
								case 'pingback':
									echo t('commentions.snippet.list.at');
									if ( isset( $domain ) )
										echo ' ' . t('commentions.snippet.list.mentioned') . ' <a href="' . $comment['source'] . '" rel="noopener">' . $domain . '</a>';
									break;
								case 'like':
									echo t('commentions.snippet.list.liked') . ' <a href="' . $comment['source'] . '" rel="noopener">' . $domain . '</a>';
									break;
								case 'bookmark':
									echo t('commentions.snippet.list.bookmarked') . ' <a href="' . $comment['source'] . '" rel="noopener">' . $domain . '</a>';
									break;
								case 'reply':
									echo t('commentions.snippet.list.replies') . ' <a href="' . $comment['source'] . '" rel="noopener">' . $domain . '</a>:';
									break;
								default:
									echo ":";
									break;
							}
						
							?>
						
						</h4>

						<p class="commentions-list-date">
							<?= date ( 'Y-m-d H:i', strtotime( $comment['timestamp'] ) ) ?>
						</p>

						<?php if ( $comment['type'] == 'reply' || $comment['type'] == 'comment' ) : ?>
						<div class="commentions-list-message">
							<?= strip_tags( kirbytext( $comment['text'] ), '<br><p><ul><ol><li><em><strong><i><b><blockquote><q>' ) ?>
						</div>
						<?php endif; ?>

				   </li>

			   <?php endforeach; ?>

			</ul>

		<?php endif; ?>

	</div>
