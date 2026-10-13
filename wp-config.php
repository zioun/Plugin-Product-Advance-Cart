<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'solub' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'H!Svy#RFpsI)VsDlvgNO)]SD-{}=`,fC7Oitl[h$8.p;;,wQ/C</Ql$r2~WLg;EY' );
define( 'SECURE_AUTH_KEY',  'gm-$^Lp(_Uy+0eBf4il4qas0dpP^Y{fl_xkVyZGImIokDb-Zs3J]4t6$S>~U!oxW' );
define( 'LOGGED_IN_KEY',    '*7IX#b=Cq<ZR$ptG6/%M>IkYmZ4;?-X2+z/Z=@SD!)ePMAO02B (g)EAK2F6.all' );
define( 'NONCE_KEY',        'mci_+PFHui.<Am;rEeYu_:G!|nL{$8>lj3:LT3fg^[$KsCny<{$!j^)ZVvw yQ:0' );
define( 'AUTH_SALT',        '1`Yo(t^DD1,b74Ifg6D{3tAN7<{cH<@!~IT|y]E^nna(7=5ybF;>p1bqT=7DZW4J' );
define( 'SECURE_AUTH_SALT', 'b{V[5x,N0@0{`|0I8Gl`AnbPt6C@mWbJHj)tp96%z{]G+|7w6_E9v@r?aJldWb+B' );
define( 'LOGGED_IN_SALT',   '9qL#K|.!D^4l;`[037LJH2Uom9,+$i)K9[$-JQ]6ahSt/{!1q b|Tg0h(Ka3h5gu' );
define( 'NONCE_SALT',       'Uy,w@{&1Y|9h-0Ko<5eq$xTw(yQp5JP:s2K/}[cFWHRJ{` !zx&1Dl-n=1^R8:f,' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define( 'WP_DEBUG', true );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
