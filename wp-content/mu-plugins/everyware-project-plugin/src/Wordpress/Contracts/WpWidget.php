<?php declare(strict_types=1);


namespace Everyware\ProjectPlugin\Wordpress\Contracts;

/**
 * Interface WpWidget
 *
 * @package Everyware\ProjectPlugin\Wordpress\Contracts
 */
interface WpWidget
{
    /**
     * Outputs the settings update form.
     *
     * @since 2.8.0
     *
     * @param array $instance Current settings.
     * @return string Default return is 'noform'.
     */
    public function form( $instance ): ?string;

    /**
     * Updates a particular instance of a widget.
     *
     * This function should check that `$new_instance` is set correctly. The newly-calculated
     * value of `$instance` should be returned. If false is returned, the instance won't be
     * saved/updated.
     *
     * @since 2.8.0
     *
     * @param array $new_instance New settings for this instance as input by the user via
     *                            WP_Widget::form().
     * @param array $old_instance Old settings for this instance.
     * @return mixed array Settings to save or bool false to cancel saving.
     */
    public function update( $new_instance, $old_instance );

    /**
     * Echoes the widget content.
     *
     * Sub-classes should over-ride this function to generate their widget code.
     *
     * @since 2.8.0
     *
     * @param array $args     Display arguments including 'before_title', 'after_title',
     *                        'before_widget', and 'after_widget'.
     * @param array $instance The settings for the particular instance of the widget.
     */
    public function widget( $args, $instance ):void;
}
