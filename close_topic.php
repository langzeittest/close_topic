<?php

if (!defined('PHORUM')) return;

//
// Flag new topic immediately as closed or no-reply-allowed.
//
function mod_close_topic_before_post($postdata) {
    global $PHORUM;

    if (    is_array($PHORUM['mod_close_topic']['forum_flag'])
         && isset($PHORUM['mod_close_topic']['forum_flag'][$postdata['forum_id']])
         && $PHORUM['mod_close_topic']['forum_flag'][$postdata['forum_id']]) {
        $postdata['closed'] = 1;
    }

    return $postdata;
}

//
// Flag moved topic immediately as closed or no-reply-allowed.
//
function mod_close_topic_move_thread($msgthd_id) {
    global $PHORUM;

    // Get Message.
    $message = phorum_db_get_message($msgthd_id, 'message_id', TRUE);

    // Check if destination forum is marked as "close topic".
    if (    isset($message)
         && is_array($message)
         && is_array($PHORUM['mod_close_topic']['forum_flag'])
         && isset($PHORUM['mod_close_topic']['forum_flag'][$message['forum_id']])
         && $PHORUM['mod_close_topic']['forum_flag'][$message['forum_id']]) {
        // Close thread.
        phorum_db_close_thread($msgthd_id);
    }
}

?>