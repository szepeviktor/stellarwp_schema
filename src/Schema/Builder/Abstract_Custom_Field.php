<?php
/**
 * Groups the base methods and functions used by all custom field implementations.
 *
 * @since   1.0.0
 *
 * @package StellarWP\Schema\Builder
 */

namespace StellarWP\Schema\Builder;

use StellarWP\Schema\Container;
use StellarWP\Schema\Tables;

/**
 * Class Abstract_Custom_Field
 *
 * @since   1.0.0
 *
 * @package StellarWP\Schema\Builder
 */
abstract class Abstract_Custom_Field implements Field_Schema_Interface {
	/**
	 * @since 1.0.0
	 *
	 * @var string|null The version number for this schema definition.
	 */
	const SCHEMA_VERSION = null;

	/**
	 * @since 1.0.0
	 *
	 * @var string The base table name.
	 */
	protected static $base_table_name = '';

	/**
	 * @var Container The dependency injection container.
	 */
	protected $container;

	/**
	 * @since 1.0.0
	 *
	 * @var string The slug used to identify the custom field alterations.
	 */
	protected static $schema_slug = '';

	/**
	 * @since 1.0.0
	 *
	 * @var array<string> Custom fields defined in this field schema.
	 */
	protected $fields = [];

	/**
	 * @var string The organizational group this field set belongs to.
	 */
	protected static $group = '';

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param Container $container The container to use.
	 */
	public function __construct( $container = null ) {
		$this->container = $container ?: Container::init();
	}

	/**
	 * {@inheritdoc}
	 */
	public function after_update( array $results ) {
		// No-op by default.
		return $results;
	}

	/**
	 * Gets the base table name.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public static function base_table_name() {
		return static::$base_table_name;
	}

	/**
	 * {@inheritdoc}
	 */
	public function drop() {
		if ( ! $this->exists() ) {

			return false;
		}

		global $wpdb;
		$this_table   = $this->table_schema()::table_name( true );
		$drop_columns = 'DROP COLUMN `' . implode( '`, DROP COLUMN `', $this->fields() ) . '`';

		return $wpdb->query( sprintf( "ALTER TABLE %s %s", $this_table, $drop_columns ) );
	}

	/**
	 * Returns whether a fields' schema definition exists in the table or not.
	 *
	 * @since 1.0.0
	 *
	 * @return bool Whether a set of fields exists in the database or not.
	 */
	public function exists() {
		global $wpdb;
		$table_name = $this->table_schema()::table_name( true );
		$q          = 'select `column_name` from information_schema.columns
					where table_schema = database()
					and `table_name` = %s';
		$rows       = $wpdb->get_results( $wpdb->prepare( $q, $table_name ) );
		$fields     = $this->fields();
		$rows       = array_map( function ( $row ) {
			return $row->column_name;
		}, $rows );

		foreach ( $fields as $field ) {
			if ( ! in_array( $field, $rows, true ) ) {

				return false;
			}
		}

		return true;
	}

	/**
	 * Fields being added to the table.
	 *
	 * @since 1.0.0
	 *
	 * @return array<string>
	 */
	public function fields() {
		return (array) $this->fields;
	}

	/**
	 * The base table name of the schema.
	 */
	public static function get_schema_slug() {
		return static::$schema_slug;
	}

	/**
	 * {@inheritdoc}
	 */
	abstract public function get_sql();

	/**
	 * Gets the field schema's version.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_version(): string {
		return static::get_schema_slug() . ':' . static::SCHEMA_VERSION;
	}

	/**
	 * {@inheritdoc}
	 */
	public static function group_name() {
		return static::$group;
	}

	/**
	 * {@inheritdoc}
	 */
	public function table_schema() {
		return $this->container->make( Tables\Collection::class )->offsetGet( $this->base_table_name() );
	}
}
