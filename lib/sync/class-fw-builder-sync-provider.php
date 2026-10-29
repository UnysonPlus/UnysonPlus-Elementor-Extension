<?php if ( ! defined( 'FW' ) ) { die( 'Forbidden' ); }

/**
 * One page builder's side of the sync.
 *
 * A provider translates between the neutral design (FW_Builder_Sync_Design) and its builder's global
 * settings, and tells the extension when the builder side was saved. Register one with:
 *
 *   add_filter( 'fw_builder_sync_providers', function ( $p ) { $p['my-builder'] = new My_Provider(); return $p; } );
 */
abstract class FW_Builder_Sync_Provider {

	/** Stable id (also the identity-map key). */
	abstract public function slug();

	/** Human name for the settings screen. */
	abstract public function label();

	/** Is the builder installed and active on this site? */
	abstract public function is_available();

	/**
	 * Write the design into the builder's global settings.
	 *
	 * @param array $design FW_Builder_Sync_Design::read() shape
	 * @return string[] the builder setting keys that changed
	 */
	abstract public function push( array $design );

	/**
	 * Read the builder's global settings as a (partial) design.
	 *
	 * @return array { design: array, renames: array( old slug => new name ) }
	 */
	abstract public function pull();

	/**
	 * Hook the builder's own "settings saved" event and call $on_saved() from it. The extension guards
	 * re-entry, so the provider does not need to.
	 *
	 * @param callable $on_saved
	 */
	abstract public function listen( callable $on_saved );

	/** Identity map persisted per provider: neutral key => builder id. */
	protected function ids() {
		$v = get_option( 'fw_builder_sync_ids_' . $this->slug(), array() );
		return is_array( $v ) ? $v : array();
	}

	protected function save_ids( array $ids ) {
		update_option( 'fw_builder_sync_ids_' . $this->slug(), $ids, false );
	}
}
