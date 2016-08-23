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
 *∑
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

//Ricardo Bandala Added
define('WP_DEBUG', true);


// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'blushdrop');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', '2414');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'S)/.G[rNv$gHHu9VsqK *]/ayBr?q?{<4/~Uo:)tgx7weONMZv=lKX^())4f+nl_');
define('SECURE_AUTH_KEY',  '*i4j)U/7y:ybg%=]wdg!g9 !n[t7#k*]Sr0)KqSDTl6p[3,%,/DN<>MYe[bvhS@F');
define('LOGGED_IN_KEY',    'z6=9_ueR)r289475!n%r9,uEd)9$Lb2FNFu`2arX@0.G4S9Ebfcik8~wFh)ChR@W');
define('NONCE_KEY',        'w=73xluKb&WmUY5cfq3Rh]vHeNKI|= WZuoR4et{$r_n[[dAU]I8Ps6Gb(hDM7zO');
define('AUTH_SALT',        'u8Cn~b37>v)%+h0s.;giI[[*^A[krmV$aP;yeNlnl_wUF}:c0=V3?E(6F]T(]^_r');
define('SECURE_AUTH_SALT', 'Sa{M%$pv#%%+C[NB~L^mk)]Nf<qD$+7tH%/xz72knboh.UQFH*|p?{.ZdQy$i!Z&');
define('LOGGED_IN_SALT',   '%JML{PX23)z.3[ (I!HD2Kvb<w:EV]U/y%N[MWCZ-9fq5F{(HXDf}>3Y1I.^<gi;');
define('NONCE_SALT',       '&Ip*6gIl2(,P~St!n.6I.8+HX}`JbmeJXt^:q8>X8!S`_vF?c|{O8v6e/t3vB*s?');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

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
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');
//TODO implement abspath in production
/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
