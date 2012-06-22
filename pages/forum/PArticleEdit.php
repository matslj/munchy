<?php
// ===========================================================================================
//
// PArticleEdit.php
//
// A WYSIWYG editor
//
// Author: Mikael Roos, mos@bth.se
//


// -------------------------------------------------------------------------------------------
//
// Get pagecontroller helpers. Useful methods to use in most pagecontrollers
//
$pc = CPageController::getInstance();
//$pc->LoadLanguage(__FILE__);


// -------------------------------------------------------------------------------------------
//
// Interception Filter, controlling access, authorithy and other checks.
//
$intFilter = new CInterceptionFilter();

$intFilter->FrontControllerIsVisitedOrDie();
$intFilter->UserIsSignedInOrRecirectToSignIn();
//$intFilter->UserIsMemberOfGroupAdminOrDie();


// -------------------------------------------------------------------------------------------
//
// Take care of _GET/_POST variables. Store them in a variable (if they are set).
//
$articleId	= $pc->GETisSetOrSetDefault('article-id', 0);
$topicId	= $pc->GETisSetOrSetDefault('topic-id', 0);
$editor         = $pc->GETisSetOrSetDefault('editor', 'plain');
$userId		= $_SESSION['idUser'];

// Always check whats coming in...
$pc->IsNumericOrDie($topicId, 0);
$pc->IsNumericOrDie($articleId, 0);
$pc->IsStringOrDie($editor);

// -------------------------------------------------------------------------------------------
//
// Create a new database object, connect to the database, get the query and execute it.
// Relates to files in directory TP_SQLPATH.
//
$title 		= "";
$content 	= "";
// Publish button is initially disabled
$publishDisabled = 'disabled="disabled"';

// If nicedit is chosen - include required html
$nicedit = '';
if (strcmp($editor, 'nicedit') == 0) {
$nicedit = <<<EOD
<!-- Updated for NiceEditor ============================================================= -->
<script src="http://js.nicedit.com/nicEdit-latest.js" type="text/javascript"></script>
<script type="text/javascript">
bkLib.onDomLoaded(function() {
    new nicEditor({buttonList : ['bold','italic','underline','strikethrough','image','fontSize']}).panelInstance('content');
});
</script>
EOD;
// Toggle editor
$editor = 'plain';
} else {
    $editor = 'nicedit';
}

// Connect
$db 	= new CDatabaseController();
$mysqli = $db->Connect();

// Get the SP names
$spGetArticleAndTopicDetails	= DBSP_PGetArticleAndTopicDetails;

$query = <<< EOD
CALL {$spGetArticleAndTopicDetails}({$topicId}, {$articleId}, '{$userId}');
EOD;

// Perform the query
$results = Array();
$res = $db->MultiQuery($query);
$db->RetrieveAndStoreResultsFromMultiQuery($results);
$saved = 'Not yet';

// Get article details
$row = $results[0]->fetch_object();
if ($row) {
    $title 	= $row->title;
}
$results[0]->close();

// Get article details
$row = $results[1]->fetch_object();
if ($row) {
    $content 	= $row->content;
    $saved	= empty($row->latest) ? 'Not yet' : $row->latest;
}
$results[1]->close();

$mysqli->close();

$htmlArticleTitle = "<h2>I tråden: \"{$title}\"</h2>";
if ($topicId == $articleId) {
    $htmlArticleTitle = "<p>Title: <input id='title' class='changables title' type='text' name='title' value='{$title}'></p>";
}
if($topicId > 0 && $articleId > 0) {
    $publishDisabled = '';
}

// Javascript settings
$js = WS_JAVASCRIPT;
$needjQuery = TRUE;
$htmlHead = <<<EOD
<!-- jGrowl latest -->
<link rel='stylesheet' href='{$js}jgrowl/jquery.jgrowl.css' type='text/css' />
<script type='text/javascript' src='{$js}jgrowl/jquery.jgrowl.js'></script>
<!-- jquery.autosave latest -->
<script type='text/javascript' src='{$js}jquery-autosave/jquery.autosave.js'></script>
EOD;

$redirectOnSuccess = 'json';
$javaScript = <<<EOD
// ----------------------------------------------------------------------------------------------
//
//
//
(function($){
$(document).ready(function() {
        // Registering autosave
        $('#form1').autosave({
            interval: 	10000,
            url: function(e, o, data, callback) {
                var postURL = $('#form1').attr('action');
                $.post(postURL, data, callback, "json");
            },
            record: 	function(e,o) {
                    // Don't want the setting of hidden fields to trigger an autosave...
                    $('input[type|="hidden"]').addClass('autosave-ignore');
            },
            before: 	function(e,o) {
                    if ($('#form1').isDirty()) {
                        $.jGrowl("Changes have been made. Saving...");
                        // ...but I want the hidden fields posted.
                        $('input[type|="hidden"]').removeClass('autosave-ignore');
                    }
                    // console.log('jquery.autosave before saving');
                    return true;
            },
            save: 	function(e,o,response) {
                    // console.log("Topic-id: " + response.topicId + ", article-id: " + response.articleId);
                    $('#topic_id').val(response.topicId);
                    $('#article_id').val(response.articleId);
                    updateEditorLink(response.articleId, response.topicId);
                    $('p.notice').html("Saved: " + response.timestamp);
                    $('button#savenow').attr('disabled', 'disabled');
                    $.jGrowl("Saving complete");
                    // console.log('jquery.autosave saving');
                    // console.log(o.data);
            }
    });

        function updateEditorLink(articleId, topicId) {
            $('#editorToggler').attr('href', '?p=article-edit&article-id=' + articleId + '&topic-id=' + topicId + '&editor={$editor}')
        }

        // This function regulates the disabled state of the publish button.
        function manipulatePublishButton() {
            var empty = true;
            $('.changables').each(function() {
                // console.log(this.id);
                if ($(this).val()) {
                    empty = false;
                }
            });
            // console.log("Empty = " + empty);
            if (empty) {
                $('button#publish').attr('disabled', 'disabled');
            } else {
                $('button#publish').removeAttr('disabled');
            }
        }
        
        // Some event binding - used only for regulating disabled status on buttons
        $('#form1').bind('keyup', function() {
            $('button#savenow').removeAttr('disabled');
            manipulatePublishButton();
        });

	// ----------------------------------------------------------------------------------------------
	//
	// Event handler for buttons in form. Instead of messing up the html-code with javascript.
	// Using Event bubbling as described in this document:
	// http://docs.jquery.com/Tutorials:AJAX_and_Events
	//
	$('#form1').click(function(event) {
		if ($(event.target).is('button#publish')) {
                        $('#action').val('publish');
			// Disable the button until form has changed again
			$(event.target).attr('disabled', 'disabled');
			$(event.target).submit();
		} else if ($(event.target).is('button#savenow')) {
			$('#action').val('draft');
                        $(event.target).attr('disabled', 'disabled');
                        $(event.target).submit();
		} else if ($(event.target).is('button#discard')) {
			history.back();
		} else if ($(event.target).is('a#viewPost')) {
			$.jGrowl('View published post...');
			if($('#isPublished').val() == 1) {
				$('a#viewPost').attr('href', '?p=article-edit&amp;article-id=%1\$d&amp;topic-id=%2\$d' + $('#topic_id').val() + '#post-' + $('#post_id').val());
			} else {
				alert('The post is not yet published. Press "Publish" to do so.');
				return(false);
			}
		}
	});
});
})(jQuery);

EOD;

$img = WS_IMAGES;

// <input type='hidden' name='redirect_on_success' value='article-edit&amp;article-id=%1\$d&amp;topic-id=%2\$d'>

// -------------------------------------------------------------------------------------------
//
// Page specific code
//
$htmlMain = <<<EOD
{$nicedit}
<!-- ==================================================================================== -->
<h1>Edit post</h1>
<form id="form1" class='editor1' action='?p=article-save' method='POST'>
<input type='hidden' name='redirect_on_success' value='{$redirectOnSuccess}'>
<input type='hidden' name='redirect_on_failure' value='article-edit&amp;article-id=%1\$d&amp;topic-id=%2\$d'>
<input type='hidden' id='article_id' name='article_id' value='{$articleId}'>
<input type='hidden' id='topic_id' name='topic_id' value='{$topicId}'>
<input type='hidden' id='action' 	name='action' value=''>
{$htmlArticleTitle}
<p>
<textarea class='changables size500x400' id='content' name='content'>{$content}</textarea>
</p>
<p class="notice">
Saved: {$saved}
</p>
<p>
<button id='publish' {$publishDisabled} type='submit'><img src='{$img}/silk/accept.png' alt=''> Publish</button>
<button id='savenow' disabled='disabled' type='submit'><img src='{$img}/silk/disk.png' alt=''> Save now</button>
<button id='discard' type='reset'><img src='{$img}/silk/cancel.png' alt=''> Discard</button>
</p>

</form>						
EOD;

$htmlLeft 	= "";
$htmlRight	= <<<EOD
<h3 class='columnMenu'>Toggle editor</h3>
<p>
<a id='editorToggler' href='?p=article-edit&article-id={$articleId}&topic-id={$topicId}&editor={$editor}'>Click here to toggle editor</a>
</p>
EOD;


// -------------------------------------------------------------------------------------------
//
// Create and print out the resulting page
//
$page = new CHTMLPage();

$page->printPage('Edit article', $htmlLeft, $htmlMain, $htmlRight, $htmlHead, $javaScript, $needjQuery);
exit;

?>