<?php
define( 'WP_CACHE', true );

/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'protal' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', 'root' );

/** Database hostname */
define( 'DB_HOST', '127.0.0.1' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

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
define( 'AUTH_KEY',          'i#;<d^3[*C=*v+Ow}=u1BSw!#6b_mj1GmGTwSVqC9dID:}qA zNiSO@wU7&=GVXV' );
define( 'SECURE_AUTH_KEY',   '<qU@AQ:)ETu%]]Q5MP48:%nM^0S?wjW;s G<{5O<8w~7~vwYn_/fs<WsD[kbhw{~' );
define( 'LOGGED_IN_KEY',     'n$<L%3tf(6x{)H^C{ 4M@Q,iV<z>^$:1)Sn5AE{$MqqK>#]Xqt!@#hpl9&u)Y&c2' );
define( 'NONCE_KEY',         '<qmLFj2U`c`H2Gm:Q ryJgpo*=i(UjfHTel?I*|dN;Ey{zb<fq/q=nmC^>!(6}82' );
define( 'AUTH_SALT',         'M]m3=:6!T)Xlf>jOhy=)wZpSUv^wMbSe139[kQ*j&;bz&nk:$4&uRyB:*KGP|x$~' );
define( 'SECURE_AUTH_SALT',  'LxB2/`JuZgm^+i{ljcc4L!Lx(UMrruDzft.H<cc2M&X?z:(aCJEtiHG|`f_BZYcS' );
define( 'LOGGED_IN_SALT',    '%j[%y1BU&Dlig`O$P2L5[Mr015AS~Q>V~J/DP??/s8:F, jr6L^Di`Mdn7c$q|{8' );
define( 'NONCE_SALT',        ',-m>o&OS}I>lc]n[n h:leq<T=!K*+^XyqPthiaeLv[Vj]o59)GJ=58a9;GIuZ1e' );
define( 'WP_CACHE_KEY_SALT', '1SPBvuy/CMdMxbmYHh|MU]wo~R?`|T 8odWLw^?s|mJE`g!)]/C1U`XdulP/>qMj' );


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';


/* Add any custom values between this line and the "stop editing" line. */



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
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
if ( ! defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', false );
}

define( 'FS_METHOD', 'direct' );
define( 'WP_AUTO_UPDATE_CORE', 'minor' );
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';


define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);