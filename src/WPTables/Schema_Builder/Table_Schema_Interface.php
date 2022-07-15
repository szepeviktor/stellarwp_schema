<?php
/**
 * The API implemented by each custom table.
 *
 * @since   TBD
 *
 * @package StellarWP\WPTables\Schema_Builder
 */

namespace StellarWP\WPTables\Schema_Builder;

/**
 * Interface Table_Schema_Interface
 *
 * @since   TBD
 *
 * @package StellarWP\WPTables\Schema_Builder
 */
interface Table_Schema_Interface {
	/**
	 * Returns the name of the column that is guaranteed to uniquely identify an
	 * entry across updates.
	 *
	 * @since TBD
	 *
	 * @return string The name of the column that is guaranteed to uniquely identify an
	 *                entry across updates.
	 */
	public static function uid_column();

	/**
	 * Empties the custom table.
	 *
	 * @since TBD
	 *
	 * @return int|false The number of removed rows, or `false` to indicate a failure.
	 */
	public function empty_table();

	/**
	 * Drop the custom table.
	 *
	 * @since TBD
	 *
	 * @return boolean `true` if successful operation, `false` to indicate a failure.
	 */
	public function drop();

	/**
	 * Creates, or updates, the custom table.
	 *
	 * @since TBD
	 *
	 * @return boolean `true` if successful operation, `false` to indicate a failure.
	 */
	public function update();

	/**
	 * Returns the custom table name.
	 *
	 * @since TBD
	 *
	 * @return string The custom table name, prefixed by the current `wpdb` prefix,
	 *                if required.
	 */
	public static function table_name();

	/**
	 * Returns the custom table name.
	 *
	 * @since TBD
	 *
	 * @return string The base custom table name.
	 */
	public static function base_table_name();

	/**
	 * References our stored version versus the version defined in the class.
	 *
	 * @since TBD
	 *
	 * @return bool Whether our latest schema has been applied.
	 */
	public function is_schema_current();

	/**
	 * Returns whether a table exists or not in the database.
	 *
	 * @since TBD
	 *
	 * @return bool
	 */
	public function exists();
}