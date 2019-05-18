<?php

namespace IndieWeb\comments;

function truncateString($text, $length) {
  ob_start();
  $short = \str::short($text, $length);
  ob_end_clean();
  return $short;
}

function truncate($text, $maxTextLength, $maxLines) {
  $lines = explode("\n", $text);
  $visibleLines = array_filter($lines);
  if(count($visibleLines) > $maxLines) {
    $newContent = array();
    $visibleLinesAdded = 0;
    $i = 0;
    while($visibleLinesAdded < $maxLines && $i < count($lines)) {
      $line = $lines[$i];
      $newContent[] = $line;
      if(trim($line) != '')
        $visibleLinesAdded++;
      $i++;
    }
    $text = implode("\n", $newContent);
    // Tack on extra chars and then tell cassis to ellipsize it shorter to take advantage of proper ellipsizing logic.
    // This is for when the full text is shorter than $maxTextLength but has more lines than $maxLines
    $text .= ' ....';
    $text = truncateString($text, min($maxTextLength, strlen($text)-1));
  } else {
    $text = truncateString($text, $maxTextLength);
  }
  return $text;
}

function removeScheme(&$url) {
  if(is_array($url)) {
    foreach($url as $i=>$u) {
      removeScheme($url[$i]);
    }
  } else {
    $url = preg_replace('/^https?/', '', $url);
  }
}

function parse($mf, $refURL=false, $maxTextLength=150, $maxLines=2) {
  // When parsing a comment, the $refURL is the URL being commented on.
  // This is used to check for an explicit in-reply-to property set to this URL.

  // Remove the scheme from the refURL and treat http and https links as the same
  removeScheme($refURL);

  $type = 'mention';
  $published = false;
  $name = false;
  $text = false;
  $url = false;
  $author = array(
    'name' => false,
    'photo' => false,
    'url' => false
  );
  $rsvp = null;

  if(array_key_exists('type', $mf) && in_array('h-entry', $mf['type']) && array_key_exists('properties', $mf)) {
    $properties = $mf['properties'];

    if(array_key_exists('author', $properties)) {
      $authorProperty = $properties['author'][0];
      if(is_array($authorProperty)) {

        if(array_key_exists('name', $authorProperty['properties'])) {
          $author['name'] = $authorProperty['properties']['name'][0];
        }

        if(array_key_exists('url', $authorProperty['properties'])) {
          $author['url'] = $authorProperty['properties']['url'][0];
        }

        if(array_key_exists('photo', $authorProperty['properties'])) {
          $author['photo'] = $authorProperty['properties']['photo'][0];
        }

      } elseif(is_string($authorProperty)) {
        $author['url'] = $authorProperty;
      }
    }

    if(array_key_exists('published', $properties)) {
      $published = $properties['published'][0];
    }

    if(array_key_exists('url', $properties)) {
      $url = $properties['url'][0];
    }

    // If the post has an explicit in-reply-to property, verify it matches $refURL and set the type to "reply"
    if($refURL && array_key_exists('in-reply-to', $properties)) {
      // in-reply-to may be a string or an h-cite
      foreach($properties['in-reply-to'] as $check) {
        removeScheme($check);
        if(is_string($check) && $check == $refURL) {
          $type = 'reply';
          continue;
        } elseif(is_array($check)) {
          if(array_key_exists('type', $check) && in_array('h-cite', $check['type'])) {
            if(array_key_exists('properties', $check) && array_key_exists('url', $check['properties'])) {
              if(in_array($refURL, $check['properties']['url'])) {
                $type = 'reply';
              }
            }
          }
        }
      }
    }

    // Check if the reply is an RSVP
    if(array_key_exists('rsvp', $properties)) {
      $rsvp = $properties['rsvp'][0];
      $type = 'rsvp';
    }

    // Check if the reply is an invitation
    if(array_key_exists('invitee', $properties)) {
      $inviteeProperty = $properties['invitee'][0];
      if(is_array($inviteeProperty)) {

        if(array_key_exists('name', $inviteeProperty['properties'])) {
          $invitee['name'] = $inviteeProperty['properties']['name'][0];
        }

        if(array_key_exists('url', $inviteeProperty['properties'])) {
          $invitee['url'] = $inviteeProperty['properties']['url'][0];
        }

        if(array_key_exists('photo', $inviteeProperty['properties'])) {
          $invitee['photo'] = $inviteeProperty['properties']['photo'][0];
        }

      } elseif(is_string($inviteeProperty)) {
        $invitee['url'] = $inviteeProperty;
      }
      $type = 'invite';
    }

    // Check if this post is a "repost"
    if($refURL && array_key_exists('repost-of', $properties)) {
      removeScheme($properties['repost-of']);
      if(in_array($refURL, $properties['repost-of']))
        $type = 'repost';
    }

    // Also check for "u-repost" since some people are sending that. Probably "u-repost-of" will win out.
    if($refURL && array_key_exists('repost', $properties)) {
      removeScheme($properties['repost']);
      if(in_array($refURL, $properties['repost']))
        $type = 'repost';
    }

    if($refURL && array_key_exists('like-of', $properties)) {
      removeScheme($properties['like-of']);
      if(in_array($refURL, $properties['like-of']))
        $type = 'like';
    }

    // Check if this post is a "like"
    if($refURL && array_key_exists('like', $properties)) {
      removeScheme($properties['like']);
      if(in_array($refURL, $properties['like']))
        $type = 'like';
    }

    // From http://indiewebcamp.com/comments-presentation#How_to_display

    // If the entry has an e-content, and if the content is not too long, use that
    if(array_key_exists('content', $properties)) {
      $content = $properties['content'][0];
      if ((is_array($content) && array_key_exists('value', $content)) || is_string($content)) {
        if (is_array($content)) {
          $content = $content['value'];
        }

        $visibleLines = array_filter(explode("\n", $content));
        if(strlen($content) <= $maxTextLength && count($visibleLines) <= $maxLines) {
          $text = $content;
        }
      }
      // If the content is not a string or array with “value”, something is wrong.
    }

    // If there is no e-content, or if it is too long
    if($text == false) {
      // if the h-entry has a p-summary, and the text is not too long, use that
      if(array_key_exists('summary', $properties)) {
        $summary = $properties['summary'][0];
        if(is_array($summary) && array_key_exists('value', $summary))
          $summary = $summary['value'];

        if(strlen($summary) <= $maxTextLength) {
          $text = $summary;
        } else {
          // if the p-summary is too long, then truncate the p-summary
          $text = truncate($summary, $maxTextLength, $maxLines);
        }
      } else {
        // if no p-summary, but there is an e-content, use a truncated e-content
        if(array_key_exists('content', $properties)) {
          // $content already exists from line 127, and is guaranteed to be a string.
          $text = truncate($content, $maxTextLength, $maxLines);
        }
      }
    }

    // If there is no e-content and no p-summary
    if($text == false) {
      // If there is a p-name, and it's not too long, use that
      if(array_key_exists('name', $properties)) {
        $pname = $properties['name'][0];
        if(strlen($pname) <= $maxTextLength) {
          $text = $pname;
        } else {
          // if the p-name is too long, truncate it
          $text = truncate($pname, $maxTextLength, $maxLines);
        }
      }
    }

    // Now see if the "name" property of the h-entry is unique or part of the content
    if(array_key_exists('name', $properties)) {
      $nameSanitized = strtolower(strip_tags($properties['name'][0]));
      $nameSanitized = preg_replace('/ ?\.+$/', '', $nameSanitized); // Remove trailing ellipses
      // Using the already truncated version of the content here. But the "name" would not have been truncated so may be longer than the content.
      $contentSanitized = strtolower(strip_tags($text));
      $contentSanitized = preg_replace('/ ?\.+$/', '', $contentSanitized); // Remove trailing ellipses

      // If this is a "mention" instead of a "reply", and if there is no "content" property,
      // then we actually want to use the "name" property as the name and leave "text" blank.
      if($type == 'mention' && !array_key_exists('content', $properties)) {
        $name = $properties['name'][0];
        $text = false;
      } else {
        if($nameSanitized != $contentSanitized and $nameSanitized !== '') {
          // If the name is the beginning of the content, we don't care
          // Same if the content is the beginning of the name (like with really long notes)
          if(!(strpos($contentSanitized, $nameSanitized) === 0) && !(strpos($nameSanitized, $contentSanitized) === 0)) {
            // The name was determined to be different from the content, so return it
            $name = $properties['name'][0]; //truncate($properties['name'][0], $maxTextLength, $maxLines);
          }
        }
      }
    }

  }

  $result = array(
    'author' => $author,
    'published' => $published,
    'name' => $name,
    'text' => $text,
    'url' => $url,
    'type' => $type
  );

  if($type == 'invite') 
    $result['invitee'] = $invitee;

  if($rsvp !== null) {
    $result['rsvp'] = $rsvp;
  }

  return $result;
}

