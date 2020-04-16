<?php

use sgkirby\Commentions\Frontend;

function commentions($template = null)
{
    Frontend::render($template);
}

// DEPRECATED as of 1.0.0: separate helpers replaced with commentions() helper + template variable
function commentionsFeedback()
{
    commentions('feedback');
}
function commentionsForm()
{
    commentions('form');
}
function commentionsList(string $format = 'list')
{
    if ($format == 'grouped') {
        commentions('grouped');
    } elseif ($format == 'raw') {
        commentions('raw');
    } else {
        commentions('list');
    }
}
function commentionsEndpoints()
{
    commentions('endpoints');
}
function commentionsCss()
{
    commentions('css');
}
