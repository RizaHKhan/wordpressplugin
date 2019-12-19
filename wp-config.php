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
define( 'DB_NAME', 'practice' );

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
define( 'AUTH_KEY',         '[FF^?ydw&-&7-sld^GgQ[E1.M~vDX!rJi~(wn}M;i#2)-j|Zv0#`Fi>{ZVVSv5P ' );
define( 'SECURE_AUTH_KEY',  'K+K@zOO,]Qzgm-Jk{6#yR`!%!4SfTtWxibtP Xs:p20&$c>Japqxp;ZsKeazN]+o' );
define( 'LOGGED_IN_KEY',    '<^z9^Qj?1ax^vGQ/{tJ$}S.ZL}`5fGsCzQu,F9^J<@Xs~Nw>HLU|rvt0v?vk0A.l' );
define( 'NONCE_KEY',        'u[N.~hESPS:)WOwn /KrB!pSZK#?<78Dc6[bQnv4MF@*R*B0sc0|s5-6$s(tO5Ub' );
define( 'AUTH_SALT',        'm4]6lx.9#71Mf]E#8=Z^p#h&n~)?jg~=VE+U?GJNvDvM@-~!Ja&JY*vYuxhgnN=!' );
define( 'SECURE_AUTH_SALT', 'hYeh)(+Dit?Hg&m_[]#@,:ngC^1!.+Nd!lz*H}$=(O?G1w@vL/HR a!#kkU&69Iq' );
define( 'LOGGED_IN_SALT',   'QHs`yVVsJfI0%Zv+T?<s>4%u{T=*~[;Bt-w-h:8}J.I=e_fWR*Z_w/`bpFL>b_2)' );
define( 'NONCE_SALT',       'lY)tPK7Dz/bO<D4m@8vx39GYU)F2r&]rp_FdG=_j%M}8l`?sG#>-hK~ZtSSII+Qx' );

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
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
