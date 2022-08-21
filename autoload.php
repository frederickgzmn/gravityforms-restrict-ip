<?php
/**
 * Full strict types.

 *  @package Autoloader
 */

declare(strict_types=1);
/**
 * Anonymous function that registers a custom autoloader

 * @param string $prefix
 * @param string $base_dir
 */
return function ( string $prefix, string $base_dir ) {
	spl_autoload_register(
		function ( string $class ) use ( $prefix, $base_dir ) {
			$len = strlen( $prefix );
			if ( strncmp( $prefix, $class, $len ) !== 0 ) {
				return;
			}

			$relative_class = substr( $class, $len );
			$file           = $base_dir . str_replace( '\\', '/', $relative_class ) . '.php';
			if ( file_exists( $file ) ) {
				require $file;
			}
		}
	);
};
