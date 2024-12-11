<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>

<form method="get" class="searchform" action="<?php echo esc_url( home_url( '/' ) ); ?>" role="search">
	<label class="sr-only" for="s"><?php esc_html_e( 'Search', 'everyware-theme-base-1' ); ?></label>
	<div class="input-group">
		<input class="field form-control s" name="s" type="text"
			placeholder="<?php esc_attr_e( 'Search &hellip;', 'everyware-theme-base-1' ); ?>" value="<?php the_search_query(); ?>">
		<span class="input-group-append">
			<input class="submit searchsubmit btn btn-primary fa fa-input" type="submit"
			value="&#xf002">
	</span>
	</div>
</form>
