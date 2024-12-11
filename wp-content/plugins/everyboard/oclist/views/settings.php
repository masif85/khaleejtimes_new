<?php
$options = OcList::getSettings();
$ocApi = new OcAPI();

$bool_properties = $ocApi->get_properties_by_type('Article', 'BOOLEAN');
$article_relations = $ocApi->get_properties_by_type('List', 'Article');
$list_properties = $ocApi->get_properties_by_type('List', 'STRING');

$notifier_bindings = $options['notifier_bindings'];

if (empty($notifier_bindings)) {
    $notifier_bindings['contenttype'] = 'List';
}

$errorMessages = [
    'labels' => [
        'error' => '<strong>' . __('Error', 'everyboard') . ':</strong> ',
        'warning' => '<strong>' . __('Warning', 'everyboard') . ':</strong> ',
    ],
    'article_relation_property' => [
        'multiple_properties' => __('We found multiple relational properties between lists and articles in your Open Content. Please select the property to use.',
            'everyboard'),
        'missing_properties' => sprintf(
            __('Could not find any article relations to list in your Open Content. Please make sure that the relation %s is set up.',
                'everyboard'),
            "<code>Articles</code>"
        )
    ]
];

$descriptions = [
    'list_query' => sprintf(
        __('The query will only fetch the content type %s. The default query fetches all lists from Open Content but you can further specify the query to filter which lists that should be available for your boards.',
            'everyboard'),
        "<code>List</code>"
    ),
    'article_relation_property' => __('Article relation property is used to fetch data for articles in a list.',
        'everyboard'),
    'published_property' => sprintf(
        __('Published property is a %s used to check if an article is published or not.', 'everyboard'),
        "<code>Boolean</code>"
    ),
    'notifier_listener' => __('By registering a listener it will make sure that cached OC List documents are updated with events from Open Content.',
        'everyboard')
];

?>
<div class="wrap">
    <h1><?php _e('OC List Settings', 'everyboard'); ?></h1>
    <form method="POST" id="oclist_settings_form">
        <div class="settings--section">
            <table class="form-table">
                <tbody>
                <tr>
                    <th scope="row"><label><?php _e('OC List Query', 'everyboard'); ?>:</label></th>
                    <td>
                        <textarea name="list_query" placeholder="*:*" rows="5"
                                  cols="60"><?php print $options['list_query']; ?></textarea>
                        <p class="description"><?php echo $descriptions['list_query']; ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label><?php _e('Published property', 'everyboard'); ?>:</label></th>
                    <td>
                        <select name="published_property">
                            <option value="">
                                <?php _e('-- Select --', 'everyboard'); ?>
                            </option>
                            <?php foreach ($bool_properties as $property) {
                                $selected = selected($property, $options['published_property'], false);
                                print "<option value=\"{$property}\" {$selected}>{$property}</option>";
                            } ?>
                        </select>
                        <br>
                        <p class="description"><?php echo $descriptions['published_property']; ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label><?php _e('Article relation property', 'everyboard'); ?>:</label></th>
                    <td>
                        <?php if (count($article_relations) === 1) : ?>
                            <p class="description"><?php echo sprintf(
                                    __('Using the relation %s as value.', 'everyboard'),
                                    "<code>{$article_relations[0]}</code>"
                                ); ?>
                            </p>
                            <input type="hidden" name="article_relation_property"
                                   value="<?php echo $article_relations[0]; ?>">
                        <?php elseif (count($article_relations) > 1) : ?>
                            <p class="description"><?php
                                echo $errorMessages['article_relation_property']['multiple_properties']; ?>
                            </p>
                            <br>
                            <select name="article_relation_property">
                                <option value="">
                                    <?php _e('-- Select --', 'everyboard'); ?>
                                </option>
                                <?php foreach ($article_relations as $property) {
                                    $selected = selected($property, $options['article_relation_property'], false);
                                    print "<option value=\"{$property}\" {$selected}>{$property}</option>";
                                } ?>
                            </select>
                        <?php else : ?>
                            <div class="error">
                                <p><?php
                                    echo $errorMessages['labels']['error'];
                                    echo $errorMessages['article_relation_property']['missing_properties']; ?></p>
                            </div>
                        <?php endif; ?>
                        <p class="description">
                            <?php echo $descriptions['article_relation_property']; ?>
                        </p>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        <div class="settings--section">
            <h2><?php _e('Notifier for OC Lists', 'everyboard'); ?></h2>
            <hr>
            <table class="form-table">
                <tbody>
                <tr>
                    <th scope="row">
                        <label><?php _e('Notifier URL', 'everyboard'); ?>:</label>
                    </th>
                    <td>
                        <input
                            type="text"
                            id="oc_notifier20_url"
                            name="notifier_url"
                            size="60"
                            placeholder="http://"
                            value="<?php echo $options['notifier_url']; ?>"
                            <?php disabled($options['notifier_registered']) ?>
                        />
                        <?php if ($options['notifier_registered']) : ?>
                            <input
                                type="hidden"
                                id="oc_notifier20_registered"
                                name="notifier_url"
                                value="<?php echo $options['notifier_url']; ?>">
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label><?php _e('Listener', 'everyboard'); ?>:</label></th>
                    <td>
                        <p class="description"><?php echo $descriptions['notifier_listener']; ?></p>
                        <br>
                        <div class="notifier20_binding">
                            <?php foreach ($notifier_bindings as $binding => $value) : ?>
                                <div>
                                    <select <?php disabled($options['notifier_registered']) ?>
                                        class="bindings_selector">
                                        <?php foreach ($list_properties as $property) {
                                            $selected = selected($property, $binding, false);
                                            print "<option value=\"{$property}\" {$selected}>{$property}</option>";
                                        } ?>
                                    </select><input
                                        type="text"
                                        class="binding_input"
                                        value="<?php echo $value; ?>"
                                        name="notifier_bindings[<?php echo $binding; ?>]"
                                        <?php disabled($options['notifier_registered']) ?>
                                    /><?php if ( ! $options['notifier_registered']) { ?><input type="button" class="delete_binding" value="Remove"/><?php } ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <?php if ( ! $options['notifier_registered']) : ?>
                            <p>
                                <input
                                    type="button"
                                    id="oc_notifier20_add_binding"
                                    class="button"
                                    value="Add binding"
                                    data-contenttype="List"
                                />
                            </p>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row"></th>
                    <td>
                        <input
                            type="button"
                            id="oc_notifier20_button"
                            class="button"
                            data-type="oclist"
                            data-unregister="<?php echo empty($options['notifier_registered']) ? "false" : "true"; ?>"
                            value="<?php
                            echo empty($options['notifier_registered']) ?
                                __('Register Listener', 'everyboard') :
                                __('Deregister Listener', 'everyboard'); ?>"
                        />
                    </td>
                </tr>
                </tbody>
            </table>
        </div>

        <input type="hidden" name="oclist_settings_form_posted" value="true"/>
        <input type="submit" value="Save changes" class="button-primary" style="margin-top: 25px;">
    </form>
</div>
