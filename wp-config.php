<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'wp_widget' );

/** MySQL database username */
define( 'DB_USER', 'root' );

/** MySQL database password */
define( 'DB_PASSWORD', '' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         ':g]XF=i^ WVl3vFK#NG:.?raaiKoRPXbsJ;%BOa[COah0gPC{dG,Ys}cP*uVVZUr' );
define( 'SECURE_AUTH_KEY',  'L@lSbBSX?ir_sNa_RZRg9g|:)aBI]dELX)tWzt>n4;b$WUr/Us&P-5Au929U^b.X' );
define( 'LOGGED_IN_KEY',    'P (}aFmy/WI6p+1m2!EKpw7qRYr[^@!}:4*R{nIUvldJYyV~8RIL!$Xt!saTpU6/' );
define( 'NONCE_KEY',        'uimH]A!3zZ)1O$0eES,-G)qDn-pK}oW.<$s^>a;oNV6Xg)P_sE[3n&_%nPGe_2Dq' );
define( 'AUTH_SALT',        '>,dNF7XO-!1Vo}V1sz_iD8kp&q.E]|bjvU0WGMzIOWBKad@%1!A#bN}d>T&d=xjO' );
define( 'SECURE_AUTH_SALT', 'snT[x;x#8y(0z/ZO$20VF!$/|`.Xm8x{C3.(=Sk|foFypoN$ luHQ]HTjX~:e:qJ' );
define( 'LOGGED_IN_SALT',   'RxIAjJ{?7`c~P1W3dFK%Y3hMhG@*ZvG<_AG|x$B:b&0yt)*rL9}k<jyE.|z?PAj<' );
define( 'NONCE_SALT',       '.(]A.x@7b!|zMxw(n{9acm1[H<9^e)cWMoob?mWLmU*Np6$PLL[o$4q$&E/*OedX' );

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'widg_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define( 'WP_DEBUG', false );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

/** Sets up WordPress vars and included files. */
require_once( ABSPATH . 'wp-settings.php' );
