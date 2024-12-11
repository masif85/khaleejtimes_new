<h1>EveryList</h1>
<div id="main-message-wrapper" class="message-wrapper">

</div>

<div class="list-actions">
    <select class="list-selector">
        <option>(( <?php _e('Choose a list') ?> ))</option>
        <?php foreach( $lists as $id => $list ) :?>
            <option value="<?php echo $id; ?>" data-list-name="<?php echo $list->listname; ?>"><?php echo $list->listname; ?></option>
        <?php endforeach; ?>
    </select>

    <input id="everylist_create" type="button" value="<?php _e('Create new list', 'everyboard'); ?>" />
</div>

<div class="list-area">
</div>

<div class="dialog" id="create-list-dialog" title="<?php _e('Create new list', 'everyboard'); ?>">
    <form>
        <div class="message-wrapper"></div>
        <label for="listname"><?php _e('List name', 'everyboard'); ?></label>
        <input id="listname" name="listname" type="text" class="text ui-widget-content ui-corner-all" />

        <input type="submit" tabindex="-1" style="position:absolute; left:-9999px" />
    </form>
</div>

<div class="dialog" id="generate-list-url-dialog" title="<?php _e('URL for NP', 'everyboard'); ?>">
    <form>
        <textarea id="list-url-textbox" class="text ui-widget-content ui-corner-all" rows="7" cols="34"></textarea>
    </form>
</div>

<div class="dialog" id="remove-list-dialog"><?php _e('Are you sure you\'d like to remove this list?', 'everyboard'); ?></div>
<div class="dialog" id="duplicate-dialog"><?php _e('The article is already in the active list', 'everyboard'); ?></div>
