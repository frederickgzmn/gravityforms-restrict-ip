<?php

declare(strict_types=1);

namespace GFRestrictIP\Main;

use GFRestrictIP;

/**
 * Class GFRestrictIP\Page
 * @package GFRestrictIP
 */
class Page {

	public function __construct() {

		add_action( 'admin_menu', [ $this, 'gfrestrictip_menu' ] );
	}

	public function gfrestrictip_menu() {

		add_menu_page(
			__('GForm RestrictIP', 'gfrestrictip'),
			__('GF RestrictIP', 'gfrestrictip'),
			'manage_options',
			'gfrestrictip-page',
			[ $this, 'gfrestrictip_page' ],
			'dashicons-text'
		);
	}

	public function gfrestrictip_page() {

		?>

        <h1>

			<?php esc_html_e('Welcome to my custom admin page.', 'my-plugin-textdomain'); ?>

        </h1>

		<?php

	}

}