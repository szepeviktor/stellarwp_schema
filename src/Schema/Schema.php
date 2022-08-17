<?php

namespace StellarWP\Schema;

use lucatume\DI52\ServiceProvider as Service_Provider;

class Schema extends Service_Provider {
	/**
	 * Gets the Builder.
	 *
	 * @since 1.0.0
	 *
	 * @return Builder
	 */
	public static function builder() {
		static::init();

		return Container::init()->make( Builder::class );
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

		return Container::init()->make( Fields\Collection::class );
	}

	/**
	 * Initializes the service provider.
	 *
	 * @since 1.0.0
	 */
	public static function init(): void {
		$container = Container::init();

		if ( $container->getVar( 'stellarwp_schema_registered', false ) ) {
			return;
		}

		$container->register( static::class );
		$container->setVar( 'stellarwp_schema_registered', true );
	}

	/**
	 * Binds and sets up implementations.
	 *
	 * @since 1.0.0
	 */
	public function register() {
		$this->container->singleton( static::class, $this );
		$this->container->singleton( Builder::class, Builder::class );
		$this->container->singleton( Fields\Collection::class, Fields\Collection::class );
		$this->container->singleton( Tables\Collection::class, Tables\Collection::class );

		/**
		 * These providers should be the ones that extend the bulk of features for CT1,
		 * with only the bare minimum of providers registered above, to determine important state information.
		 */
		$this->container->register( Full_Activation_Provider::class );
		// Set a flag in the container to indicate there was a full activation of the CT1 component.
		$this->container->setVar( 'stellarwp_schema_fully_activated', true );

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

		return Container::init()->make( Tables\Collection::class );
	}
}
