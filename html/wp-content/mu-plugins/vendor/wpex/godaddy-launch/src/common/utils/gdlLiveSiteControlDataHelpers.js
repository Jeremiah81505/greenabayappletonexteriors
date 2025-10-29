/* global gdlLiveSiteControlData */

/**
 * Helper functions for safely handling gdlLiveSiteControlData global variable.
 *
 * The gdlLiveSiteControlData variable is localized by PHP only when:
 * - User has edit_theme_options capability
 * - Site hasn't been published yet
 *
 * After publishing, the variable is no longer localized but JavaScript code
 * may still try to access it, causing ReferenceError. These helpers provide
 * safe access patterns.
 *
 * @package
 */

/**
 * Safely checks if gdlLiveSiteControlData is available.
 *
 * @return {boolean} True if gdlLiveSiteControlData is defined, false otherwise
 */
export function hasLiveSiteControlData() {
	return typeof gdlLiveSiteControlData !== 'undefined';
}

/**
 * Safely gets a property from gdlLiveSiteControlData.
 *
 * @param {string} property     - The property name to access
 * @param {*}      defaultValue - Default value if property doesn't exist
 * @return {*} The property value or default value
 */
export function getLiveSiteControlProperty( property, defaultValue = undefined ) {
	if ( ! hasLiveSiteControlData() ) {
		return defaultValue;
	}

	return gdlLiveSiteControlData[ property ] ?? defaultValue;
}

/**
 * Safely checks if the current site is a migrated site.
 *
 * @return {boolean} True if gdlLiveSiteControlData exists and isMigratedSite is true, false otherwise
 */
export function isMigratedSite() {
	return !! getLiveSiteControlProperty( 'isMigratedSite', false );
}
