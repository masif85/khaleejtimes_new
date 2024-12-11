<?php

use USKit\Base\PageTemplate;
use Infomaker\Everyware\Base\Models\Page;
use Infomaker\Everyware\Twig\View;
use USKit\Base\Teaser;
use USKit\Base\NewsMLArticle;
use Infomaker\Everyware\Base\OpenContent\QueryBuilder;
use Infomaker\Everyware\Base\OpenContent\OpenContentProvider;
use USKit\Base\ViewModels\BasePage;

$current_page = Page::current();

if ($current_page instanceof Page) {

	$pageTemplate = new BasePage($current_page);
	
}

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$sidebar_pos = get_theme_mod( 'sidebar_position_sectionfront' );
?>

<div class="wrapper" id="page-wrapper">
	<div class="container" id="section-front">
		<div class="row">

			<?php 
			if ( $sidebar_pos == 'right' || $sidebar_pos == 'left' || $sidebar_pos == 'both' ) {
				get_template_part( 'sidebar-templates/left-sidebar-check-sectionfront' ); 
			}
			?>

			<main class="site-main" id="main">

				<header class="entry-header">
					<h1 class="entry-title">Create new account</h1>
				</header>

				<?php require get_theme_file_path( '/inc/custom_registration.php' ); ?>
				<form method="post">
					<div class="form-group">
						<label for="registration_username">Username</label>
						<input type="text" class="form-control" id="registration_username" name="registration_username" placeholder="Enter username">
						<p class="registration_note">Several special characters are allowed, including period (.), hyphen (-), underscore (_), and the @ sign. Max character limit is 25.</p>
					</div>
					<div class="form-group">
						<label for="registration_email">Email address</label>
						<input type="email" class="form-control" id="registration_email" name="registration_email" placeholder="Enter email">
						<p class="registration_note">A valid email address. All emails from the system will be sent to this address. The email address is not made public and will only be used if you wish to receive a new password.</p>
					</div>
					<div class="form-group">
						<label for="registration_password">Password</label>
						<input type="password" class="form-control" id="registration_password" name="registration_password" placeholder="Password">
						<p class="registration_note">Password must be at least 8 characters and contain 1 capital letter, 1 lowercase letter, and 1 number.</p>
					</div>
					<div class="form-group">
						<label for="registration_confirm_password">Confirm Password</label>
						<input type="password" class="form-control" id="registration_confirm_password" name="registration_confirm_password" placeholder="Confirm Password">
					</div>
					<input type="submit" value="Create new account" class="btn btn-primary" name="btnSubmit">
				</form>

			</main>

			<?php 
				if ( $sidebar_pos == 'right' || $sidebar_pos == 'left' || $sidebar_pos == 'both' ) {
					get_template_part( 'sidebar-templates/right-sidebar-check-sectionfront' ); 
				}
			?>

		</div><!-- .row -->
	</div><!-- Container end -->
</div><!-- Wrapper end -->

<?php get_footer(); ?>
