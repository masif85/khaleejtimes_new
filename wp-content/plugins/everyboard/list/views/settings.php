<h1>List Settings</h1>

<form method="POST" id="list_settings_form">
    <div class="settings--section">
        <h3>Published value</h3>
        <p>
            <?php _e('Property to determine if a article is fully published', 'everyboard'); ?>
        </p>
        <select id="select_publish_property" name="select_publish_property">
            <?php
            foreach( $metafield as $meta ) :
                if( isset( $meta[0]) ) :
                    ?>
                    <option <?php echo $options['property'] === $meta[0] ? 'selected' : ''; ?>>
                        <?php echo $meta[0]; ?>
                    </option>
                <?php
                endif;
            endforeach;
            ?>
        </select>

        <input type="text" name="publish_property" id="publish_property" value="<?php echo $options['value']; ?>" />
    </div>


	<div class="settings--section">
        <h3>Active items in EveryList</h3>
        <p>
            <?php _e('Settings for how many items that will be presented and save in EveryList. The other items will be deleted.', 'everyboard'); ?>
        </p>
		<input type="number" name="number_of_items_to_save" value="<?php echo !empty($options['number_of_items_to_save']) ? $options['number_of_items_to_save'] : 20; ?>" />
    </div>

    <input type="submit" value="Save">
    <input type="hidden" name="list_settings_form_posted" value="true" />
</form>
