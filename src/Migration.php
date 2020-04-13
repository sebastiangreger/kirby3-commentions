<?php

namespace sgkirby\Commentions;

use Kirby\Data\Data;
use Kirby\Data\Yaml;
use Kirby\Http\Response;

class Migration {


    public static function route() {

		// display error to non-admin users
		if ( ! kirby()->user()->isAdmin() )
			return new Response( 'You have to log in as admin user to proceed.', 'text/html', 403 );
		
		// POST request is the filled in form	
		if ( kirby()->request()->is('POST') && get('backup') == 'yes' && get('disclaimer') == 'yes' ) :
		
			// loop through all pages
			foreach ( site()->index() as $page ) :
			
				// extract the 'comments' field
				$data = $page->comments()->toArray();
				$comments = Data::decode( $data['comments'], 'yaml' );
				
				if ( is_array( $comments ) && sizeof( $comments > 0 ) ) :
				
					// write it to the commentions file
					Commentions::write( $page, $comments, 'comments' );
					
					if ( $comments == Commentions::read( $page, 'comments' ) )
						echo 'identical';
					
					// delete the 'comments' field from the page
					//$page->update([ 'comments' => null ]);
				
				endif;
				
			endforeach;
		
			return new Response( 'ok', 'text/html' );
		
		// GET request is the default view
		else :

			$html = '
				<h1>Kirby3-Commentions migration assistant</h1>
				<p>Version 1.x of the Commentions plugin uses a new way of storing its data; a migration is therefore necessary. This tool attempts to carry out the migration in an automated manner. It may not work in all circumstances, so it is absolutely required to create a backup before using this. For more details about the changes from version 0.x to 1.x, see the <a href="">GitHub page</a>.</p>
			';
			$evidence = '';

			// check for presence of the old content/.commentions folder
			if ( is_dir( kirby()->root() . DS . 'content' . DS . '.commentions' ) )
				$evidence .= '<li>The folder ' . kirby()->root() . DS . 'content' . DS . '.commentions' . ' (used for storing the comments inbox and Webmention queue) is no longer used in version 1.x</li>';

			// check for any content files that contain a comments field with a data pattern as used in Commentions
			$pageswithcomments = 0;
			foreach( site()->index()->pluck('comments') as $probe ) :
				$probe = Data::decode( $probe->comments(), 'yaml' );
				if ( sizeof( $probe ) > 0 && isset( $probe[0]['type'] ) && isset( $probe[0]['timestamp'] ) && isset( $probe[0]['approved'] ) )
					$pageswithcomments++;
			endforeach;
			if ( $pageswithcomments > 0 )
				$evidence .= '<li>One or more pages appear to contain comments stored in the old 0.x version format</li>';

			// if any evidence has been found, output the list
			if ( $evidence != '' ) :
				$html .= '
					<h2>Evidence of old data formats found:</h2>
					<p>The following signs indicate that your site contains Commentions data in the now outdated 0.x format:</p>
					<ul>' . $evidence . '</ul>
					<h2>A migration from version 0.x to 1.x is required:</h2>
				';
				if ( kirby()->request()->is('POST') )
					$html .= '<p style="color:red;">Please confirm the two safety checks to proceed!</p>';
				$html .= '
					<form action="' . kirby()->site()->url() . DS . 'commentions-migrationassistant" method="post">
						<label><input type="checkbox" name="backup" value="yes">Yes, I created a complete, verified backup of all my data</label><br>
						<label><input type="checkbox" name="disclaimer" value="yes">Yes, I understand that this tool comes with no warranty</label><br><br>
						<input type="submit" name="submit" value="Try to migrate my Commentions data now">
					</form>
				';
			else :
				$html .= '
					<h2>No signs of old 0.x data found:</h2>
					<p>Based on the automated checks, it does not appear that your site has any commentions data in the old (0.x) format. Sorry, this tool won\'t be able to assist you.</p>
				';
			endif;

			return new Response( $html, 'text/html' );

		endif;

	}


}
