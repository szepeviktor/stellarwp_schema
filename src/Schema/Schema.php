<?php

namespace StellarWP\Schema;

use StellarWP\Schema\Config;

class Schema {
	const VERSION = '1.1.0';

	/**
	 * Container object.
	 *
	 * @var object
	 */
	private $container;

	/**
	 * Gets the Builder.
	 *
	 * @since 1.0.0
	 *
	 * @return Builder
	 */
	public static function builder() {
		static::init();

		return Config::get_container()->make( Builder::class );
	}

	/**
	 * Gets the field collection.
	 *
	 * @since 1.0.0
	 *
	 * @return Fields\Collection
	 */
	public static function fields() {
		static::init();

		return Config::get_container()->make( Fields\Collection::class );
	}

	/**
	 * Initializes the service provider.
	 *
	 * @since 1.0.0
	 */
	public static function init(): void {

		$container = Config::get_container();
		$db_class  = Config::get_db();

		if ( empty( $container ) || empty( $db_class ) ) {
			throw new \RuntimeException( 'You must call StellarWP\Schema\Config::set_container() and StellarWP\Schema\Config::set_db() before calling StellarWP\Schema\Schema::init().' );
		}

		if ( $container->make( 'stellarwp_schema_registered' ) ) {
			return;
		}

		$db_class::init();
		$container->singleton( static::class, static::class );
		$container->make( static::class )->register();
		$container->bind( 'stellarwp_schema_registered', static function() { return true; } );
	}

	/**
	 * Constructor.
	 *
	 * @param object $container
	 */
	public function __construct( $container = null ) {
		$this->container = $container ?: Config::get_container();
	}

	/**
	 * Binds and sets up implementations.
	 *
	 * @since 1.0.0
	 */
	public function register() {
		$this->container->singleton( static::class, $this );
		$this->container->singleton( Builder::class );
		$this->container->singleton( Fields\Collection::class );
		$this->container->singleton( Tables\Collection::class );

		/**
		 * These providers should be the ones that extend the bulk of features for CT1,
		 * with only the bare minimum of providers registered above, to determine important state information.
		 */
		$this->container->singleton( Full_Activation_Provider::class, Full_Activation_Provider::class );
		$this->container->make( Full_Activation_Provider::class )->register();

		// Set a flag in the container to indicate there was a full activation of the CT1 component.
		//$this->container->setVar( 'stellarwp_schema_fully_activated', true );
		$this->container->bind( 'stellarwp_schema_fully_activated', static function() { return true; } );

		$this->register_hooks();
	}

	/**
	 * Registers all hooks.
	 *
	 * @since 1.0.0
	 */
	private function register_hooks() : void {
		if ( did_action( 'plugins_loaded' ) ) {
			$this->container->make( Builder::class )->up();
		} else {
			/**
			 * Filters the priority of the plugins_loaded action for running Builder::up.
			 *
			 * @param int $priority The priority of the action.
			 */
			$priority = apply_filters( 'stellarwp_schema_up_plugins_loaded_priority', 1000 );

			add_action( 'plugins_loaded', $this->container->callback( Builder::class, 'up' ), $priority, 0 );
		}
	}

	/**
	 * Gets the table collection.
	 *
	 * @since 1.0.0
	 *
	 * @return Tables\Collection
	 */
	public static function tables() {
		static::init();

		return Config::get_container()->make( Tables\Collection::class );
	}
}
