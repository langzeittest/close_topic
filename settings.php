<?php

    // Make sure that this script is loaded from the admin interface.
    if (!defined('PHORUM_ADMIN')) return;

    // Save settings in case this script is run after posting
    // the settings form.
    if (    count($_POST)
         && isset($_POST['forum_flag']) ) {

        // Create the settings array for this module.
        $PHORUM['mod_close_topic'] = array
            ( 'forum_flag' => $_POST['forum_flag'] );

        if (!phorum_db_update_settings(array('mod_close_topic'=>$PHORUM['mod_close_topic']))) {
            $error = 'Database error while updating settings.';
        } else {
            phorum_admin_okmsg('Settings Updated');
        }
    }

    // We build the settings form by using the PhorumInputForm object.
    include_once './include/admin/PhorumInputForm.php';
    $frm = new PhorumInputForm('', 'post', 'Save settings');
    $frm->hidden('module', 'modsettings');
    $frm->hidden('mod', 'close_topic');

    // Here we display an error in case one was set by saving
    // the settings before.
    if (!empty($error)){
        phorum_admin_error($error);
    }

    $frm->addbreak('Edit Settings for the Close Topic module');
    // Forum flags
    $frm->addbreak('Configure where you want to automaticly close topics');
    $tree = phorum_mod_close_topic_getforumtree();
    $forumlist = array();
    foreach ($tree as $data) {
        $level = $data[0];
        $node = $data[1];
        $name = str_repeat('&nbsp;&nbsp;', $level);
        $name .= $node['folder_flag'] ? 'Folder: ' : 'Forum: ';
        $name .= $node['name'];

        if ($node['folder_flag']) {
            // No settings for folders.
            $frm->addrow($name);
        } else {
            // Settings for forums.
            $checked = (@in_array($name, $PHORUM['mod_close_topic']['forum_flag']))? 1 : 0;
            $frm->addrow
                ( $name,
                  $frm->checkbox
                      ('forum_flag['.$node['forum_id'].']', $name , '', $checked) );
        }
    }
    // Show settings form
    $frm->show();

    //
    // Internal functions
    //

    function phorum_mod_close_topic_getforumtree() {
        // Retrieve all forums and create a list of all parents
        // with their child nodes.
        $forums = phorum_db_get_forums();
        $nodes = array();
        foreach ($forums as $id => $data) {
            $nodes[$data['parent_id']][$id] = $data;
        }

        // Create the full tree of forums and folders.
        $treelist = array();
        phorum_mod_close_topic_mktree(0, $nodes, 0, $treelist);
        return $treelist;
    }

    // Recursive function for building the forum tree.
    function phorum_mod_close_topic_mktree($level, $nodes, $node_id, &$treelist) {
        // Should not happen but prevent warning messages, just in case...
        if (!isset($nodes[$node_id])) return;

        foreach ($nodes[$node_id] as $id => $node) {

            // Add the node to the treelist.
            $treelist[] = array($level, $node);

            // Recurse folders.
            if ($node['folder_flag']) {
                $level++;
                phorum_mod_close_topic_mktree($level, $nodes, $id, $treelist);
                $level--;
            }
        }
    }

?>
